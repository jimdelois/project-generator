<?php

namespace DeLois\ProjectGenerator\Model;

class Author {

  const DEFAULT_NAME  = 'Jim DeLois';
  const DEFAULT_EMAIL = 'jim.delois@improvframework.org';

  protected $_name;

  protected $_email;

  protected $_homepage;

  public function __construct( $name = self::DEFAULT_NAME , $email = self::DEFAULT_EMAIL ) {

    $this->_name  = $name;
    $this->_email = $email;

  }

  public function getName() {

    return $this->_name;

  }

  public function getEmail() {

    return $this->_email;

  }

  public function setHomepage( $homepage ) {

    $this->_homepage = $homepage;

  }

  public function getHomepage() {

    return $this->_homepage;

  }

}
