<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Etechflow\Faq\Model\CategoryFactory;
use Etechflow\Faq\Model\ResourceModel\Category as CategoryResource;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::category';

    private CategoryFactory $categoryFactory;
    private CategoryResource $categoryResource;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource
    ) {
        parent::__construct($context);
        $this->categoryFactory  = $categoryFactory;
        $this->categoryResource = $categoryResource;
    }

    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $id   = (int) ($this->getRequest()->getParam('category_id') ?: $this->getRequest()->getParam('id'));
        $back = $this->resultRedirectFactory->create()->setPath('*/*/');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid category ID.'));
            return $back;
        }

        $model = $this->categoryFactory->create();
        $this->categoryResource->load($model, $id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('Category not found.'));
            return $back;
        }

        try {
            $this->categoryResource->delete($model);
            $this->messageManager->addSuccessMessage(__('Category deleted (all its FAQs were also removed).'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $back;
    }
}
