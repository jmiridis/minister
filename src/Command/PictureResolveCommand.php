<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Picture;

#[AsCommand(
    name: 'app:picture-resolve',
    description: 'Resolves all pictures',
)]
class PictureResolveCommand extends Command
{
    private EntityManagerInterface $em;
    private CacheManager           $imagineCacheManager;
    private FilterManager          $imagineFilterManager;
    private DataManager            $imagineDataManager;

    public function __construct(
        EntityManagerInterface $em,
        CacheManager           $imagineCacheManager,
        FilterManager          $imagineFilterManager,
        DataManager            $imagineDataManager
    ) {
        parent::__construct();
        $this->em                   = $em;
        $this->imagineCacheManager  = $imagineCacheManager;
        $this->imagineFilterManager = $imagineFilterManager;
        $this->imagineDataManager   = $imagineDataManager;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->em->getConnection()->beginTransaction();
        try {
            $uploadPath = 'images/gallery';
            $filter     = 'picture_gallery';
            $count      = 0;
            foreach ($this->em->getRepository(Picture::class)->findAll() as $picture) {
                $output->write(sprintf('Processing image #%s:  %s ...', $picture->getId(), $picture->getImage()));
                /** @var Picture $picture */
                $imagePath = sprintf('%s/%s', $uploadPath, $picture->getImage());
                if (0 === filesize($imagePath)) {
                    $this->em->remove($picture);
                    $output->writeln(' deleted.');
                    continue;
                }

                $image = $this->imagineFilterManager->applyFilter(
                    $this->imagineDataManager->find($filter, $imagePath),
                    $filter
                );
                $this->imagineCacheManager->store($image, $picture->getImage(), $filter);
                $count++;
            }
//            $this->em->getConnection()->rollBack();
            $this->em->getConnection()->commit();

            $io->success("$count images have been resolved.");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
