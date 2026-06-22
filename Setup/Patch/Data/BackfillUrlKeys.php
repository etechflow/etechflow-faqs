<?php
declare(strict_types=1);

namespace Etechflow\Faq\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Filter\FilterManager;

class BackfillUrlKeys implements DataPatchInterface
{
    private ModuleDataSetupInterface $setup;
    private FilterManager $filterManager;

    public function __construct(
        ModuleDataSetupInterface $setup,
        FilterManager $filterManager
    ) {
        $this->setup = $setup;
        $this->filterManager = $filterManager;
    }

    public function apply(): self
    {
        $this->setup->startSetup();
        $conn  = $this->setup->getConnection();
        $table = $this->setup->getTable('etechflow_faq_item');

        $rows = $conn->fetchAll(
            $conn->select()
                ->from($table, ['item_id', 'category_id', 'question', 'url_key'])
                ->where('url_key IS NULL OR url_key = ?', '')
        );

        $usedPerCategory = [];
        $existing = $conn->fetchAll(
            $conn->select()
                ->from($table, ['category_id', 'url_key'])
                ->where('url_key IS NOT NULL AND url_key != ?', '')
        );
        foreach ($existing as $e) {
            $usedPerCategory[(int)$e['category_id']][$e['url_key']] = true;
        }

        foreach ($rows as $row) {
            $catId = (int) $row['category_id'];
            $slug  = $this->slugify((string) $row['question']);
            if ($slug === '') {
                $slug = 'faq-' . $row['item_id'];
            }
            $finalSlug = $slug;
            $n = 2;
            while (!empty($usedPerCategory[$catId][$finalSlug])) {
                $finalSlug = $slug . '-' . $n;
                $n++;
            }
            $usedPerCategory[$catId][$finalSlug] = true;

            $conn->update(
                $table,
                ['url_key' => $finalSlug],
                ['item_id = ?' => (int) $row['item_id']]
            );
        }

        $this->setup->endSetup();
        return $this;
    }

    private function slugify(string $value): string
    {
        $slug = $this->filterManager->translitUrl($value);
        $slug = strtolower(trim($slug, '-'));
        if (strlen($slug) > 140) {
            $slug = substr($slug, 0, 140);
            $slug = trim($slug, '-');
        }
        return $slug;
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
