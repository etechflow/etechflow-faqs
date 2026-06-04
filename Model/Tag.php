<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model;

use Magento\Framework\Model\AbstractModel;

class Tag extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\Etechflow\Faq\Model\ResourceModel\Tag::class);
    }

    public function getTagId(): ?int      { return $this->getId() ? (int) $this->getId() : null; }
    public function getSlug(): string     { return (string) $this->getData('slug'); }
    public function getLabel(): string    { return (string) $this->getData('label'); }
    public function getIsActive(): int    { return (int) $this->getData('is_active'); }
}
