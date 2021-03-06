<?php

namespace App\Controller;

use App\Entity\GroupMeetingAttendance;
use App\Entity\Mother;
use App\Service\GroupMeetingManagerInterface;
use App\Service\MotherManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MotherController extends AbstractController
{
    /**
     * @Route("/group-meetings/mother/{groupMeetingAttendance}", name="mother_in_group_meeting")
     */
    public function showMotherInGroupMeetingContext(GroupMeetingAttendance $groupMeetingAttendance)
    {

        $children = $groupMeetingAttendance
          ->getPerson()
          ->getChildren()
          ->toArray();

        // Sort children by first name.
        usort($children, function($a, $b) {
            return strcmp($a->getFirstName(), $b->getFirstName());
        });


        return $this->render('mother/measurements_weight.html.twig', [
            'group_meeting_attendance' => $groupMeetingAttendance,
            'children' => $children,
        ]);
    }
}
