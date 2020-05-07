<?php

namespace App\DataFixtures;

use App\Entity\Child;
use App\Entity\ChildMeasurements;
use App\Entity\ChildMeasurementsHeight;
use App\Entity\ChildMeasurementsWeight;
use App\Entity\GroupMeeting;
use App\Entity\GroupMeetingAttendance;
use App\Entity\Mother;
use App\Entity\Relationship;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {

        $faker = \Faker\Factory::create();

        // Create group meetings.
        $groupMeetings = [];
        for ($i = 0; $i < 3; $i++) {
            $groupMeeting = new GroupMeeting();
            $groupMeeting->setName($faker->city . ' ' . $faker->word);
            $groupMeeting->setDate($faker->dateTime);

            $manager->persist($groupMeeting);
            $groupMeetings[] = $groupMeeting;
        }

        // Create Mothers and children.
        $mothers = [];
        for ($i = 0; $i < 100; $i++) {
            $mother = new Mother();
            $mother->setFirstName($faker->firstNameFemale);
            $mother->setLastName($faker->lastName);
            $mother->setBirthdayEstimated(False);

            $counter = 0;
            for ($j = 0; $j < 3; $j++) {
                $child = new Child();
                $child->setFirstName($faker->firstNameFemale);
                $child->setLastName($faker->lastName);

                $manager->persist($child);

                // Add relationship to the mother.
                $relationship = new Relationship();
                $relationship->setChild($child);
                $relationship->setAdult($mother);

                $manager->persist($relationship);

                $groupMeetingAttendance = new GroupMeetingAttendance();
                $groupMeetingAttendance->setPerson($child);
                $groupMeetingAttendance->setGroupMeeting($groupMeetings[$counter]);
                $manager->persist($groupMeetingAttendance);

                $counter++;
                if ($counter >= count($groupMeetings)) {
                    $counter = 0;
                }

                if ($faker->numberBetween(1, 3) == 3) {
                    // Some Child should be without measurements.
                    continue;
                }

                $height = new ChildMeasurementsHeight();
                $height->setValue($faker->numberBetween(1, 20));
                $height->setGroupMeetingAttendance($groupMeetingAttendance);
                $manager->persist($height);

                $weight = new ChildMeasurementsWeight();
                $weight->setValue($faker->numberBetween(1, 20));
                $weight->setGroupMeetingAttendance($groupMeetingAttendance);
                $manager->persist($weight);
            }

            $manager->persist($mother);
            $mothers[] = $mother;
        }

        // Create group meetings attendance list.
        $counter = 0;
        foreach ($mothers as $mother) {
            $groupMeetingAttendance = new GroupMeetingAttendance();
            $groupMeetingAttendance->setPerson($mother);
            $groupMeetingAttendance->setGroupMeeting($groupMeetings[$counter]);

            $counter++;
            if ($counter >= count($groupMeetings)) {
                $counter = 0;
            }

            $manager->persist($groupMeetingAttendance);
        }

        $manager->flush();
    }
}
