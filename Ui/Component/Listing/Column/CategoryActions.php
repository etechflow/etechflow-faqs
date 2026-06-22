<?php
declare(strict_types=1);

namespace Etechflow\Faq\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CategoryActions extends Column
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
            if (!isset($item['category_id'])) continue;
            $id = (int)$item['category_id'];
            $item[$name] = [
                'edit' => [
                    'href'  => $this->urlBuilder->getUrl('etechflow_faq/category/edit', ['category_id' => $id]),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href'  => $this->urlBuilder->getUrl('etechflow_faq/category/delete', ['category_id' => $id]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete this category?'),
                        'message' => __('All Q&A items in this category will also be deleted. Continue?'),
                    ],
                ],
            ];
        }
        return $dataSource;
    }
}
