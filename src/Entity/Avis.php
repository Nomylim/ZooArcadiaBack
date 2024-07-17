<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["avis:read", "avis:write"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["avis:read", "avis:write"])]

    private ?string $pseudo = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["avis:read", "avis:write"])]

    private ?string $avis = null;

    #[ORM\Column]
    #[Groups(["avis:read", "avis:write"])]

    private ?bool $valide = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getAvis(): ?string
    {
        return $this->avis;
    }

    public function setAvis(string $avis): static
    {
        $this->avis = $avis;

        return $this;
    }

    public function isValide(): ?bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;

        return $this;
    }
}
