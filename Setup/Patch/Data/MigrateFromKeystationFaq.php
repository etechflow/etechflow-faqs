<?php
declare(strict_types=1);

namespace Etechflow\Faq\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Migrates data from legacy Keystation_Faq tables (if they exist) into
 * Etechflow_Faq tables. Safe to run on any install:
 *   - If keystation_faq_* tables don't exist → no-op.
 *   - If etechflow_faq_* already has data → no-op (assumes you migrated already).
 *   - Otherwise: copies categories + items preserving IDs.
 */
class MigrateFromKeystationFaq implements DataPatchInterface
{
    private ModuleDataSetupInterface $setup;

    public function __construct(ModuleDataSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public function apply(): self
    {
        $this->setup->startSetup();
        $conn = $this->setup->getConnection();

        // Source tables (legacy)
        $srcCat  = $this->setup->getTable('keystation_faq_category');
        $srcItem = $this->setup->getTable('keystation_faq_item');

        // Target tables (new)
        $dstCat  = $this->setup->getTable('etechflow_faq_category');
        $dstItem = $this->setup->getTable('etechflow_faq_item');

        if (!$conn->isTableExists($srcCat) || !$conn->isTableExists($srcItem)) {
            $this->setup->endSetup();
            return $this; // nothing to migrate
        }
        if (!$conn->isTableExists($dstCat) || !$conn->isTableExists($dstItem)) {
            $this->setup->endSetup();
            return $this; // target schema not in place yet — Magento ran patches before db_schema?
        }
        if ((int) $conn->fetchOne("SELECT COUNT(*) FROM $dstCat") > 0) {
            $this->setup->endSetup();
            return $this; // target already populated
        }

        // Copy categories (preserve category_id)
        $catRows = $conn->fetchAll("SELECT * FROM $srcCat");
        foreach ($catRows as $row) {
            $row = $this->intersectColumns($conn, $dstCat, $row);
            $conn->insert($dstCat, $row);
        }

        // Copy items (preserve item_id + category_id)
        $itemRows = $conn->fetchAll("SELECT * FROM $srcItem");
        foreach ($itemRows as $row) {
            $row = $this->intersectColumns($conn, $dstItem, $row);
            $conn->insert($dstItem, $row);
        }

        $this->setup->endSetup();
        return $this;
    }

    /**
     * Drop any keys from $row that don't exist as columns on $table.
     * Lets us migrate even when the new schema has extra/different columns.
     */
    private function intersectColumns($conn, string $table, array $row): array
    {
        $desc = $conn->describeTable($table);
        return array_intersect_key($row, $desc);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
