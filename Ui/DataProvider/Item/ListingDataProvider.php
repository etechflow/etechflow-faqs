<?php
declare(strict_types=1);
namespace Etechflow\Faq\Ui\DataProvider\Item;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Etechflow\Faq\Model\ResourceModel\Item\CollectionFactory;

class ListingDataProvider extends AbstractDataProvider
{
    private ?array $loadedData = null;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collection->getSelect()->joinLeft(
            ['c' => 'etechflow_faq_category'],
            'main_table.category_id = c.category_id',
            ['category_label' => 'c.label']
        );
    }

    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->toArray();
        $this->loadedData = [
            'totalRecords' => $this->collection->getSize(),
            'items'        => array_values($items['items']),
        ];
        return $this->loadedData;
    }
}
