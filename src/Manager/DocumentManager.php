<?php

namespace Gibass\DomainMakerBundle\Manager;

use Symfony\Component\Finder\Finder;

class DocumentManager
{
    public function __construct()
    {
    }

    public function listDirectories(string $sourcePath): array
    {
        if (!$this->isDirectoryExist($sourcePath)) {
            return [];
        }

        $finder = new Finder();
        $finder->directories()->in($sourcePath)->depth('== 0')->sortByName();

        $directories = [];

        foreach ($finder as $dir) {
            $directories[] = $dir->getFilename();
        }

        return $directories;
    }

    public function listFiles(string $sourcePath): array
    {
        if (!$this->isDirectoryExist($sourcePath)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()->in($sourcePath)->depth('== 0')->sortByName();

        $files = [];

        foreach ($finder as $file) {
            $files[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        }

        return $files;
    }

    public function createDirectory(string $path): void
    {
        mkdir($path, 0777, true);
    }

    public function isDirectoryExist(string $path): bool
    {
        return is_dir($path);
    }
}
