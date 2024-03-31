<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
// Serializer Groups
use Symfony\Component\Serializer\Annotation\Groups;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllChimpokodex", "getOnPicture"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getAllChimpokodex", "getOnPicture"])]
    private ?string $realName = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getAllChimpokodex", "getOnPicture"])]
    private ?string $realPath = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getOnPicture"])]
    private ?string $publicPath = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getOnPicture"])]
    private ?string $mimeType = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getOnPicture"])]
    private ?string $name = null;

    #[ORM\Column(length: 25)]
    #[Groups(["getOnPicture"])]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getOnPicture"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getOnPicture"])]
    private ?\DateTimeInterface $updatedAt = null;

    #[Vich\UploadableField(mapping: "pictures", fileNameProperty: "realPath")]
    private $file;

    #[ORM\OneToMany(mappedBy: 'picture', targetEntity: Chimpokodex::class)]
    private Collection $chimpokodexes;

    public function __construct()
    {
        $this->chimpokodexes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): static
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): static
    {
        $this->realPath = $realPath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): static
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
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

    public  function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): Picture
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return Collection<int, Chimpokodex>
     */
    public function getChimpokodexes(): Collection
    {
        return $this->chimpokodexes;
    }

    public function addChimpokodex(Chimpokodex $chimpokodex): static
    {
        if (!$this->chimpokodexes->contains($chimpokodex)) {
            $this->chimpokodexes->add($chimpokodex);
            $chimpokodex->setPicture($this);
        }

        return $this;
    }

    public function removeChimpokodex(Chimpokodex $chimpokodex): static
    {
        if ($this->chimpokodexes->removeElement($chimpokodex)) {
            // set the owning side to null (unless already changed)
            if ($chimpokodex->getPicture() === $this) {
                $chimpokodex->setPicture(null);
            }
        }

        return $this;
    }
}
