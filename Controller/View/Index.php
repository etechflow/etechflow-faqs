<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\View;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Etechflow\Faq\Api\FaqRepositoryInterface;
use Magento\Framework\Registry;

class Index implements HttpGetActionInterface
{
    private RequestInterface $request;
    private PageFactory $pageFactory;
    private ResultFactory $resultFactory;
    private FaqRepositoryInterface $faqRepository;
    private Registry $registry;

    public function __construct(
        RequestInterface $request,
        PageFactory $pageFactory,
        ResultFactory $resultFactory,
        FaqRepositoryInterface $faqRepository,
        Registry $registry
    ) {
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->resultFactory = $resultFactory;
        $this->faqRepository = $faqRepository;
        $this->registry = $registry;
    }

    public function execute(): ResultInterface
    {
        $category = (string) $this->request->getParam('category', '');
        $slug     = (string) $this->request->getParam('slug', '');

        $item = $this->faqRepository->getByUrlKey($category, $slug);
        if (!$item) {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        $categoryModel = $this->faqRepository->getCategoryByIdentifier($category);

        $this->registry->register('current_faq_item', $item, true);
        $this->registry->register('current_faq_category', $categoryModel, true);

        /** @var \Magento\Framework\View\Result\Page $page */
        $page = $this->pageFactory->create();

        $metaTitle = $item->getMetaTitle() !== '' ? $item->getMetaTitle() : $item->getQuestion();
        $page->getConfig()->getTitle()->set($metaTitle);
        if ($item->getMetaDescription() !== '') {
            $page->getConfig()->setDescription($item->getMetaDescription());
        }

        return $page;
    }
}
