<?php

declare(strict_types=1);

namespace Tests\Unit\Client\DataDog;

use App\Client\DataDog\DataDogFilter;
use Tests\UnitTestCase;

class DataDogFilterUnitTest extends UnitTestCase
{
    public function testCanBeCreatedFromJsonString(): void
    {
        $result = DataDogFilter::fromJsonString(
            '{"filter":{"query":"@environment.name:prod -status:(warn OR info) @environment.type:telenor @request.method:POST @request.url:\"/orders\" @info.message:\"Couldn\'t create model ORDERS.\"","from":1646548674837,"to":1649140674837}}'
        );

        $expected = [
            'query' => "@environment.name:prod -status:(warn OR info) @environment.type:telenor @request.method:POST @request.url:\"/orders\" @info.message:\"Couldn't create model ORDERS.\"",
            // Dates should be rounded
            'from' => 1646548675000,
            'to' => 1649140675000,
        ];

        $this->assertEquals($expected, $result->jsonSerialize());
    }

    public function testCannotBeCreatedFromInvalidJsonString(): void
    {
        $this->expectException(\Exception::class);

        DataDogFilter::fromJsonString('invalid');
    }
}