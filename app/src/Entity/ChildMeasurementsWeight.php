<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChildMeasurementsWeightRepository")
 */
class ChildMeasurementsWeight extends Measurements
{
    use ChildMeasurementsFloatValue;
}
