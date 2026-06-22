<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Pending extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('etechflow_faq_pending', 'pending_id');
    }
}
