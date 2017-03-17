<?php

namespace DeLois\ProjectGenerator\Console\Command;

use DeLois\ProjectGenerator\Console\Command\Abstracts\AbstractCreateCommand;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComponentCreateCommand extends AbstractCreateCommand
{


    /**
     * @var \SplFileInfo
     */
    protected $asset_base;


    public function __construct($name, FilesystemInterface $filesystem, \SplFileInfo $asset_base)
    {

        if ($asset_base->getRealPath() === false || !$asset_base->isDir() || !$asset_base->isReadable()) {
            throw new \RuntimeException(
                sprintf('The configured Asset Base ("%s") is not a readable directory.', $asset_base->getRealPath())
            );
        }
        $this->asset_base = $asset_base;
        parent::__construct($name, $filesystem);

    }

    protected function configure()
    {

        $this->setDescription('Create a new Component Project from the Template');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Capture User Input

        $this->promptForPackageFqn($input, $output);

        $this->promptForVendorPackage($input, $output);

        $this->promptForVendorPackageDisplay($input, $output);

        $this->promptForHomepage($input, $output);

        $this->promptForRepositoryUrl($input, $output);

        $this->promptForAuthor($input, $output);

        $this->promptForDescription($input, $output);

        $this->promptForBaseNamespace($input, $output);

        $this->promptForFilesystemPath($input, $output);

        // Copy Project Assets

        $this->copyAssetsFrom($this->asset_base);

        // Composer

        $this->composerRequire($this->asset_base, $output);

        // Source Control

        $this->gitInit($output);

        // Complete.

        $output->writeln(
            sprintf(
                '<info>Successfully created: %s | %s (%s)</info>',
                $this->project->getVendorDisplay(),
                $this->project->getPackageDisplay(),
                $this->project->getFqn()
            )
        );

    }


}
