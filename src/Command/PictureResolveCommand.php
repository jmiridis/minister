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
use Symfony\Component\HttpKernel\KernelInterface;

use App\Entity\Picture;

#[AsCommand(
    name: 'app:picture-resolve',
    description: 'Resolves all pictures',
)]
class PictureResolveCommand extends Command
{
    private KernelInterface        $kernel;
    private EntityManagerInterface $em;
    private CacheManager           $imagineCacheManager;
    private FilterManager          $imagineFilterManager;
    private DataManager            $imagineDataManager;

    public function __construct(
        KernelInterface        $kernel,
        EntityManagerInterface $em,
        CacheManager           $imagineCacheManager,
        FilterManager          $imagineFilterManager,
        DataManager            $imagineDataManager
    ) {
        parent::__construct();
        $this->kernel               = $kernel;
        $this->em                   = $em;
        $this->imagineCacheManager  = $imagineCacheManager;
        $this->imagineFilterManager = $imagineFilterManager;
        $this->imagineDataManager   = $imagineDataManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            //$uploadPath = $this->kernel->getProjectDir() . '/public/images/gallery';
            $uploadPath = 'images/gallery';
            $count      = 0;
            foreach ($this->em->getRepository(Picture::class)->findAll() as $picture) {
                $io->note(sprintf('Processing image #%s:  %s ...', $picture->getId(), $picture->getImage()));
                $filter = 'picture_gallery';
                /** @var Picture $picture */
                $imagePath = sprintf('%s/%s', $uploadPath, $picture->getImage());
                $image     = $this->imagineFilterManager->applyFilter(
                    $this->imagineDataManager->find($filter, $imagePath),
                    $filter
                );
                $this->imagineCacheManager->store($image, $picture->getImage(), $filter);
                $count++;
            }

            $io->success("$count images have been resolved.");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
