<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model;

use Etechflow\Faq\Api\FaqRepositoryInterface;
use Etechflow\Faq\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Etechflow\Faq\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Etechflow\Faq\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Etechflow\Faq\Model\ResourceModel\Vote as VoteResource;
use Etechflow\Faq\Model\VoteFactory;
use Magento\Framework\App\ResourceConnection;

class FaqRepository implements FaqRepositoryInterface
{
    private CategoryCollectionFactory $catFactory;
    private ItemCollectionFactory $itemFactory;
    private TagCollectionFactory $tagFactory;
    private VoteResource $voteResource;
    private VoteFactory $voteFactory;
    private ResourceConnection $resource;

    public function __construct(
        CategoryCollectionFactory $catFactory,
        ItemCollectionFactory $itemFactory,
        TagCollectionFactory $tagFactory,
        VoteResource $voteResource,
        VoteFactory $voteFactory,
        ResourceConnection $resource
    ) {
        $this->catFactory   = $catFactory;
        $this->itemFactory  = $itemFactory;
        $this->tagFactory   = $tagFactory;
        $this->voteResource = $voteResource;
        $this->voteFactory  = $voteFactory;
        $this->resource     = $resource;
    }

    public function getAll(): array
    {
        $categories = $this->getCategories();
        if (empty($categories)) {
            return [];
        }
        $catIds = array_map(static fn($c) => (int) $c->getId(), $categories);

        $itemCollection = $this->itemFactory->create();
        $itemCollection->addFieldToFilter('category_id', ['in' => $catIds]);
        $itemCollection->addFieldToFilter('is_active', 1);
        // Featured items first, then by sort_order
        $itemCollection->setOrder('is_featured', 'DESC');
        $itemCollection->setOrder('sort_order', 'ASC');

        $grouped = [];
        foreach ($itemCollection as $item) {
            $grouped[(int) $item->getCategoryId()][] = $item;
        }
        foreach ($categories as $category) {
            $category->setItems($grouped[(int) $category->getId()] ?? []);
        }
        return $categories;
    }

    public function getCategories(): array
    {
        $collection = $this->catFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('sort_order', 'ASC');
        return array_values($collection->getItems());
    }

    public function getItemsByCategoryId(int $categoryId): array
    {
        $collection = $this->itemFactory->create();
        $collection->addFieldToFilter('category_id', $categoryId);
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('is_featured', 'DESC');
        $collection->setOrder('sort_order', 'ASC');
        return array_values($collection->getItems());
    }

    public function getCategoryByIdentifier(string $identifier): ?\Etechflow\Faq\Model\Category
    {
        $collection = $this->catFactory->create();
        $collection->addFieldToFilter('identifier', $identifier);
        $collection->addFieldToFilter('is_active', 1);
        $collection->setPageSize(1);
        $items = array_values($collection->getItems());
        return $items[0] ?? null;
    }

    public function getByUrlKey(string $categoryIdentifier, string $urlKey): ?\Etechflow\Faq\Model\Item
    {
        $category = $this->getCategoryByIdentifier($categoryIdentifier);
        if (!$category) {
            return null;
        }
        $collection = $this->itemFactory->create();
        $collection->addFieldToFilter('category_id', (int) $category->getId());
        $collection->addFieldToFilter('url_key', $urlKey);
        $collection->addFieldToFilter('is_active', 1);
        $collection->setPageSize(1);
        $items = array_values($collection->getItems());
        return $items[0] ?? null;
    }

    public function getRelatedItems(int $categoryId, int $excludeItemId, int $limit = 6): array
    {
        $collection = $this->itemFactory->create();
        $collection->addFieldToFilter('category_id', $categoryId);
        $collection->addFieldToFilter('is_active', 1);
        if ($excludeItemId > 0) {
            $collection->addFieldToFilter('item_id', ['neq' => $excludeItemId]);
        }
        $collection->setOrder('is_featured', 'DESC');
        $collection->setOrder('sort_order', 'ASC');
        $collection->setPageSize($limit);
        return array_values($collection->getItems());
    }

    public function getFeaturedItems(int $limit = 6): array
    {
        $collection = $this->itemFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('is_featured', 1);
        $collection->setOrder('sort_order', 'ASC');
        $collection->setPageSize($limit);
        return array_values($collection->getItems());
    }

    public function search(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }
        $like = '%' . $query . '%';
        $collection = $this->itemFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter(
            ['question', 'answer', 'subtitle'],
            [
                ['like' => $like],
                ['like' => $like],
                ['like' => $like],
            ]
        );
        $collection->setOrder('is_featured', 'DESC');
        $collection->setOrder('sort_order', 'ASC');
        $collection->setPageSize($limit);
        return array_values($collection->getItems());
    }

    public function getTags(): array
    {
        $collection = $this->tagFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('label', 'ASC');
        return array_values($collection->getItems());
    }

    public function getTagBySlug(string $slug): ?\Etechflow\Faq\Model\Tag
    {
        $collection = $this->tagFactory->create();
        $collection->addFieldToFilter('slug', $slug);
        $collection->addFieldToFilter('is_active', 1);
        $collection->setPageSize(1);
        $items = array_values($collection->getItems());
        return $items[0] ?? null;
    }

    public function getItemsByTagSlug(string $slug): array
    {
        $tag = $this->getTagBySlug($slug);
        if (!$tag) {
            return [];
        }
        $conn = $this->resource->getConnection();
        $joinT = $this->resource->getTableName('etechflow_faq_item_tag');
        $itemIds = $conn->fetchCol(
            $conn->select()->from($joinT, ['item_id'])->where('tag_id = ?', (int) $tag->getId())
        );
        if (empty($itemIds)) {
            return [];
        }
        $collection = $this->itemFactory->create();
        $collection->addFieldToFilter('item_id', ['in' => $itemIds]);
        $collection->addFieldToFilter('is_active', 1);
        $collection->setOrder('is_featured', 'DESC');
        $collection->setOrder('sort_order', 'ASC');
        return array_values($collection->getItems());
    }

    public function recordVote(int $itemId, bool $helpful, string $ipHash): bool
    {
        if ($itemId <= 0 || $ipHash === '') {
            return false;
        }
        $vote = $this->voteFactory->create();
        $vote->setData([
            'item_id' => $itemId,
            'vote'    => $helpful ? 1 : 0,
            'ip_hash' => $ipHash,
        ]);
        try {
            $this->voteResource->save($vote);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            return false;
        } catch (\Zend_Db_Statement_Exception $e) {
            // unique constraint violation (item_id + ip_hash) — duplicate vote
            return false;
        } catch (\Exception $e) {
            // Fall through — best-effort
            if (stripos($e->getMessage(), 'duplicate') !== false) {
                return false;
            }
            throw $e;
        }
        $this->voteResource->refreshItemCounts($itemId);
        return true;
    }
}
