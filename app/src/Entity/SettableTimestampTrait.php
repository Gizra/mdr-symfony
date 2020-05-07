<?php

namespace App\Entity;

trait SettableTimestampTrait {

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
