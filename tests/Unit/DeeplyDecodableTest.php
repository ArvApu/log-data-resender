<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\LogParser\DeeplyDecodable;
use Tests\TestCase;

class DeeplyDecodableTest extends TestCase
{
    public function testDecodeParametersFromObject(): void
    {
        $logParser = new class {
            use DeeplyDecodable;

            public function parse(array $events): array
            {
                /*
                 * Method 'decodeParametersFromObject' is protected, but it is critical to have it tested,
                 * so we give access to it via public method.
                 */
                return $this->decodeParametersFromObject($events);
            }
        };

        $result = $logParser->parse($this->getTestData());

        $this->assertEquals($this->getExpectedData(), $result);
    }

    private function getTestData(): array
    {
        return [
            'Value_1' => 'Some basic string',
            'Value_2' => [
                'Array_val1',
                'Array_val2',
            ],
            'Value_3' => [
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
            ],
            'Value_4' => [
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
                'Array_val1',
                'Array_val2',
            ],
            'Value_5' => '[]',
            'Value_6' => [],
            'Value_7' => '{"Assoc_array_val1":"Some basic string","Assoc_array_val2":"Some basic string"}',
            'Value_8' => '{"0":"Deeper","Value_6_deeper":"{\"Assoc_array_val1\":\"Some basic string\",\"Assoc_array_val2\":\"Some basic string\"}"}',
            'Value_9' => 10,
            'Value_10' => true,
            'Value_11' => (object) [
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
            ],
            'Value_12' => [
                (object) [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ],
                (object) [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ]
            ],
            'Value_13' => [
                [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ],
                [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ]
            ],
            'Value_14' => json_encode([
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
            ]),
            'Value_15' => json_encode([
                'Value_6_deeper' => json_encode([
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => json_encode([
                        'Assoc_array_val1' => 10,
                        'Assoc_array_val2' => false,
                    ]),
                ]),
            ]),
        ];
    }

    private function getExpectedData(): array
    {
        return [
            'Value_1' => 'Some basic string',
            'Value_2' => [
                'Array_val1',
                'Array_val2',
            ],
            'Value_3' => [
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
            ],
            'Value_4' => [
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
                'Array_val1',
                'Array_val2',
            ],
            'Value_5' => [],
            'Value_6' => [],
            'Value_7' => [
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
            ],
            'Value_8' => [
                'Deeper',
                'Value_6_deeper' => [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ],
            ],
            'Value_9' => 10,
            'Value_10' => true,
            'Value_11' => (object)[
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
            ],
            'Value_12' => [
                (object)[
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ],
                (object)[
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ],
            ],
            'Value_13' => [
                [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ],
                [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => 'Some basic string',
                ],
            ],
            'Value_14' => [
                'Assoc_array_val1' => 'Some basic string',
                'Assoc_array_val2' => 'Some basic string',
            ],
            'Value_15' => [
                'Value_6_deeper' => [
                    'Assoc_array_val1' => 'Some basic string',
                    'Assoc_array_val2' => [
                        'Assoc_array_val1' => 10,
                        'Assoc_array_val2' => false,
                    ],
                ],
            ],
        ];
    }
}
