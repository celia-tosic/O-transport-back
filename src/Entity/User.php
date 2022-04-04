<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("api_drivers_list")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("api_drivers_list")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups("api_drivers_list")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups("api_drivers_list")
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups("api_drivers_list")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=Delivery::class, mappedBy="admin")
     */
    private $deliveriesCreatedByAdmin;

    /**
     * @ORM\OneToMany(targetEntity=Delivery::class, mappedBy="driver")
     */
    private $deliveriesCarriedByDriver;

    public function __construct()
    {
        $this->deliveriesCreatedByAdmin = new ArrayCollection();
        $this->deliveriesCarriedByDriver = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
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

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveriesCreatedByAdmin(): Collection
    {
        return $this->deliveriesCreatedByAdmin;
    }

    public function addDeliveriesCreatedByAdmin(Delivery $deliveriesCreatedByAdmin): self
    {
        if (!$this->deliveriesCreatedByAdmin->contains($deliveriesCreatedByAdmin)) {
            $this->deliveriesCreatedByAdmin[] = $deliveriesCreatedByAdmin;
            $deliveriesCreatedByAdmin->setAdmin($this);
        }

        return $this;
    }

    public function removeDeliveriesCreatedByAdmin(Delivery $deliveriesCreatedByAdmin): self
    {
        if ($this->deliveriesCreatedByAdmin->removeElement($deliveriesCreatedByAdmin)) {
            // set the owning side to null (unless already changed)
            if ($deliveriesCreatedByAdmin->getAdmin() === $this) {
                $deliveriesCreatedByAdmin->setAdmin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveriesCarriedByDriver(): Collection
    {
        return $this->deliveriesCarriedByDriver;
    }

    public function addDeliveriesCarriedByDriver(Delivery $deliveriesCarriedByDriver): self
    {
        if (!$this->deliveriesCarriedByDriver->contains($deliveriesCarriedByDriver)) {
            $this->deliveriesCarriedByDriver[] = $deliveriesCarriedByDriver;
            $deliveriesCarriedByDriver->setDriver($this);
        }

        return $this;
    }

    public function removeDeliveriesCarriedByDriver(Delivery $deliveriesCarriedByDriver): self
    {
        if ($this->deliveriesCarriedByDriver->removeElement($deliveriesCarriedByDriver)) {
            // set the owning side to null (unless already changed)
            if ($deliveriesCarriedByDriver->getDriver() === $this) {
                $deliveriesCarriedByDriver->setDriver(null);
            }
        }

        return $this;
    }

}
