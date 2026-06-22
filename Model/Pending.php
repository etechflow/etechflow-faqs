<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model;

use Magento\Framework\Model\AbstractModel;

class Pending extends AbstractModel
{
    public const STATUS_PENDING  = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;

    protected function _construct(): void
    {
        $this->_init(\Etechflow\Faq\Model\ResourceModel\Pending::class);
    }

    public function getPendingId(): ?int    { return $this->getId() ? (int) $this->getId() : null; }
    public function getCategoryId(): ?int   { $v = $this->getData('category_id'); return $v === null ? null : (int) $v; }
    public function getQuestion(): string   { return (string) $this->getData('question'); }
    public function getVisitorName(): string  { return (string) $this->getData('visitor_name'); }
    public function getVisitorEmail(): string { return (string) $this->getData('visitor_email'); }
    public function getStatus(): int        { return (int) $this->getData('status'); }
}
