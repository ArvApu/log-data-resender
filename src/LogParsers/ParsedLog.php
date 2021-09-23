<?php

declare(strict_types=1);

namespace App\LogParsers;

class ParsedLog implements \JsonSerializable
{
    private array $body;
    private string $method;
    private string $url;
    private ?string $masterUserId;

    /**
     * @param array $body
     * @param string $method
     * @param string $url
     * @param string|null $masterUserId
     */
    public function __construct(array $body, string $method, string $url, ?string $masterUserId = null)
    {
        $this->body = $body;
        $this->method = $method;
        $this->url = $url;
        $this->masterUserId = $masterUserId;
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