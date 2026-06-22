<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IconKey implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'orders',    'label' => __('Orders / Cart')],
            ['value' => 'returns',   'label' => __('Returns / Refunds')],
            ['value' => 'delivery',  'label' => __('Delivery / Shipping')],
            ['value' => 'payment',   'label' => __('Payment / Card')],
            ['value' => 'collect',   'label' => __('Click & Collect')],
            ['value' => 'account',   'label' => __('Your Account')],
            ['value' => 'support',   'label' => __('Customer Services')],
            ['value' => 'technical', 'label' => __('Products / Technical')],
            ['value' => 'locksmith', 'label' => __('Auto Locksmith / Key')],
        ];
    }
}
