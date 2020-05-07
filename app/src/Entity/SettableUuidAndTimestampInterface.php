<?php


namespace App\Entity;


interface SettableUuidAndTimestampInterface
{

    public function getId(): ?string;

    public function setId(string $id): void;

    public function getTimestamp(): ?\DateTimeInterface;

    public function setTimestamp(\DateTimeInterface $timestamp);

}