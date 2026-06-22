<?php
declare(strict_types=1);

namespace Etechflow\Faq\Ui\DataProvider\Pending;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Etechflow\Faq\Model\ResourceModel\Pending\CollectionFactory;

class ListingDataProvider extends AbstractDataProvider
{
    private ?array $loadedData = null;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
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
