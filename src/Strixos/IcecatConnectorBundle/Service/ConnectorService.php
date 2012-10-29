<?php
namespace Strixos\IcecatConnectorBundle\Service;

use Strixos\IcecatConnectorBundle\Entity\Config;
use Strixos\IcecatConnectorBundle\Entity\ConfigManager;
use Strixos\IcecatConnectorBundle\Entity\SourceSupplier;

use Strixos\IcecatConnectorBundle\Extract\ProductXmlExtractor;
use Strixos\IcecatConnectorBundle\Extract\SuppliersXmlExtractor;
use Strixos\IcecatConnectorBundle\Extract\DownloadAndUnpackSource;

use Strixos\IcecatConnectorBundle\Transform\LanguagesTransform;
use Strixos\IcecatConnectorBundle\Transform\ProductsTransform;
use Strixos\IcecatConnectorBundle\Transform\SuppliersTransform;
use Strixos\IcecatConnectorBundle\Transform\ProductXmlToArrayTransformer;
use Strixos\IcecatConnectorBundle\Transform\ProductArrayToCatalogProductTransformer;

use Strixos\IcecatConnectorBundle\Load\BatchLoader;
/**
 * Connector service, accessibble from anywhere in application
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConnectorService
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;
    
    /**
     * Config manager
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->configManager = new ConfigManager($this->container->get('doctrine.orm.entity_manager'));
    }

    /**
     * Import suppliers from icecat database
     */
    public function importSuppliers()
    {
        // Get config
        $login    = $this->configManager->getValue(Config::LOGIN);
        $password = $this->configManager->getValue(Config::PASSWORD);
        $baseDir  = $this->configManager->getValue(Config::BASE_DIR);
        $url      = $this->configManager->getValue(Config::SUPPLIERS_URL);
        $filePath    = $baseDir . $this->configManager->getValue(Config::SUPPLIERS_FILE);
        $forceDownloadFile = true;
        
        // Call extractor
        $extractor = new SuppliersXmlExtractor($url, $login, $password);
        $extractor->extract();
        $xmlContent = $extractor->getReadContent();
        
        $transformer = new SuppliersTransform($this->container->get('doctrine.orm.entity_manager'), $xmlContent);
        $transformer->transform();
    }

    /**
     * Import languages from icecat database
     */
    public function importLanguages()
    {
        // Get config
        $login    = $this->configManager->getValue(Config::LOGIN);
        $password = $this->configManager->getValue(Config::PASSWORD);
        $baseDir  = $this->configManager->getValue(Config::BASE_DIR);
        $url      = $this->configManager->getValue(Config::LANGUAGES_URL);
        $filePath = $baseDir . $this->configManager->getValue(Config::LANGUAGES_FILE);
        $archivePath = $baseDir . $this->configManager->getValue(Config::LANGUAGES_ARCHIVED_FILE);
        $forceDownloadFile = true;

        // Call extractor
        $extractor = new DownloadAndUnpackSource($url, $login, $password, $archivePath, $filePath, $forceDownloadFile);
        $extractor->extract();
        $xmlContent = $extractor->getReadContent();

        $loader = new BatchLoader($this->container->get('doctrine.orm.entity_manager'));
        $transformer = new LanguagesTransform($loader, $xmlContent);
        $transformer->transform();
    }

    /**
     * Import products from icecat database
     */
    public function importProducts()
    {
        // Get config
        $login    = $this->configManager->getValue(Config::LOGIN);
        $password = $this->configManager->getValue(Config::PASSWORD);
        $baseDir  = $this->configManager->getValue(Config::BASE_DIR);
        $url      = $this->configManager->getValue(Config::PRODUCTS_URL);
        $filePath = $baseDir . $this->configManager->getValue(Config::PRODUCTS_FILE);
        $archivePath = $baseDir . $this->configManager->getValue(Config::PRODUCTS_ARCHIVED_FILE);
        $forceDownloadFile = false;
        
        // Call extractor
        $extractor = new DownloadAndUnpackSource($url, $login, $password, $archivePath, $filePath, $forceDownloadFile);
        $extractor->extract();

        $transformer = new ProductsTransform($this->container->get('doctrine.orm.entity_manager'), $filePath);
        $transformer->transform();
    }

    /**
     * Import a product by its icecat id
     * @param string $productId
     */
    public function importProductFromIcecatXml($productId)
    {
        // TODO by configuration, for now en_US first is important
        $localeIceToPim = array('US' => 'en_US', 'FR' => 'fr_FR');
        
        // 1. get base product from icecat referential
        $em = $this->container->get('doctrine.orm.entity_manager');
        $baseProduct = $em->getRepository('StrixosIcecatConnectorBundle:SourceProduct')->find($productId);
        $prodId = $baseProduct->getProdId();
        $supplierName = $baseProduct->getSupplier()->getName();
        
        foreach ($localeIceToPim as $icecatLocale => $pimLocale) {

            // 2. extract product xml from icecat
            $extractor = new ProductXmlExtractor($prodId, $supplierName, $icecatLocale, $this->configManager);
            $extractor->extract();
            $simpleXml = $extractor->getReadContent();

            // 3. transform product xml to lines (associative array)
            $transformer = new ProductXmlToArrayTransformer($simpleXml);
            $transformer->transform();
            $productBaseData = $transformer->getProductBaseData();
            $productFeatures = $transformer->getProductFeatures();

            // 4. transform array to pim product
            $productTypeService = $this->container->get('akeneo.catalog.model_producttype');
            $productService = $this->container->get('akeneo.catalog.model_product');
            $transformer = new ProductArrayToCatalogProductTransformer($productTypeService, $productService, $productBaseData, $productFeatures, $pimLocale);
            $transformer->transform();

        // 5. load product (move persist / flush from transform to allow batch using for supplier import)
       /*
        foreach ($locales as $locale) {
            $fp = $extract->process($prodId, $supplierName, $locale);
            $transform->process($fp);
        }*/
        }
        
        // 6. Update icecat product imported
        $baseProduct->setIsImported(true);
        $em->persist($baseProduct);
        $em->flush();
    }

    /**
     * Import all products from a supplier
     * @param SourceSupplier $supplier
     */
    public function importProductsFromSupplier(SourceSupplier $supplier)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $products = $em->getRepository('StrixosIcecatConnectorBundle:SourceProduct')->findBySupplier($supplier);

        foreach ($products as $product) {
            $this->importProductFromIcecatXml($product->getId());
        }
    }
}
