<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use \Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class CreateHatProduct implements DataPatchInterface
{

    protected ModuleDataSetupInterface $setup;

    protected ProductInterfaceFactory $productInterfaceFactory;

    protected ProductRepositoryInterface $productRepository;

    protected State $appState;

    protected EavSetup $eavSetup;

    protected StoreManagerInterface $storeManager;

    protected SourceItemInterfaceFactory $sourceItemFactory;

    protected SourceItemsSaveInterface $sourceItemsSaveInterface;

    protected CategoryLinkManagementInterface $categoryLink;

    protected CategoryCollectionFactory $categoryCollectionFactory;

    protected array $sourceItems = [];

    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        CategoryLinkManagementInterface $categoryLink,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->setup = $setup;
        $this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->categoryLink = $categoryLink;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getVersion() {}

    public function getAliases()
    {
        return [];
    }
    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
        return true;
    }

    public function execute()
    {
        // Create Product
        $product = $this->productInterfaceFactory->create();

        // Check if the ID slready exists.
        if ($product->getIdBySku('some-hat-product')) {
            return;
        }

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        // Set attributes
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setName("some-hat-product")
            ->setSku("some-hat-product")
            ->setUrlKey('hat')
            ->setPrice(14.99)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);

        // Save product
        $product = $this->productRepository->save($product);

        // Add product to category
        $this->categoryLink->assignProductToCategories($product->getSku(), [11]);
    }
}
