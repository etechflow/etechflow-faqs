<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\Submit;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\ScopeInterface;
use Etechflow\Faq\Model\Pending;
use Etechflow\Faq\Model\PendingFactory;
use Etechflow\Faq\Model\ResourceModel\Pending as PendingResource;

/**
 * AJAX endpoint that accepts a visitor-submitted question.
 * Returns JSON {ok, message}. Spam protection:
 *   1. Honeypot — hidden field "website" must be empty.
 *   2. Min/max length checks.
 *   3. Per-IP rate limit (max 3 submissions/hour) at the model layer.
 *   4. Magento reCAPTCHA — wire up via di.xml (see INSTALL.md → Visitor submissions).
 *
 * CSRF-exempt: this is a public AJAX endpoint that creates a row with
 * status=pending. Bad actors gain nothing beyond what they could already do.
 */
class Index implements HttpPostActionInterface, CsrfAwareActionInterface
{
    private const RATE_LIMIT_PER_HOUR = 3;

    private RequestInterface $request;
    private JsonFactory $jsonFactory;
    private PendingFactory $pendingFactory;
    private PendingResource $pendingResource;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        PendingFactory $pendingFactory,
        PendingResource $pendingResource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request         = $request;
        $this->jsonFactory     = $jsonFactory;
        $this->pendingFactory  = $pendingFactory;
        $this->pendingResource = $pendingResource;
        $this->scopeConfig     = $scopeConfig;
    }

    public function execute(): ResultInterface
    {
        $json = $this->jsonFactory->create();

        $enabled = (string) $this->scopeConfig->getValue(
            'etechflow_faq/features/enable_visitor_submissions',
            ScopeInterface::SCOPE_STORE
        );
        if ($enabled !== '1') {
            return $json->setData(['ok' => false, 'message' => 'Visitor submissions are disabled.']);
        }

        // Honeypot — bots fill this; humans never see it
        if (trim((string) $this->request->getParam('website', '')) !== '') {
            return $json->setData(['ok' => true, 'message' => 'Thank you!']);
        }

        $question = trim((string) $this->request->getParam('question', ''));
        if (strlen($question) < 10) {
            return $json->setData(['ok' => false, 'message' => 'Please write a question of at least 10 characters.']);
        }
        if (strlen($question) > 1000) {
            return $json->setData(['ok' => false, 'message' => 'Question is too long (max 1000 characters).']);
        }

        $name  = trim((string) $this->request->getParam('name', ''));
        $email = trim((string) $this->request->getParam('email', ''));
        $catId = (int) $this->request->getParam('category_id', 0);
        $ip    = (string) ($this->request instanceof HttpRequest ? $this->request->getClientIp(true) : '');
        $ipHash = hash('sha256', $ip . '|kfaq');

        // Per-IP rate limit
        $conn = $this->pendingResource->getConnection();
        $table = $this->pendingResource->getMainTable();
        $recent = (int) $conn->fetchOne(
            $conn->select()
                ->from($table, ['COUNT(*)'])
                ->where('visitor_ip = ?', $ipHash)
                ->where('created_at >= ?', date('Y-m-d H:i:s', time() - 3600))
        );
        if ($recent >= self::RATE_LIMIT_PER_HOUR) {
            return $json->setData(['ok' => false, 'message' => 'Too many submissions. Please try again later.']);
        }

        try {
            $pending = $this->pendingFactory->create();
            $pending->setData([
                'category_id'   => $catId > 0 ? $catId : null,
                'question'      => $question,
                'visitor_name'  => $name !== '' ? mb_substr($name, 0, 120) : null,
                'visitor_email' => $email !== '' ? mb_substr($email, 0, 160) : null,
                'visitor_ip'    => $ipHash,
                'status'        => Pending::STATUS_PENDING,
            ]);
            $this->pendingResource->save($pending);
            return $json->setData(['ok' => true, 'message' => 'Thanks! Your question has been submitted for review.']);
        } catch (\Exception $e) {
            return $json->setData(['ok' => false, 'message' => 'Could not save your question. Please try again.']);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
