<?php

namespace App\Entity;

use App\Repository\ChimpokomonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
// Serializer Groups
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChimpokomonRepository::class)]
class Chimpokomon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?int $pv = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?int $pvMax = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chimpokodex $chimpokodex = null;

    #[ORM\Column(length: 24)]
    #[Groups(["getAllChimpokokomon"])]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getAllChimpokokomon"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getAllChimpokokomon"])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'chimpokomon', cascade: ['persist', 'remove'])]
    #[Groups(["getAllChimpokokomon"])]
    private ?Team $team = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?int $attack = null;

    #[ORM\Column]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?int $defense = null;

    #[ORM\ManyToOne]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?Picture $picture = null;

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

    public function getPv(): ?int
    {
        return $this->pv;
    }

    public function setPv(int $pv): static
    {
        $this->pv = $pv;

        return $this;
    }

    public function getPvMax(): ?int
    {
        return $this->pvMax;
    }

    public function setPvMax(int $pvMax): static
    {
        $this->pvMax = $pvMax;

        return $this;
    }

    public function getChimpokodex(): ?Chimpokodex
    {
        return $this->chimpokodex;
    }

    public function setChimpokodex(?Chimpokodex $chimpokodex): static
    {
        $this->chimpokodex = $chimpokodex;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        // unset the owning side of the relation if necessary
        if ($team === null && $this->team !== null) {
            $this->team->setChimpokomon(null);
        }

        // set the owning side of the relation if necessary
        if ($team !== null && $team->getChimpokomon() !== $this) {
            $team->setChimpokomon($this);
        }

        $this->team = $team;

        return $this;
    }

    public function getAttack(): ?int
    {
        return $this->attack;
    }

    public function setAttack(int $attack): static
    {
        $this->attack = $attack;

        return $this;
    }

    public function getDefense(): ?int
    {
        return $this->defense;
    }

    public function setDefense(int $defense): static
    {
        $this->defense = $defense;

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
}