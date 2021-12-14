<?php

declare(strict_types=1);

namespace App\LogParsers;

class ParsedLog implements \JsonSerializable
{
    public function __construct(
        private array $body,
        private string $method,
        private string $url,
        private ?string $masterUserId = null,
    ) {
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function jsonSerialize(): array
    {
        return $this->getBody();
    }

    public function getMasterUserId(): ?string
    {
        return $this->masterUserId;
    }
}