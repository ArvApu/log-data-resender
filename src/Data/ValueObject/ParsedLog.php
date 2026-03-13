<?php

declare(strict_types=1);

namespace App\Data\ValueObject;

use JetBrains\PhpStorm\ArrayShape;

final readonly class ParsedLog implements \JsonSerializable
{
    public function __construct(
        private string $body,
        private string $method,
        private string $url,
        private string $modelId,
        private ?string $masterUserId = null,
        private bool $isSecuredForPost = true,
    ) {
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getModelId(): string
    {
        return $this->modelId;
    }

    public function getMasterUserId(): ?string
    {
        return $this->masterUserId;
    }

    public function isSecuredForPost(): bool
    {
        return $this->isSecuredForPost;
    }

    /** @phpstan-ignore-next-line */
    #[ArrayShape([
        'body' => 'string',
        'method' => 'string',
        'url' => 'string',
        'model_id' => 'string',
        'master_user_id' => 'null|string',
    ])]
    public function jsonSerialize(): array
    {
        return [
            'body' => $this->getBody(),
            'method' => $this->getMethod(),
            'url' => $this->getUrl(),
            'model_id' => $this->getModelId(),
            'master_user_id' => $this->getMasterUserId(),
        ];
    }
}
