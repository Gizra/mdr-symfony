<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChildRepository")
 */
class Child extends Person
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relationship", mappedBy="child", orphanRemoval=true)
     */
    private $relationships;

    public function __construct()
    {
        parent::__construct();
        $this->relationships = new ArrayCollection();
    }

    /**
     * @return Collection|Relationship[]
     */
    public function getRelationships(): Collection
    {
        return $this->relationships;
    }

    public function addRelationship(Relationship $relationship): self
    {
        if (!$this->relationships->contains($relationship)) {
            $this->relationships[] = $relationship;
            $relationship->setChild($this);
        }

        return $this;
    }

    public function removeRelationship(Relationship $relationship): self
    {
        if ($this->relationships->contains($relationship)) {
            $this->relationships->removeElement($relationship);
            // set the owning side to null (unless already changed)
            if ($relationship->getChild() === $this) {
                $relationship->setChild(null);
            }
        }

        return $this;
    }
}
