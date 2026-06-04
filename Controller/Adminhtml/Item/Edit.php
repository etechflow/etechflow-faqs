<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Etechflow\Faq\Model\ItemFactory;
use Etechflow\Faq\Model\ResourceModel\Item as ItemResource;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::item';

    private PageFactory $pageFactory;
    private ItemFactory $itemFactory;
    private ItemResource $itemResource;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ItemFactory $itemFactory,
        ItemResource $itemResource
    ) {
        parent::__construct($context);
        $this->pageFactory  = $pageFactory;
        $this->itemFactory  = $itemFactory;
        $this->itemResource = $itemResource;
    }

    public function execute(): Page|\Magento\Framework\Controller\Result\Redirect
    {
        $id    = (int) ($this->getRequest()->getParam('item_id') ?: $this->getRequest()->getParam('id'));
        $model = $this->itemFactory->create();

        if ($id) {
            $this->itemResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('FAQ not found.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $page = $this->pageFactory->create();
        $page->setActiveMenu('Etechflow_Faq::item');
        $page->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit FAQ') : __('New FAQ')
        );
        return $page;
    }
}
