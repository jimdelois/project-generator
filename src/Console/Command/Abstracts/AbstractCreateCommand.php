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

abstract class AbstractCreateCommand extends Command
{

    /**
     * @var \DeLois\ProjectGenerator\Model\Project
     */
    protected $project;

    /**
     * @param \Symfony\Component\Console\Helper\QuestionHelper
     */
    protected $prompt;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, FilesystemInterface $filesystem)
    {

        $this->filesystem = $filesystem;
        $this->project = new Project();

        parent::__construct($name);

    }


    /**
     * {@inheritdoc}
     *
     * This serves as an additional "init" hook. We cannot grab helpers
     *  out until after the Application object has been injected.
     */
    public function setApplication(Application $application = null)
    {

        parent::setApplication($application);
        $this->prompt = $this->getHelper('question');
    }


    protected function promptForPackageFqn(InputInterface $input, OutputInterface $output)
    {

        $question_fqn = (new Question('Project FQN (e.g., "jimdelois/my-project"): '))
            ->setValidator(function ($input) {
                $this->project->setFqn($input);
            });
        $this->prompt->ask($input, $output, $question_fqn);

    }

    protected function promptForVendorPackage(InputInterface $input, OutputInterface $output)
    {

        $question_vendor = new Question(
            sprintf('Vendor (%s): ', $this->project->getVendor()),
            $this->project->getVendor()
        );
        $vendor = $this->prompt->ask($input, $output, $question_vendor);
        $this->project->setVendor($vendor);
        $this->project->setVendorDisplayFrom($vendor);

        $question_package = new Question(
            sprintf('Package Name (%s): ', $this->project->getPackage()),
            $this->project->getPackage()
        );
        $package = $this->prompt->ask($input, $output, $question_package);
        $this->project->setPackage($package);
        $this->project->setPackageDisplayFrom($package);

    }

    protected function promptForVendorPackageDisplay(InputInterface $input, OutputInterface $output)
    {

        $question_vd = new Question(
            sprintf('Vendor Display (%s): ', $this->project->getVendorDisplay()),
            $this->project->getVendorDisplay()
        );
        $vendor_display = $this->prompt->ask($input, $output, $question_vd);
        $this->project->setVendorDisplay($vendor_display);

        $question_pd = new Question(
            sprintf('Package Display (%s): ', $this->project->getPackageDisplay()),
            $this->project->getPackageDisplay()
        );
        $package_display = $this->prompt->ask($input, $output, $question_pd);
        $this->project->setPackageDisplay($package_display);

    }

    protected function promptForHomepage(InputInterface $input, OutputInterface $output)
    {

        $default_site = 'https://github.com/' . $this->project->getFqn();
        $question_site = new Question(sprintf('Package Website (%s): ', $default_site), $default_site);
        $package_site = $this->prompt->ask($input, $output, $question_site);
        $this->project->setHomepage($package_site);

    }

    protected function promptForRepositoryUrl(InputInterface $input, OutputInterface $output)
    {

        $default_repo = sprintf('git@github.com:%s.git', $this->project->getFqn());
        $question_repo = new Question(sprintf('Package Repository URL (%s): ', $default_repo), $default_repo);
        $package_repo = $this->prompt->ask($input, $output, $question_repo);
        $this->project->setRepositoryUrl($package_repo);

    }

    protected function promptForAuthor(InputInterface $input, OutputInterface $output)
    {

        $question_name = new Question(sprintf('Author Name (%s): ', Author::DEFAULT_NAME), Author::DEFAULT_NAME);
        $name = $this->prompt->ask($input, $output, $question_name);

        // TODO: Add Validation
        $question_email = new Question(sprintf('Author Email (%s): ', Author::DEFAULT_EMAIL), Author::DEFAULT_EMAIL);
        $email = $this->prompt->ask($input, $output, $question_email);

        // TODO: Add Validation
        $question_website = new Question(
            sprintf('Author Homepage (%s): ', Author::DEFAULT_HOMEPAGE),
            Author::DEFAULT_HOMEPAGE
        );
        $homepage = $this->prompt->ask($input, $output, $question_website);
        $author = new Author($name, $email);
        $author->setHomepage($homepage);
        $this->project->addAuthor($author);

    }

    protected function promptForDescription(InputInterface $input, OutputInterface $output)
    {

        // TODO: Add Validation
        $question_desc = new Question('Project Description: ');
        $description = $this->prompt->ask($input, $output, $question_desc);
        $this->project->describeAs($description);

    }

    protected function promptForBaseNamespace(InputInterface $input, OutputInterface $output)
    {

        $question_ns = new Question('Base Namespace: ');
        $namespace = $this->prompt->ask($input, $output, $question_ns);
        $this->project->setNamespace($namespace);

    }

    protected function promptForFilesystemPath(InputInterface $input, OutputInterface $output)
    {

        // TODO: Add Validation
        $default_path  =
            Project::DEFAULT_DIR . $this->project->getVendor() . DIRECTORY_SEPARATOR . $this->project->getPackage();
        $question_path = new Question(sprintf('Filesystem Path (%s): ', $default_path), $default_path);
        $path = $this->prompt->ask($input, $output, $question_path);

        $file = new \SplFileInfo($path);
        $this->project->installAt($file, $this->filesystem);

    }


    protected function registerStreamFilters($stream)
    {

        // TODO: Fix this.
        $author = $this->project->getAuthors()[0];

        $filters = [
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_NAMESPACE__\\',
                TokenReplacementFilter::PARAM_KEY_REPLACE => ltrim($this->project->getNamespace(), '\\')
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_NAMESPACE_COMPOSER__\\\\',
                TokenReplacementFilter::PARAM_KEY_REPLACE => ltrim(
                    str_replace('\\', '\\' . '\\', $this->project->getNamespace()),
                    '\\'
                )
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_FQN__',
                TokenReplacementFilter::PARAM_KEY_REPLACE => $this->project->getFqn()
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_VENDOR__',
                TokenReplacementFilter::PARAM_KEY_REPLACE => $this->project->getVendor()
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_PACKAGE__',
                TokenReplacementFilter::PARAM_KEY_REPLACE => $this->project->getPackage()
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_VENDOR_DISPLAY__',
                TokenReplacementFilter::PARAM_KEY_REPLACE => $this->project->getVendorDisplay()
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_PACKAGE_DISPLAY__',
                TokenReplacementFilter::PARAM_KEY_REPLACE => $this->project->getPackageDisplay()
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_DESCRIPTION__',
                TokenReplacementFilter::PARAM_KEY_REPLACE => $this->project->getDescription()
            ],
            [
                TokenReplacementFilter::PARAM_KEY_SEARCH  => '__TOKEN_HOMEPAGE__',
                TokenReplacementFilter::PARAM_KEY_REPLACE => $this->project->getHomepage()
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
                TokenReplacementFilter::PARAM_KEY_REPLACE => date('Y') // TODO: Don't
            ],
        ];

        foreach ($filters as $filter_params) {
            stream_filter_append(
                $stream,
                TokenReplacementFilter::DEFAULT_NAME,
                \STREAM_FILTER_READ,
                $filter_params
            );

        }

    }


    protected function copyAssetsFrom(\SplFileInfo $from)
    {

        $asset_base_path = $from->getRealPath();
        $target_dir_path = $this->project->getInstallDir()->getRealPath();

        if ($asset_base_path === false) {
            throw new \RuntimeException(sprintf('Unable to copy assets from "%s"', $from->getPathname()));
        }

        if ($target_dir_path === false) {
            throw new \RuntimeException(
                sprintf('Unable to copy assets to "%s"', $this->project->getInstallDir()->getPathname())
            );
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $from->getRealPath(),
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        // TODO: Make a "projectgen.json" file IN the assets, which lists the ignores via a JSON key.
        $ignore_list = [ 'composer_require.json' ];

        $it = new \CallbackFilterIterator($it, function (\SplFileInfo $file) use ($ignore_list) {
            return !in_array($file->getFilename(), $ignore_list);
        });


        foreach ($it as $file) {
            $filename_from = str_replace($asset_base_path, '', $file->getRealPath());
            $filename_to = $target_dir_path . $filename_from;

            if ($file->isDir()) {
                $this->filesystem->createDir($target_dir_path . $filename_from);
                continue;
            }

            $stream = fopen($file->getRealPath(), 'r+b');

            $this->registerStreamFilters($stream);

            $this->filesystem->writeStream($filename_to, $stream);

            fclose($stream);

        }

    }

    protected function composerRequire(\SplFileInfo $from, OutputInterface $output)
    {

        $target_dir_path = $this->project->getInstallDir()->getRealPath();
//        $target_dir_path = '/Users/delois/Development/source/Personal/dev/jimdelois/my-test-project';

        $cmd = [];
        $cmd[] = sprintf('cd %s', $target_dir_path);

        $composer_file = $from->getRealPath() . '/composer_require.json';

        // TODO: Check if file exists.

        $json = json_decode($this->filesystem->read($composer_file));

        $map = function (string $package, string $version) {
            if ($version !== 'latest') {
                return sprintf('%s:%s', $package, $version);
            }
            return $package;
        };


        $requires     = array_map($map, array_keys((array)$json->{'require'}), (array)$json->{'require'});
        $require_devs = array_map($map, array_keys((array)$json->{'require-dev'}), (array)$json->{'require-dev'});

        $requires = implode(' ', $requires);
        $cmd[] = $requires ? sprintf('composer require %s', $requires) : null;

        $require_devs = implode(' ', $require_devs);
        $cmd[] = $require_devs ? sprintf('composer require --dev %s', $require_devs) : null;


        $output->writeln(
            '<info>Installing Composer Packages (this may take some time)</info>'
        );

        $cmd = implode(' && ', array_filter($cmd));

        passthru($cmd);
    }

    protected function gitInit(OutputInterface $output)
    {

        $output->writeln(sprintf('<info>Configuring Git Repository (%s)</info>', $this->project->getRepositoryUrl()));
        $cmd = [];

        $cmd[] = sprintf('cd %s', $this->project->getInstallDir()->getRealPath());
        $cmd[] = 'git init';

        $cmd[] = sprintf('git remote add origin %s', $this->project->getRepositoryUrl());
        $cmd[] = sprintf('git remote add upstream %s', $this->project->getRepositoryUrl());

        $cmd = implode(' && ', $cmd);

        passthru($cmd);
    }
}
