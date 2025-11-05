<?php

namespace App\Entity;

use App\Enum\ServiceOrderStatus;
use App\Enum\ServiceOrderSubject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_order')]
class ServiceOrder
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    private ?Ulid $id = null;

    #[ORM\Column(enumType: ServiceOrderStatus::class)]
    private ?ServiceOrderStatus $status = null;

    #[ORM\Column(enumType: ServiceOrderSubject::class)]
    private ?ServiceOrderSubject $subjectType = null;

    #[ORM\Column(type: 'ulid')]
    #[Assert\Ulid]
    private ?Ulid $subjectId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getStatus(): ?ServiceOrderStatus
    {
        return $this->status;
    }

    public function setStatus(ServiceOrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSubjectType(): ?ServiceOrderSubject
    {
        return $this->subjectType;
    }

    public function setSubjectType(ServiceOrderSubject $subjectType): static
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function getSubjectId(): ?Ulid
    {
        return $this->subjectId;
    }

    public function setSubjectId(Ulid $subjectId): static
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
