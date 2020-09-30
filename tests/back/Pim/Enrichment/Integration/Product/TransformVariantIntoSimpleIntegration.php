<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class TransformVariantIntoSimpleIntegration extends TestCase
{
    private $product;

    /**
     * @test
     */
    public function temoin()
    {
        $this->assertValuesCategoriesAndAssociationsArePreserved($this->product);
    }

    /** @test */
    public function it_keeps_categories_and_values(): void
    {
        $this->simplifyProduct($this->product);
        $this->assertValuesCategoriesAndAssociationsArePreserved($this->product);

        $this->saveProduct($this->product);
        Assert::assertTrue(null === $this->product->getParent(), 'parent is not null');
        Assert::assertTrue(null === $this->product->getFamilyVariant(), 'family variant is not null');
        $this->assertValuesCategoriesAndAssociationsArePreserved($this->product);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('variant');
        Assert::assertTrue(null === $product->getParent(), 'parent is not null');
        Assert::assertTrue(null === $product->getFamilyVariant(), 'family variant is not null');
        $this->assertValuesCategoriesAndAssociationsArePreserved($product);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('random', ['family' => 'familyA']);
        $this->createProduct('other', ['family' => 'familyA1']);
        $this->createProductModel(['code' => 'pm_1', 'family_variant' => 'familyVariantA1']);
        $this->createProductModel(['code' => 'pm_2', 'family_variant' => 'familyVariantA2']);

        $this->createProductModel(
            [
                'code' => 'root',
                'family_variant' => 'familyVariantA2',
                'values' => [
                    'a_date' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => '2020-09-29',
                        ],
                    ],
                    'a_scopable_price' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => [
                                [
                                    'amount' => 10,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => [
                                [
                                    'amount' => 8,
                                    'currency' => 'USD',
                                ],
                            ],
                        ],
                    ],
                ],
                'categories' => ['categoryA1', 'categoryA2'],
                'associations' => [
                    'PACK' => [
                        'products' => ['random'],
                        'product_models' => ['pm_1'],
                        'groups' => ['groupA'],
                    ],
                ],
            ]
        );

        $this->product = $this->createProduct(
            'variant',
            [
                'parent' => 'root',
                'categories' => ['categoryB'],
                'values' => [
                    'a_simple_select' => [
                        [
                            'data' => 'optionA',
                            'scope' => null,
                            'locale' => null,
                        ],
                    ],
                    'a_yes_no' => [
                        [
                            'data' => true,
                            'scope' => null,
                            'locale' => null,
                        ],
                    ],
                    'a_text' => [
                        [
                            'data' => 'variant text',
                            'scope' => null,
                            'locale' => null,
                        ],
                    ],
                ],
                'associations' => [
                    'PACK' => [
                        'products' => ['other'],
                    ],
                    'UPSELL' => [
                        'product_models' => ['pm_2'],
                        'groups' => ['groupB'],
                    ],
                ],
            ]
        );
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assertValuesCategoriesAndAssociationsArePreserved(ProductInterface $product): void
    {
        $categories = $product->getCategoryCodes();
        Assert::assertSame(
            ['categoryA1', 'categoryA2', 'categoryB'],
            $categories
        );

        $values = $product->getValues();
        Assert::assertEqualsCanonicalizing(
            ['sku', 'a_date', 'a_scopable_price', 'a_simple_select', 'a_yes_no', 'a_text'],
            $values->getAttributeCodes()
        );

        $normalizedAssociations = $this->get('pim_catalog.normalizer.standard.product.associations')->normalize(
            $product,
            'standard'
        );
        $expectedAssociations = [
            'PACK' => [
                'groups' => ['groupA'],
                'products' => ['random', 'other'],
                'product_models' => ['pm_1'],
            ],
            'SUBSTITUTION' => [
                'groups' => [],
                'products' => [],
                'product_models' => [],
            ],
            'UPSELL' => [
                'groups' => ['groupB'],
                'products' => [],
                'product_models' => ['pm_2'],
            ],
            'X_SELL' => [
                'groups' => [],
                'products' => [],
                'product_models' => [],
            ],
        ];
        Assert::assertSame(array_keys($normalizedAssociations), array_keys($expectedAssociations));
        foreach ($normalizedAssociations as $associationType => $association) {
            Assert::assertEqualsCanonicalizing(
                $expectedAssociations[$associationType]['products'],
                $association['products']
            );
            Assert::assertEqualsCanonicalizing(
                $expectedAssociations[$associationType]['product_models'],
                $association['product_models']
            );
            Assert::assertEqualsCanonicalizing(
                $expectedAssociations[$associationType]['groups'],
                $association['groups']
            );
        }
    }

    private function simplifyProduct(ProductInterface $product): void
    {
        $this->get('pim_catalog.updater.product')->update($product, ['parent' => null]);
    }

    private function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->saveProduct($product);

        return $product;
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, sprintf('The product model is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function saveProduct(ProductInterface $product): void
    {
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, sprintf('The product is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product')->save($product);
    }
}
