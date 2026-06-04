<?php
declare(strict_types=1);
namespace Etechflow\Faq\Api\Data;

interface ItemInterface
{
    public const ITEM_ID          = 'item_id';
    public const CATEGORY_ID      = 'category_id';
    public const URL_KEY          = 'url_key';
    public const QUESTION         = 'question';
    public const SUBTITLE         = 'subtitle';
    public const ANSWER           = 'answer';
    public const META_TITLE       = 'meta_title';
    public const META_DESCRIPTION = 'meta_description';
    public const SORT_ORDER       = 'sort_order';
    public const IS_ACTIVE        = 'is_active';
    public const IS_FEATURED      = 'is_featured';
    public const HELPFUL_COUNT    = 'helpful_count';
    public const UNHELPFUL_COUNT  = 'unhelpful_count';

    /**
     * @return int|null
     */
    public function getItemId(): ?int;

    /**
     * @return int
     */
    public function getCategoryId(): int;

    /**
     * @return string
     */
    public function getUrlKey(): string;

    /**
     * @return string
     */
    public function getQuestion(): string;

    /**
     * @return string
     */
    public function getSubtitle(): string;

    /**
     * @return string
     */
    public function getAnswer(): string;

    /**
     * @return string
     */
    public function getMetaTitle(): string;

    /**
     * @return string
     */
    public function getMetaDescription(): string;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @return int
     */
    public function getIsActive(): int;

    /**
     * @return int
     */
    public function getIsFeatured(): int;

    /**
     * @return int
     */
    public function getHelpfulCount(): int;

    /**
     * @return int
     */
    public function getUnhelpfulCount(): int;
}
