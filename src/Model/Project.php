<?php

namespace DeLois\ProjectGenerator\Model;

use DeLois\ProjectGenerator\Model\Exception\InvalidFqnException;
use League\Flysystem\FilesystemInterface;

class Project
{

    const DEFAULT_VENDOR = 'jimdelois';
    const DEFAULT_DIR = '/Users/delois/Development/source/Personal/dev/';

    protected $fqn;

    protected $authors = [];

    protected $vendor;

    protected $package;

    protected $vendor_display;

    protected $package_display;

    protected $homepage;

    protected $repository_url;

    protected $description;

    protected $namespace;

    protected $install_dir;

    public function setFqn($fqn)
    {

        list($this->fqn, $this->vendor, $this->package) = $this->validatePackageFqn($fqn);

        $this->setVendorDisplayFrom($this->vendor);
        $this->setPackageDisplayFrom($this->package);

    }

    public function getFqn()
    {

        return $this->fqn;

    }

    public function addAuthor(Author $author)
    {

        $this->authors[] = $author;
        return $this;

    }

    /**
     * @return Author[]
     */
    public function getAuthors()
    {

        return $this->authors;

    }

    public function setVendorDisplayFrom($string)
    {

        if ($string === self::DEFAULT_VENDOR) {
            $this->vendor_display = Author::DEFAULT_NAME;
        } else {
            $this->vendor_display = ucwords($string);
        }

        return $this;

    }

    public function setPackageDisplayFrom($string)
    {

        $this->package_display = ucwords($string);
        return $this;

    }

    public function setVendor($vendor)
    {

        $this->vendor = $vendor;
        return $this;

    }

    public function getVendor()
    {

        return $this->vendor;

    }

    public function setPackage($package)
    {

        $this->package = $package;
        return $this;

    }

    public function getPackage()
    {

        return $this->package;

    }

    public function setVendorDisplay($vendor_display)
    {

        $this->vendor_display = $vendor_display;
        return $this;

    }

    public function getVendorDisplay()
    {

        return $this->vendor_display;

    }

    public function setPackageDisplay($package_display)
    {

        $this->package_display = $package_display;
        return $this;

    }

    public function getPackageDisplay()
    {

        return $this->package_display;

    }

    public function describeAs($description)
    {

        $this->description = $description;
        return $this;

    }

    public function getDescription()
    {

        return $this->description;

    }

    public function setNamespace($namespace)
    {

        $namespace = '\\' . $namespace . '\\';

        $pattern = '|(\\\+)|';
        $namespace = preg_replace($pattern, '\\', $namespace);

        $this->namespace = $namespace;
        return $this;

    }


    public function installAt(\SplFileInfo $file, FilesystemInterface $filesystem)
    {

        $dir = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
        if (!$filesystem->has($dir)) {
            $result = $filesystem->createDir($dir);

            if ($result === false) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s".', $dir));
            }
        }

        $this->install_dir = new \SplFileInfo($dir);
        return $this;

    }

    /**
     * @return \SplFileInfo
     */
    public function getInstallDir()
    {
        return $this->install_dir;

    }

    public function getNamespace()
    {

        return $this->namespace;

    }

    public function setHomepage($homepage)
    {

        $this->homepage = $homepage;
        return $this;

    }

    public function getHomepage()
    {

        return $this->homepage;

    }

    public function setRepositoryUrl(string $url)
    {
        $this->repository_url = $url;
        return $this;
    }

    public function getRepositoryUrl()
    {
        return $this->repository_url;
    }

    private function validatePackageFqn($fqn)
    {

        $matches = [];
        preg_match('|^([a-z0-9\._-]+)\/([a-z0-9\._-]+)$|i', $fqn, $matches);

        if (count($matches) === 0) {
            throw new InvalidFqnException(
                'Package name must be of the format "vendor/package-name" and contain only valid characters.'
            );
        }

        return $matches;

    }
}
