<?php

declare(strict_types=1);

namespace Tests\Unit\Service\LogsParser;

use App\Service\LogsParser\ParsedLog;
use Tests\UnitTestCase;

class ParsedLogTest extends UnitTestCase
{
    private ParsedLog $fakeParsedLog;

    public function setUp(): void
    {
        $this->fakeParsedLog = new ParsedLog(
            json_encode(['foo' => 'bar']),
            'GET',
            '127.0.0.1/fake/test',
            'fake_model_id',
            'faker_master_user_id',
        );
    }

    public function testGetBody(): void
    {
        $this->assertEquals(json_encode(['foo' => 'bar']), $this->fakeParsedLog->getBody());
    }

    public function testGetMethod(): void
    {
        $this->assertEquals('GET', $this->fakeParsedLog->getMethod());
    }

    public function testGetUrl(): void
    {
        $this->assertEquals('127.0.0.1/fake/test', $this->fakeParsedLog->getUrl());
    }

    public function testGetModelId(): void
    {
        $this->assertEquals('fake_model_id', $this->fakeParsedLog->getModelId());
    }

    public function testGetMasterUserId(): void
    {
        $this->assertEquals('faker_master_user_id', $this->fakeParsedLog->getMasterUserId());
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                'body' => json_encode(['foo' => 'bar']),
                'method' => 'GET',
                'url' => '127.0.0.1/fake/test',
                'model_id' => 'fake_model_id',
                'master_user_id' => 'faker_master_user_id',
            ],
            $this->fakeParsedLog->jsonSerialize()
        );
    }
}