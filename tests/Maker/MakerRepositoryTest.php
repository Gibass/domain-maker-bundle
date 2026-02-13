<?php

namespace Gibass\DomainMakerBundle\Test\Maker;

use Gibass\DomainMakerBundle\Exception\FileAlreadyExistException;
use Gibass\DomainMakerBundle\Exception\NoItemToChooseException;
use Gibass\DomainMakerBundle\Test\Helper\Provider\MakerTestContent;
use Gibass\DomainMakerBundle\Test\Helper\Provider\MakerTestFailed;
use Gibass\DomainMakerBundle\Test\Helper\Provider\MakerTestGenerate;
use Gibass\DomainMakerBundle\Test\Helper\TestCase\MakerTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MakerRepositoryTest extends MakerTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $application = new Application($kernel);
        $this->commandTester = new CommandTester($application->find('maker:repository'));
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
        yield 'CreateDomainRepositoryAndEntity' => [
            MakerTestGenerate::create()
                ->createDomain('CreateDomain')
                ->setArgs(['name' => 'User'])
                ->addInputs([0, 'User']) // Create Entity - Entity Name
                ->setFiles([
                    'CreateDomain/Domain/Model/Entity/User.php',
                    'CreateDomain/Domain/Gateway/UserGatewayInterface.php',
                    'CreateDomain/Infrastructure/Adapter/Repository/UserRepository.php'
                ])
        ];

        yield 'ChooseDomainAndCreateRepositoryWithExistingGateway' => [
            MakerTestGenerate::create()
                ->chooseDomain(0) // CreateDomain
                ->setArgs(['name' => 'PostRepository'])
                ->createFile([
                        'CreateDomain/Domain/Gateway/PostGatewayInterface.php',
                    ]
                )
                ->addInputs([0, 'Post']) // Create Entity - Entity Name
                ->setFiles([
                    'CreateDomain/Domain/Model/Entity/Post.php',
                    'CreateDomain/Domain/Gateway/PostGatewayInterface.php',
                    'CreateDomain/Infrastructure/Adapter/Repository/PostRepository.php'
                ])
        ];

        yield 'ChooseDomainAndCreateRepositoryAndGatewayAndChooseEntity' => [
            MakerTestGenerate::create()
                ->chooseDomain(0) // CreateDomain
                ->setArgs(['name' => 'PostAdmin'])
                ->addInputs([1, 0]) // Choose Entity - Post
                ->setFiles([
                    'CreateDomain/Domain/Model/Entity/Post.php',
                    'CreateDomain/Domain/Gateway/PostAdminGatewayInterface.php',
                    'CreateDomain/Infrastructure/Adapter/Repository/PostAdminRepository.php'
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
        yield 'CheckCreateDomainRepositoryAndEntity' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('Repository', 'UserRepository.php', 'namespace App\\CreateDomain\\Infrastructure\\Adapter\\Repository')
                ->addContent('Repository', 'UserRepository.php', 'use App\\CreateDomain\\Domain\\Gateway\\UserGatewayInterface;')
                ->addContent('Repository', 'UserRepository.php', 'use App\\CreateDomain\\Domain\\Model\\Entity\\User;')
                ->addContent('Repository', 'UserRepository.php', 'use Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository;')
                ->addContent('Repository', 'UserRepository.php', 'use Doctrine\\Persistence\\ManagerRegistry;')
                ->addContent('Repository', 'UserRepository.php', '@extends ServiceEntityRepository<User>')
                ->addContent('Repository', 'UserRepository.php', 'class UserRepository extends ServiceEntityRepository implements UserGatewayInterface')
                ->addContent('Repository', 'UserRepository.php', 'public function __construct(ManagerRegistry $registry)')
                ->addContent('Repository', 'UserRepository.php', 'parent::__construct($registry, User::class);')
        ];

        yield 'CheckChooseDomainAndCreateRepositoryWithExistingGateway' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('Repository', 'PostRepository.php', 'namespace App\\CreateDomain\\Infrastructure\\Adapter\\Repository')
                ->addContent('Repository', 'PostRepository.php', 'use App\\CreateDomain\\Domain\\Gateway\\PostGatewayInterface;')
                ->addContent('Repository', 'PostRepository.php', 'use App\\CreateDomain\\Domain\\Model\\Entity\\Post;')
                ->addContent('Repository', 'PostRepository.php', 'use Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository;')
                ->addContent('Repository', 'PostRepository.php', 'use Doctrine\\Persistence\\ManagerRegistry;')
                ->addContent('Repository', 'PostRepository.php', '@extends ServiceEntityRepository<Post>')
                ->addContent('Repository', 'PostRepository.php', 'class PostRepository extends ServiceEntityRepository implements PostGatewayInterface')
                ->addContent('Repository', 'PostRepository.php', 'public function __construct(ManagerRegistry $registry)')
                ->addContent('Repository', 'PostRepository.php', 'parent::__construct($registry, Post::class);')
        ];

        yield 'CheckChooseDomainAndCreateRepositoryAndGatewayAndChooseEntity' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('Repository', 'PostAdminRepository.php', 'namespace App\\CreateDomain\\Infrastructure\\Adapter\\Repository')
                ->addContent('Repository', 'PostAdminRepository.php', 'use App\\CreateDomain\\Domain\\Gateway\\PostAdminGatewayInterface;')
                ->addContent('Repository', 'PostAdminRepository.php', 'use App\\CreateDomain\\Domain\\Model\\Entity\\Post;')
                ->addContent('Repository', 'PostAdminRepository.php', 'use Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository;')
                ->addContent('Repository', 'PostAdminRepository.php', 'use Doctrine\\Persistence\\ManagerRegistry;')
                ->addContent('Repository', 'PostAdminRepository.php', '@extends ServiceEntityRepository<Post>')
                ->addContent('Repository', 'PostAdminRepository.php', 'class PostAdminRepository extends ServiceEntityRepository implements PostAdminGatewayInterface')
                ->addContent('Repository', 'PostAdminRepository.php', 'public function __construct(ManagerRegistry $registry)')
                ->addContent('Repository', 'PostAdminRepository.php', 'parent::__construct($registry, Post::class);')
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
        yield 'CreateRepositoryWithExistingFileThrowingException' => [
            MakerTestFailed::create()
                ->chooseDomain(0) // CreateDomain
                ->setArgs(['name' => 'User'])
                ->addInputs([1, 1]) // Choose Entity - User
                ->setException(FileAlreadyExistException::class, 'CreateDomain/Infrastructure/Adapter/Repository/UserRepository.php" is already exist.'),
        ];

        yield 'CreateRepositoryAndChooseEntityInEmptyFolder' => [
            MakerTestFailed::create()
                ->createDomain('EmptyDomain')
                ->setArgs(['name' => 'Empty'])
                ->addInput(1)
                ->setException(NoItemToChooseException::class, 'EmptyDomain/Domain/Model/Entity')
        ];
    }
}
