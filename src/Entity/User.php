<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 155)]
    private ?string $email = null;

    #[ORM\Column(length: 12)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'enduser', targetEntity: Upload::class)]
    private Collection $filename;

    #[ORM\OneToOne(mappedBy: 'scanResults', cascade: ['persist', 'remove'])]
    private ?Upload $scanResultss = null;

    #[ORM\OneToOne(mappedBy: 'scandate', cascade: ['persist', 'remove'])]
    private ?ScanResult $scanRes = null;

    public function __construct()
    {
        $this->filename = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Upload>
     */
    public function getFilename(): Collection
    {
        return $this->filename;
    }

    public function addFilename(Upload $filename): static
    {
        if (!$this->filename->contains($filename)) {
            $this->filename->add($filename);
            $filename->setEnduser($this);
        }

        return $this;
    }

    public function removeFilename(Upload $filename): static
    {
        if ($this->filename->removeElement($filename)) {
            // set the owning side to null (unless already changed)
            if ($filename->getEnduser() === $this) {
                $filename->setEnduser(null);
            }
        }

        return $this;
    }

    public function getScanResultss(): ?Upload
    {
        return $this->scanResultss;
    }

    public function setScanResultss(?Upload $scanResultss): static
    {
        // unset the owning side of the relation if necessary
        if ($scanResultss === null && $this->scanResultss !== null) {
            $this->scanResultss->setScanResults(null);
        }

        // set the owning side of the relation if necessary
        if ($scanResultss !== null && $scanResultss->getScanResults() !== $this) {
            $scanResultss->setScanResults($this);
        }

        $this->scanResultss = $scanResultss;

        return $this;
    }

    public function getScanRes(): ?ScanResult
    {
        return $this->scanRes;
    }

    public function setScanRes(?ScanResult $scanRes): static
    {
        // unset the owning side of the relation if necessary
        if ($scanRes === null && $this->scanRes !== null) {
            $this->scanRes->setScandate(null);
        }

        // set the owning side of the relation if necessary
        if ($scanRes !== null && $scanRes->getScandate() !== $this) {
            $scanRes->setScandate($this);
        }

        $this->scanRes = $scanRes;

        return $this;
    }
}
