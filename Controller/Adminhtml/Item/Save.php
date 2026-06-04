<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filter\FilterManager;
use Etechflow\Faq\Model\ItemFactory;
use Etechflow\Faq\Model\ResourceModel\Item as ItemResource;
use Etechflow\Faq\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::item';

    private ItemFactory $itemFactory;
    private ItemResource $itemResource;
    private FilterManager $filterManager;
    private ItemCollectionFactory $itemCollectionFactory;

    public function __construct(
        Context $context,
        ItemFactory $itemFactory,
        ItemResource $itemResource,
        FilterManager $filterManager,
        ItemCollectionFactory $itemCollectionFactory
    ) {
        parent::__construct($context);
        $this->itemFactory  = $itemFactory;
        $this->itemResource = $itemResource;
        $this->filterManager = $filterManager;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $data = $this->getRequest()->getPostValue();
        $back = $this->resultRedirectFactory->create();

        if (!$data) {
            return $back->setPath('*/*/');
        }

        $id    = (int) ($data['item_id'] ?? 0);
        $model = $this->itemFactory->create();

        if ($id) {
            $this->itemResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('FAQ not found.'));
                return $back->setPath('*/*/');
            }
        }

        $categoryId = (int) ($data['category_id'] ?? 0);
        $rawSlug    = trim((string) ($data['url_key'] ?? ''));
        if ($rawSlug === '') {
            $rawSlug = $this->slugify((string) ($data['question'] ?? ''));
        } else {
            $rawSlug = $this->slugify($rawSlug);
        }
        if ($rawSlug === '') {
            $rawSlug = 'faq-' . ($id ?: time());
        }
        $data['url_key'] = $this->ensureUnique($categoryId, $rawSlug, $id);

        // New records POST an empty item_id. Left as '', Magento treats the
        // model as existing (getId() !== null) and runs an UPDATE that matches
        // nothing — "saved successfully" but no row. Null it so it INSERTs.
        if (empty($data['item_id'])) {
            $data['item_id'] = null;
        }

        $model->setData($data);

        try {
            $this->itemResource->save($model);
            $this->messageManager->addSuccessMessage(__('FAQ saved successfully.'));

            if ($this->getRequest()->getParam('back')) {
                return $back->setPath('*/*/edit', ['id' => $model->getId()]);
            }
            return $back->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $back->setPath('*/*/edit', ['id' => $id ?: null]);
        }
    }

    private function slugify(string $value): string
    {
        $slug = $this->filterManager->translitUrl($value);
        $slug = strtolower(trim($slug, '-'));
        if (strlen($slug) > 140) {
            $slug = trim(substr($slug, 0, 140), '-');
        }
        return $slug;
    }

    private function ensureUnique(int $categoryId, string $slug, int $excludeId): string
    {
        $base = $slug;
        $candidate = $slug;
        $n = 2;
        while ($this->slugExists($categoryId, $candidate, $excludeId)) {
            $candidate = $base . '-' . $n;
            $n++;
            if ($n > 200) { break; }
        }
        return $candidate;
    }

    private function slugExists(int $categoryId, string $slug, int $excludeId): bool
    {
        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToFilter('category_id', $categoryId);
        $collection->addFieldToFilter('url_key', $slug);
        if ($excludeId > 0) {
            $collection->addFieldToFilter('item_id', ['neq' => $excludeId]);
        }
        return $collection->getSize() > 0;
    }
}
