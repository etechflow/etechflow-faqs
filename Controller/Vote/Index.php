<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\Vote;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Etechflow\Faq\Api\FaqRepositoryInterface;

/**
 * AJAX endpoint that records a helpful (1) / unhelpful (0) vote on an item.
 * One vote per (item_id, hashed-IP). Returns JSON {ok, recorded, message}.
 *
 * CSRF-exempt: votes are rate-limited at the model layer (unique index on
 * item_id + ip_hash), so a stolen form key gains the attacker nothing
 * beyond what they could do anonymously already.
 */
class Index implements HttpPostActionInterface, CsrfAwareActionInterface
{
    private RequestInterface $request;
    private JsonFactory $jsonFactory;
    private FaqRepositoryInterface $faqRepository;

    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        FaqRepositoryInterface $faqRepository
    ) {
        $this->request       = $request;
        $this->jsonFactory   = $jsonFactory;
        $this->faqRepository = $faqRepository;
    }

    public function execute(): ResultInterface
    {
        $json = $this->jsonFactory->create();
        $itemId = (int) $this->request->getParam('item_id');
        $vote   = $this->request->getParam('vote');
        if ($itemId <= 0 || !in_array($vote, ['yes', 'no'], true)) {
            return $json->setData(['ok' => false, 'message' => 'Bad request']);
        }
        $ip = (string) ($this->request instanceof HttpRequest ? $this->request->getClientIp(true) : '');
        $ipHash = hash('sha256', $ip . '|kfaq');

        $recorded = $this->faqRepository->recordVote($itemId, $vote === 'yes', $ipHash);
        return $json->setData([
            'ok'       => true,
            'recorded' => $recorded,
            'message'  => $recorded ? 'Thanks for your feedback!' : 'You have already voted.',
        ]);
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
