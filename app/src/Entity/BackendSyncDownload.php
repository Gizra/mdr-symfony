<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BackendSyncDownloadRepository")
 */
class BackendSyncDownload
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * Last time sync was successful.
     */
    private $last_success_general;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * Last time we tried to contact DB, but may not be successful.
     */
    private $last_try_general;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_success_authority;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_try_authority;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $last_id_general;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $last_id_authority;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastSuccessGeneral(): ?\DateTimeInterface
    {
        return $this->last_success_general;
    }

    public function setLastSuccessGeneral(?\DateTimeInterface $last_success_general): self
    {
        $this->last_success_general = $last_success_general;
        $this->setLastTryGeneral($last_success_general);

        return $this;
    }

    public function getLastTryGeneral(): ?\DateTimeInterface
    {
        return $this->last_try_general;
    }

    public function setLastTryGeneral(?\DateTimeInterface $last_try_general): self
    {
        $this->last_try_general = $last_try_general;

        return $this;
    }

    public function getLastSuccessAuthority(): ?\DateTimeInterface
    {
        return $this->last_success_authority;
    }

    public function setLastSuccessAuthority(?\DateTimeInterface $last_success_authority): self
    {
        $this->last_success_authority = $last_success_authority;
        $this->setLastTryAuthority($last_success_authority);

        return $this;
    }

    public function getLastTryAuthority(): ?\DateTimeInterface
    {
        return $this->last_try_authority;
    }

    public function setLastTryAuthority(?\DateTimeInterface $last_try_authority): self
    {
        $this->last_try_authority = $last_try_authority;

        return $this;
    }

    public function getLastIdGeneral(): ?int
    {
        return $this->last_id_general;
    }

    public function setLastIdGeneral(?int $last_id_general): self
    {
        $this->last_id_general = $last_id_general;

        return $this;
    }

    public function getLastIdAuthority(): ?int
    {
        return $this->last_id_authority;
    }

    public function setLastIdAuthority(?int $last_id_authority): self
    {
        $this->last_id_authority = $last_id_authority;

        return $this;
    }
}
