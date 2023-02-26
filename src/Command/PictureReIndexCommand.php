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
    name: 'app:picture-re-index',
    description: 'Re-index all picture positions',
)]
class PictureReIndexCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $conn       = $this->em->getConnection();
            if(true) {
                $io->note('Removing soft-deleted images ...');
                $affectedRows = $conn->executeStatement("DELETE FROM picture WHERE deleted_at IS NOT NULL");
                $io->note(sprintf('Removed %s images.', $affectedRows));
            }

            $orderedIds = $conn->executeQuery("SELECT id FROM picture ORDER BY position ASC")
                ->fetchAllAssociative();

            $io->note(sprintf('Re-indexing %s images ...', count($orderedIds)));

            foreach ($orderedIds as $idx => $record) {
                $position = $idx + 1;
                $id = $record['id'];
                $query    = "UPDATE picture SET position = $position WHERE id = $id";
                $conn->executeStatement($query);
            }

            $io->success("done.");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
