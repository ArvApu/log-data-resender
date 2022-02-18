<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\LogsParser\LogsParser;
use App\LogsParser\ParsedLog;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\LogsParser\LogsParser
 */
class LogsParserTest extends TestCase
{
    private LogsParser $parser;

    public function setUp(): void
    {
        $this->parser = new LogsParser();
    }

    /**
     * @covers ::parse
     * @dataProvider logsParserTestDataProvider
     *
     * @param array $expected
     * @param array $logs
     * @param string|null $parseType
     *
     * @return void
     */
    public function testParse(array $logs, array $expected, ?string $parseType = null): void
    {
        if ($parseType !== null) {
            $this->parser->setParsingStrategy($parseType);
        }

        $results = $this->parser->parse($logs);

        $this->assertEquals($results, $expected);
    }

    public function logsParserTestDataProvider(): array
    {
        $data = [
            'Is able to parse backoffice logs' => [
                'logs' => json_decode(file_get_contents($this->getUnitFixturesDir('bo-logs-mock.json')), true),
                'expected' => [
                    new ParsedLog(
                        json_encode([
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
                        ]),
                        'POST',
                        'https://localhost.test/orders',
                        '89B72460-6D6F-4C4F-9CA0-46D15FFE4CD6',
                        'test-d7e7-d593-70f3-694794e80f05',
                    ),
                ],
                'parseType' => LogsParser::BO_LOG_TYPE_PARSER,
            ],
            'Is able to parse pos logs' => [
                'logs' => json_decode(file_get_contents($this->getUnitFixturesDir('pos-logs-mock.json')), true),
                'expected' => [
                    new ParsedLog(
                        json_encode([
                            'attributes' => json_encode([
                                [
                                    'is_shared_bill' => '1',
                                ],
                                [
                                    'register_sequence_number' => '1'
                                ],
                                [
                                    'shared_bill_description' => '5'
                                ],
                                [
                                    'source' => 'Extenda GO POS'
                                ],
                                [
                                    'to_go' => '0'
                                ],
                            ]),
                            'coordinate_precision' => '',
                            'coupons' => '[]',
                            'currency' => '',
                            'customer_id' => '',
                            'discounts' => '[]',
                            'email' => '',
                            'fees' => '[]',
                            'grant_purchased_coupons' => '',
                            'id' => '53FECB42-70E6-4DC8-B784-DE580BF4AEEB',
                            'is_paid' => '0',
                            'is_preorder' => '1',
                            'lat' => '',
                            'lng' => '',
                            'order_line_items' => json_encode([
                                [
                                    'addon_line_items' => '[]',
                                    'attributes' => json_encode([
                                        [
                                            'display_sequence_number' => '1639440746.269'
                                        ],
                                        [
                                            'vat_rate_id' => '72bd8101-3b65-f9fd-d260-81685cc5c522'
                                        ],
                                    ]),
                                    'comment' => 'Test',
                                    'cost_price' => 0,
                                    'discounts' => '[]',
                                    'effective_amount' => 19900,
                                    'effective_sales_tax_rate' => null,
                                    'effective_vat_rate' => 0.25,
                                    'external_product' => 0,
                                    'id' => '2118E257-8E36-4781-9C0F-D1831E3AF4C1',
                                    'product_category_id' => '1e9379e3-b5b0-526b-85a5-426ea3bc2afa',
                                    'product_category_name' => 'Varmretter',
                                    'product_id' => '98f879fb-be1a-83b9-5536-208d9877600e',
                                    'product_name' => '24. Kylling Gongbao',
                                    'product_variant_id' => null,
                                    'product_variant_name' => null,
                                    'quantity' => 1,
                                    'retail_price' => 19900,
                                    'sales_tax_amount' => null,
                                    'sales_tax_rate' => null,
                                    'sequence_number' => null,
                                    'sku' => '24',
                                    'stock_location_id' => 'eae4b36c-2d80-34a1-9b5d-d5374432bef0',
                                    'total_line_amount' => 19900,
                                    'type_id' => 400,
                                    'unit_id' => 'pc',
                                    'user_id' => null,
                                    'user_name' => null,
                                    'vat_amount' => 3980,
                                    'vat_rate' => 0.25,
                                ],
                                [
                                    'addon_line_items' => '[]',
                                    'attributes' => json_encode([
                                        [
                                            'display_sequence_number' => '1639440760.201',
                                        ],
                                        [
                                            'vat_rate_id' => '72bd8101-3b65-f9fd-d260-81685cc5c522',
                                        ],
                                    ]),
                                    'comment' => null,
                                    'cost_price' => 0,
                                    'discounts' => '[]',
                                    'effective_amount' => 4200,
                                    'effective_sales_tax_rate' => null,
                                    'effective_vat_rate' => 0.25,
                                    'external_product' => 0,
                                    'id' => 'FC451A12-4EAC-4C32-A013-BE19132CB697',
                                    'product_category_id' => '95e3b1ed-b9d8-b7e2-67b3-e9c5c55c507c',
                                    'product_category_name' => 'Nigiri 2 Biter',
                                    'product_id' => 'b4d5cf58-a94f-1ccd-ad87-43d4db3cb823',
                                    'product_name' => '154. Scampi Nigiri',
                                    'product_variant_id' => null,
                                    'product_variant_name' => null,
                                    'quantity' => 1,
                                    'retail_price' => 4200,
                                    'sales_tax_amount' => null,
                                    'sales_tax_rate' => null,
                                    'sequence_number' => null,
                                    'sku' => '154',
                                    'stock_location_id' => 'eae4b36c-2d80-34a1-9b5d-d5374432bef0',
                                    'total_line_amount' => 4200,
                                    'type_id' => 400,
                                    'unit_id' => 'pc',
                                    'user_id' => null,
                                    'user_name' => null,
                                    'vat_amount' => 840,
                                    'vat_rate' => 0.25,
                                ],
                            ]),
                            'register_id' => 'fe3c928a-5415-476d-71ed-95aa69480cb8',
                            'register_name' => 'Kasse Restaurant 1',
                            'sales_tax_label' => '',
                            'shipping_information' => '',
                            'shop_id' => '27b00b07-fef5-5991-c146-69fba92c301f',
                            'shop_name' => 'ABC Restaurant',
                            'timestamp' => '1639440764',
                            'transactions' => '[]',
                            'user_id' => '',
                            'user_name' => 'Faker Maker',
                            'vat_label' => '',
                        ]),
                        'POST',
                        'https://localhost.test/orders',
                        '53FECB42-70E6-4DC8-B784-DE580BF4AEEB',
                        'test-cf76-58f5-1601-cb124f0b4dc7',
                    ),
                    new ParsedLog(
                        json_encode([
                            'attributes' => '[]',
                            'comment' => '',
                            'currency' => 'NOK',
                            'deposited_gift_card' => '',
                            'deposited_voucher' => json_encode([
                                [
                                    'amount' => 0,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 0,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'diff_card' => json_encode([
                                [
                                    'amount' => 0,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 0,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'diff_cash' => json_encode([
                                [
                                    'amount' => 0,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 0,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'diff_miscellaneous' => json_encode([
                                [
                                    'amount' => 0,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 0,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'dynamic_values' => '{}',
                            'email' => '',
                            'end_bank' => json_encode([
                                [
                                    'amount' => 0,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 0,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'end_card' => json_encode([
                                [
                                    'amount' => 279900,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 279900,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'end_cash' => json_encode([
                                [
                                    'amount' => 216100,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 216100,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'end_dintero' => '',
                            'end_gift_card' => '',
                            'end_invoice' => '',
                            'end_loyalty_points' => json_encode([
                                [
                                    'amount' => 0,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 0,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'end_miscellaneous' => json_encode([
                                [
                                    'amount' => 8000,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 8000,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'end_mobile_pay' => '',
                            'end_time' => '1631365841',
                            'end_user_id' => '05f40180-9521-8623-a47d-066a7e059fee',
                            'end_vipps' => '',
                            'end_voucher' => json_encode([
                                [
                                    'amount' => 0,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 0,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'id' => 'A00A880C-2CB4-475F-8C1D-F4E76C2A409F',
                            'register_id' => 'e80b8c1c-f4c9-bd8c-ba78-9f49f45017fc',
                            'shop_id' => '328d2de2-e69e-72f8-b6a1-a12ea4d88c7f',
                            'start_cash' => json_encode([
                                [
                                    'amount' => 216100,
                                    'base_currency' => 'NOK',
                                    'base_currency_amount' => 216100,
                                    'currency' => 'NOK',
                                    'denomination' => null,
                                ],
                            ]),
                            'start_time' => '1631346130',
                            'start_user_id' => '05f40180-9521-8623-a47d-066a7e059fee',
                        ]),
                        'PUT',
                        'https://localhost.test/day_tallies/A00A880C-2CB4-475F-8C1D-F4E76C2A409F',
                        'A00A880C-2CB4-475F-8C1D-F4E76C2A409F',
                        'e60a9592-204a-4a85-f8f3-d20881864f78'
                    ),
                ],
                'parseType' => LogsParser::POS_LOG_TYPE_PARSER,
            ],
            'Is able to parse data dog logs' => [
                'logs' => json_decode(file_get_contents($this->getUnitFixturesDir('dd-logs-mock.json')), true),
                'expected' => [
                    new ParsedLog(
                        json_encode([
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
                        ]),
                        'PUT',
                        'https://localhost.test/day_tallies',
                        '89B72460-6D6F-4C4F-9CA0-46D15FFE4CD6',
                        'test-d7e7-d593-70f3-694794e80f05',
                    ),
                ],
                'parseType' => LogsParser::DD_LOG_TYPE_PARSER,
            ],
        ];

        $key = 'Is able to parse mixed logs';

        $data[$key] = [
            'logs' => [],
            'expected' => [],
        ];

        foreach ($data as $datum) {
            $data[$key]['logs'] = array_merge($data[$key]['logs'], $datum['logs']);
            $data[$key]['expected'] = array_merge($data[$key]['expected'], $datum['expected']);
        }

        return $data;
    }
}