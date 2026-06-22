<?php
declare(strict_types=1);
namespace Etechflow\Faq\Ui\DataProvider\Category;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Etechflow\Faq\Model\ResourceModel\Category\CollectionFactory;

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
    }

    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->collection->addExpressionFieldToSelect(
            'faq_count',
            '(SELECT COUNT(*) FROM etechflow_faq_item fi WHERE fi.category_id = main_table.category_id)',
            []
        );

        $items = $this->collection->toArray();
        $this->loadedData = [
            'totalRecords' => $this->collection->getSize(),
            'items'        => array_values($items['items']),
        ];
        return $this->loadedData;
    }
}
