<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RelationshipRepository")
 */
class Relationship implements SettableUuidAndTimestampInterface
{

    use SettableUuidTrait;
    use SettableTimestampTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Child", inversedBy="relationships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $child;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adult", inversedBy="relationships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adult;

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): self
    {
        $this->child = $child;

        return $this;
    }

    public function getAdult(): ?Adult
    {
        return $this->adult;
    }

    public function setAdult(?Adult $adult): self
    {
        $this->adult = $adult;

        return $this;
    }

}
