<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasurementsRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
abstract class Measurements implements SettableUuidAndTimestampInterface
{

    use SettableUuidTrait;
    use SettableTimestampTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupMeetingAttendance", inversedBy="measurements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupMeetingAttendance;

    public function getGroupMeetingAttendance(): ?GroupMeetingAttendance
    {
        return $this->groupMeetingAttendance;
    }

    public function setGroupMeetingAttendance(?GroupMeetingAttendance $groupMeetingAttendance): self
    {
        $this->groupMeetingAttendance = $groupMeetingAttendance;

        return $this;
    }



}
