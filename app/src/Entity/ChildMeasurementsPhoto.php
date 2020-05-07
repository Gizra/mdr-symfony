<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChildMeasurementsPhotoRepository")
 */
class ChildMeasurementsPhoto extends Measurements
{

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $remoteUri;

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getRemoteUri(): ?string
    {
        return $this->remoteUri;
    }

    public function setRemoteUri(?string $remoteUri): self
    {
        $this->remoteUri = $remoteUri;

        return $this;
    }
}
