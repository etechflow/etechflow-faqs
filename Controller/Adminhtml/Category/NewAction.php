<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;

class NewAction extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::category';

    public function execute(): Redirect
    {
        return $this->resultRedirectFactory->create()->setPath('*/*/edit');
    }
}
