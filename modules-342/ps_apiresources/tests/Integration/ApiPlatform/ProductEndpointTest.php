<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PsApiResourcesTest\Integration\ApiPlatform;

use PrestaShop\PrestaShop\Core\Domain\Product\Pack\ValueObject\PackStockType;
use PrestaShop\PrestaShop\Core\Domain\Product\Stock\ValueObject\OutOfStockType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\DeliveryTimeNoteType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductCondition;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductVisibility;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\RedirectType;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\ProductGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Query\ProductQueryBuilder;
use PrestaShop\PrestaShop\Core\Search\Filters\ProductFilters;
use Tests\Resources\Resetter\LanguageResetter;
use Tests\Resources\Resetter\ProductResetter;
use Tests\Resources\ResourceResetter;

class ProductEndpointTest extends ApiTestCase
{
    protected static array $defaultProductData = [
        'type' => ProductType::TYPE_STANDARD,
        'names' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'descriptions' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'shortDescriptions' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'tags' => [],
        'priceTaxExcluded' => 0.0,
        'priceTaxIncluded' => 0.0,
        'ecotaxTaxExcluded' => 0.0,
        'ecotaxTaxIncluded' => 0.0,
        // US-FL Rate (6%)
        'taxRulesGroupId' => 9,
        'onSale' => false,
        'wholesalePrice' => 0.0,
        'unitPriceTaxExcluded' => 0.0,
        'unitPriceTaxIncluded' => 0.0,
        'unity' => '',
        'unitPriceRatio' => 0.0,
        'visibility' => ProductVisibility::VISIBLE_EVERYWHERE,
        'availableForOrder' => true,
        'onlineOnly' => false,
        'showPrice' => true,
        'condition' => ProductCondition::NEW,
        'showCondition' => false,
        'manufacturerId' => 0,
        'isbn' => '',
        'upc' => '',
        'gtin' => '',
        'mpn' => '',
        'reference' => '',
        'width' => 0.0,
        'height' => 0.0,
        'depth' => 0.0,
        'weight' => 0.0,
        'additionalShippingCost' => 0.0,
        'carrierReferenceIds' => [],
        'deliveryTimeNoteType' => DeliveryTimeNoteType::TYPE_DEFAULT,
        'deliveryTimeInStockNotes' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'deliveryTimeOutOfStockNotes' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'metaTitles' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'metaDescriptions' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'linkRewrites' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'redirectType' => RedirectType::TYPE_DEFAULT,
        'packStockType' => PackStockType::STOCK_TYPE_DEFAULT,
        'outOfStockType' => OutOfStockType::OUT_OF_STOCK_DEFAULT,
        'quantity' => 0,
        'minimalQuantity' => 1,
        'lowStockThreshold' => 0,
        'lowStockAlertEnabled' => false,
        'availableNowLabels' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'location' => '',
        'availableLaterLabels' => [
            'en-US' => '',
            'fr-FR' => '',
        ],
        'coverThumbnailUrl' => 'http://myshop.com/img/p/en-default-cart_default.jpg',
        'active' => false,
        'shopIds' => [
            1,
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        (new ResourceResetter())->backupTestModules();
        ProductResetter::resetProducts();
        LanguageResetter::resetLanguages();
        self::addLanguageByLocale('fr-FR');
        self::createApiClient(['product_write', 'product_read']);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        ProductResetter::resetProducts();
        LanguageResetter::resetLanguages();
        // Reset modules folder that are removed with the FR language
        (new ResourceResetter())->resetTestModules();
    }

    public function getProtectedEndpoints(): iterable
    {
        yield 'get endpoint' => [
            'GET',
            '/product/1',
        ];

        yield 'create endpoint' => [
            'POST',
            '/product',
        ];

        yield 'update endpoint' => [
            'PATCH',
            '/product/1',
            'application/merge-patch+json',
        ];

        yield 'delete endpoint' => [
            'DELETE',
            '/product/1',
        ];

        yield 'upload image endpoint' => [
            'POST',
            '/product/1/image',
            'multipart/form-data',
        ];

        yield 'get image endpoint' => [
            'GET',
            '/product/image/1',
        ];

        yield 'update image endpoint' => [
            'POST',
            '/product/image/1',
            'multipart/form-data',
        ];

        yield 'list images endpoint' => [
            'GET',
            '/product/1/images',
        ];
    }

    public function testAddProduct(): int
    {
        $productsNumber = $this->getProductsNumber();
        $bearerToken = $this->getBearerToken(['product_write']);
        $response = static::createClient()->request('POST', '/product', [
            'auth_bearer' => $bearerToken,
            'json' => [
                'type' => ProductType::TYPE_STANDARD,
                'names' => [
                    'en-US' => 'product name',
                    'fr-FR' => 'nom produit',
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(201);
        $newProductsNumber = $this->getProductsNumber();
        self::assertEquals($productsNumber + 1, $newProductsNumber);

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotFalse($decodedResponse);
        $this->assertArrayHasKey('productId', $decodedResponse);
        $productId = $decodedResponse['productId'];
        $this->assertEquals(
            [
                'type' => ProductType::TYPE_STANDARD,
                'productId' => $productId,
                'names' => [
                    'en-US' => 'product name',
                    'fr-FR' => 'nom produit',
                ],
                'linkRewrites' => [
                    'en-US' => 'product-name',
                    'fr-FR' => 'nom-produit',
                ],
                'descriptions' => [
                    'en-US' => '',
                    'fr-FR' => '',
                ],
                'active' => false,
                'shopIds' => [
                    1,
                ],
            ] + self::$defaultProductData,
            $decodedResponse
        );

        return $productId;
    }

    /**
     * @depends testAddProduct
     *
     * @param int $productId
     *
     * @return int
     */
    public function testPartialUpdateProduct(int $productId): int
    {
        $productsNumber = $this->getProductsNumber();
        $bearerToken = $this->getBearerToken(['product_write']);

        // Update product with partial data, even multilang fields can be updated language by language
        $response = static::createClient()->request('PATCH', '/product/' . $productId, [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'content-type' => 'application/merge-patch+json',
            ],
            'json' => [
                'names' => [
                    'fr-FR' => 'nouveau nom',
                ],
                'descriptions' => [
                    'en-US' => 'new description',
                ],
                'active' => true,
            ],
        ]);
        self::assertResponseStatusCodeSame(200);
        // No new product
        $this->assertEquals($productsNumber, $this->getProductsNumber());

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotFalse($decodedResponse);
        // Returned data has modified fields, the others haven't changed
        $this->assertEquals(
            [
                'type' => ProductType::TYPE_STANDARD,
                'productId' => $productId,
                'names' => [
                    'en-US' => 'product name',
                    'fr-FR' => 'nouveau nom',
                ],
                'linkRewrites' => [
                    'en-US' => 'product-name',
                    'fr-FR' => 'nom-produit',
                ],
                'descriptions' => [
                    'en-US' => 'new description',
                    'fr-FR' => '',
                ],
                'active' => true,
                'shopIds' => [
                    1,
                ],
            ] + self::$defaultProductData,
            $decodedResponse
        );

        // Update product with partial data, only name default language the other names are not impacted
        $response = static::createClient()->request('PATCH', '/product/' . $productId, [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'content-type' => 'application/merge-patch+json',
            ],
            'json' => [
                'names' => [
                    'en-US' => 'new product name',
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(200);
        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotFalse($decodedResponse);
        // Returned data has modified fields, the others haven't changed
        $this->assertEquals(
            [
                'type' => ProductType::TYPE_STANDARD,
                'productId' => $productId,
                'names' => [
                    'en-US' => 'new product name',
                    'fr-FR' => 'nouveau nom',
                ],
                'linkRewrites' => [
                    'en-US' => 'product-name',
                    'fr-FR' => 'nom-produit',
                ],
                'descriptions' => [
                    'en-US' => 'new description',
                    'fr-FR' => '',
                ],
                'active' => true,
                'shopIds' => [
                    1,
                ],
            ] + self::$defaultProductData,
            $decodedResponse
        );

        return $productId;
    }

    /**
     * @depends testPartialUpdateProduct
     *
     * @param int $productId
     */
    public function testGetProduct(int $productId): int
    {
        $bearerToken = $this->getBearerToken(['product_read']);
        $response = static::createClient()->request('GET', '/product/' . $productId, [
            'auth_bearer' => $bearerToken,
        ]);
        self::assertResponseStatusCodeSame(200);

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotFalse($decodedResponse);
        // Returned data has modified fields, the others haven't changed
        $this->assertEquals(
            [
                'type' => ProductType::TYPE_STANDARD,
                'productId' => $productId,
                'names' => [
                    'en-US' => 'new product name',
                    'fr-FR' => 'nouveau nom',
                ],
                'linkRewrites' => [
                    'en-US' => 'product-name',
                    'fr-FR' => 'nom-produit',
                ],
                'descriptions' => [
                    'en-US' => 'new description',
                    'fr-FR' => '',
                ],
                'active' => true,
                'shopIds' => [
                    1,
                ],
            ] + self::$defaultProductData,
            $decodedResponse
        );

        return $productId;
    }

    /**
     * @depends testPartialUpdateProduct
     *
     * @param int $productId
     */
    public function testUpdateAllProductFields(int $productId): int
    {
        $bearerToken = $this->getBearerToken(['product_read', 'product_write']);

        $updateProduct = [
            'type' => ProductType::TYPE_STANDARD,
            'names' => [
                'en-US' => 'new name',
                'fr-FR' => 'nouveau nom',
            ],
            'descriptions' => [
                'en-US' => 'new description',
                'fr-FR' => 'nouvelle description',
            ],
            'shortDescriptions' => [
                'en-US' => 'new short description',
                'fr-FR' => 'nouvelle description courte',
            ],
            'priceTaxExcluded' => 10.0,
            'ecotaxTaxExcluded' => 2.0,
            // US-GA Rate (4%)
            'taxRulesGroupId' => 10,
            'onSale' => true,
            'wholesalePrice' => 3.45,
            'unitPriceTaxExcluded' => 5.0,
            'unity' => 'per kg',
            'visibility' => ProductVisibility::VISIBLE_IN_CATALOG,
            'availableForOrder' => false,
            'onlineOnly' => true,
            'showPrice' => false,
            'condition' => ProductCondition::USED,
            'showCondition' => false,
            'manufacturerId' => 1,
            'isbn' => '978-3-16-148410-0',
            'upc' => '72527273070',
            'gtin' => '978020137962',
            'mpn' => 'mpn1',
            'reference' => 'ref1',
            'width' => 10.20,
            'height' => 90.60,
            'depth' => 32.70,
            'weight' => 10.07,
            'additionalShippingCost' => 1.2,
            'deliveryTimeNoteType' => DeliveryTimeNoteType::TYPE_SPECIFIC,
            'deliveryTimeInStockNotes' => [
                'en-US' => 'under 2 days',
                'fr-FR' => 'moins de 2 jours',
            ],
            'deliveryTimeOutOfStockNotes' => [
                'en-US' => 'one month',
                'fr-FR' => 'un mois',
            ],
            'metaTitles' => [
                'en-US' => 'new meta title',
                'fr-FR' => 'nouveau titre meta',
            ],
            'metaDescriptions' => [
                'en-US' => 'new meta description',
                'fr-FR' => 'nouvelle description meta',
            ],
            'linkRewrites' => [
                'en-US' => 'new-link',
                'fr-FR' => 'nouveau-lien',
            ],
            'packStockType' => PackStockType::STOCK_TYPE_BOTH,
            'minimalQuantity' => 3,
            'lowStockThreshold' => 5,
            'lowStockAlertEnabled' => true,
            'availableNowLabels' => [
                'en-US' => 'available now',
                'fr-FR' => 'disponible maintenant',
            ],
            'availableLaterLabels' => [
                'en-US' => 'available later',
                'fr-FR' => 'disponible plus tard',
            ],
            'active' => false,
            // Multi-parameters setter
            'redirectOption' => [
                'redirectType' => RedirectType::TYPE_CATEGORY_PERMANENT,
                'redirectTarget' => 1,
            ],
        ];

        // Build expected data
        $expectedUpdateProduct = [
            'productId' => $productId,
            // These fields are not part of the posted data but are automatically updated after data is modified
            'priceTaxIncluded' => 10.4,
            'ecotaxTaxIncluded' => 2.0,
            'unitPriceTaxIncluded' => 5.2,
            'unitPriceRatio' => 2.0,
        ] + $updateProduct + self::$defaultProductData;

        // Redirect options are passed as a sub object but they are returned independently when product is read
        unset($expectedUpdateProduct['redirectOption']);
        $expectedUpdateProduct['redirectType'] = RedirectType::TYPE_CATEGORY_PERMANENT;
        $expectedUpdateProduct['redirectTarget'] = 1;

        // Update product with partial data, even multilang fields can be updated language by language
        $response = static::createClient()->request('PATCH', '/product/' . $productId, [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'content-type' => 'application/merge-patch+json',
            ],
            'json' => $updateProduct,
        ]);
        self::assertResponseStatusCodeSame(200);

        // Check updated response
        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertEquals($expectedUpdateProduct, $decodedResponse);

        // Now check the result when we GET the product
        $response = static::createClient()->request('GET', '/product/' . $productId, [
            'auth_bearer' => $bearerToken,
        ]);
        self::assertResponseStatusCodeSame(200);
        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertEquals($expectedUpdateProduct, $decodedResponse);

        return $productId;
    }

    /**
     * @depends testUpdateAllProductFields
     *
     * @param int $productId
     */
    public function testAddImage(int $productId): int
    {
        $bearerToken = $this->getBearerToken(['product_write']);
        $uploadedImage = $this->prepareUploadedFile(__DIR__ . '/../../Resources/assets/image/Hummingbird_cushion.jpg');

        $response = static::createClient()->request('POST', '/product/' . $productId . '/image', [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'content-type' => 'multipart/form-data',
            ],
            'extra' => [
                'files' => [
                    'image' => $uploadedImage,
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(201);

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotFalse($decodedResponse);
        $this->assertArrayHasKey('imageId', $decodedResponse);
        $this->assertIsInt($decodedResponse['imageId']);
        $imageId = $decodedResponse['imageId'];
        $this->assertGreaterThan(0, $decodedResponse['imageId']);
        $this->assertArrayHasKey('imageUrl', $decodedResponse);
        $this->assertMatchesRegularExpression('@/img/p[/0-9]+' . $imageId . '\.jpg@', $decodedResponse['imageUrl']);
        $this->assertArrayHasKey('thumbnailUrl', $decodedResponse);
        $this->assertMatchesRegularExpression('@/img/p[/0-9]+/' . $imageId . '-small_default\.jpg@', $decodedResponse['thumbnailUrl']);
        $this->assertArrayHasKey('legends', $decodedResponse);
        $this->assertEquals([
            'en-US' => '',
            'fr-FR' => '',
        ], $decodedResponse['legends']);
        $this->assertArrayHasKey('cover', $decodedResponse);
        $this->assertIsBool($decodedResponse['cover']);
        $this->assertArrayHasKey('position', $decodedResponse);
        $this->assertIsInt($decodedResponse['position']);
        $this->assertEquals(1, $decodedResponse['position']);

        return $imageId;
    }

    /**
     * @depends testAddImage
     *
     * @param int $imageId
     */
    public function testGetImage(int $imageId): string
    {
        $bearerToken = $this->getBearerToken(['product_read']);
        $response = static::createClient()->request('GET', '/product/image/' . $imageId, ['auth_bearer' => $bearerToken]);
        self::assertResponseStatusCodeSame(200);

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotFalse($decodedResponse);
        $this->assertArrayHasKey('imageId', $decodedResponse);
        $this->assertIsInt($decodedResponse['imageId']);
        $this->assertEquals($imageId, $decodedResponse['imageId']);
        $this->assertGreaterThan(0, $decodedResponse['imageId']);
        $this->assertArrayHasKey('imageUrl', $decodedResponse);
        $this->assertMatchesRegularExpression('@/img/p[/0-9]+' . $imageId . '\.jpg@', $decodedResponse['imageUrl']);
        $this->assertArrayHasKey('thumbnailUrl', $decodedResponse);
        $this->assertMatchesRegularExpression('@/img/p[/0-9]+/' . $imageId . '-small_default\.jpg@', $decodedResponse['thumbnailUrl']);
        $this->assertArrayHasKey('legends', $decodedResponse);
        $this->assertEquals([
            'en-US' => '',
            'fr-FR' => '',
        ], $decodedResponse['legends']);
        $this->assertArrayHasKey('cover', $decodedResponse);
        $this->assertIsBool($decodedResponse['cover']);
        $this->assertTrue($decodedResponse['cover']);
        $this->assertArrayHasKey('position', $decodedResponse);
        $this->assertIsInt($decodedResponse['position']);
        $this->assertEquals(1, $decodedResponse['position']);

        return $this->getImageMD5($decodedResponse);
    }

    /**
     * @depends testAddImage
     * @depends testGetImage
     *
     * @param int $imageId
     */
    public function testUpdateImage(int $imageId, string $imageMD5): int
    {
        $bearerToken = $this->getBearerToken(['product_write']);
        $uploadedImage = $this->prepareUploadedFile(__DIR__ . '/../../Resources/assets/image/Brown_bear_cushion.jpg');

        $response = static::createClient()->request('POST', '/product/image/' . $imageId, [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'content-type' => 'multipart/form-data',
            ],
            'extra' => [
                'parameters' => [
                    'legends' => [
                        'en-US' => 'legend en',
                        'fr-FR' => 'legend fr',
                    ],
                ],
                'files' => [
                    'image' => $uploadedImage,
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(200);

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotFalse($decodedResponse);
        $this->assertArrayHasKey('imageId', $decodedResponse);
        $this->assertIsInt($decodedResponse['imageId']);
        $imageId = $decodedResponse['imageId'];
        $this->assertGreaterThan(0, $decodedResponse['imageId']);
        $this->assertArrayHasKey('imageUrl', $decodedResponse);
        $this->assertMatchesRegularExpression('@/img/p[/0-9]+' . $imageId . '\.jpg@', $decodedResponse['imageUrl']);
        $this->assertArrayHasKey('thumbnailUrl', $decodedResponse);
        $this->assertMatchesRegularExpression('@/img/p[/0-9]+/' . $imageId . '-small_default\.jpg@', $decodedResponse['thumbnailUrl']);
        $this->assertArrayHasKey('legends', $decodedResponse);
        $this->assertEquals([
            'en-US' => 'legend en',
            'fr-FR' => 'legend fr',
        ], $decodedResponse['legends']);
        $this->assertArrayHasKey('cover', $decodedResponse);
        $this->assertIsBool($decodedResponse['cover']);
        $this->assertArrayHasKey('position', $decodedResponse);
        $this->assertIsInt($decodedResponse['position']);
        $this->assertEquals(1, $decodedResponse['position']);

        $newImageMD5 = $this->getImageMD5($decodedResponse);
        $this->assertNotEquals($imageMD5, $newImageMD5);

        return $imageId;
    }

    /**
     * @depends testGetProduct
     * @depends testUpdateImage
     */
    public function testListImages(int $productId, int $imageId): void
    {
        $bearerToken = $this->getBearerToken(['product_write', 'product_read']);

        // First add a new image so that we have at least to images
        $uploadedImage = $this->prepareUploadedFile(__DIR__ . '/../../Resources/assets/image/Hummingbird_cushion.jpg');
        $newImageResponse = static::createClient()->request('POST', '/product/' . $productId . '/image', [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'content-type' => 'multipart/form-data',
            ],
            'extra' => [
                'files' => [
                    'image' => $uploadedImage,
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(201);
        $newImage = json_decode($newImageResponse->getContent(), true);
        $newImageId = $newImage['imageId'];

        // Get the whole list of images
        $response = static::createClient()->request('GET', '/product/' . $productId . '/images', ['auth_bearer' => $bearerToken]);
        self::assertResponseStatusCodeSame(200);
        $productImages = json_decode($response->getContent(), true);
        $this->assertEquals(2, count($productImages));
        $this->assertEquals([
            [
                'imageId' => $imageId,
                'imageUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($imageId, false),
                'thumbnailUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($imageId, true),
                'legends' => [
                    'en-US' => 'legend en',
                    'fr-FR' => 'legend fr',
                ],
                'cover' => true,
                'position' => 1,
                'shopIds' => [
                    1,
                ],
            ],
            [
                'imageId' => $newImageId,
                'imageUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($newImageId, false),
                'thumbnailUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($newImageId, true),
                'legends' => [
                    'en-US' => '',
                    'fr-FR' => '',
                ],
                'cover' => false,
                'position' => 2,
                'shopIds' => [
                    1,
                ],
            ],
        ], $productImages);

        // Now update the second image to be the cover and have position 1
        static::createClient()->request('POST', '/product/image/' . $newImageId, [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'content-type' => 'multipart/form-data',
            ],
            'extra' => [
                'parameters' => [
                    // We use string on purpose because form data are sent by string, thus we validate here that the denormalization still
                    // works with string value (actually we only ignore the wrong type, but it works nonetheless)
                    'cover' => '1',
                    'position' => '1',
                ],
            ],
        ]);
        self::assertResponseStatusCodeSame(200);

        // Now check the updated list, the content is changed but so is the order because images are sorted by position
        $response = static::createClient()->request('GET', '/product/' . $productId . '/images', ['auth_bearer' => $bearerToken]);
        self::assertResponseStatusCodeSame(200);
        $productImages = json_decode($response->getContent(), true);
        $this->assertEquals(2, count($productImages));
        $this->assertEquals([
            [
                'imageId' => $newImageId,
                'imageUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($newImageId, false),
                'thumbnailUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($newImageId, true),
                'legends' => [
                    'en-US' => '',
                    'fr-FR' => '',
                ],
                'cover' => true,
                'position' => 1,
                'shopIds' => [
                    1,
                ],
            ],
            [
                'imageId' => $imageId,
                'imageUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($imageId, false),
                'thumbnailUrl' => 'http://myshop.com/img/p/' . $this->getImagePath($imageId, true),
                'legends' => [
                    'en-US' => 'legend en',
                    'fr-FR' => 'legend fr',
                ],
                'cover' => false,
                'position' => 2,
                'shopIds' => [
                    1,
                ],
            ],
        ], $productImages);
    }

    /**
     * @depends testGetProduct
     * @depends testListImages
     *
     * @param int $productId
     */
    public function testDeleteProduct(int $productId): void
    {
        $productsNumber = $this->getProductsNumber();
        $readBearerToken = $this->getBearerToken(['product_read']);
        // Delete product with token without write permission
        static::createClient()->request('DELETE', '/product/' . $productId, [
            'auth_bearer' => $readBearerToken,
        ]);
        self::assertResponseStatusCodeSame(403);
        // The product should still exists
        static::createClient()->request('GET', '/product/' . $productId, [
            'auth_bearer' => $readBearerToken,
        ]);
        self::assertResponseStatusCodeSame(200);

        // Delete product with proper token
        $writeBearerToken = $this->getBearerToken(['product_write']);
        $response = static::createClient()->request('DELETE', '/product/' . $productId, [
            'auth_bearer' => $writeBearerToken,
        ]);
        self::assertResponseStatusCodeSame(204);
        $this->assertEmpty($response->getContent());

        // One less products
        $this->assertEquals($productsNumber - 1, $this->getProductsNumber());

        $bearerToken = $this->getBearerToken(['product_read', 'product_write']);
        static::createClient()->request('GET', '/product/' . $productId, [
            'auth_bearer' => $bearerToken,
        ]);
        self::assertResponseStatusCodeSame(404);
    }

    protected function getProductsNumber(): int
    {
        /** @var ProductQueryBuilder $productQueryBuilder */
        $productQueryBuilder = $this->getContainer()->get('prestashop.core.grid.query_builder.product');
        $queryBuilder = $productQueryBuilder->getCountQueryBuilder(new ProductFilters(ShopConstraint::allShops(), ProductFilters::getDefaults(), ProductGridDefinitionFactory::GRID_ID));

        return (int) $queryBuilder->executeQuery()->fetchOne();
    }

    protected function getImagePath(int $imageId, bool $isThumbnail): string
    {
        return implode('/', str_split((string) $imageId)) . '/' . $imageId . ($isThumbnail ? '-small_default' : '') . '.jpg';
    }

    protected function getImageMD5(array $image): string
    {
        $matches = [];
        $imageId = $image['imageId'];
        preg_match('@/p/[/0-9]+' . $imageId . '\.jpg@', $image['imageUrl'], $matches);
        $imageFilePath = _PS_IMG_DIR_ . $matches[0];
        $this->assertTrue(file_exists($imageFilePath));

        return md5_file($imageFilePath);
    }
}
