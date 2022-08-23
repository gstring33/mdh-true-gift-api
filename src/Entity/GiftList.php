<?php

namespace App\Entity;

use App\Repository\GiftListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GiftListRepository::class)]
class GiftList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isPublished = null;

    #[ORM\OneToOne(mappedBy: 'giftList', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'giftList', targetEntity: Gift::class, orphanRemoval: true)]
    private Collection $Gifts;

    #[ORM\Column(length: 255)]
    private ?string $uuid = null;

    public function __construct()
    {
        $this->Gifts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setGiftList(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getGiftList() !== $this) {
            $user->setGiftList($this);
        }

        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Gift>
     */
    public function getGifts(): Collection
    {
        return $this->Gifts;
    }

    public function addGift(Gift $gift): self
    {
        if (!$this->Gifts->contains($gift)) {
            $this->Gifts->add($gift);
            $gift->setGiftList($this);
        }

        return $this;
    }

    public function removeGift(Gift $gift): self
    {
        if ($this->Gifts->removeElement($gift)) {
            // set the owning side to null (unless already changed)
            if ($gift->getGiftList() === $this) {
                $gift->setGiftList(null);
            }
        }

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
