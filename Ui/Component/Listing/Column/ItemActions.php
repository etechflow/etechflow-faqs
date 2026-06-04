<?php
declare(strict_types=1);

namespace Etechflow\Faq\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ItemActions extends Column
{
    private UrlInterface $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) return $dataSource;
        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['item_id'])) continue;
            $id = (int)$item['item_id'];
            $item[$name] = [
                'edit' => [
                    'href'  => $this->urlBuilder->getUrl('etechflow_faq/item/edit', ['item_id' => $id]),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href'  => $this->urlBuilder->getUrl('etechflow_faq/item/delete', ['item_id' => $id]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete this Q&A?'),
                        'message' => __('Are you sure you want to delete this question and answer?'),
                    ],
                ],
            ];
        }
        return $dataSource;
    }
}
