<?php

namespace App\Entity;

use App\Repository\ChimpokodexRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
// Serializer Groups
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChimpokodexRepository::class)]
class Chimpokodex
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllChimpokodex"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getAllChimpokodex"])]
    #[Assert\NotBlank(message: "Un chimpokodex doit avoir un nom")]
    #[Assert\NotNull(message: "Un chimpokodex doit avoir un nom")]
    #[Assert\Length(min: 2, max: 255,
        minMessage: "Le nom d'un chimpokodex doit forcément faire plus de {{limit}} caractères.", maxMessage: "Le nom d'un chimpokodex doit forcément faire moins de {{limit}} caractères.")]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'devolution')]
    private Collection $evolution;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'evolution')]
    private Collection $devolution;

    #[ORM\Column]
    #[Groups(["getAllChimpokodex"])]
    private ?int $maxPv = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokodex"])]
    private ?int $minPv = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokodex"])]
    private ?int $minAttack = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokodex"])]
    private ?int $maxAttack = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokodex"])]
    private ?int $maxDefense = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokodex"])]
    private ?int $minDefense = null;

    #[ORM\ManyToOne(inversedBy: 'chimpokodexes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getAllChimpokodex"])]
    private ?Picture $picture = null;

    #[ORM\Column(length: 24)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getAllChimpokodex"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getAllChimpokodex"])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->evolution = new ArrayCollection();
        $this->devolution = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getEvolution(): Collection
    {
        return $this->evolution;
    }

    public function addEvolution(self $evolution): static
    {
        if (!$this->evolution->contains($evolution)) {
            $this->evolution->add($evolution);
        }

        return $this;
    }

    public function removeEvolution(self $evolution): static
    {
        $this->evolution->removeElement($evolution);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getDevolution(): Collection
    {
        return $this->devolution;
    }

    public function addDevolution(self $devolution): static
    {
        if (!$this->devolution->contains($devolution)) {
            $this->devolution->add($devolution);
            $devolution->addEvolution($this);
        }

        return $this;
    }

    public function removeDevolution(self $devolution): static
    {
        if ($this->devolution->removeElement($devolution)) {
            $devolution->removeEvolution($this);
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getMinPv(): ?int
    {
        return $this->minPv;
    }

    public function setMinPv(int $minPv): static
    {
        $this->minPv = $minPv;

        return $this;
    }

    public function getMinAttack(): ?int
    {
        return $this->minAttack;
    }

    public function setMinAttack(int $minAttack): static
    {
        $this->minAttack = $minAttack;

        return $this;
    }

    public function getMaxAttack(): ?int
    {
        return $this->maxAttack;
    }

    public function setMaxAttack(int $maxAttack): static
    {
        $this->maxAttack = $maxAttack;

        return $this;
    }

    public function getMaxPv(): ?int
    {
        return $this->maxPv;
    }

    public function setMaxPv(int $maxPv): static
    {
        $this->maxPv = $maxPv;

        return $this;
    }

    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    public function setPicture(?Picture $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getMaxDefense(): ?int
    {
        return $this->maxDefense;
    }

    public function setMaxDefense(int $maxDefense): static
    {
        $this->maxDefense = $maxDefense;

        return $this;
    }

    public function getMinDefense(): ?int
    {
        return $this->minDefense;
    }

    public function setMinDefense(int $minDefense): static
    {
        $this->minDefense = $minDefense;

        return $this;
    }
}