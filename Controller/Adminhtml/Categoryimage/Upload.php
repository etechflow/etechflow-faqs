<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\Adminhtml\Categoryimage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * AJAX endpoint that handles the category icon image upload widget.
 * Saves uploaded files into pub/media/etechflow_faq/icons/ and returns
 * the JSON shape Magento's fileUploader UI component expects.
 */
class Upload extends Action
{
    public const ADMIN_RESOURCE = 'Etechflow_Faq::category';
    private const MEDIA_SUBDIR = 'etechflow_faq/icons';

    private JsonFactory $jsonFactory;
    private UploaderFactory $uploaderFactory;
    private Filesystem $filesystem;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->jsonFactory     = $jsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem      = $filesystem;
        $this->storeManager    = $storeManager;
    }

    public function execute(): ResultInterface
    {
        $json = $this->jsonFactory->create();
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'icon_image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);

            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $absDir   = $mediaDir->getAbsolutePath(self::MEDIA_SUBDIR);

            $result = $uploader->save($absDir);

            $relative = self::MEDIA_SUBDIR . '/' . ltrim($result['file'], '/');
            $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $result['url']  = rtrim($base, '/') . '/' . $relative;
            $result['name'] = $result['name'] ?? basename($result['file']);
            // Magento fileUploader looks for these keys
            $result['file']  = $relative;
            $result['size']  = $result['size'] ?? 0;
            $result['type']  = $result['type'] ?? '';
            $result['error'] = 0;

            return $json->setData($result);
        } catch (\Throwable $e) {
            return $json->setData([
                'error'     => $e->getMessage(),
                'errorcode' => $e->getCode() ?: 1,
            ]);
        }
    }
}
