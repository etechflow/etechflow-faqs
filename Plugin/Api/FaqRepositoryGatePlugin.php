<?php

declare(strict_types=1);

namespace Etechflow\Faq\Plugin\Api;

use Etechflow\Faq\Api\FaqRepositoryInterface;
use Etechflow\Faq\Model\LicenseValidator;

/**
 * License gate for the FAQ Repository — covers both REST API and any
 * internal callers (storefront blocks, widget, sitemap provider, etc.).
 *
 * When the licence is invalid, every read method returns an empty result
 * (empty array, null, false) so no FAQ content is exposed by the API or
 * rendered in storefront blocks. Recording a vote also short-circuits to
 * "false" (vote not recorded).
 *
 * Registered in etc/di.xml against \Etechflow\Faq\Api\FaqRepositoryInterface
 * so it applies in every area (frontend, adminhtml, webapi_rest, crontab).
 */
class FaqRepositoryGatePlugin
{
    public function __construct(
        private readonly LicenseValidator $licenseValidator
    ) {
    }

    public function aroundGetAll(FaqRepositoryInterface $subject, callable $proceed): array
    {
        return $this->licenseValidator->isValid() ? $proceed() : [];
    }

    public function aroundGetCategories(FaqRepositoryInterface $subject, callable $proceed): array
    {
        return $this->licenseValidator->isValid() ? $proceed() : [];
    }

    public function aroundGetItemsByCategoryId(FaqRepositoryInterface $subject, callable $proceed, int $categoryId): array
    {
        return $this->licenseValidator->isValid() ? $proceed($categoryId) : [];
    }

    public function aroundGetCategoryByIdentifier(FaqRepositoryInterface $subject, callable $proceed, string $identifier)
    {
        return $this->licenseValidator->isValid() ? $proceed($identifier) : null;
    }

    public function aroundGetByUrlKey(FaqRepositoryInterface $subject, callable $proceed, string $categoryIdentifier, string $urlKey)
    {
        return $this->licenseValidator->isValid() ? $proceed($categoryIdentifier, $urlKey) : null;
    }

    public function aroundGetRelatedItems(FaqRepositoryInterface $subject, callable $proceed, int $categoryId, int $excludeItemId, int $limit = 6): array
    {
        return $this->licenseValidator->isValid() ? $proceed($categoryId, $excludeItemId, $limit) : [];
    }

    public function aroundGetFeaturedItems(FaqRepositoryInterface $subject, callable $proceed, int $limit = 6): array
    {
        return $this->licenseValidator->isValid() ? $proceed($limit) : [];
    }

    public function aroundSearch(FaqRepositoryInterface $subject, callable $proceed, string $query, int $limit = 8): array
    {
        return $this->licenseValidator->isValid() ? $proceed($query, $limit) : [];
    }

    public function aroundGetTags(FaqRepositoryInterface $subject, callable $proceed): array
    {
        return $this->licenseValidator->isValid() ? $proceed() : [];
    }

    public function aroundGetTagBySlug(FaqRepositoryInterface $subject, callable $proceed, string $slug)
    {
        return $this->licenseValidator->isValid() ? $proceed($slug) : null;
    }

    public function aroundGetItemsByTagSlug(FaqRepositoryInterface $subject, callable $proceed, string $slug): array
    {
        return $this->licenseValidator->isValid() ? $proceed($slug) : [];
    }

    public function aroundRecordVote(FaqRepositoryInterface $subject, callable $proceed, int $itemId, bool $helpful, string $ipHash): bool
    {
        return $this->licenseValidator->isValid() ? $proceed($itemId, $helpful, $ipHash) : false;
    }
}
