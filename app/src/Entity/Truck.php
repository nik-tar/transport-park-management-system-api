<?php

namespace App\Entity;

use App\DTO\Entity\DetailsInterface;
use App\DTO\Entity\TruckDetails;
use App\Entity\Contract\DetailedEntityInterface;
use App\Entity\Contract\ServiceableInterface;
use App\Enum\ServiceOrderSubject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'truck')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('plate')]
class Truck implements ServiceableInterface, DetailedEntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    private ?Ulid $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(min: 2, max: 128)]
    private ?string $model = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(min: 2, max: 128)]
    private ?string $brand = null;

    #[ORM\Column(length: 64, unique: true)]
    #[Assert\Length(min: 2, max: 64)]
    private ?string $plate = null;

    #[ORM\OneToOne(mappedBy: 'truck')]
    private ?FleetSet $fleetSet = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $inService;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getPlate(): ?string
    {
        return $this->plate;
    }

    public function setPlate(string $plate): static
    {
        $this->plate = $plate;

        return $this;
    }

    public function getFleetSet(): ?FleetSet
    {
        return $this->fleetSet;
    }

    public function isInService(): bool
    {
        return $this->inService;
    }

    public function setInService(bool $inService): static
    {
        $this->inService = $inService;

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

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getSubjectType(): ServiceOrderSubject
    {
        return ServiceOrderSubject::Truck;
    }

    /**
     * @return TruckDetails
     */
    public function getDetails(): DetailsInterface
    {
        return new TruckDetails(
            model: $this->model,
            brand: $this->brand,
            plateNumber: $this->plate,
            isInService: $this->inService,
        );
    }
}
