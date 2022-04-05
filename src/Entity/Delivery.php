<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=DeliveryRepository::class)
 */
class Delivery
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups("api_deliveries_list")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("api_deliveries_list")
     */
    private $merchandise;

    /**
     * @ORM\Column(type="float")
     * @Groups("api_deliveries_list")     
     */
    private $volume;

    /**
     * @ORM\Column(type="string", length=510, nullable=true)
     * @Groups("api_deliveries_list")
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="deliveries")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("api_deliveries_list") 
     */
    private $customer;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="deliveriesCreatedByAdmin")
     * @ORM\JoinColumn(nullable=false)
     */
    private $admin;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="deliveriesCarriedByDriver")
     * @ORM\JoinColumn(nuldeliveries_pending_list     
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
