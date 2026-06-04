<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::category';

    private PageFactory $pageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute(): Page
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Etechflow_Faq::category');
        $page->getConfig()->getTitle()->prepend(__('FAQ Categories'));
        return $page;
    }
}
