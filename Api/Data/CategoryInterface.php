<?php
declare(strict_types=1);
namespace Etechflow\Faq\Api\Data;

interface CategoryInterface
{
    public const CATEGORY_ID = 'category_id';
    public const IDENTIFIER  = 'identifier';
    public const LABEL       = 'label';
    public const ICON_KEY    = 'icon_key';
    public const ICON_IMAGE  = 'icon_image';
    public const SORT_ORDER  = 'sort_order';
    public const IS_ACTIVE   = 'is_active';

    /**
     * @return int|null
     */
    public function getCategoryId(): ?int;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string
     */
    public function getIconKey(): string;

    /**
     * @return string
     */
    public function getIconImage(): string;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @return int
     */
    public function getIsActive(): int;

    /**
     * @return \Etechflow\Faq\Api\Data\ItemInterface[]
     */
    public function getItems(): array;

    /**
     * @param \Etechflow\Faq\Api\Data\ItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
