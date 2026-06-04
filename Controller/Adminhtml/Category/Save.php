<?php
declare(strict_types=1);
namespace Etechflow\Faq\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Etechflow\Faq\Model\CategoryFactory;
use Etechflow\Faq\Model\ResourceModel\Category as CategoryResource;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_Faq::category';

    private CategoryFactory $categoryFactory;
    private CategoryResource $categoryResource;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource
    ) {
        parent::__construct($context);
        $this->categoryFactory  = $categoryFactory;
        $this->categoryResource = $categoryResource;
    }

    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $data = $this->getRequest()->getPostValue();
        $back = $this->resultRedirectFactory->create();

        if (!$data) {
            return $back->setPath('*/*/');
        }

        $id    = (int) ($data['category_id'] ?? 0);
        $model = $this->categoryFactory->create();

        if ($id) {
            $this->categoryResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('Category not found.'));
                return $back->setPath('*/*/');
            }
        }

        if (empty($data['identifier']) && !empty($data['label'])) {
            $data['identifier'] = preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $data['label']));
        }

        // Magento fileUploader posts icon_image as either a string (when unchanged)
        // or an array of {file, url, ...} (when newly uploaded). Normalize to the
        // relative media path we store in the DB.
        if (isset($data['icon_image'])) {
            if (is_array($data['icon_image'])) {
                $upload = $data['icon_image'][0] ?? null;
                $data['icon_image'] = is_array($upload) && !empty($upload['file']) ? (string) $upload['file'] : '';
            }
            $data['icon_image'] = (string) $data['icon_image'];
        }

        // New records POST an empty category_id. Left as '', Magento treats the
        // model as existing (getId() !== null) and runs an UPDATE that matches
        // nothing — "saved successfully" but no row. Null it so it INSERTs.
        if (empty($data['category_id'])) {
            $data['category_id'] = null;
        }

        $model->setData($data);

        try {
            $this->categoryResource->save($model);
            $this->messageManager->addSuccessMessage(__('Category saved successfully.'));

            if ($this->getRequest()->getParam('back')) {
                return $back->setPath('*/*/edit', ['id' => $model->getId()]);
            }
            return $back->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $back->setPath('*/*/edit', ['id' => $id ?: null]);
        }
    }
}
