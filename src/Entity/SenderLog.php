<?php

namespace App\Entity;

use App\Repository\SenderLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SenderLogRepository::class)]
class SenderLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $modelId = null;

    #[ORM\Column]
    private ?int $failedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $masterUserId = null;

    #[ORM\Column(length: 255)]
    private ?string $session = null;

    #[ORM\Column(nullable: true)]
    private array $responseData = [];

    #[ORM\OneToOne(inversedBy: 'senderLog', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Log $log = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModelId(): ?string
    {
        return $this->modelId;
    }

    public function setModelId(string $modelId): static
    {
        $this->modelId = $modelId;

        return $this;
    }

    public function getFailedAt(): ?int
    {
        return $this->failedAt;
    }

    public function setFailedAt(int $failedAt): static
    {
        $this->failedAt = $failedAt;

        return $this;
    }

    public function getMasterUserId(): ?string
    {
        return $this->masterUserId;
    }

    public function setMasterUserId(?string $masterUserId): static
    {
        $this->masterUserId = $masterUserId;

        return $this;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(string $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }

    public function setResponseData(array $responseData): static
    {
        $this->responseData = $responseData;

        return $this;
    }

    public function getLog(): ?Log
    {
        return $this->log;
    }

    public function setLog(Log $log): static
    {
        $this->log = $log;

        return $this;
    }
}
