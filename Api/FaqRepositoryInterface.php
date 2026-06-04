<?php
declare(strict_types=1);
namespace Etechflow\Faq\Api;

interface FaqRepositoryInterface
{
    /**
     * Return all active FAQ categories, each with its nested items.
     *
     * @return \Etechflow\Faq\Api\Data\CategoryInterface[]
     */
    public function getAll(): array;

    /**
     * Return all active FAQ categories (without items).
     *
     * @return \Etechflow\Faq\Api\Data\CategoryInterface[]
     */
    public function getCategories(): array;

    /**
     * Return active FAQ items for a given category.
     *
     * @param int $categoryId
     * @return \Etechflow\Faq\Api\Data\ItemInterface[]
     */
    public function getItemsByCategoryId(int $categoryId): array;

    /**
     * Return an active category by its public identifier.
     *
     * @param string $identifier
     * @return \Etechflow\Faq\Model\Category|null
     */
    public function getCategoryByIdentifier(string $identifier): ?\Etechflow\Faq\Model\Category;

    /**
     * Return an active FAQ item by category identifier and url_key (slug).
     *
     * @param string $categoryIdentifier
     * @param string $urlKey
     * @return \Etechflow\Faq\Model\Item|null
     */
    public function getByUrlKey(string $categoryIdentifier, string $urlKey): ?\Etechflow\Faq\Model\Item;

    /**
     * Return up to $limit other active items in the same category, excluding $excludeItemId.
     *
     * @param int $categoryId
     * @param int $excludeItemId
     * @param int $limit
     * @return \Etechflow\Faq\Api\Data\ItemInterface[]
     */
    public function getRelatedItems(int $categoryId, int $excludeItemId, int $limit = 6): array;

    /**
     * Featured items across all categories, capped at $limit.
     *
     * @param int $limit
     * @return \Etechflow\Faq\Api\Data\ItemInterface[]
     */
    public function getFeaturedItems(int $limit = 6): array;

    /**
     * Server-side search across questions + answers.
     *
     * @param string $query
     * @param int $limit
     * @return \Etechflow\Faq\Api\Data\ItemInterface[]
     */
    public function search(string $query, int $limit = 8): array;

    /**
     * Return all active tags.
     *
     * @return \Etechflow\Faq\Model\Tag[]
     */
    public function getTags(): array;

    /**
     * Return a single active tag by slug, or null.
     *
     * @param string $slug
     * @return \Etechflow\Faq\Model\Tag|null
     */
    public function getTagBySlug(string $slug): ?\Etechflow\Faq\Model\Tag;

    /**
     * Items linked to the given tag (active items only).
     *
     * @param string $slug
     * @return \Etechflow\Faq\Api\Data\ItemInterface[]
     */
    public function getItemsByTagSlug(string $slug): array;

    /**
     * Persist a helpful/unhelpful vote for an item, deduped by hashed IP.
     * Returns true if a new vote was recorded, false if the visitor already voted.
     *
     * @param int $itemId
     * @param bool $helpful
     * @param string $ipHash
     * @return bool
     */
    public function recordVote(int $itemId, bool $helpful, string $ipHash): bool;
}
