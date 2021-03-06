<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"adult" = "Adult", "caregiver" = "Caregiver", "child" = "Child", "mother" = "Mother"})
 */
abstract class Person implements SettableUuidAndTimestampInterface
{

    use SettableUuidTrait;
    use SettableTimestampTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupMeetingAttendance", mappedBy="person")
     */
    private $groupMeetingAttendances;

    public function __construct()
    {
        $this->groupMeetingAttendances = new ArrayCollection();
    }


    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection|GroupMeetingAttendance[]
     */
    public function getGroupMeetingAttendances(): Collection
    {
        return $this->groupMeetingAttendances;
    }

    public function addGroupMeetingAttendance(GroupMeetingAttendance $groupMeetingAttendance): self
    {
        if (!$this->groupMeetingAttendances->contains($groupMeetingAttendance)) {
            $this->groupMeetingAttendances[] = $groupMeetingAttendance;
            $groupMeetingAttendance->setPerson($this);
        }

        return $this;
    }

    public function removeGroupMeetingAttendance(GroupMeetingAttendance $groupMeetingAttendance): self
    {
        if ($this->groupMeetingAttendances->contains($groupMeetingAttendance)) {
            $this->groupMeetingAttendances->removeElement($groupMeetingAttendance);
            // set the owning side to null (unless already changed)
            if ($groupMeetingAttendance->getPerson() === $this) {
                $groupMeetingAttendance->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * Get the type of the class.
     *
     * In Twig we avoid using `instanceOf`, so this makes it easier to get it.
     *
     * @return string
     */
    public function getType() {
        $class = explode('\\', get_class($this));
        $type =  end($class);
        return strtolower($type);
    }
}
