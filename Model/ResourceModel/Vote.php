<?php
declare(strict_types=1);
namespace Etechflow\Faq\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Vote extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('etechflow_faq_vote', 'vote_id');
    }

    /**
     * Recompute and cache helpful/unhelpful counts on the item table.
     */
    public function refreshItemCounts(int $itemId): void
    {
        $conn   = $this->getConnection();
        $voteT  = $this->getTable('etechflow_faq_vote');
        $itemT  = $this->getTable('etechflow_faq_item');
        $row = $conn->fetchRow($conn->select()
            ->from($voteT, [
                'helpful'   => new \Zend_Db_Expr('SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END)'),
                'unhelpful' => new \Zend_Db_Expr('SUM(CASE WHEN vote = 0 THEN 1 ELSE 0 END)'),
            ])
            ->where('item_id = ?', $itemId));
        $conn->update($itemT, [
            'helpful_count'   => (int) ($row['helpful']   ?? 0),
            'unhelpful_count' => (int) ($row['unhelpful'] ?? 0),
        ], ['item_id = ?' => $itemId]);
    }
}
