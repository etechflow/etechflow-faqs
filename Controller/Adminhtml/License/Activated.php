<?php

declare(strict_types=1);

namespace Etechflow\Faq\Controller\Adminhtml\License;

use Etechflow\Faq\Model\LicenseValidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Type\Config as ConfigCacheType;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Landing page after Stripe payment. The eTechFlow portal verifies the session
 * with ITS OWN Stripe keys and returns the SP-XXXX license key, which is saved.
 * No payment keys live in Magento.
 */
class Activated extends Action
{
    public const ADMIN_RESOURCE = 'Etechflow_Faq::faq';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly CurlFactory $curlFactory,
        private readonly WriterInterface $configWriter,
        private readonly CacheInterface $cache,
        private readonly LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $sessionId = trim((string) $this->getRequest()->getParam('session_id', ''));
        $plan      = trim((string) $this->getRequest()->getParam('plan', ''));
        $domain    = trim((string) $this->getRequest()->getParam('domain', '')) ?: $this->licenseValidator->getCurrentHost();
        $name      = trim((string) $this->getRequest()->getParam('name', ''));
        $email     = trim((string) $this->getRequest()->getParam('email', ''));

        if ($sessionId === '') {
            $this->messageManager->addErrorMessage(__('Invalid payment callback.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('etechflow_faq/license/gate');
        }

        $portal  = rtrim(str_replace('/license/validate', '', $this->licenseValidator->getPortalUrl()), '/');
        $payload = json_encode(array_filter([
            'session_id' => $sessionId,
            'domain'     => $domain,
            'name'       => $name,
            'email'      => $email,
            'plan'       => $plan,
        ]));

        $licenseKey = '';
        $planName   = '';
        $error      = '';

        try {
            $curl = $this->curlFactory->create();
            $curl->setTimeout(30);
            $curl->addHeader('Content-Type', 'application/json');
            $curl->addHeader('Accept', 'application/json');
            $curl->post($portal . '/license/activate', $payload);
            $status = (int) $curl->getStatus();
            $body   = (string) $curl->getBody();
            $data   = json_decode($body, true);

            if ($status === 200 && !empty($data['license_key'])) {
                $licenseKey = (string) $data['license_key'];
                $planName   = (string) ($data['plan'] ?? $plan);
            } else {
                $error = is_array($data) && !empty($data['error']) ? $data['error'] : ('Portal returned status ' . $status . ': ' . $body);
            }
        } catch (\Throwable $e) {
            $error = 'Could not reach portal: ' . $e->getMessage();
        }

        if ($licenseKey) {
            $this->configWriter->save(LicenseValidator::XML_PATH_LICENSE_KEY, $licenseKey);
            $this->cache->clean([ConfigCacheType::CACHE_TAG]);
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('Subscription Activated'));

        $block = $page->getLayout()->getBlock('etechflow.faq.license.activated');
        if ($block) {
            $block->setData('license_key', $licenseKey)
                  ->setData('plan', $planName)
                  ->setData('error', $error)
                  ->setData('settings_url', $this->getUrl('adminhtml/system_config/edit/section/etechflow_faq'))
                  ->setData('management_url', $this->getUrl('etechflow_faq/item/index'));
        }

        return $page;
    }
}