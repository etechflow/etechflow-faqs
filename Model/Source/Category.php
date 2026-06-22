<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Etechflow\Faq\Model\ResourceModel\Category\Collection;
use Etechflow\Faq\Model\ResourceModel\Category\CollectionFactory;

class Category implements OptionSourceInterface
{
    private CollectionFactory $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setOrder('sort_order', 'ASC');

        $options = [['value' => '', 'label' => __('-- Select Category --')]];
        foreach ($collection as $cat) {
            $options[] = ['value' => $cat->getId(), 'label' => $cat->getLabel()];
        }
        return $options;
    }
}
