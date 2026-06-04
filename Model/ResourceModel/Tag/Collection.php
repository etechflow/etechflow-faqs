<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\ResourceModel\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'tag_id';

    protected function _construct(): void
    {
        $this->_init(
            \Etechflow\Faq\Model\Tag::class,
            \Etechflow\Faq\Model\ResourceModel\Tag::class
        );
    }
}
