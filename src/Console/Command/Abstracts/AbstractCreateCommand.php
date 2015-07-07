<?php

namespace DeLois\ProjectGenerator\Console\Command\Abstracts;

use DeLois\ProjectGenerator\Filter\TokenReplacementFilter;
use DeLois\ProjectGenerator\Model\Author;
use DeLois\ProjectGenerator\Model\Project;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCreateCommand extends Command {

  /**
   * @var \DeLois\ProjectGenerator\Model\Project
   */
  protected $_project;

  /**
   * @param \Symfony\Component\Console\Helper\QuestionHelper
   */
  protected $_prompt;

  /**
   * @var \League\Flysystem\FilesystemInterface
   */
  protected $_filesystem;

  /**
   * {@inheritdoc}
   */
  public function __construct( $name = null, FilesystemInterface $filesystem ) {

    $this->_filesystem = $filesystem;
    $this->_project    = new Project();

    parent::__construct( $name );

  }


  /**
   * {@inheritdoc}
   *
   * This serves as an additional "init" hook. We cannot grab helpers
   *  out until after the Application object has been injected.
   */
  public function setApplication( Application $application = null ) {

    parent::setApplication( $application );
    $this->_prompt = $this->getHelper( 'question' );

  }


  protected function _promptForPackageFqn( InputInterface $input, OutputInterface $output ) {

    $question_fqn = ( new Question( 'Project FQN (e.g., "jimdelois/my-project"): ' ) )
      ->setValidator( function ( $input ) {
        $this->_project->setFqn( $input );
      } );
    $this->_prompt->ask( $input, $output, $question_fqn );

  }

  protected function _promptForVendorPackage( InputInterface $input, OutputInterface $output ) {

    $question_vendor  = new Question( sprintf( 'Vendor (%s): ', $this->_project->getVendor() ) , $this->_project->getVendor() );
    $vendor           = $this->_prompt->ask( $input, $output, $question_vendor );
    $this->_project->setVendor( $vendor );
    $this->_project->setVendorDisplayFrom( $vendor );

    $question_package = new Question( sprintf( 'Package Name (%s): ', $this->_project->getPackage() ), $this->_project->getPackage() );
    $package          = $this->_prompt->ask( $input, $output, $question_package );
    $this->_project->setPackage( $package );
    $this->_project->setPackageDisplayFrom( $package );

  }

  protected function _promptForVendorPackageDisplay( InputInterface $input, OutputInterface $output ) {

    $question_vd      = new Question( sprintf( 'Vendor Display (%s): ', $this->_project->getVendorDisplay() ) , $this->_project->getVendorDisplay() );
    $vendor_display   = $this->_prompt->ask( $input, $output, $question_vd );
    $this->_project->setVendorDisplay( $vendor_display );

    $question_pd      = new Question( sprintf( 'Package Display (%s): ', $this->_project->getPackageDisplay() ) , $this->_project->getPackageDisplay() );
    $package_display  = $this->_prompt->ask( $input, $output, $question_pd );
    $this->_project->setPackageDisplay( $package_display );

  }

  protected function _promptForHomepage( InputInterface $input, OutputInterface $output ) {

    $default_site     = 'https://github.com/' . $this->_project->getFqn();
    $question_site    = new Question( sprintf( 'Package Website (%s): ', $default_site ) , $default_site );
    $package_site     = $this->_prompt->ask( $input, $output, $question_site );
    $this->_project->setHomepage( $package_site );

  }

  protected function _promptForAuthor( InputInterface $input, OutputInterface $output ) {

    $question_name    = new Question( sprintf( 'Author Name (%s): ', Author::DEFAULT_NAME ) , Author::DEFAULT_NAME );
    $name             = $this->_prompt->ask( $input, $output, $question_name );

    // TODO: Add Validation
    $question_email   = new Question( sprintf( 'Author Email (%s): ', Author::DEFAULT_EMAIL ) , Author::DEFAULT_EMAIL );
    $email            = $this->_prompt->ask( $input, $output, $question_email );

    // TODO: Add Validation
    $question_website = new Question( sprintf( 'Author Homepage (%s): ', Author::DEFAULT_HOMEPAGE ) , Author::DEFAULT_HOMEPAGE );
    $homepage         = $this->_prompt->ask( $input, $output, $question_website );
    $author           = new Author( $name, $email );
    $author->setHomepage( $homepage );
    $this->_project->addAuthor( $author );

  }

  protected function _promptForDescription( InputInterface $input, OutputInterface $output ) {

    // TODO: Add Validation
    $question_desc    = new Question( 'Project Description: ' );
    $description      = $this->_prompt->ask( $input, $output, $question_desc );
    $this->_project->describeAs( $description );

  }

  protected function _promptForBaseNamespace( InputInterface $input, OutputInterface $output ) {

    $question_ns      = new Question( 'Base Namespace: ' );
    $namespace        = $this->_prompt->ask( $input, $output, $question_ns );
    $this->_project->setNamespace( $namespace );

  }

  protected function _promptForFilesystemPath( InputInterface $input, OutputInterface $output ) {

    // TODO: Add Validation
    $default_path     = Project::DEFAULT_DIR . $this->_project->getVendor() . DIRECTORY_SEPARATOR . $this->_project->getPackage();
    $question_path    = new Question( sprintf( 'Filesystem Path (%s): ', $default_path ), $default_path );
    $path             = $this->_prompt->ask( $input, $output, $question_path );

    $file             = new \SplFileInfo( $path );
    $this->_project->installAt( $file, $this->_filesystem );

  }


  protected function _registerStreamFilters( $stream ) {

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


  protected function _copyAssetsFrom( \SplFileInfo $from ) {

    $asset_base_path = $from->getRealPath();
    $target_dir_path = $this->_project->getInstallDir()->getRealPath();

    if ( $asset_base_path === false ) {
      throw new \RuntimeException( sprintf( 'Unable to copy assets from "%s"', $from->getPathname() ) );
    }

    if ( $target_dir_path === false ) {
      throw new \RuntimeException( sprintf( 'Unable to copy assets to "%s"', $to->getPathname() ) );
    }

    $it = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator(
        $from->getRealPath(),
        \RecursiveDirectoryIterator::SKIP_DOTS
      ),
      \RecursiveIteratorIterator::SELF_FIRST
    );



    foreach( $it as $file ) {

      $filename_from = str_replace( $asset_base_path, '', $file->getRealPath() );
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

  }

}

