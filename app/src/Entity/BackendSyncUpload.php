<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Faker\Provider\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BackendSyncUploadRepository")
 */
class BackendSyncUpload
{
    use SettableUuidTrait;

    /**
     * We make the UUID always settable by us.
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $existsOnBackend;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getExistsOnBackend(): ?bool
    {
        return $this->existsOnBackend;
    }

    public function setExistsOnBackend(bool $existsOnBackend): self
    {
        $this->existsOnBackend = $existsOnBackend;

        return $this;
    }
}
