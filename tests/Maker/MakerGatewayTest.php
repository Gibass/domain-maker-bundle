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

class MakerGatewayTest extends MakerTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $application = new Application($kernel);
        $command = $application->find('maker:gateway');
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
        yield 'CreateDomainAndGateway' => [
            new MakerTestGenerate()
                ->createDomain('Account')
                ->setArgs(['name' => 'User'])
                ->setFiles(['Account/Domain/Gateway/UserGatewayInterface.php'])
        ];

        yield 'ChooseExistingDomainAndCreateGatewayWithSuffix' => [
            new MakerTestGenerate()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'AdminGatewayInterface'])
                ->setFiles(['Account/Domain/Gateway/AdminGatewayInterface.php'])
        ];
    }

    #[DataProvider('dataTestContentProvider')]
    public function testContentSuccessful(MakerTestContent $content): void
    {
        $this->assertFileContent($content);
    }

    public static function dataTestContentProvider(): \Generator
    {
        yield 'CheckCreateDomainAndGateway' => [
            new MakerTestContent('Account')
                ->addContent('Gateway', 'UserGatewayInterface.php', 'namespace App\\Account\\Domain\\Gateway')
                ->addContent('Gateway', 'UserGatewayInterface.php', 'interface UserGatewayInterface')
        ];

        yield 'CheckChooseExistingDomainAndCreateGateway' => [
            new MakerTestContent('Account')
                ->addContent('Gateway', 'AdminGatewayInterface.php', 'namespace App\\Account\\Domain\\Gateway')
                ->addContent('Gateway', 'AdminGatewayInterface.php', 'interface AdminGatewayInterface')
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
        yield 'CreateGatewayWithExistingFileThrowingException' => [
            new MakerTestFailed()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'User'])
                ->setException(FileAlreadyExistException::class, 'Account/Domain/Gateway/UserGatewayInterface.php" is already exist.'),
        ];
    }
}
