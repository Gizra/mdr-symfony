<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChildMeasurementsHeightRepository")
 */
class ChildMeasurementsHeight extends Measurements
{

    use ChildMeasurementsFloatValue;
}
