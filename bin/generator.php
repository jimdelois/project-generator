#!/usr/bin/env php
<?php

require_once( __DIR__ . '/../vendor/autoload.php' );

use DeLois\ProjectGenerator\Console\Command\ComponentCreateCommand;
use Symfony\Component\Console\Application;
use DeLois\ProjectGenerator\Filter\TokenReplacementFilter;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

stream_filter_register( TokenReplacementFilter::DEFAULT_NAME, TokenReplacementFilter::class )
  or die( 'Failed to register filter' );

$application     = new Application();

$filesystem      = new Filesystem( new Adapter( '/' ) );
$asset_base_path = new \SplFileInfo( __DIR__ . '/../assets/component' );

$application->add( new ComponentCreateCommand( 'component:create', $asset_base_path, $filesystem ) );

$application->run();
