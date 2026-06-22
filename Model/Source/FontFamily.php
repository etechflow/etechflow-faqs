<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FontFamily implements OptionSourceInterface
{
    public const SYSTEM     = '';
    public const INTER      = 'Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
    public const ROBOTO     = 'Roboto, system-ui, sans-serif';
    public const OPEN_SANS  = '"Open Sans", system-ui, sans-serif';
    public const LATO       = 'Lato, system-ui, sans-serif';
    public const POPPINS    = 'Poppins, system-ui, sans-serif';
    public const MONTSERRAT = 'Montserrat, system-ui, sans-serif';
    public const CUSTOM     = '__custom__';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::SYSTEM,     'label' => __('Inherit from theme (default)')],
            ['value' => self::INTER,      'label' => __('Inter')],
            ['value' => self::ROBOTO,     'label' => __('Roboto')],
            ['value' => self::OPEN_SANS,  'label' => __('Open Sans')],
            ['value' => self::LATO,       'label' => __('Lato')],
            ['value' => self::POPPINS,    'label' => __('Poppins')],
            ['value' => self::MONTSERRAT, 'label' => __('Montserrat')],
            ['value' => self::CUSTOM,     'label' => __('Custom CSS — see "Custom font CSS" field below')],
        ];
    }
}
