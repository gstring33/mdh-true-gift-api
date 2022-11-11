<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[VirtualProperty(
    name: 'fullname',
    exp: 'object.getFullname()',
    options: [
        [SerializedName::class, ['fullname']]
    ]
)]
#[VirtualProperty(
    name: 'img',
    exp: 'object.getImg()'
)]

#[VirtualProperty(
    name: 'isPartner',
    exp: 'object.getIsPartner()',

)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(groups: ['firstname' => 'admin', 'dashboard_users'])]
    private ?string $uuid = null;

    #[ORM\Column]
    #[Groups(groups: ['firstname' => 'admin'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Groups(groups: ['firstname' => 'dashboard', 'admin', 'dashboard_users'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Groups(groups: ['lastname' => 'dashboard', 'admin', 'dashboard_users'])]
    private ?string $lastname = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['lastConnectionAt' => 'admin'])]
    #[SerializedName('lastConnectionAt')]
    private ?\DateTimeImmutable $lastConnectionAt = null;

    #[ORM\Column]
    #[Groups(groups: ['lastname' => 'admin'])]
    #[SerializedName('isActive')]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(groups: ['lastname' => 'admin'])]
    private ?string $email = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    private ?self $recieveGiftFrom = null;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    #[Groups(groups: ['partner' => 'dashboard'])]
    #[SerializedName('partner')]
    private ?self $offerGiftTo = null;

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(groups: ['list' => 'dashboard'])]
    #[SerializedName('list')]
    private ?GiftList $giftList = null;

    #[ORM\Column(length: 10)]
    #[Groups(groups: ['gender' => 'dashboard', 'admin', 'dashboard_users'])]
    private ?string $gender = null;

    #[Groups(groups: ['img' => 'dashboard_users'])]
    private ?string $img = null;

    #[Groups(groups: ['isPartner' => 'dashboard_users'])]
    #[SerializedName('isPartner')]
    private ?bool $isPartner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        if(!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
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

    public function getLastConnectionAt(): ?\DateTimeImmutable
    {
        return $this->lastConnectionAt;
    }

    public function setLastConnectionAt(?\DateTimeImmutable $lastConnectionAt): self
    {
        $this->lastConnectionAt = $lastConnectionAt;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
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

    public function getRecieveGiftFrom(): ?self
    {
        return $this->recieveGiftFrom;
    }

    public function setRecieveGiftFrom(?self $recieveGiftFrom): self
    {
        $this->recieveGiftFrom = $recieveGiftFrom;

        return $this;
    }

    public function getOfferGiftTo(): ?self
    {
        return $this->offerGiftTo;
    }

    public function setOfferGiftTo(?self $offerGiftTo): self
    {
        $this->offerGiftTo = $offerGiftTo;

        return $this;
    }

    public function getGiftList(): ?GiftList
    {
        return $this->giftList;
    }

    public function setGiftList(?GiftList $giftList): self
    {
        $this->giftList = $giftList;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    #[VirtualProperty()]
    #[Groups(groups: ['fullname' => 'dashboard', 'admin', 'dashboard_users'])]
    public function getFullname(): ?string
    {
        $letter = ucfirst($this->lastname[0]);
        return "{$this->firstname} {$letter}.";
    }

    #[VirtualProperty()]
    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): self
    {
        $this->img = $img;

        return $this;
    }

    #[VirtualProperty()]
    public function getIsPartner(): ?bool
    {
        return $this->getIsPartner();
    }

    public function setIsPartner(bool $isPartner): self
    {
        $this->isPartner = $isPartner;

        return $this;
    }

}
