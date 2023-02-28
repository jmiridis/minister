<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Entity\Picture;

#[AsCommand(
    name: 'app:picture-import',
    description: 'Imports pictures from a given directory relative to the project root',
)]
class PictureImportCommand extends Command
{
    private string                 $projectDir;
    private EntityManagerInterface $em;

    private OutputInterface $output;

    public function __construct(#[Autowire('%kernel.project_dir%')] $projectDir, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
        $this->em         = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $io           = new SymfonyStyle($input, $output);

        try {
            $uploadPath = $this->projectDir . '/uploads';
            $processed  = $this->import($uploadPath);
            if ($processed > 0) {
                $io->note('Flushing changes to database...');
                $this->em->flush();
            }
            $io->success("$processed files have been processed.");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @throws Exception
     */
    private function import(string $uploadPath): int
    {
        $pendingPath   = "$uploadPath/pending";
        $processedPath = "$uploadPath/processed";
        $tempPath      = "$uploadPath/temp";

        $processed = 0;
        $finder    = new Finder();
        $finder->files()->in($pendingPath);
        foreach ($finder as $file) {

            $filename = $file->getFilename();
            $this->output->writeln("\t- processing $filename");

            $this->processFile($filename, $pendingPath, $processedPath, $tempPath);

            $processed++;
        }

        return $processed;
    }

    /**
     * @throws Exception
     */
    private function processFile(string $filename, string $pendingPath, string $processedPath, string $tempPath)
    {
        $sourceFile = "$pendingPath/$filename";
        $tempFile   = "$tempPath/$filename";
        if (false === copy($sourceFile, $tempFile)) {
            throw new Exception("Could not copy $sourceFile to $tempFile");
        }

        $imageFile = new UploadedFile($tempFile, $filename, null, null, true);
        if (false === rename($sourceFile, "$processedPath/$filename")) {
            throw new Exception("Could not move $sourceFile to $processedPath/$filename");
        }

        $picture = (new Picture())
            ->setImageFile($imageFile)
            ->setIsActive(false);
        $this->em->persist($picture);
    }
}
