<?php

namespace DeLois\ProjectGenerator\Console\Command;

use DeLois\ProjectGenerator\Console\Command\Abstracts\AbstractCreateCommand;
use DeLois\ProjectGenerator\Filter\TokenReplacementFilter;
use DeLois\ProjectGenerator\Model\Author;
use DeLois\ProjectGenerator\Model\Project;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ComponentCreateCommand extends AbstractCreateCommand {

  /**
   * @var \DeLois\ProjectGenerator\Model\Project
   */
  protected $_project;

  /**
   * @var \SplFileInfo
   */
  protected $_asset_base;

  /**
   * @var \League\Flysystem\FilesystemInterface
   */
  protected $_filesystem;


  public function __construct( $name = null, \SplFileInfo $asset_base, FilesystemInterface $filesystem ) {

    if ( $asset_base->getRealPath() === false || !$asset_base->isDir() || !$asset_base->isReadable()) {
      throw new \RuntimeException( sprintf( 'The configured Asset Base ("%s") is not a readable directory.' ) );
    }
    $this->_asset_base = $asset_base;
    $this->_filesystem = $filesystem;
    parent::__construct( $name );

  }

  protected function configure() {

    $this->setDescription( 'Create a new Component Project from the Template' );

  }

  protected function execute( InputInterface $input, OutputInterface $output ) {

    $helper = $this->getHelper( 'question' );

    $question_fqn = ( new Question( 'Project FQN (e.g., "jimdelois/my-project"): ' ) )
      ->setValidator( function ( $input ) {
        $this->_project = new Project( $input );
      } );


    $helper->ask( $input, $output, $question_fqn );

    $question_vendor  = new Question( sprintf( 'Vendor (%s): ', $this->_project->getVendor() ) , $this->_project->getVendor() );
    $vendor           = $helper->ask( $input, $output, $question_vendor );
    $this->_project->setVendor( $vendor );
    $this->_project->setVendorDisplayFrom( $vendor );

    $question_package = new Question( sprintf( 'Package Name (%s): ', $this->_project->getPackage() ), $this->_project->getPackage() );
    $package          = $helper->ask( $input, $output, $question_package );
    $this->_project->setPackage( $package );
    $this->_project->setPackageDisplayFrom( $package );

    $question_vd      = new Question( sprintf( 'Vendor Display (%s): ', $this->_project->getVendorDisplay() ) , $this->_project->getVendorDisplay() );
    $vendor_display   = $helper->ask( $input, $output, $question_vd );
    $this->_project->setVendorDisplay( $vendor_display );

    $question_pd      = new Question( sprintf( 'Package Display (%s): ', $this->_project->getPackageDisplay() ) , $this->_project->getPackageDisplay() );
    $package_display  = $helper->ask( $input, $output, $question_pd );
    $this->_project->setPackageDisplay( $package_display );

    $default_site     = 'https://github.com/' . $this->_project->getFqn();
    $question_site    = new Question( sprintf( 'Package Website (%s): ', $default_site ) , $default_site );
    $package_site     = $helper->ask( $input, $output, $question_site );
    $this->_project->setHomepage( $package_site );

    $question_name    = new Question( sprintf( 'Author Name (%s): ', Author::DEFAULT_NAME ) , Author::DEFAULT_NAME );
    $name             = $helper->ask( $input, $output, $question_name );

    // TODO: Add Validation
    $question_email   = new Question( sprintf( 'Author Email (%s): ', Author::DEFAULT_EMAIL ) , Author::DEFAULT_EMAIL );
    $email            = $helper->ask( $input, $output, $question_email );

    // TODO: Add Validation
    $question_website = new Question( 'Author Homepage (default none): ' );
    $homepage         = $helper->ask( $input, $output, $question_website );

    $author           = new Author( $name, $email );

    if ( $homepage ) {
      $author->setHomepage( $homepage );
    }

    $this->_project->addAuthor( $author );

    $question_desc    = new Question( 'Project Description: ' );
    $description      = $helper->ask( $input, $output, $question_desc );
    $this->_project->describeAs( $description );

    // TODO: Add Validation
    $question_ns      = new Question( 'Base Namespace: ' );
    $namespace        = $helper->ask( $input, $output, $question_ns );
    $this->_project->setNamespace( $namespace );

    // TODO: Add Validation
    $default_path     = Project::DEFAULT_DIR . $vendor . DIRECTORY_SEPARATOR . $package;
    $question_path    = new Question( sprintf( 'Filesystem Path (%s): ', $default_path ), $default_path );
    $path             = $helper->ask( $input, $output, $question_path );

    $file             = new \SplFileInfo( $path );
    $this->_project->installAt( $file, $this->_filesystem );





    $target_dir_path = $file->getRealPath();

    $this->_asset_base->getRealPath();

    $it = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator(
        $this->_asset_base->getRealPath(),
        \RecursiveDirectoryIterator::SKIP_DOTS
      ),
      \RecursiveIteratorIterator::SELF_FIRST
    );

    foreach( $it as $file ) {

      $filename_from = str_replace( $this->_asset_base->getRealPath(), '', $file->getRealPath() );
      $filename_to   = $target_dir_path . $filename_from;

      if ( $file->isDir() ) {
        $this->_filesystem->createDir( $target_dir_path . $filename_from );
        continue;
      }

      $stream = fopen( $file->getRealPath(), 'r+');

      $this->_registerStreamFilters( $stream );

      $this->_filesystem->writeStream( $filename_to, $stream );

      fclose( $stream );

    }

    $output->writeln(
      sprintf(
        '<info>Successfully created: %s | %s (%s)</info>',
        $this->_project->getVendorDisplay(),
        $this->_project->getPackageDisplay(),
        $this->_project->getFqn()
      )
    );

  }

  private function _registerStreamFilters( $stream ) {

    // TODO: Fix this.
    $author = $this->_project->getAuthors()[0];

    $filters = [
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_NAMESPACE__\\',
        TokenReplacementFilter::PARAM_KEY_REPLACE => ltrim( $this->_project->getNamespace(), '\\' )
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_NAMESPACE_COMPOSER__\\\\',
        TokenReplacementFilter::PARAM_KEY_REPLACE => str_replace( '\\', '\\'.'\\', $this->_project->getNamespace() )
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_FQN__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $this->_project->getFqn()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_VENDOR__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $this->_project->getVendor()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_PACKAGE__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $this->_project->getPackage()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_VENDOR_DISPLAY__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $this->_project->getVendorDisplay()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_PACKAGE_DISPLAY__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $this->_project->getPackageDisplay()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_DESCRIPTION__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $this->_project->getDescription()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_HOMEPAGE__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $this->_project->getHomepage()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_AUTHOR_NAME__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $author->getName()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_AUTHOR_EMAIL__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $author->getEmail()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_AUTHOR_HOMEPAGE__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => $author->getHomepage()
      ],
      [
        TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_YEAR__',
        TokenReplacementFilter::PARAM_KEY_REPLACE => date( 'Y' ) // TODO: Don't
      ],
    ];

    foreach( $filters as $filter_params ) {

      stream_filter_append(
        $stream,
        TokenReplacementFilter::DEFAULT_NAME,
        \STREAM_FILTER_READ,
        $filter_params
      );

    }

  }

}
