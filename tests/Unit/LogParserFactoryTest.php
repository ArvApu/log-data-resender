<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\LogsParser\LogParserFactory;
use App\LogsParser\LogTypeParser\BackofficeLogTypeParser;
use App\LogsParser\LogTypeParser\POSLogTypeParser;
use Tests\TestCase;

class LogParserFactoryTest extends TestCase
{
    private LogParserFactory $factory;

    public function setUp(): void
    {
        $this->factory = new LogParserFactory();
    }

    public function testAbleToReturnBackofficeLogParser(): void
    {
        $this->assertInstanceOf(
            BackofficeLogTypeParser::class,
            $this->factory->getParser(LogParserFactory::BO_LOG_PARSER_TYPE),
        );
    }

    public function testAbleToReturnPOSLogParser(): void
    {
        $this->assertInstanceOf(
            POSLogTypeParser::class,
            $this->factory->getParser(LogParserFactory::POS_LOG_PARSER_TYPE),
        );
    }

    public function testAbleToReturnDefaultLogParser(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->getParser('some_non_existent_name_to_get_default_value');

        $this->assertEquals('Unsupported log parser type.', $this->getExpectedExceptionMessage());
    }
}