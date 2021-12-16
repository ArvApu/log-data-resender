<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\LogParser\ParsedLog;
use App\LogParser\Parser\BackofficeLogParser;
use Tests\TestCase;

class BackofficeLogParserTest extends TestCase
{
    public function testParse(): void
    {
        $parser = new BackofficeLogParser();

        $data = file_get_contents($this->getUnitFixturesDir('bo-logs-mock.json'));

        if ($data === false) {
            $this->fail('Missing data fixture.');
        }

        $results  = $parser->parse(json_decode($data, true));
        $expected = $this->getExpectedResults();

        $this->assertContainsOnlyInstancesOf(ParsedLog::class, $results);
        $this->assertEquals($expected, $results);

    }

    private function getExpectedResults(): array
    {
        return [
            new ParsedLog(
                [
                    'attributes' => json_encode([
                        [
                            'source' => 'Extenda GO POS',
                        ]
                    ]),
                    'coordinate_precision' => '',
                    'coupons' => '[]',
                    'currency' => 'NOK',
                    'customer_id' => '',
                    'discounts' => '[]',
                    'dynamic_values' => '{}',
                    'email' => '',
                    'fees' => '[]',
                    'grant_purchased_coupons' => '',
                    'id' => '89B72460-6D6F-4C4F-9CA0-46D15FFE4CD6',
                    'is_paid' => '1',
                    'is_preorder' => '0',
                    'lat' => '',
                    'lng' => '',
                    'order_line_items' => json_encode([
                        [
                            'addon_line_items' => json_encode([
                                [
                                    'addon_line_items' => '[]',
                                    'attributes' => json_encode([
                                        [
                                            'display_sequence_number' => '1639440898.021',
                                        ],
                                        [
                                            'vat_rate_id' => '30223f05-d3ec-8dad-e5ab-40f999e64a68',
                                        ],
                                    ]),
                                    'cost_price' => 200,
                                    'discounts' => '[]',
                                    'dynamic_values' => '{}',
                                    'effective_amount' => 200,
                                    'effective_sales_tax_rate' => null,
                                    'effective_vat_rate' => 0,
                                    'external_product' => 0,
                                    'id' => '691ED72D-705B-49C7-81F0-F54EDD242B7E',
                                    'product_category_id' => '02f33437-7462-0e01-29db-c4fd75c91f47',
                                    'product_category_name' => 'E - Kald drikke',
                                    'product_id' => 'ac834e26-68f5-8b6e-cc32-931895288811',
                                    'product_name' => 'Pant kr 2',
                                    'product_variant_id' => null,
                                    'product_variant_name' => null,
                                    'quantity' => 6,
                                    'retail_price' => 200,
                                    'sales_tax_amount' => null,
                                    'sales_tax_rate' => null,
                                    'sequence_number' => null,
                                    'sku' => '1977777',
                                    'stock_location_id' => 'f668e284-ff5e-a317-a0b5-c016a42d919f',
                                    'total_line_amount' => 1200,
                                    'type_id' => null,
                                    'unit_id' => 'pc',
                                    'user_id' => null,
                                    'user_name' => null,
                                    'vat_amount' => 0,
                                    'vat_rate' => 0,
                                ]
                            ]),
                            'attributes' => json_encode([
                                [
                                    'display_sequence_number' => '1639440898.021',
                                ],
                                [
                                    'vat_rate_id' => 'c5f1aee5-c9ea-5661-ae3e-9f9e45298b3e',
                                ],
                            ]),
                            'cost_price' => 1508,
                            'discounts' => '[]',
                            'dynamic_values' => '{}',
                            'effective_amount' => 3800,
                            'effective_sales_tax_rate' => null,
                            'effective_vat_rate' => 0.15,
                            'external_product' => 0,
                            'id' => '7BD0551F-611E-4783-8B3F-AE2E2622C083',
                            'product_category_id' => '02f33437-7462-0e01-29db-c4fd75c91f47',
                            'product_category_name' => 'E - Kald drikke',
                            'product_id' => '7f9be935-be8e-2933-ebfe-35373ae2bdb2',
                            'product_name' => 'Imsdal',
                            'product_variant_id' => null,
                            'product_variant_name' => null,
                            'quantity' => 6,
                            'retail_price' => 3800,
                            'sales_tax_amount' => null,
                            'sales_tax_rate' => null,
                            'sequence_number' => null,
                            'sku' => '7044610778105',
                            'stock_location_id' => 'f668e284-ff5e-a317-a0b5-c016a42d919f',
                            'total_line_amount' => 22800,
                            'type_id' => 400,
                            'unit_id' => 'pc',
                            'user_id' => null,
                            'user_name' => null,
                            'vat_amount' => 2974,
                            'vat_rate' => 0.15,
                        ],
                    ]),
                    'register_id' => '3087610d-d174-6132-7b46-9c9f00cf36d7',
                    'register_name' => '1',
                    'sales_tax_label' => 'Kunstavgift',
                    'shipping_information' => '',
                    'shop_id' => 'da909dc2-9353-69f9-0667-5d1a86c9fa9e',
                    'shop_name' => '726',
                    'timestamp' => '1639440916',
                    'transactions' => json_encode([
                        [
                            'additional_data' => null,
                            'attributes' => '[]',
                            'base_currency' => 'NOK',
                            'base_currency_amount' => 4000,
                            'card_type' => null,
                            'currency' => 'NOK',
                            'currency_amount' => 4000,
                            'debitor_account_id' => null,
                            'dynamic_values' => '{}',
                            'id' => '0EB34BAD-3A0B-40BC-BA87-9C151B67D107',
                            'order_id' => '89B72460-6D6F-4C4F-9CA0-46D15FFE4CD6',
                            'state' => 'WM_TRANSACTION_STATE_CAPTURED',
                            'surcharge_amount' => 0,
                            'tender_type_id' => '98f3d740-429f-382b-8b72-3a74f4575648',
                            'type' => 'WM_TRANSACTION_TYPE_MISCELLANEOUS',
                        ],
                    ]),
                    'user_id' => 'd8b8c814-5953-52bb-a94b-5db88611ca7e',
                    'user_name' => 'GA10040',
                    'vat_label' => 'mva.',
                ],
                'POST',
                'https://localhost.test/orders',
                'test-d7e7-d593-70f3-694794e80f05',
            ),
        ];
    }
}