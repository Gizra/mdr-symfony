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

class DownloadPhotosFromBackend extends Command
{

    use LockableTrait;

    protected static $defaultName = 'sync:fetch-photos';

    private $params;
    private $entityManager;

    const BATCH_SIZE = 50;

    public function __construct(ContainerBagInterface $params, EntityManagerInterface $entityManager)
    {
        $this->params = $params;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Download Photos from backend')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Fetch photos (i.e. download the file) for photos that are marked for fetching.')
            ->addArgument('overwrite_photos', InputArgument::OPTIONAL, 'When downloading images, if file already exists, overwrite it.', false);
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        // Get total count of Photos with the `remoteUri` populated.
        $ChildMeasurementsPhotoRepository = $this
            ->entityManager
            ->getRepository(ChildMeasurementsPhoto::class);

        $totalCount = $ChildMeasurementsPhotoRepository->createQueryBuilder('t')
            ->select('COUNT(t)')
            ->where('t.remoteUri IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        if (!$totalCount) {
            $output->writeln('No photos found for fetching.');
            return 0;
        }

        $output->writeln(sprintf('%d photos found for fetching.', $totalCount));

        $backendInfo = $this->params->get('ihangane');
        $accessToken = $backendInfo['access_token'];

        $counter = 0;
        $overwritePhotos = $input->getArgument('overwrite_photos');

        while ($counter <= $totalCount) {

            ++$counter;
            // Process in batches.
            $result = $ChildMeasurementsPhotoRepository->createQueryBuilder('t')
                ->where('t.remoteUri IS NOT NULL')
                ->orderBy('t.timestamp', 'ASC')
                ->setMaxResults(self::BATCH_SIZE)
                ->getQuery()
                ->getResult();

            /** @var ChildMeasurementsPhoto $photo */
            foreach ($result as $photo) {

                $groupMeetingAttendance = $photo->getGroupMeetingAttendance();
                $directory = $this->params->get('child_photos_directory') . '/' . $groupMeetingAttendance->getGroupMeeting()->getId();
                // @todo: Fix the file extension, currently hardcoded to `jpg`.
                $filename = $groupMeetingAttendance->getPerson()->getId() . '.jpg';

                // Check file doesn't exist already, or we explicitly want to
                // overwrite.
                if (!file_exists($directory.'/' .$filename) || $overwritePhotos) {
                    $output->writeln('Try to get photo ' . $photo->getRemoteUri() . '&access_token='.$accessToken);
                    $image = @file_get_contents($photo->getRemoteUri() . '&access_token='.$accessToken);

                    if (!$image) {
                        $output->writeln('No image or wrong connection');
                        // No image.
                        continue;
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
                    $output->writeln('Photo already exists ' . $photo->getFile());
                }

                // Remove the Uri, to unmark it for download.
                $photo->setRemoteUri(null);
                $this->entityManager->persist($photo);
            }

            $this->entityManager->flush();
        }

        return 0;
    }

}