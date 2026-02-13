<?php

namespace Gibass\DomainMakerBundle\Test\Helper\TestCase;

use Gibass\DomainMakerBundle\Test\Helper\Kernel\MakerTestKernel;
use Gibass\DomainMakerBundle\Test\Helper\Provider\MakerTestContent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class MakerTestCase extends KernelTestCase
{
    public static function tearDownAfterClass(): void
    {
        self::removeTmpDir();
    }

    protected static function getKernelClass(): string
    {
        return MakerTestKernel::class;
    }

    protected function assertFilesGenerated(array $files): void
    {
        foreach ($files as $file) {
            $this->assertFileExists(MakerTestKernel::TMP_DIR . '/src/' . $file);
        }
    }

    protected function assertFileConfigExists(array $files): void
    {
        foreach ($files as $file) {
            $this->assertFileExists(MakerTestKernel::TMP_DIR . '/config/' . $file);
        }
    }

    protected function assertFileContent(MakerTestContent $contentTest): void
    {
        foreach ($contentTest->getContents() as $content) {
            $path = MakerTestKernel::TMP_DIR . '/' . $this->getPath($contentTest->getDomain(), $content['type']) . $content['filename'];
            $message = 'No string "' . $content['content'] . '" found in ' . $path;

            $this->assertStringContainsStringIgnoringCase($content['content'], file_get_contents($path), $message);
        }
    }

    protected function createFiles(array $files): void
    {
        foreach ($files as $file) {
            (new Filesystem())->dumpFile($file, '');
        }
    }

    private static function removeTmpDir(): void
    {
        if (file_exists(MakerTestKernel::TMP_DIR)) {
            (new Filesystem())->remove(MakerTestKernel::TMP_DIR);
        }
    }

    private function getPath(string $domain, string $type): string
    {
        return match ($type) {
            'UseCase' => 'src/' . $domain . '/Domain/UseCase/',
            'Presenter' => 'src/'. $domain . '/UserInterface/Presenter/',
            'Controller' => 'src/' . $domain . '/UserInterface/Controller/',
            'Gateway' => 'src/' . $domain . '/Domain/Gateway/',
            'Repository' => 'src/' . $domain . '/Infrastructure/Adapter/Repository/',
            'Entity' => 'src/' . $domain . '/Domain/Model/Entity/',
            'Config' => 'config/',
            default => $type
        };
    }
}
