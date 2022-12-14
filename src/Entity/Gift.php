<?php

namespace App\Entity;

use App\Repository\GiftRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GiftRepository::class)]
class Gift
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['title' => 'gift','list', 'dashboard', 'dashboard_partner'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(groups: ['description' => 'gift','list', 'dashboard', 'dashboard_partner'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['link' => 'gift', 'list', 'dashboard', 'dashboard_partner'])]
    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'Gifts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GiftList $giftList = null;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['uuid' => 'gift', 'list'])]
    private ?string $uuid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
