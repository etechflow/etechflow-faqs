<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Etechflow\Faq\Model\CategoryFactory;
use Etechflow\Faq\Model\ResourceModel\Category as CategoryResource;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::category';

    private PageFactory $pageFactory;
    private CategoryFactory $categoryFactory;
    private CategoryResource $categoryResource;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource
    ) {
        parent::__construct($context);
        $this->pageFactory      = $pageFactory;
        $this->categoryFactory  = $categoryFactory;
        $this->categoryResource = $categoryResource;
    }

    public function execute(): Page|\Magento\Framework\Controller\Result\Redirect
    {
        $id    = (int) ($this->getRequest()->getParam('category_id') ?: $this->getRequest()->getParam('id'));
        $model = $this->categoryFactory->create();

        if ($id) {
            $this->categoryResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('Category not found.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $page = $this->pageFactory->create();
        $page->setActiveMenu('Etechflow_Faq::category');
        $page->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Category: %1', $model->getLabel()) : __('New Category')
        );
        return $page;
    }
}
