<?php
declare(strict_types=1);

namespace Etechflow\Faq\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class PendingActions extends Column
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
            if (!isset($item['pending_id'])) continue;
            $id = (int) $item['pending_id'];
            $item[$name] = [
                'approve' => [
                    'href'  => $this->urlBuilder->getUrl('etechflow_faq/pending/approve', ['id' => $id]),
                    'label' => __('Approve'),
                    'confirm' => [
                        'title'   => __('Approve this question?'),
                        'message' => __('A new FAQ item will be created (inactive) so you can write the answer.'),
                    ],
                ],
                'reject' => [
                    'href'  => $this->urlBuilder->getUrl('etechflow_faq/pending/reject', ['id' => $id]),
                    'label' => __('Reject'),
                    'confirm' => [
                        'title'   => __('Reject this question?'),
                        'message' => __('The submission will be marked rejected and hidden.'),
                    ],
                ],
            ];
        }
        return $dataSource;
    }
}
