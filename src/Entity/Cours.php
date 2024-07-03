<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CoursRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isArchived = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\GreaterThanOrEqual(value:16)]
    private ?int $nbreHeureTotal = null;
    
    #[ORM\Column]
    private ?int $nbreHeurePlanifie = 0;

    #[ORM\Column]
    private ?int $nbreHeureRestantPlan = 0;

    #[ORM\Column]
    private ?int $nbreHeureRealise = 0;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Semestre $semestre = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Module $module = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Professeur $professeur = null;

    #[ORM\ManyToMany(targetEntity: Classe::class, inversedBy: 'cours')]
    private Collection $classes;

    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: Session::class)]
    private Collection $sessions;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AnneeScolaire $anneeScolaire = null;


    public function __construct()
    {
        $date = new \DateTimeImmutable();
        $date = $date->format('Y-m-d'); //formater la date pour pas avoir l'heure
        $this->createAt = new \DateTimeImmutable($date);
        $this->classes = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }


    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): static
    {
        $this->semestre = $semestre;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function getProfesseur(): ?Professeur
    {
        return $this->professeur;
    }

    public function setProfesseur(?Professeur $professeur): static
    {
        $this->professeur = $professeur;

        return $this;
    }

    /**
     * @return Collection<int, Classe>
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClass(Classe $class): static
    {
        if (!$this->classes->contains($class)) {
            $this->classes->add($class);
        }

        return $this;
    }

    public function removeClass(Classe $class): static
    {
        $this->classes->removeElement($class);

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setCours($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getCours() === $this) {
                $session->setCours(null);
            }
        }

        return $this;
    }

    public function getNbreHeurePlanifie(): ?int
    {
        return $this->nbreHeurePlanifie;
    }

    public function setNbreHeurePlanifie(int $nbreHeurePlanifie): static
    {
        $this->nbreHeurePlanifie = $nbreHeurePlanifie;

        return $this;
    }

    public function getNbreHeureRealise(): ?int
    {
        return $this->nbreHeureRealise;
    }

    public function setNbreHeureRealise(int $nbreHeureRealise): static
    {
        $this->nbreHeureRealise = $nbreHeureRealise;

        return $this;
    }

    public function getAnneeScolaire(): ?AnneeScolaire
    {
        return $this->anneeScolaire;
    }

    public function setAnneeScolaire(?AnneeScolaire $anneeScolaire): static
    {
        $this->anneeScolaire = $anneeScolaire;

        return $this;
    }

    /**
     * Get the value of nbreHeureTotal
     */ 
    public function getNbreHeureTotal()
    {
        return $this->nbreHeureTotal;
    }

    /**
     * Set the value of nbreHeureTotal
     *
     * @return  self
     */ 
    public function setNbreHeureTotal($nbreHeureTotal)
    {
        $this->nbreHeureTotal = $nbreHeureTotal;

        return $this;
    }

    /**
     * Get the value of nbreHeureRestantPlan
     */ 
    public function getNbreHeureRestantPlan()
    {
        return $this->nbreHeureRestantPlan;
    }

    /**
     * Set the value of nbreHeureRestantPlan
     *
     * @return  self
     */ 
    public function setNbreHeureRestantPlan($nbreHeureRestantPlan)
    {
        $this->nbreHeureRestantPlan = $nbreHeureRestantPlan;

        return $this;
    }
}
