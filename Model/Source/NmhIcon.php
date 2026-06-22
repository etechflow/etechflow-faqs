<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Icon choices for the "Need More Help" cards on the FAQ listing
 * + the contact card on the FAQ detail page.
 *
 * Keys must match an entry in the $nmhIcons array in listing.phtml /
 * view.phtml. To add a new icon, add a `value => label` entry here AND
 * the corresponding inline SVG in the template's icon library.
 */
class NmhIcon implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'phone',     'label' => __('Phone')],
            ['value' => 'email',     'label' => __('Email / Envelope')],
            ['value' => 'chat',      'label' => __('Chat bubble')],
            ['value' => 'map-pin',   'label' => __('Map pin / Location')],
            ['value' => 'whatsapp',  'label' => __('WhatsApp')],
            ['value' => 'clock',     'label' => __('Clock / Hours')],
            ['value' => 'help',      'label' => __('Help / Question')],
            ['value' => 'support',   'label' => __('Headset / Support')],
            ['value' => 'building',  'label' => __('Building / Store')],
            ['value' => 'truck',     'label' => __('Truck / Delivery')],
            ['value' => 'shield',    'label' => __('Shield / Security')],
            ['value' => 'star',      'label' => __('Star')],
        ];
    }
}
