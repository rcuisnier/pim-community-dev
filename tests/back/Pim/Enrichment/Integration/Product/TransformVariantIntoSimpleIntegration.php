<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class TransformVariantIntoSimpleIntegration extends TestCase
{
    private $product;

    /** @test */
    public function it_does_something(): void
    {
        $categories = $this->product->getCategoryCodes();
        Assert::assertSame(
            ['categoryA1', 'categoryA2', 'categoryB'],
            $categories
        );

        $values = $this->product->getValues();
        Assert::assertEqualsCanonicalizing(
            ['sku', 'a_date', 'a_scopable_price', 'a_simple_select', 'a_yes_no', 'a_text'],
            $values->getAttributeCodes()
        );
    }

    /** @test */
    public function it_keeps_categories_and_values(): void
    {
        $this->simplifyProduct($this->product);

        $categories = $this->product->getCategoryCodes();
        Assert::assertSame(
            ['categoryA1', 'categoryA2', 'categoryB'],
            $categories
        );

        $values = $this->product->getValues();
        Assert::assertEqualsCanonicalizing(
            ['sku', 'a_date', 'a_scopable_price', 'a_simple_select', 'a_yes_no', 'a_text'],
            $values->getAttributeCodes()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $root = $this->createProductModel([
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
                        ]
                    ], [
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
            'associations' => [],
        ]);

        $this->product = $this->createProduct('variant', [
            'parent' => 'root',
            'categories' => ['categoryB'],
            'values' => [
                'a_simple_select' => [
                    [
                        'data' => 'optionA',
                        'scope' => null,
                        'locale' => null,
                    ]
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
        ]);
    }

    private function simplifyProduct(ProductInterface $product): void
    {
        $product->setParent(null);
    }

    private function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations);

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function createProductModel(array $data)
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations);

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
