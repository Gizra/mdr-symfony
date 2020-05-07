<?php

namespace App\Command;

use App\Entity\Adult;
use App\Entity\BackendSyncDownload;
use App\Entity\BackendSyncUpload;
use App\Entity\Child;
use App\Entity\ChildMeasurementsHeight;
use App\Entity\ChildMeasurementsPhoto;
use App\Entity\ChildMeasurementsWeight;
use App\Entity\GroupMeeting;
use App\Entity\GroupMeetingAttendance;
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
use Symfony\Contracts\HttpClient\ResponseInterface;

class UploadToBackend extends Command
{
    use LockableTrait;

    protected static $defaultName = 'sync:upload';

    private $params;
    private $entityManager;


    public function __construct(ContainerBagInterface $params, EntityManagerInterface $entityManager)
    {
        $this->params = $params;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Upload to backend')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Upload new content.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        // Save the last ID and time.
        $backendSyncUploadRepository = $this
          ->entityManager
          ->getRepository(BackendSyncUpload::class);

        $totalCount = $backendSyncUploadRepository->createQueryBuilder('t')
          ->select('COUNT(t)')
          ->getQuery()
          ->getSingleScalarResult();

        if (!$totalCount) {
            $output->writeln('<info>No content found for uploading.</info>');

            return 0;
        }

        $backendInfo = $this->params->get('ihangane');
        $backendUrl = $backendInfo['backend_url'];
        $accessToken = $backendInfo['access_token'];
        $authorityUuid = $backendInfo['authority_uuid'];

        $client = HttpClient::create();

        $query = [
          'access_token' => $accessToken,
          'db_version' => $backendInfo['db_version'],
        ];

        $counter = 0;
        // If we have to exit, we do it inside the while.
        $continueLoop = true;

        while ($counter < $totalCount && $continueLoop) {
            ++$counter;

            // Process one by one.
            $result = $backendSyncUploadRepository->createQueryBuilder(
              't'
            )
              ->orderBy('t.timestamp', 'ASC')
              ->setMaxResults(1)
              ->getQuery()
              ->getResult();

            /** @var BackendSyncUpload $backendSyncUpload */
            $backendSyncUpload = reset($result);

            $classNames = [
              GroupMeeting::class,
              ChildMeasurementsHeight::class,
              ChildMeasurementsWeight::class,
              ChildMeasurementsPhoto::class,
              Person::class,
              Relationship::class,
            ];

            // Match to types we got from
            // ChildMeasurementsController::markBackendSyncUpload
            foreach ($classNames as $className) {
                $value = explode('\\', $className);
                $type = end($value);
                $type = strtolower($type);

                if ($type == $backendSyncUpload->getType()) {
                    $object = $this
                        ->entityManager
                        ->getRepository($className)
                        ->find($backendSyncUpload->getId());

                    break;
                }
            }

            if (!$object) {
                throw new \Exception(sprintf('Type %s is unknown.', $backendSyncUpload->getType()));
            }

            // Upload to backend.
            // @todo: Implement other entities. For now we take care only of
            // Child measurements.
            $backendUrlForUpload = $backendUrl . '/api/sync';

            $isAuthority = true;

            if ($object instanceof Person) {
                // Currently people belong to all authorities.
                $isAuthority = false;
            }

            if ($isAuthority) {
                $backendUrlForUpload .= '/' . $authorityUuid;
            }

            // @todo: Use desarlizer.
            $item = $this->prepareItem($backendSyncUpload, $object);

            $removeFromQueue = false;
            $showSuccessOrFailureMessage = true;

            if (empty($item)) {
                // @todo: Once we'll process all entities we won't need this
                // case.
                $output->writeln(sprintf('<comment>For now we skip uploading object %s</comment>', $backendSyncUpload->getId()));
                $removeFromQueue = true;
                $statusSuccess = false;
                $showSuccessOrFailureMessage = false;
            }
            else {
                $params = [
                    // `changes` is the key teh backend is expecting.
                    'changes' => [
                        $item,
                    ],
                ];

                $response = $client->request('POST', $backendUrlForUpload, [
                    'body' => $params,
                    'query' => $query,
                ]);

                $statusSuccess = $response->getStatusCode() === 200;
            }

            if ($showSuccessOrFailureMessage) {
                if ($statusSuccess) {
                    $output->writeln(sprintf('<info>Uploaded successfully %s</info>', $backendSyncUpload->getId()));
                }
                else {
                    $output->writeln(sprintf('<error>Uploaded error %s</error>', $backendSyncUpload->getId()));

                    if (!empty($response)) {
                        dump($response->getStatusCode());
                        dump($response->getContent());
                    }
                }
            }


            if ($statusSuccess || $removeFromQueue) {
                $this->entityManager->remove($backendSyncUpload);
                $this->entityManager->flush();
            }
        }

        return 0;
    }

    private function prepareItem(BackendSyncUpload $backendSyncUpload, $object) {
        if ($object instanceof ChildMeasurementsHeight) {
            $data = $this->prepareChildMeasurementsHeight($object);
            $type = 'height';
        }
        elseif ($object instanceof ChildMeasurementsWeight) {
            $data = $this->prepareChildMeasurementsWeight($object);
            $type = 'weight';
        }
        else {
            // @todo: For now we process only Height and Weight.
            return;
        }

        return [
            'uuid' => $object->getId(),
            'data' => $data,
            'type' => $type,
            'method' => $backendSyncUpload->getExistsOnBackend() ? 'PATCH' : 'POST',
        ];
    }

    private function prepareChildMeasurementsHeight(ChildMeasurementsHeight $object) {
        return [
            'height' => $object->getValue(),
            'session' => $object->getGroupMeetingAttendance()->getGroupMeeting()->getId(),
            'person' => $object->getGroupMeetingAttendance()->getPerson()->getId(),
            'date_measured' => $object->getTimestamp()->format('Y-m-d'),
        ];
    }

    private function prepareChildMeasurementsWeight(ChildMeasurementsWeight $object) {
        return [
          'weight' => $object->getValue(),
          'session' => $object->getGroupMeetingAttendance()->getGroupMeeting()->getId(),
          'person' => $object->getGroupMeetingAttendance()->getPerson()->getId(),
          'date_measured' => $object->getTimestamp()->format('Y-m-d'),
        ];
    }
}