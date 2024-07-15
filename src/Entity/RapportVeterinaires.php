<?php

namespace App\Entity;

use App\Repository\RapportVeterinairesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RapportVeterinairesRepository::class)]
class RapportVeterinaires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['rapportveterinaire_read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['rapportveterinaire_read'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Groups(['rapportveterinaire_read'])]
    private ?string $nourriture = null;

    #[ORM\Column]
    #[Groups(['rapportveterinaire_read'])]
    private ?int $grammage = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['rapportveterinaire_read'])]
    private ?string $etatanimal = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['rapportveterinaire_read'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'Etat')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animaux $animal = null;

    #[Groups(['rapportveterinaire_read'])]
    public function getAnimalId(): ?int
    {
        return $this->animal ? $this->animal->getId() : null;
    }

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
        return $this->etatanimal;
    }

    public function setEtatAnimal(string $etatanimal): static
    {
        $this->etatanimal = $etatanimal;

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
        return $this->animal;
    }

    public function setAnimal(?Animaux $animal): static
    {
        $this->animal = $animal;

        return $this;
    }
}
