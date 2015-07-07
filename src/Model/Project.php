<?php

namespace DeLois\ProjectGenerator\Model;

use DeLois\ProjectGenerator\Model\Exception\InvalidFqnException;
use League\Flysystem\FilesystemInterface;

class Project {

  const DEFAULT_VENDOR = 'jimdelois';
  const DEFAULT_DIR    = '/Users/delois/Development/source/Personal/dev/';

  protected $_fqn;

  protected $_authors = [];

  protected $_vendor;

  protected $_package;

  protected $_vendor_display;

  protected $_package_display;

  protected $_homepage;

  protected $_description;

  protected $_namespace;

  protected $_install_dir;

  public function __construct( $fqn ) {

    list( $this->_fqn, $this->_vendor, $this->_package ) = $this->_validatePackageFqn( $fqn );

    $this->setVendorDisplayFrom( $this->_vendor );
    $this->setPackageDisplayFrom( $this->_package );

  }

  public function getFqn() {

    return $this->_fqn;

  }

  public function addAuthor( Author $author ) {

    $this->_authors[] = $author;
    return $this;

  }

  public function getAuthors() {

    return $this->_authors;

  }

  public function setVendorDisplayFrom( $string ) {

    if ( $string === self::DEFAULT_VENDOR ) {
      $this->_vendor_display = Author::DEFAULT_NAME;
    } else {
      $this->_vendor_display = ucwords( $string );
    }

    return $this;

  }

  public function setPackageDisplayFrom( $string ) {

    $this->_package_display = ucwords( $string );
    return $this;

  }

  public function setVendor( $vendor ) {

    $this->_vendor = $vendor;
    return $this;

  }

  public function getVendor() {

    return $this->_vendor;

  }

  public function setPackage( $package ) {

    $this->_package = $package;
    return $this;

  }

  public function getPackage() {

    return $this->_package;

  }

  public function setVendorDisplay( $vendor_display ) {

    $this->_vendor_display = $vendor_display;
    return $this;

  }

  public function getVendorDisplay() {

    return $this->_vendor_display;

  }

  public function setPackageDisplay( $package_display ) {

    $this->_package_display = $package_display;
    return $this;

  }

  public function getPackageDisplay() {

    return $this->_package_display;

  }

  public function describeAs( $description ) {

    $this->_description = $description;
    return $this;

  }

  public function getDescription() {

    return $this->_description;

  }

  public function setNamespace( $namespace ) {

    $namespace = '\\' . $namespace . '\\';

    $pattern   = '|(\\\+)|';
    $namespace = preg_replace( $pattern, '\\', $namespace );

    $this->_namespace = $namespace;
    return $this;

  }


  public function installAt( \SplFileInfo $file, FilesystemInterface $filesystem ) {

    $dir = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
    if ( !$filesystem->has( $dir ) ) {

      $result = $filesystem->createDir( $dir );

      if ( $result === false ) {
        throw new \RuntimeException( sprintf( 'Unable to create directory "%s".', $dir ) );
      }

    }

    $this->_install_dir = new \SplFileInfo( $dir );
    return $this;

  }

  public function getNamespace() {

    return $this->_namespace;

  }

  public function setHomepage( $homepage ) {

    $this->_homepage = $homepage;
    return $this;

  }

  public function getHomepage() {

    return $this->_homepage;

  }

  private function _validatePackageFqn( $fqn ) {

    $matches = [];
    preg_match( '|^([a-z0-9\._-]+)\/([a-z0-9\._-]+)$|i', $fqn, $matches );

    if ( count( $matches ) === 0 ) {
      throw new InvalidFqnException( 'Package name must be of the format "vendor/package-name" and contain only valid characters.' );
    }

    return $matches;

  }

}
