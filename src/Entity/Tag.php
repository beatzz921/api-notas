<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titulo;

    /**
     * @ORM\ManyToMany(targetEntity=Nota::class, mappedBy="tag")
     */
    private $notas;

    public function __construct()
    {
        $this->notas = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->titulo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * @return Collection<int, Nota>
     */
    public function getNotas(): Collection
    {
        return $this->notas;
    }

    public function addNota(Nota $nota): self
    {
        if (!$this->notas->contains($nota)) {
            $this->notas[] = $nota;
            $nota->addTag($this);
        }

        return $this;
    }

    public function removeNota(Nota $nota): self
    {
        if ($this->notas->removeElement($nota)) {
            $nota->removeTag($this);
        }

        return $this;
    }

    public function serializar()
    {
        $objeto = [];
        $objeto['id'] = $this->getId();
        $objeto['titulo'] = $this->getTitulo();

        return $objeto;
    }
}
