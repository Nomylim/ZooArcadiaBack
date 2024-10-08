<?php

namespace App\Entity;

use App\Repository\AnimauxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimauxRepository::class)]
class Animaux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['animal_read','nourriture_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['animal_read'])]

    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['animal_read'])]

    private ?string $race = null;

    #[ORM\ManyToOne(targetEntity:"Habitats", inversedBy: 'animaux')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['animal_read'])]

    private ?Habitats $habitat = null;

    /**
     * @var Collection<int, RapportVeterinaires>
     */
    #[ORM\OneToMany(targetEntity: RapportVeterinaires::class, mappedBy: 'animal', orphanRemoval: true)]
    private Collection $rapports;

    /**
     * @var Collection<int, Nourriture>
     */
    #[ORM\OneToMany(targetEntity: Nourriture::class, mappedBy: 'animal', orphanRemoval: true)]
    private Collection $nourriture;

    public function __construct()
    {
        $this->rapports = new ArrayCollection();
        $this->nourriture = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getRace(): ?string
    {
        return $this->race;
    }

    public function setRace(string $race): static
    {
        $this->race = $race;

        return $this;
    }

    public function getHabitat(): ?Habitats
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitats $habitat): static
    {
        $this->habitat = $habitat;

        return $this;
    }

    /**
     * @return Collection<int, RapportVeterinaires>
     */
    public function getRapport(): Collection
    {
        return $this->rapports;
    }
     public function getEtat(): ?string
    {
        if($this->rapports->isEmpty()){
            return null;
        }
        return $this->rapports->last()->getEtatAnimal();
    }
    /*
    public function addEtat(RapportVeterinaires $etat): static
    {
        if (!$this->etat->contains($etat)) {
            $this->etat->add($etat);
            $etat->setAnimal($this);
        }

        return $this;
    }

    public function removeEtat(RapportVeterinaires $etat): static
    {
        if ($this->etat->removeElement($etat)) {
            // set the owning side to null (unless already changed)
            if ($etat->getAnimal() === $this) {
                $etat->setAnimal(null);
            }
        }

        return $this;
    }*/

    /**
     * @return Collection<int, Nourriture>
     */
    public function getNourriture(): Collection
    {
        return $this->nourriture;
    }

    public function addNourriture(Nourriture $nourriture): static
    {
        if (!$this->nourriture->contains($nourriture)) {
            $this->nourriture->add($nourriture);
            $nourriture->setAnimal($this);
        }

        return $this;
    }

    public function removeNourriture(Nourriture $nourriture): static
    {
        if ($this->nourriture->removeElement($nourriture)) {
            // set the owning side to null (unless already changed)
            if ($nourriture->getAnimal() === $this) {
                $nourriture->setAnimal(null);
            }
        }

        return $this;
    }
}
