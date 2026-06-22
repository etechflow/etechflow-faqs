<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Etechflow\Faq\Model\ItemFactory;
use Etechflow\Faq\Model\ResourceModel\Item as ItemResource;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::item';

    private ItemFactory $itemFactory;
    private ItemResource $itemResource;

    public function __construct(
        Context $context,
        ItemFactory $itemFactory,
        ItemResource $itemResource
    ) {
        parent::__construct($context);
        $this->itemFactory  = $itemFactory;
        $this->itemResource = $itemResource;
    }

    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $id   = (int) ($this->getRequest()->getParam('item_id') ?: $this->getRequest()->getParam('id'));
        $back = $this->resultRedirectFactory->create()->setPath('*/*/');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid FAQ ID.'));
            return $back;
        }

        $model = $this->itemFactory->create();
        $this->itemResource->load($model, $id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('FAQ not found.'));
            return $back;
        }

        try {
            $this->itemResource->delete($model);
            $this->messageManager->addSuccessMessage(__('FAQ deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $back;
    }
}
