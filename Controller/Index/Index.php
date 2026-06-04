<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Etechflow\Faq\Block\Config;

/**
 * Public listing page at /faqs.
 *
 * Note: a CMS page with URL key "faqs" will take priority over this controller
 * via the URL-rewrite layer — that's intentional and lets existing stores
 * keep their CMS-based listing. To use this controller, remove the CMS page
 * (see INSTALL.md → "Migrating from a CMS-page-based FAQ").
 */
class Index implements HttpGetActionInterface
{
    private PageFactory $pageFactory;
    private Config $configHelper;

    public function __construct(
        PageFactory $pageFactory,
        Config $configHelper
    ) {
        $this->pageFactory  = $pageFactory;
        $this->configHelper = $configHelper;
    }

    public function execute(): ResultInterface
    {
        $page = $this->pageFactory->create();

        $title = $this->configHelper->get('hero/title');
        $page->getConfig()->getTitle()->set($title !== '' ? $title : __('Help Centre & FAQs'));

        $description = $this->configHelper->get('hero/subtitle');
        if ($description !== '') {
            $page->getConfig()->setDescription($description);
        }

        return $page;
    }
}
