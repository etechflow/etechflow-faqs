<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Category extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('etechflow_faq_category', 'category_id');
    }
}
