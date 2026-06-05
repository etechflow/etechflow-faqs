<?php

declare(strict_types=1);

namespace Etechflow\Faq\Plugin\Controller;

use Etechflow\Faq\Model\LicenseValidator;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Storefront license gate. Registered in etc/frontend/di.xml against
 * every FAQ frontend controller (Index/Index, View/Index, Vote/Index,
 * Submit/Index, Search/Index).
 *
 * FAQ controllers implement HttpGetActionInterface / HttpPostActionInterface
 * directly (modern Magento style — no AbstractAction base class), so they
 * never call dispatch(). The plugin instead intercepts execute() — the
 * single method these controllers implement.
 *
 * When the licence is invalid, forwards to Magento's noroute handler so the
 * customer sees the theme's standard 404 page rather than a half-rendered
 * FAQ page or an empty result.
 */
class StorefrontGatePlugin
{
    public function __construct(
        private readonly LicenseValidator $licenseValidator,
        private readonly ResultFactory $resultFactory
    ) {
    }

    /**
     * @param ActionInterface $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundExecute(ActionInterface $subject, callable $proceed)
    {
        if (!$this->licenseValidator->isValid()) {
            $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $forward->forward('noroute');
            return $forward;
        }
        return $proceed();
    }
}
