<?php

namespace App\Entity;

use App\Repository\RapportVeterinairesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RapportVeterinairesRepository::class)]
class RapportVeterinaires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $nourriture = null;

    #[ORM\Column]
    private ?int $grammage = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $EtatAnimal = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'Etat')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animaux $Animal = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNourriture(): ?string
    {
        return $this->nourriture;
    }

    public function setNourriture(string $nourriture): static
    {
        $this->nourriture = $nourriture;

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

    public function getEtatAnimal(): ?string
    {
        return $this->EtatAnimal;
    }

    public function setEtatAnimal(string $EtatAnimal): static
    {
        $this->EtatAnimal = $EtatAnimal;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAnimal(): ?Animaux
    {
        return $this->Animal;
    }

    public function setAnimal(?Animaux $Animal): static
    {
        $this->Animal = $Animal;

        return $this;
    }
}
