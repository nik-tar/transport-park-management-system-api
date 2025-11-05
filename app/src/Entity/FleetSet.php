<?php

namespace App\Entity;

use App\DTO\Entity\DetailsInterface;
use App\DTO\Entity\FleetSetDetails;
use App\Entity\Contract\DetailedEntityInterface;
use App\Entity\Contract\ServiceableInterface;
use App\Enum\ServiceOrderSubject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'fleet_set')]
#[ORM\HasLifecycleCallbacks]
class FleetSet implements ServiceableInterface, DetailedEntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    private ?Ulid $id = null;

    /**
     * @var Collection<int, Driver>
     */
    #[ORM\ManyToMany(targetEntity: Driver::class, mappedBy: 'fleetSets')]
    #[Assert\Count(max: 2, maxMessage: 'You can select at most 2 drivers')]
    private Collection $drivers;

    #[ORM\OneToOne(inversedBy: 'fleetSet', cascade: ['persist'])]
    #[ORM\JoinColumn(unique: true, nullable: false)]
    private Truck $truck;

    #[ORM\OneToOne(inversedBy: 'fleetSet', cascade: ['persist'])]
    #[ORM\JoinColumn(unique: true, nullable: false)]
    private Trailer $trailer;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        Truck $truck,
        Trailer $trailer
    ) {
        $this->drivers = new ArrayCollection();
        $this->truck = $truck;
        $this->trailer = $trailer;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Driver>
     */
    public function getDrivers(): Collection
    {
        return $this->drivers;
    }

    public function addDriver(Driver $driver): static
    {
        if (!$this->drivers->contains($driver)) {
            $this->drivers->add($driver);
            $driver->addFleetSet($this);
        }

        return $this;
    }

    public function removeDriver(Driver $driver): static
    {
        if ($this->drivers->removeElement($driver)) {
            $driver->removeFleetSet($this);
        }

        return $this;
    }

    public function getTruck(): Truck
    {
        return $this->truck;
    }

    public function getTrailer(): Trailer
    {
        return $this->trailer;
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
        return ServiceOrderSubject::FleetSet;
    }

    public function getDetails(): DetailsInterface
    {
        $truck = $this->getTruck();
        $trailer = $this->getTrailer();

        return new FleetSetDetails(
            truckId: $truck->getId()->toString(),
            trailerId: $trailer->getId()->toString(),
            driversCount: $this->getDrivers()->count(),
            isInService: $truck->isInService() && $trailer->isInService(),
        );
    }
}
