<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllChimpokokomon, getAllTeam"])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'team', cascade: ['persist', 'remove'])]
    #[Groups(["getAllTeam"])]
    private ?Chimpokomon $chimpokomon = null;

    #[ORM\OneToOne(inversedBy: 'team', cascade: ['persist', 'remove'])]
    #[Groups(["getAllChimpokokomon", "getAllTeam"])]
    private ?User $trainer = null;

    #[ORM\Column]
    #[Groups(["getAllTeam"])]
    private ?bool $favorite = null;

    #[ORM\Column(length: 25)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChimpokomon(): ?Chimpokomon
    {
        return $this->chimpokomon;
    }

    public function setChimpokomon(?Chimpokomon $chimpokomon): static
    {
        $this->chimpokomon = $chimpokomon;

        return $this;
    }

    public function getTrainer(): ?User
    {
        return $this->trainer;
    }

    public function setTrainer(?User $trainer): static
    {
        $this->trainer = $trainer;

        return $this;
    }

    public function isFavorite(): ?bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): static
    {
        $this->favorite = $favorite;

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
}
