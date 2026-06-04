<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Etechflow\Faq\Model\Category;
use Etechflow\Faq\Model\ResourceModel\Category as CategoryResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'category_id';

    protected function _construct(): void
    {
        $this->_init(Category::class, CategoryResource::class);
    }
}
