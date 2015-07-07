<?php

namespace DeLois\ProjectGenerator\Console\Command\Abstracts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCreateCommand extends Command {

  /**
   * @var \Symfony\Component\Console\Helper\QuestionHelper;
   */
  protected $_prompt;

  protected $_project;

  public function __construct( $name = null ) {


    parent::__construct( $name );
//    $this->_prompt = $this->getHelper( 'question' );

  }


  protected function _promptForPackageFqn() {

//    $question_package_fqn = ( new Question( 'Package FQN (e.g, "jimdelois/my-project"): ') )
//      ->setValidator( function( $input ) {
//
//        $matches = [];
//
//        preg_match( )
//      } );

  }

}

