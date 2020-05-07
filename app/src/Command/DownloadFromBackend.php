<?php

namespace App\Command;

use App\Entity\Adult;
use App\Entity\BackendSyncDownload;
use App\Entity\Child;
use App\Entity\ChildMeasurementsHeight;
use App\Entity\ChildMeasurementsPhoto;
use App\Entity\ChildMeasurementsWeight;
use App\Entity\GroupMeeting;
use App\Entity\GroupMeetingAttendance;
use App\Entity\Measurements;
use App\Entity\Mother;
use App\Entity\Person;
use App\Entity\Relationship;
use App\Entity\SettableUuidAndTimestampInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata as ClassMetadataAlias;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DownloadFromBackend extends Command
{
    use LockableTrait;

    protected static $defaultName = 'sync:download';

    private $params;
    private $entityManager;

    /**
     * @var array
     *
     * As there are duplicated UUID on the server, we flush every time
     * so we don't try to recreate them here as-well.
     * @todo: https://github.com/Gizra/ihangane/issues/1473
     */
    private $entities;

    public function __construct(ContainerBagInterface $params, EntityManagerInterface $entityManager)
    {
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->entities = [];

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Download from backend')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Will contact backend, and download latest data both from "general", and "authority".')

            ->addArgument('defer_photos_download', InputArgument::OPTIONAL, 'When syncing photos, we will not fetch the images. Instead they will be marked for a later download, thus making the sync process faster.', false)
            ->addArgument('overwrite_photos', InputArgument::OPTIONAL, 'When downloading images, if file already exists, overwrite it.', false);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $classNames = [
          GroupMeeting::class,
          ChildMeasurementsHeight::class,
          ChildMeasurementsWeight::class,
          ChildMeasurementsPhoto::class,
          Person::class,
          Relationship::class,
        ];

        // Allow setting UUID.
        foreach ($classNames as $className) {
            $metadata = $this->entityManager->getClassMetaData($className);
            $metadata->setIdGeneratorType(ClassMetadataAlias::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());
        }

        // Save the last ID and time.
        $backendSyncs = $this
          ->entityManager
          ->getRepository(BackendSyncDownload::class)
          ->findAll();

        if (!empty($backendSyncs)) {
            $backendSync = reset($backendSyncs);
        }
        else {
            $backendSync = new BackendSyncDownload();
            $backendSync->setLastIdGeneral(1);
            $backendSync->setLastIdAuthority(1);
        }

        $backendInfo = $this->params->get('ihangane');
        $backendUrl = $backendInfo['backend_url'];
        $accessToken = $backendInfo['access_token'];
        $authorityUuid = $backendInfo['authority_uuid'];
        $lastIdGeneral = $backendSync->getLastIdGeneral();

        $output->writeln('Trying to download from: <info>'.$backendUrl.'/api/sync</info> from revision ID '.'<info>'.$lastIdGeneral.'</info>');

        $client = HttpClient::create();

        $query = [
          'access_token' => $accessToken,
          'db_version' => $backendInfo['db_version'],
        ];

        $progressBarGeneral = null;

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] -- %message%');

        // If we have to exit, we do it inside the while.
        $continueLoop = true;
        $dataFound = false;
        $responseError = false;

        while (true && $continueLoop) {
            $query['base_revision'] = $lastIdGeneral;

            $response = $client->request('GET', $backendUrl . '/api/sync', ['query' => $query]);
            $statusCode = $response->getStatusCode();
            $content = $response->toArray();
            if ($statusCode !== 200) {
                $this->statusCodeError($output, $response, $backendSync, true);
                $continueLoop = false;
                $responseError = true;
            }
            elseif (empty($content['data']['batch'])) {
                $this->endOfBatch($output, $backendSync, $lastIdGeneral, $dataFound, true);
                $continueLoop = false;
            }
            else {
                // We have data.
                $dataFound = true;
                if (empty($progressBarGeneral)) {
                    $progressBarGeneral = $this->initProgressBar($output, $response);
                    $progressBarGeneral->setFormat('very_verbose');
                }

                $progressBarGeneral->setMessage('Download from revision ID: <info>'.$lastIdGeneral.'</info>');

                foreach ($content['data']['batch'] as $row) {
                    $lastIdGeneral = $row['vid'];
                    $output->writeln('Revision ' . $lastIdGeneral);

                    switch ($row['type']) {
                        case 'person':
                            $this->processPerson($row);
                            break;

                        case 'relationship':
                            $this->processRelationship($row);
                            break;

                        case 'session':
                            $this->processGroupMeeting($row);
                            break;

                        default:
                            break;
                    }

                }

                $this->finalizeLoop($backendSync, $progressBarGeneral, $lastIdGeneral, true);
            }
        }

        if (!empty($progressBarGeneral)) {
            $progressBarGeneral->finish();
        }

        if ($responseError) {
            return 0;
        }

        // Download authority specific data.
        $lastIdAuthority = $backendSync->getLastIdAuthority();
        $output->writeln('Trying to download from: <info>'.$backendUrl.'/api/sync/'.$authorityUuid . '</info> from revision ID '.'<info>'.$lastIdAuthority.'</info>');
        $progressBarAuthority = null;

        // If we have to exit, we do it inside the while.
        $continueLoop = true;
        $dataFound = false;
        while (true && $continueLoop) {
            $query['base_revision'] = $lastIdAuthority;

            $response = $client->request('GET', $backendUrl . '/api/sync/'.$authorityUuid, ['query' => $query]);
            $statusCode = $response->getStatusCode();
            $content = $response->toArray();
            if ($statusCode !== 200) {
                $this->statusCodeError($output, $response, $backendSync, false);
                $continueLoop = false;
            }
            elseif (empty($content['data']['batch'])) {
                $this->endOfBatch($output, $backendSync, $lastIdAuthority, $dataFound, false);
                $continueLoop = false;
            }
            else {
                // We have data.
                $dataFound = true;
                if (empty($progressBarAuthority)) {
                    $progressBarAuthority = $this->initProgressBar($output, $response);
                    $progressBarAuthority->setFormat('very_verbose');
                }

                $progressBarAuthority->setMessage('Download from revision ID: <info>'.$lastIdAuthority.'</info>');

                foreach ($content['data']['batch'] as $row) {
                    $lastIdAuthority = $row['vid'];
                    $output->writeln('Revision ' . $lastIdAuthority);

                    switch ($row['type']) {
                        case 'height':
                            $object = new ChildMeasurementsHeight();
                            $this->processChildMeasurementsValue($row, $object);
                            break;

                        case 'weight':
                            $object = new ChildMeasurementsWeight();
                            $this->processChildMeasurementsValue($row, $object);
                            break;

                        case 'photo':
                            $this->processPhoto($row, $accessToken, $input, $output);
                            break;

                        case 'relationship':
                            $this->processRelationship($row);
                            break;

                        default:
                            break;
                    }
                }

                $this->finalizeLoop($backendSync, $progressBarAuthority, $lastIdAuthority, false);
            }
        }

        if (!empty($progressBarAuthority)) {
            $progressBarAuthority->finish();
        }

        $output->writeln("\n\r<info>Done</info>");

        return 0;
    }

    private function statusCodeError(OutputInterface $output, ResponseInterface $response, BackendSyncDownload $backendSync, bool $isGeneral = true) {
        // Error response.
        $output->writeln("\n\rGot status code " . $response->getStatusCode());


        $func = $isGeneral ? 'setLastTryGeneral' : 'setLastTryAuthority';
        $now = new \DateTime();
        $backendSync->{$func}($now);
        $this->entityManager->persist($backendSync);
        $this->entityManager->flush();
    }

    private function endOfBatch(OutputInterface $output, BackendSyncDownload $backendSync, int $lastId, bool $dataFound, $isGeneral = true) {
        if (!$dataFound) {
            // On first iteration we didn't find data.
            $output->writeln("\n\r<info>No new data</info>");
        }

        $func = $isGeneral ? 'setLastIdGeneral' : 'setLastIdAuthority';
        $backendSync->{$func}($lastId);

        $func = $isGeneral ? 'setLastSuccessGeneral' : 'setLastSuccessAuthority';
        $now = new \DateTime();
        $backendSync->{$func}($now);
        $this->entityManager->persist($backendSync);
        $this->entityManager->flush();
    }

    private function initProgressBar(OutputInterface $output, ResponseInterface $response) {
        $content = $response->toArray();
        // How many times we would need to download.
        $pageCount = $content['data']['revision_count'] / count($content['data']['batch']);

        // creates a new progress bar.
        $progressBar = new ProgressBar($output, $pageCount);
        $progressBar->setFormat('custom');

        // starts and displays the progress bar
        $progressBar->start();
        return $progressBar;
    }

    private function finalizeLoop(BackendSyncDownload $backendSync, ProgressBar $progressBar, int $lastId, bool $isGeneral = true) {
        $func = $isGeneral ? 'setLastIdGeneral' : 'setLastIdAuthority';
        $backendSync->{$func}($lastId);

        $func = $isGeneral ? 'setLastSuccessGeneral' : 'setLastSuccessAuthority';
        $now = new \DateTime();
        $backendSync->{$func}($now);
        $this->entityManager->persist($backendSync);
        $this->entityManager->flush();

        // Advances the progress bar.
        $progressBar->advance();
    }

    // Create entities

    /**
     * Create a Mother or a Child.
     *
     * A child is 13 years or below.
     *
     * @param array $row
     *
     * @throws \Exception
     */
    private function processPerson(array $row) {
        $uuid = $row['uuid'];

        if (!empty($this->entities[$uuid])) {
            // @todo: Check why entity already exists.
            return;
        }

        // @todo: Why first name not filled, when we have label?
        $label = explode(' ', $row['label'], 2);
        $label[1] = !empty($label[1]) ? $label[1] : 'unknown';

        $firstName = $row['first_name'] ?: $label[0];
        $lastName = $row['second_name'] ?: $label[1];

        $isMother = true;
        if (!empty($row['birth_date'])) {
            $timestamp = strtotime($row['birth_date']);
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            $interval = $date->diff(new \DateTime());

            // Mother is defined as above 13.
            $isMother = $interval->y > 13;
        }

        $personRepository = $this
          ->entityManager
          ->getRepository(Person::class);

        $person = $personRepository->findOneBy(['id' => $uuid]);

        if (!$person) {
            $person = $isMother ? new Mother() : new Child();
        }

        if ($isMother) {
            $person->setBirthdayEstimated($row['birth_date_estimated']);
        }

        $person->setFirstName($firstName);
        $person->setLastName($lastName);
        $person->setId($uuid);
        $this->setTimestamp($person, $row);

        $this->entityManager->persist($person);

        $this->entities[$uuid] = true;
    }

    private function getGroupMeetingAttendance(array $row) {

        $groupMeetingAttendanceRepository = $this
          ->entityManager
          ->getRepository(GroupMeetingAttendance::class);

        $groupMeetingAttendance = $groupMeetingAttendanceRepository
          ->findOneBy([
            'groupMeeting' => $row['session'],
            'person' => $row['person'],
          ]);

        if (!empty($groupMeetingAttendance)) {
            return $groupMeetingAttendance;
        }

        $groupMeeting =
            $this
                ->entityManager
                ->getRepository(GroupMeeting::class)
                ->find($row['session']);

        $person =
          $this
            ->entityManager
            ->getRepository(Person::class)
            ->find($row['person']);

        if (empty($groupMeeting) || empty($person)) {
            // @todo: Why is entity empty?
            return;
        }

        $groupMeetingAttendance = new GroupMeetingAttendance();
        $groupMeetingAttendance->setGroupMeeting($groupMeeting);
        $groupMeetingAttendance->setPerson($person);

        $this->entityManager->persist($groupMeetingAttendance);

        // Flush so we don't create duplicate attendances.
        $this->entityManager->flush();

        return $groupMeetingAttendance;
    }

    private function processChildMeasurementsValue(array $row, $childMeasurementsValueNew) {
        $uuid = $row['uuid'];

        if (!empty($this->entities[$uuid])) {
            return;
        }

        $childMeasurementsValueExisting = $this
          ->entityManager
          ->getRepository(Measurements::class)
          ->find($uuid);


        // Use existing, or a new measurements.
        $childMeasurementsValue = $childMeasurementsValueExisting ?: $childMeasurementsValueNew;

        $groupMeetingAttendance = $this->getGroupMeetingAttendance($row);
        if (!$groupMeetingAttendance) {
            // @todo: Why is attendance missing
            return;
        }


        if ($childMeasurementsValue instanceof ChildMeasurementsHeight) {
            $value = $row['height'];
        }
        elseif ($childMeasurementsValue instanceof ChildMeasurementsWeight) {
            $value = $row['weight'];
        }
        else {
            throw new \Exception(sprintf('For now it an unknown class %s , in DownloadFromBackend::processChildMeasurementsValue', get_class($childMeasurementsValue)));
        }

        $childMeasurementsValue->setGroupMeetingAttendance($groupMeetingAttendance);
        $childMeasurementsValue->setValue($value);
        $childMeasurementsValue->setId($uuid);
        $this->setTimestamp($childMeasurementsValue, $row);

        $this->entityManager->persist($childMeasurementsValue);

        $this->entities[$uuid] = true;
    }

    private function processPhoto(array $row, $accessToken, InputInterface $input, OutputInterface $output) {
        $uuid = $row['uuid'];

        if (!empty($this->entities[$uuid])) {
            return;
        }

        $groupMeetingAttendance = $this->getGroupMeetingAttendance($row);
        if (!$groupMeetingAttendance) {
            // @todo: Why is attendance missing
            return;
        }

        $photo = $this
          ->entityManager
          ->getRepository(Measurements::class)
          ->find($uuid);

        if (!$photo) {
            $photo = new ChildMeasurementsPhoto();
        }

        $photo->setId($uuid);
        $photo->setGroupMeetingAttendance($groupMeetingAttendance);

        $directory = $this->params->get('child_photos_directory') . '/' . $groupMeetingAttendance->getGroupMeeting()->getId();
        // @todo: Fix the file extension, currently hardcoded to `jpg`.
        $filename = $groupMeetingAttendance->getPerson()->getId() . '.jpg';

        $deferPhotosDownload = $input->getArgument('defer_photos_download');
        $overwritePhotos = $input->getArgument('overwrite_photos');

        if (!$deferPhotosDownload) {
            // Check file doesn't exist already, or we explicitly want to
            // overwrite.
            if (!file_exists($directory.'/' .$filename) || $overwritePhotos) {
                $output->writeln('Try to get photo ' . $row['photo'] . '&access_token='.$accessToken);
                $image = @file_get_contents($row['photo'] . '&access_token='.$accessToken);

                if (!$image) {
                    $output->writeln('No image or wrong connection');
                    // @todo: Mark for re-download?
                    // No image.
                    return;
                }

                if (!is_dir($directory)) {
                    // Dir doesn't exist, make it.
                    mkdir($directory, 0777, true);
                }

                $output->writeln('Saving photo ...');
                file_put_contents($directory.'/' .$filename, $image);
                $output->writeln('... saved');
            }
            else {
                $output->writeln('Photo already exists');
            }
        }
        else {
            $output->writeln('Deferred photo download');
        }

        $photo->setFile($filename);

        if ($deferPhotosDownload) {
            // Set the remote URI, so we could sync it in a second go.
            $photo->setRemoteUri($row['photo']);
        }

        $this->setTimestamp($photo, $row);

        $this->entityManager->persist($photo);

        $this->entities[$uuid] = true;
    }

    private function processRelationship(array $row) {
        $uuid = $row['uuid'];

        if (!empty($this->entities[$uuid])) {
            return;
        }

        // Get or create Group meeting attendance.
        $personRepository = $this
          ->entityManager
          ->getRepository(Person::class);

        // There are cases where the "person" is an adult, and the
        // "related_to" is the child.
        $person1 = $personRepository->findOneBy(['id' => $row['person']]);
        $person2 = $personRepository->findOneBy(['id' => $row['related_to']]);

        if ($person1 instanceof Child
          && $person2 instanceof Adult
        ) {
            $child = $person1;
            $adult = $person2;
        }
        elseif ($person1 instanceof Adult
          && $person2 instanceof Child) {
            $adult = $person1;
            $child = $person2;
        }

        if (empty($adult) || empty($child)) {
            // @todo: Check why adult or child entities do not exist, or are
            // wrong (e.g. a relationship is between two children).
            return;
        }

        $relationship = $personRepository->find($uuid);
        if (!$relationship) {
            $relationship = new Relationship();
        }

        $relationship->setChild($child);
        $relationship->setAdult($adult);
        $relationship->setId($uuid);
        $this->setTimestamp($relationship, $row);

        $this->entityManager->persist($relationship);

        $this->entities[$uuid] = true;
    }

    private function processGroupMeeting(array $row) {
        $uuid = $row['uuid'];

        if (!empty($this->entities[$uuid])) {
            return;
        }

        $label = $row['label'];

        // @todo: Improve?
        $timestamp = strtotime($row['scheduled_date']['value']);
        $now = new \DateTime();
        $now->setTimestamp($timestamp);

        $groupMeetingRepository = $this
          ->entityManager
          ->getRepository(GroupMeeting::class);

        $groupMeeting = $groupMeetingRepository->find($uuid);
        if (!$groupMeeting) {
            $groupMeeting = new GroupMeeting();
        }

        $groupMeeting->setName($label);
        $groupMeeting->setDate($now);
        $groupMeeting->setId($uuid);

        $this->entities[$uuid] = true;

        $this->entityManager->persist($groupMeeting);
    }

    private function setTimestamp(SettableUuidAndTimestampInterface $object, array $row) {
        if ($object instanceof Person || $object instanceof Relationship) {
            $timestamp = $row['timestamp'];
        }
        else {
            $timestamp = strtotime($row['date_measured']);
        }

        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        $object->setTimestamp($date);
    }



}