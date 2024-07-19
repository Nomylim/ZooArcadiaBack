<?php

namespace App\Entity;

use App\Repository\NourritureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NourritureRepository::class)]
class Nourriture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['nourriture_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['nourriture_read'])]
    private ?string $nom = null;

    #[ORM\Column]
    #[Groups(['nourriture_read'])]
    private ?int $grammage = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['nourriture_read'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['nourriture_read'])]
    private ?\DateTimeInterface $heure = null;

    #[ORM\ManyToOne(inversedBy: 'nourriture')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['nourriture_read'])]
    private ?Animaux $animal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getGrammage(): ?int
    {
        return $this->grammage;
    }

    public function setGrammage(int $grammage): static
    {
        $this->grammage = $grammage;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getAnimal(): ?Animaux
    {
        return $this->animal;
    }

    public function setAnimal(?Animaux $animal): static
    {
        $this->animal = $animal;

        return $this;
    }
}
