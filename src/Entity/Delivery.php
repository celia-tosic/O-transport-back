<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DeliveryRepository::class)
 */
class Delivery
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"api_deliveries_details", "api_delivery_deleted", "api_driver_deliveries"})
     * @Groups("api_deliveries_list")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups("api_driver_deliveries")
     * @Groups({"api_deliveries_list", "api_deliveries_details"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("api_driver_deliveries")
     * @Groups({"api_deliveries_list", "api_deliveries_details", "api_delivery_deleted"})
     * @Assert\NotBlank(message="Le nom de la marchandise ne peut être vide")
     */
    private $merchandise;

    /**
     * @ORM\Column(type="float")
     * @Groups("api_driver_deliveries")      
     * @Groups({"api_deliveries_list", "api_deliveries_details","api_delivery_deleted"})
     * @Assert\NotBlank(message="Le type de marchandise ne peut être vide")
     * @Assert\Range(
     *      min = 1,
     *      max = 20,
     *      notInRangeMessage = "Le volume doit être compris entre 1 et 20 m³",
     * )
     */
    private $volume;

    /**
     * @ORM\Column(type="string", length=510, nullable=true)
     * @Groups("api_deliveries_list")
     * @Groups({"api_deliveries_list", "api_deliveries_details", "api_delivery_deleted"})
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("api_deliveries_list")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("api_deliveries_list")
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="deliveries")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("api_driver_deliveries")
     * @Groups({"api_deliveries_list", "api_deliveries_details"}) 
     */
    private $customer;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="deliveriesCreatedByAdmin")
     * @ORM\JoinColumn(nullable=true)
     */
    private $admin;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="deliveriesCarriedByDriver")
     * @ORM\JoinColumn(nullable=true, onDelete = "SET NULL")
     * @Groups({"api_deliveries_list", "api_deliveries_details"})
     */
    private $driver;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getMerchandise(): ?string
    {
        return $this->merchandise;
    }

    public function setMerchandise(string $merchandise): self
    {
        $this->merchandise = $merchandise;

        return $this;
    }

    public function getVolume(): ?float
    {
        return $this->volume;
    }

    public function setVolume(float $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

}
