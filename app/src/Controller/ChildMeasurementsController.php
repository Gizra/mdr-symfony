<?php

namespace App\Controller;

use App\Entity\BackendSyncUpload;
use App\Entity\ChildMeasurements;
use App\Entity\ChildMeasurementsPhoto;
use App\Entity\ChildMeasurementsWeight;
use App\Entity\GroupMeetingAttendance;
use App\Entity\SettableUuidAndTimestampInterface;
use App\Form\Type\ChildMeasurementsPhotoType;
use App\Form\Type\ChildMeasurementsWeightType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChildMeasurementsController extends AbstractController
{
    /**
     * @Route("/group-meetings/child/weight/{groupMeetingAttendance}", name="child_measurements_weight")
     */
    public function showChildMeasurementsWeight(
      GroupMeetingAttendance $groupMeetingAttendance,
      Request $request,
      EntityManagerInterface $entityManager
    )
    {

        $groupMeeting = $groupMeetingAttendance->getGroupMeeting();
        $child = $groupMeetingAttendance->getPerson();

        // Check if Child already has measurements for the selected group
        // meeting.
        $allMeasurements = $groupMeetingAttendance->getMeasurements() ?: [];
        $hasExistingMeasurements = true;

        $currentMeasurements = null;

        foreach ($allMeasurements as $measurements) {
            if ($measurements instanceof ChildMeasurementsWeight) {
                $currentMeasurements = $measurements;
                break;
            }
        }

        $existsOnBackend = true;
        if (!$currentMeasurements) {
            // New measurements
            $currentMeasurements = new ChildMeasurementsWeight();
            $currentMeasurements->setGroupMeetingAttendance($groupMeetingAttendance);
            $hasExistingMeasurements = false;
            $existsOnBackend = false;
        }

        $form = $this->createForm(ChildMeasurementsWeightType::class, $currentMeasurements);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($currentMeasurements);

            // Mark for upload.
            $this->markBackendSyncUpload($entityManager, $currentMeasurements, $existsOnBackend);

            $entityManager->flush();

            // Reload page.
            return $this->redirect($request->getUri());
        }

        return $this->render('child/measurements_weight.html.twig', [
          'child' => $child,
          'measurements' => $currentMeasurements,
          'has_existing_measurements' => $hasExistingMeasurements,
          'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/group-meetings/child/photo/{groupMeetingAttendance}", name="child_measurements_photo")
     */
    public function showChildMeasurementsPhoto(
      GroupMeetingAttendance $groupMeetingAttendance,
      Request $request,
      EntityManagerInterface $entityManager
    )
    {

        $groupMeeting = $groupMeetingAttendance->getGroupMeeting();
        $child = $groupMeetingAttendance->getPerson();

        // Check if Child already has measurements for the selected group
        // meeting.
        $allMeasurements = $groupMeetingAttendance->getMeasurements() ?: [];
        $hasExistingMeasurements = false;

        $currentMeasurements = null;

        foreach ($allMeasurements as $measurements) {
            if ($measurements instanceof ChildMeasurementsPhoto) {
                $currentMeasurements = $measurements;
                break;
            }
        }

        $existsOnBackend = true;
        if (!$currentMeasurements) {
            // New measurements
            $currentMeasurements = new ChildMeasurementsPhoto();
            $currentMeasurements->setGroupMeetingAttendance($groupMeetingAttendance);
            $existsOnBackend = false;
        }

        $form = $this->createForm(ChildMeasurementsPhotoType::class, $currentMeasurements);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photoFile */
            $photoFile = $form['file']->getData();


            if ($photoFile) {
                $directory = $this->getParameter('child_photos_directory') . '/' . $groupMeetingAttendance->getGroupMeeting()->getId();
                if (!is_dir($directory)) {
                    // Dir doesn't exist, make it.
                    mkdir($directory, 0777, true);
                }

                $filename = $groupMeetingAttendance->getPerson()->getId() . $photoFile->guessExtension();

                // Move the file to the directory where images are stored
                $photoFile->move(
                  $directory,
                  $filename
                );

                $currentMeasurements->setFile($filename);
            }

            $entityManager->persist($currentMeasurements);

            // Mark for upload.
            $this->markBackendSyncUpload($entityManager, $currentMeasurements, $existsOnBackend);

            $entityManager->flush();

            // Reload page.
            return $this->redirect($request->getUri());
        }

        return $this->render('child/measurements_photo.html.twig', [
          'child' => $child,
          'measurements' => $currentMeasurements,
          'has_existing_measurements' => $hasExistingMeasurements,
          'form' => $form->createView(),
        ]);
    }

    private function markBackendSyncUpload(EntityManagerInterface $entityManager, SettableUuidAndTimestampInterface $currentMeasurements, bool $existsOnBackend) {
        $backendSyncUploadRepository = $entityManager->getRepository(BackendSyncUpload::class);
        if ($backendSyncUploadRepository->find($currentMeasurements->getId())) {
            // Already saved.
            return;
        }

        $className = explode('\\', get_class($currentMeasurements));
        $type = end($className);
        $type = strtolower($type);

        $backendSyncUpload = new BackendSyncUpload();
        $backendSyncUpload->setId($currentMeasurements->getId());
        $backendSyncUpload->setType($type);
        $backendSyncUpload->setExistsOnBackend($existsOnBackend);
        $entityManager->persist($backendSyncUpload);
    }
}
