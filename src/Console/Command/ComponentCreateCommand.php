<?php

namespace DeLois\ProjectGenerator\Console\Command;

use DeLois\ProjectGenerator\Console\Command\Abstracts\AbstractCreateCommand;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComponentCreateCommand extends AbstractCreateCommand {



  /**
   * @var \SplFileInfo
   */
  protected $_asset_base;


  public function __construct( $name = null, FilesystemInterface $filesystem, \SplFileInfo $asset_base ) {

    if ( $asset_base->getRealPath() === false || !$asset_base->isDir() || !$asset_base->isReadable()) {
      throw new \RuntimeException( sprintf( 'The configured Asset Base ("%s") is not a readable directory.', $asset_base->getRealPath() ) );
    }
    $this->_asset_base = $asset_base;
    parent::__construct( $name, $filesystem );

  }

  protected function configure() {

    $this->setDescription( 'Create a new Component Project from the Template' );

  }

  protected function execute( InputInterface $input, OutputInterface $output ) {

    // Capture User Input

    $this->_promptForPackageFqn( $input, $output );

    $this->_promptForVendorPackage( $input, $output );

    $this->_promptForVendorPackageDisplay( $input, $output );

    $this->_promptForHomepage( $input, $output );

    $this->_promptForAuthor( $input, $output );

    $this->_promptForDescription( $input, $output );

    $this->_promptForBaseNamespace( $input, $output );

    $this->_promptForFilesystemPath( $input, $output );


    // Copy Project Assets

    $this->_copyAssetsFrom( $this->_asset_base );


    // Complete.

    $output->writeln(
      sprintf(
        '<info>Successfully created: %s | %s (%s)</info>',
        $this->_project->getVendorDisplay(),
        $this->_project->getPackageDisplay(),
        $this->_project->getFqn()
      )
    );

  }



}
