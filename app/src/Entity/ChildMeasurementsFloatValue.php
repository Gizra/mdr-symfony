<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ChildMeasurementsFloatValue
{

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }
}
