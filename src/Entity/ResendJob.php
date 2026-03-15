<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\Enum\ResendJobStatus;
use App\Repository\ResendJobRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResendJobRepository::class)]
class ResendJob
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $status = ResendJobStatus::QUEUED->value;

    #[ORM\Column(length: 255)]
    private string $source;

    #[ORM\Column(length: 255)]
    private string $parser;

    #[ORM\Column(type: 'json')]
    private array $modifiers = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $filter = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $filterFilePath = null;

    #[ORM\Column(type: 'json')]
    private array $counts = [];

    #[ORM\Column]
    private int $processedCount = 0;

    #[ORM\Column(nullable: true)]
    private ?int $totalCount = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ResendJobStatus
    {
        return ResendJobStatus::from($this->status);
    }

    public function setStatus(ResendJobStatus $status): static
    {
        $this->status = $status->value;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getParser(): string
    {
        return $this->parser;
    }

    public function setParser(string $parser): static
    {
        $this->parser = $parser;

        return $this;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function setModifiers(array $modifiers): static
    {
        $this->modifiers = $modifiers;

        return $this;
    }

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function setFilter(?string $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFilterFilePath(): ?string
    {
        return $this->filterFilePath;
    }

    public function setFilterFilePath(?string $filterFilePath): static
    {
        $this->filterFilePath = $filterFilePath;

        return $this;
    }

    public function getCounts(): array
    {
        return $this->counts;
    }

    public function setCounts(array $counts): static
    {
        $this->counts = $counts;

        return $this;
    }

    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    public function setProcessedCount(int $processedCount): static
    {
        $this->processedCount = $processedCount;

        return $this;
    }

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function setTotalCount(?int $totalCount): static
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
