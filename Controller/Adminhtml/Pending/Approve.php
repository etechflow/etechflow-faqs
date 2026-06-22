<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\Adminhtml\Pending;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Filter\FilterManager;
use Etechflow\Faq\Model\PendingFactory;
use Etechflow\Faq\Model\Pending;
use Etechflow\Faq\Model\ResourceModel\Pending as PendingResource;
use Etechflow\Faq\Model\ItemFactory;
use Etechflow\Faq\Model\ResourceModel\Item as ItemResource;

/**
 * Approve a pending visitor question: create a new FAQ item (with empty
 * answer for the admin to fill in) and mark the pending row as approved.
 */
class Approve extends Action
{
    public const ADMIN_RESOURCE = 'Etechflow_Faq::pending';

    private PendingFactory $pendingFactory;
    private PendingResource $pendingResource;
    private ItemFactory $itemFactory;
    private ItemResource $itemResource;
    private FilterManager $filterManager;

    public function __construct(
        Context $context,
        PendingFactory $pendingFactory,
        PendingResource $pendingResource,
        ItemFactory $itemFactory,
        ItemResource $itemResource,
        FilterManager $filterManager
    ) {
        parent::__construct($context);
        $this->pendingFactory  = $pendingFactory;
        $this->pendingResource = $pendingResource;
        $this->itemFactory     = $itemFactory;
        $this->itemResource    = $itemResource;
        $this->filterManager   = $filterManager;
    }

    public function execute(): Redirect
    {
        $id = (int) $this->getRequest()->getParam('id');
        $back = $this->resultRedirectFactory->create()->setPath('*/*/');
        if ($id <= 0) {
            $this->messageManager->addErrorMessage(__('Invalid ID.'));
            return $back;
        }
        $pending = $this->pendingFactory->create();
        $this->pendingResource->load($pending, $id);
        if (!$pending->getId() || $pending->getStatus() !== Pending::STATUS_PENDING) {
            $this->messageManager->addErrorMessage(__('Pending question not found or already processed.'));
            return $back;
        }
        try {
            // Create FAQ item with empty answer
            $slug = strtolower(trim($this->filterManager->translitUrl($pending->getQuestion()), '-'));
            if (strlen($slug) > 140) { $slug = trim(substr($slug, 0, 140), '-'); }
            $item = $this->itemFactory->create();
            $item->setData([
                'category_id' => $pending->getCategoryId() ?: 1,
                'question'    => $pending->getQuestion(),
                'url_key'     => $slug ?: 'visitor-' . $pending->getId(),
                'answer'      => '[Pending answer — please write the response.]',
                'is_active'   => 0, // not visible until admin fills the answer
                'sort_order'  => 0,
            ]);
            $this->itemResource->save($item);

            $pending->setData('status', Pending::STATUS_APPROVED);
            $pending->setData('approved_item_id', (int) $item->getId());
            $this->pendingResource->save($pending);

            $this->messageManager->addSuccessMessage(
                __('Approved. A new FAQ item was created (inactive). Click <a href="%1">here</a> to write the answer and activate it.',
                    $this->getUrl('etechflow_faq/item/edit', ['item_id' => (int) $item->getId()])
                )
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $back;
    }
}
