<?php
declare(strict_types=1);

namespace Etechflow\Faq\Ui\DataProvider\Item\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Etechflow\Faq\Model\ResourceModel\Item\CollectionFactory;

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
        /** @var \Etechflow\Faq\Model\Item $item */
        foreach ($items as $item) {
            $row = $item->getData();
            $row['question']         = (string) ($row['question']         ?? '');
            $row['url_key']          = (string) ($row['url_key']          ?? '');
            $row['subtitle']         = (string) ($row['subtitle']         ?? '');
            $row['answer']           = (string) ($row['answer']           ?? '');
            $row['meta_title']       = (string) ($row['meta_title']       ?? '');
            $row['meta_description'] = (string) ($row['meta_description'] ?? '');
            $row['category_id']      = (int)    ($row['category_id']      ?? 0);
            $row['sort_order']       = (int)    ($row['sort_order']       ?? 0);
            $row['is_active']        = (int)    ($row['is_active']        ?? 1);
            $row['is_featured']      = (int)    ($row['is_featured']      ?? 0);
            $this->loadedData[$item->getId()] = $row;
        }
        return $this->loadedData;
    }
}
