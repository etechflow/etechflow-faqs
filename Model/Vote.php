<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model;

use Magento\Framework\Model\AbstractModel;

class Vote extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\Etechflow\Faq\Model\ResourceModel\Vote::class);
    }
}
