<?php

namespace App\Entity;

use App\Repository\UploadRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadRepository::class)]
class Upload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'filename')]
    private ?User $enduser = null;

    #[ORM\OneToOne(inversedBy: 'scanResultss', cascade: ['persist', 'remove'])]
    private ?User $scanResults = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

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

    public function getEnduser(): ?User
    {
        return $this->enduser;
    }

    public function setEnduser(?User $enduser): static
    {
        $this->enduser = $enduser;

        return $this;
    }

    public function getScanResults(): ?User
    {
        return $this->scanResults;
    }

    public function setScanResults(?User $scanResults): static
    {
        $this->scanResults = $scanResults;

        return $this;
    }
}
