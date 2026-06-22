<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model;

use Magento\Framework\Model\AbstractModel;
use Etechflow\Faq\Api\Data\ItemInterface;

class Item extends AbstractModel implements ItemInterface
{
    protected function _construct(): void
    {
        $this->_init(\Etechflow\Faq\Model\ResourceModel\Item::class);
    }

    public function getItemId(): ?int               { return $this->getId() ? (int) $this->getId() : null; }
    public function getCategoryId(): int            { return (int) $this->getData(self::CATEGORY_ID); }
    public function getUrlKey(): string             { return (string) $this->getData(self::URL_KEY); }
    public function getQuestion(): string           { return (string) $this->getData(self::QUESTION); }
    public function getSubtitle(): string           { return (string) $this->getData(self::SUBTITLE); }
    public function getAnswer(): string             { return (string) $this->getData(self::ANSWER); }
    public function getMetaTitle(): string          { return (string) $this->getData(self::META_TITLE); }
    public function getMetaDescription(): string    { return (string) $this->getData(self::META_DESCRIPTION); }
    public function getSortOrder(): int             { return (int) $this->getData(self::SORT_ORDER); }
    public function getIsActive(): int              { return (int) $this->getData(self::IS_ACTIVE); }
    public function getIsFeatured(): int            { return (int) $this->getData(self::IS_FEATURED); }
    public function getHelpfulCount(): int          { return (int) $this->getData(self::HELPFUL_COUNT); }
    public function getUnhelpfulCount(): int        { return (int) $this->getData(self::UNHELPFUL_COUNT); }
}
