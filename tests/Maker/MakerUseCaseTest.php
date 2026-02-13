<?php

namespace Gibass\DomainMakerBundle\Test\Maker;

use Gibass\DomainMakerBundle\Exception\FileAlreadyExistException;
use Gibass\DomainMakerBundle\Test\Helper\Provider\MakerTestContent;
use Gibass\DomainMakerBundle\Test\Helper\Provider\MakerTestFailed;
use Gibass\DomainMakerBundle\Test\Helper\Provider\MakerTestGenerate;
use Gibass\DomainMakerBundle\Test\Helper\TestCase\MakerTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MakerUseCaseTest extends MakerTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $application = new Application($kernel);
        $command = $application->find('maker:use-case');
        $this->commandTester = new CommandTester($command);
    }

    #[DataProvider('dataTestGenerateProvider')]
    public function testGenerateSuccess(MakerTestGenerate $generate)
    {
        $this->commandTester->setInputs($generate->getInputs());

        $this->commandTester->execute($generate->getArgs());

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertFilesGenerated($generate->getFiles());
    }

    public static function dataTestGenerateProvider(): \Generator
    {
        yield 'CreateDomainAndUseCase' => [
            MakerTestGenerate::create()
                ->createDomain('CreateDomain')
                ->setArgs(['name' => 'TestUseCase'])
                ->setFiles(['CreateDomain/Domain/UseCase/TestUseCase.php'])
        ];

        yield 'ChooseExistingDomainAndCreateUseCase' => [
            MakerTestGenerate::create()
                ->chooseDomain(0)
                ->setArgs(['name' => 'NewUseCase'])
                ->setFiles(['CreateDomain/Domain/UseCase/NewUseCase.php'])
        ];

        yield 'CreateDomainAndUseCaseWithSnakeCaseInput' => [
            MakerTestGenerate::create()
                ->createDomain('snake_case_domain')
                ->setArgs(['name' => 'snake_use_case'])
                ->setFiles(['SnakeCaseDomain/Domain/UseCase/SnakeUseCase.php'])
        ];

        yield 'ChooseExistingDomainAndCreateUseCaseWithSpace' => [
            MakerTestGenerate::create()
                ->chooseDomain(1)
                ->setArgs(['name' => 'Space use case'])
                ->setFiles(['SnakeCaseDomain/Domain/UseCase/SpaceUseCase.php'])
        ];
    }

    #[DataProvider('dataTestContentProvider')]
    public function testContentSuccessful(MakerTestContent $content): void
    {
        $this->assertFileContent($content);
    }

    public static function dataTestContentProvider(): \Generator
    {
        yield 'CheckCreatedUseCaseContentWithCreatedDomain' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('UseCase', 'TestUseCase.php', 'namespace App\\CreateDomain\\Domain\\UseCase')
                ->addContent('UseCase', 'TestUseCase.php', 'class TestUseCase')
                ->addContent('UseCase', 'TestUseCase.php', 'public function execute(): void')
        ];

        yield 'CheckCreatedUseCaseContentWithChosenDomain' => [
            MakerTestContent::create('SnakeCaseDomain')
                ->addContent('UseCase', 'SpaceUseCase.php', 'namespace App\\SnakeCaseDomain\\Domain\\UseCase')
                ->addContent('UseCase', 'SpaceUseCase.php', 'class SpaceUseCase')
                ->addContent('UseCase', 'SpaceUseCase.php', 'public function execute(): void')
        ];
    }

    #[DataProvider('dataTestFailedProvider')]
    public function testFailedGenerate(MakerTestFailed $failed): void
    {
        $this->expectException($failed->getException());
        $this->expectExceptionMessage($failed->getMessage());

        $this->commandTester->setInputs($failed->getInputs());

        $this->commandTester->execute($failed->getArgs());
    }

    public static function dataTestFailedProvider(): \Generator
    {
        yield 'CreateUseCaseWithExistingFileThrowingException' => [
            MakerTestFailed::create()
                ->chooseDomain(0)
                ->setArgs(['name' => 'NewUseCase'])
                ->setException(FileAlreadyExistException::class, 'CreateDomain/Domain/UseCase/NewUseCase.php" is already exist.'),
        ];
    }
}
