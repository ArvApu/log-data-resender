<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\LogParser\ParsedLog;
use Tests\TestCase;

class ParsedLogTest extends TestCase
{
    private ParsedLog $fakeParsedLog;

    public function setUp(): void
    {
        $this->fakeParsedLog = new ParsedLog(
            ['foo' => 'bar'],
            'GET',
            '127.0.0.1/fake/test',
            'faker_master_user_id',
        );
    }

    public function testGetBody(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->fakeParsedLog->getBody());
    }

    public function testGetMethod(): void
    {
        $this->assertEquals('GET', $this->fakeParsedLog->getMethod());
    }

    public function testGetUrl(): void
    {
        $this->assertEquals('127.0.0.1/fake/test', $this->fakeParsedLog->getUrl());
    }

    public function testGetMasterUserId(): void
    {
        $this->assertEquals('faker_master_user_id', $this->fakeParsedLog->getMasterUserId());
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->fakeParsedLog->jsonSerialize());
    }
}