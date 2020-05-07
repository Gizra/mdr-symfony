<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MotherRepository")
 *
 * Hold the full Mother data.
 */
class Mother extends Adult
{

    /**
     * @ORM\Column(type="boolean")
     */
    private $birthdayEstimated;

    public function getBirthdayEstimated(): ?bool
    {
        return $this->birthdayEstimated;
    }

    public function setBirthdayEstimated(bool $birthdayEstimated): self
    {
        $this->birthdayEstimated = $birthdayEstimated;

        return $this;
    }
}
