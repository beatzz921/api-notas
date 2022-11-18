<?php

namespace App\Entity;

use App\Repository\NotaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotaRepository::class)
 */
class Nota
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
     * @ORM\Column(type="text")
     */
    private $descripcion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $eliminada;

    /**
     * @ORM\Column(type="boolean")
     */
    private $publica;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="notas")
     */
    private $tags;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaEliminada;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getEliminada(): ?bool
    {
        return $this->eliminada;
    }

    public function setEliminada(bool $eliminada): self
    {
        $this->eliminada = $eliminada;

        return $this;
    }

    public function getPublica(): ?bool
    {
        return $this->publica;
    }

    public function setPublica(bool $publica): self
    {
        $this->publica = $publica;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getTagsTitulo()
    {
        $titulos = '';
        if (count($this->getTags()) == 0) {
            $titulos = 'No existen tags asociados.';
        } else {
            foreach ($this->getTags() as $index => $tag) {
                if ($index == 0) {
                    $titulos .= $tag->getTitulo();
                } else {
                    $titulos .= ', '.$tag->getTitulo();
                }
            }
        }
        return $titulos;
    }

    public function getFechaEliminada(): ?\DateTimeInterface
    {
        return $this->fechaEliminada;
    }

    public function setFechaEliminada(?\DateTimeInterface $fechaEliminada): self
    {
        $this->fechaEliminada = $fechaEliminada;

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }
}
