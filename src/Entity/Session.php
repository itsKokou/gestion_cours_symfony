<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?\DateTimeInterface $heureD = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?\DateTimeInterface $heureF = null;

    #[ORM\Column]
    private ?bool $isArchived = false;

    #[ORM\ManyToOne(inversedBy: 'sessions', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $cours = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    private ?Salle $salle = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $codeSession = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: Absence::class)]
    private Collection $absences;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    private ?Professeur $professeur = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: Declaration::class)]
    private Collection $declarations;

    #[ORM\Column]
    private ?bool $isAbsence = false;

    public function __construct()
    {
        $this->absences = new ArrayCollection();
        $this->declarations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHeureD(): ?\DateTimeInterface
    {
        return $this->heureD;
    }

    public function setHeureD(\DateTimeInterface $heureD): static
    {
        $this->heureD = $heureD;

        return $this;
    }

    public function getHeureF(): ?\DateTimeInterface
    {
        return $this->heureF;
    }

    public function setHeureF(\DateTimeInterface $heureF): static
    {
        $this->heureF = $heureF;

        return $this;
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

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    public function getCodeSession(): ?string
    {
        return $this->codeSession;
    }

    public function setCodeSession(?string $codeSession): static
    {
        $this->codeSession = $codeSession;

        return $this;
    }

    /**
     * @return Collection<int, Absence>
     */
    public function getAbsences(): Collection
    {
        return $this->absences;
    }

    public function addAbsence(Absence $absence): static
    {
        if (!$this->absences->contains($absence)) {
            $this->absences->add($absence);
            $absence->setSession($this);
        }

        return $this;
    }

    public function removeAbsence(Absence $absence): static
    {
        if ($this->absences->removeElement($absence)) {
            // set the owning side to null (unless already changed)
            if ($absence->getSession() === $this) {
                $absence->setSession(null);
            }
        }

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
     * @return Collection<int, Declaration>
     */
    public function getDeclarations(): Collection
    {
        return $this->declarations;
    }

    public function addDeclaration(Declaration $declaration): static
    {
        if (!$this->declarations->contains($declaration)) {
            $this->declarations->add($declaration);
            $declaration->setSession($this);
        }

        return $this;
    }

    public function removeDeclaration(Declaration $declaration): static
    {
        if ($this->declarations->removeElement($declaration)) {
            // set the owning side to null (unless already changed)
            if ($declaration->getSession() === $this) {
                $declaration->setSession(null);
            }
        }

        return $this;
    }

    public function isIsAbsence(): ?bool
    {
        return $this->isAbsence;
    }

    public function setIsAbsence(bool $isAbsence): static
    {
        $this->isAbsence = $isAbsence;

        return $this;
    }
}
