<?php

namespace App\Entity;

use App\Repository\ScanResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScanResultRepository::class)]
class ScanResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $vulnerabilities = null;

    #[ORM\Column(length: 255)]
    private ?string $integer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $scanDate = null;

    #[ORM\OneToOne(inversedBy: 'scanRes', cascade: ['persist', 'remove'])]
    private ?User $scandate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVulnerabilities(): ?int
    {
        return $this->vulnerabilities;
    }

    public function setVulnerabilities(?int $vulnerabilities): static
    {
        $this->vulnerabilities = $vulnerabilities;

        return $this;
    }

    public function getInteger(): ?string
    {
        return $this->integer;
    }

    public function setInteger(string $integer): static
    {
        $this->integer = $integer;

        return $this;
    }

    public function getScanDate(): ?\DateTimeInterface
    {
        return $this->scanDate;
    }

    public function setScanDate(?\DateTimeInterface $scanDate): static
    {
        $this->scanDate = $scanDate;

        return $this;
    }
}
