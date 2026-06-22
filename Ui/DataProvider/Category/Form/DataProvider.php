<?php
declare(strict_types=1);

namespace Etechflow\Faq\Ui\DataProvider\Category\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Etechflow\Faq\Model\ResourceModel\Category\CollectionFactory;

/**
 * Canonical Magento_Cms-style form data provider.
 * Constructor signature matches Magento's standard exactly (5 positional args)
 * so the compiled DI auto-resolves $collectionFactory and the UI component
 * passes name, primaryFieldName, requestFieldName, meta, data in order.
 */
class DataProvider extends AbstractDataProvider
{
    /** @var array|null */
    protected $loadedData;

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

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        $this->loadedData = [];
        /** @var \Etechflow\Faq\Model\Category $item */
        foreach ($items as $item) {
            $row = $item->getData();
            // Normalize nullable string/int columns so UI form inputs always
            // receive a non-null value (form JS chokes silently on null).
            $row['identifier'] = (string) ($row['identifier'] ?? '');
            $row['label']      = (string) ($row['label']      ?? '');
            $row['icon_key']   = (string) ($row['icon_key']   ?? 'orders');
            $row['icon_image'] = (string) ($row['icon_image'] ?? '');
            $row['sort_order'] = (int)    ($row['sort_order'] ?? 0);
            $row['is_active']  = (int)    ($row['is_active']  ?? 1);
            $this->loadedData[$item->getId()] = $row;
        }
        return $this->loadedData;
    }
}
