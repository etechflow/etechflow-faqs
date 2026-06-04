<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Etechflow\Faq\Model\Item;
use Etechflow\Faq\Model\ResourceModel\Item as ItemResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'item_id';

    protected function _construct(): void
    {
        $this->_init(Item::class, ItemResource::class);
    }
}
