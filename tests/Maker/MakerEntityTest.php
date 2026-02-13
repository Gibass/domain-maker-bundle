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

class MakerEntityTest extends MakerTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $this->application = new Application($kernel);
        $this->commandTester = new CommandTester($this->application->find('maker:entity'));
    }

    #[DataProvider('dataTestGenerateProvider')]
    public function testGenerateSuccess(MakerTestGenerate $generate): void
    {
        $this->commandTester->setInputs($generate->getInputs());

        foreach ($generate->getFilesToCreate() as $files) {
            $this->createFiles($files);
        }

        $this->commandTester->execute($generate->getArgs());

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertFilesGenerated($generate->getFiles());
    }

    public static function dataTestGenerateProvider(): \Generator
    {
        yield 'CreateDomainAndEntity' => [
            MakerTestGenerate::create()
                ->createDomain('Account')
                ->setArgs(['name' => 'User'])
                ->setFiles([
                    'Account/Domain/Model/Entity/User.php',
                    'Account/Domain/Gateway/UserGatewayInterface.php',
                    'Account/Infrastructure/Adapter/Repository/UserRepository.php'
                ])
        ];

        yield 'ChooseDomainAndCreateEntityWithExistingGateway' => [
            MakerTestGenerate::create()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'Post'])
                ->createFile([
                        'Account/Domain/Gateway/PostGatewayInterface.php',
                    ]
                )
                ->setFiles([
                    'Account/Domain/Model/Entity/Post.php',
                    'Account/Domain/Gateway/PostGatewayInterface.php',
                    'Account/Infrastructure/Adapter/Repository/PostRepository.php'
                ])
        ];

        yield 'CreateDomainAndEntityWithExistingRepoAndGateway' => [
            MakerTestGenerate::create()
                ->createDomain('Business') // Business
                ->setArgs(['name' => 'Money'])
                ->createFile([
                        'Business/Domain/Gateway/MoneyGatewayInterface.php',
                        'Business/Infrastructure/Adapter/Repository/MoneyRepository.php',
                    ]
                )
                ->setFiles([
                    'Business/Domain/Model/Entity/Money.php',
                    'Business/Domain/Gateway/MoneyGatewayInterface.php',
                    'Business/Infrastructure/Adapter/Repository/MoneyRepository.php'
                ])
        ];
    }

    #[DataProvider('dataTestContentProvider')]
    public function testContentSuccessful(MakerTestContent $content): void
    {
        $this->assertFileContent($content);
    }

    public static function dataTestContentProvider(): \Generator
    {
        yield 'CheckCreateDomainAndEntity' => [
            MakerTestContent::create('Account')
                ->addContent('Entity', 'User.php', 'namespace App\\Account\\Domain\\Model\\Entity')
                ->addContent('Entity', 'User.php', 'use App\\Account\\Infrastructure\\Adapter\\Repository\\UserRepository;')
                ->addContent('Entity', 'User.php', 'use Doctrine\\ORM\\Mapping as ORM;')
                ->addContent('Entity', 'User.php', '#[ORM\Entity(repositoryClass: UserRepository::class)]')
                ->addContent('Entity', 'User.php', 'class User')
        ];

        yield 'CreateDomainAndEntityWithExistingRepoAndGateway' => [
            MakerTestContent::create('Business')
                ->addContent('Entity', 'Money.php', 'namespace App\\Business\\Domain\\Model\\Entity')
                ->addContent('Entity', 'Money.php', 'use App\\Business\\Infrastructure\\Adapter\\Repository\\MoneyRepository;')
                ->addContent('Entity', 'Money.php', 'use Doctrine\\ORM\\Mapping as ORM;')
                ->addContent('Entity', 'Money.php', '#[ORM\Entity(repositoryClass: MoneyRepository::class)]')
                ->addContent('Entity', 'Money.php', 'class Money')
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
        yield 'CreateEntityWithExistingFileThrowingException' => [
            MakerTestFailed::create()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'User'])
                ->setException(FileAlreadyExistException::class, 'Account/Domain/Model/Entity/User.php" is already exist.'),
        ];
    }
}
