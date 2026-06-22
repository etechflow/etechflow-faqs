<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\Adminhtml\Pending;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Etechflow\Faq\Model\PendingFactory;
use Etechflow\Faq\Model\Pending;
use Etechflow\Faq\Model\ResourceModel\Pending as PendingResource;

class Reject extends Action
{
    public const ADMIN_RESOURCE = 'Etechflow_Faq::pending';

    private PendingFactory $pendingFactory;
    private PendingResource $pendingResource;

    public function __construct(
        Context $context,
        PendingFactory $pendingFactory,
        PendingResource $pendingResource
    ) {
        parent::__construct($context);
        $this->pendingFactory  = $pendingFactory;
        $this->pendingResource = $pendingResource;
    }

    public function execute(): Redirect
    {
        $id = (int) $this->getRequest()->getParam('id');
        $back = $this->resultRedirectFactory->create()->setPath('*/*/');
        if ($id <= 0) {
            $this->messageManager->addErrorMessage(__('Invalid ID.'));
            return $back;
        }
        $pending = $this->pendingFactory->create();
        $this->pendingResource->load($pending, $id);
        if (!$pending->getId()) {
            $this->messageManager->addErrorMessage(__('Not found.'));
            return $back;
        }
        $pending->setData('status', Pending::STATUS_REJECTED);
        $this->pendingResource->save($pending);
        $this->messageManager->addSuccessMessage(__('Question rejected.'));
        return $back;
    }
}
