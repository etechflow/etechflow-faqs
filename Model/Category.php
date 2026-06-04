<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model;

use Magento\Framework\Model\AbstractModel;
use Etechflow\Faq\Api\Data\CategoryInterface;

class Category extends AbstractModel implements CategoryInterface
{
    private array $_itemList = [];

    protected function _construct(): void
    {
        $this->_init(\Etechflow\Faq\Model\ResourceModel\Category::class);
    }

    public function getCategoryId(): ?int
    {
        return $this->getId() ? (int) $this->getId() : null;
    }

    public function getIdentifier(): string
    {
        return (string) $this->getData('identifier');
    }

    public function getLabel(): string
    {
        return (string) $this->getData('label');
    }

    public function getIconKey(): string
    {
        return (string) $this->getData('icon_key');
    }

    public function getIconImage(): string
    {
        return (string) $this->getData('icon_image');
    }

    public function getSortOrder(): int
    {
        return (int) $this->getData('sort_order');
    }

    public function getIsActive(): int
    {
        return (int) $this->getData('is_active');
    }

    public function getItems(): array
    {
        return $this->_itemList;
    }

    public function setItems(array $items): self
    {
        $this->_itemList = $items;
        return $this;
    }
}
