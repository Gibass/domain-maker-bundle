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

class MakerControllerTest extends MakerTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $application = new Application($kernel);
        $command = $application->find('maker:controller');
        $this->commandTester = new CommandTester($command);
    }

    #[DataProvider('dataTestGenerateProvider')]
    public function testGenerateSuccess(MakerTestGenerate $generate)
    {
        $this->commandTester->setInputs($generate->getInputs());

        $this->commandTester->execute($generate->getArgs());

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertFilesGenerated($generate->getFiles());
        $this->assertFileConfigExists(['routes.yaml']);
    }

    public static function dataTestGenerateProvider(): \Generator
    {
        yield 'CreateDomainAndController' => [
            MakerTestGenerate::create()
                ->createDomain('createDomain')
                ->setArgs(['name' => 'createUser'])
                ->addInputs([0, 0]) // Don't Create UseCase & Presenter
                ->setFiles(['CreateDomain/UserInterface/Controller/CreateUserController.php'])
        ];

        yield 'ChooseDomainAndCreateControllerWithSuffix' => [
            MakerTestGenerate::create()
                ->chooseDomain(0) // CreateDomain
                ->setArgs(['name' => 'DeleteUserController'])
                ->addInputs([0, 0]) // Don't Create UseCase & Presenter
                ->setFiles(['CreateDomain/UserInterface/Controller/DeleteUserController.php'])
        ];

        yield 'CreateDomainAndControllerWithCreatingUseCase' => [
            MakerTestGenerate::create()
                ->createDomain('Security')
                ->setArgs(['name' => 'Auth'])
                ->addInputs([1, 'Login']) // Create UseCase - UseCase Name
                ->addInput(0) // Don't Create a Presenter
                ->setFiles([
                    'Security/Domain/UseCase/Login.php',
                    'Security/UserInterface/Controller/AuthController.php'
                ])
        ];

        yield 'ChooseDomainAndCreateControllerWithCreatingPresenter' => [
            MakerTestGenerate::create()
                ->chooseDomain(1) // Security
                ->setArgs(['name' => 'Password'])
                ->addInput(0) // Don't Create UseCase
                ->addInputs([1, 0, 'UpdatePassword']) // Create Presenter - Type HTML - Presenter Name
                ->setFiles([
                    'Security/UserInterface/Presenter/Html/UpdatePasswordPresenterHTML.php',
                    'Security/UserInterface/Controller/PasswordController.php'
                ])
        ];

        yield 'CreateDomainAndControllerWithCreatingUseCaseAndPresenter' => [
            MakerTestGenerate::create()
                ->createDomain('Account')
                ->setArgs(['name' => 'CreateAccount'])
                ->addInputs([1, 'CreateAccount']) // Create UseCase - UseCase Name
                ->addInputs([1, 0, 'CreateAccount']) // Create Presenter - Type HTML - Presenter Name
                ->setFiles([
                    'Account/Domain/UseCase/CreateAccount.php',
                    'Account/UserInterface/Presenter/Html/CreateAccountPresenterHTML.php',
                    'Account/UserInterface/Controller/CreateAccountController.php'
                ])
        ];

        yield 'ChooseDomainAndCreateControllerWithExistingUseCaseAndCreatePresenter' => [
            MakerTestGenerate::create()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'CreateAdmin'])
                ->addInputs([2, 0]) // Choose UseCase - choose 'CreateAccount'
                ->addInputs([1, 1, 'CreateAdmin']) // Create Presenter - Type JSON - Presenter Name
                ->setFiles([
                    'Account/Domain/UseCase/CreateAccount.php',
                    'Account/UserInterface/Presenter/Json/CreateAdminPresenterJSON.php',
                    'Account/UserInterface/Controller/CreateAdminController.php'
                ])
        ];

        yield 'ChooseDomainAndCreateControllerWithCreatingUseCaseAndExistingPresenter' => [
            MakerTestGenerate::create()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'CreateUser'])
                ->addInputs([1, 'CreateUser']) // Create UseCase - UseCase Name
                ->addInputs([2, 1, 0]) // Choose Presenter - Type JSON - Choose 'CreateAdmin'
                ->setFiles([
                    'Account/Domain/UseCase/CreateUser.php',
                    'Account/UserInterface/Presenter/Json/CreateAdminPresenterJSON.php',
                    'Account/UserInterface/Controller/CreateUserController.php'
                ])
        ];

        yield 'ChooseDomainAndCreateControllerWithExistingUseCaseAndExistingPresenter' => [
            MakerTestGenerate::create()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'CreateRole'])
                ->addInputs([2, 0]) // Choose UseCase - choose 'CreateAccount'
                ->addInputs([2, 1, 0]) // Choose Presenter - Type JSON - Choose 'CreateAdmin'
                ->setFiles([
                    'Account/Domain/UseCase/CreateUser.php',
                    'Account/UserInterface/Presenter/Json/CreateAdminPresenterJSON.php',
                    'Account/UserInterface/Controller/CreateRoleController.php'
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
        yield 'CheckCreateDomainAndController' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('Controller', 'CreateUserController.php', 'namespace App\\CreateDomain\\UserInterface\\Controller')
                ->addContent('Controller', 'CreateUserController.php', 'use Symfony\\Component\\HttpFoundation\Response;')
                ->addContent('Controller', 'CreateUserController.php', 'class CreateUserController extends AbstractController')
                ->addContent('Controller', 'CreateUserController.php', '#[Route(\'/create/user\', name: \'app_create_user\')]')
                ->addContent('Controller', 'CreateUserController.php', 'public function createUserAction(): Response')
                ->addContent('Controller', 'CreateUserController.php', 'return new Response();')
        ];

        yield 'CheckCreateDomainAndControllerWithCreatingUseCase' => [
            MakerTestContent::create('Security')
                ->addContent('Controller', 'AuthController.php', 'namespace App\\Security\\UserInterface\\Controller')
                ->addContent('Controller', 'AuthController.php', 'use Symfony\\Component\\HttpFoundation\Response;')
                ->addContent('Controller', 'AuthController.php', 'use App\\Security\\Domain\\UseCase\\Login;')
                ->addContent('Controller', 'AuthController.php', 'class AuthController extends AbstractController')
                ->addContent('Controller', 'AuthController.php', '#[Route(\'/auth\', name: \'app_auth\')]')
                ->addContent('Controller', 'AuthController.php', 'public function authAction(Login $useCase): Response')
                ->addContent('Controller', 'AuthController.php', 'return $useCase->execute();')
        ];

        yield 'CheckChooseDomainAndCreateControllerWithCreatingPresenter' => [
            MakerTestContent::create('Security')
                ->addContent('Controller', 'PasswordController.php', 'namespace App\\Security\\UserInterface\\Controller')
                ->addContent('Controller', 'PasswordController.php', 'use Symfony\\Component\\HttpFoundation\Response;')
                ->addContent('Controller', 'PasswordController.php', 'use App\\Security\\UserInterface\\Presenter\\Html\\UpdatePasswordPresenterHTML;')
                ->addContent('Controller', 'PasswordController.php', 'class PasswordController extends AbstractController')
                ->addContent('Controller', 'PasswordController.php', '#[Route(\'/password\', name: \'app_password\')]')
                ->addContent('Controller', 'PasswordController.php', 'public function passwordAction(UpdatePasswordPresenterHTML $presenter): Response')
                ->addContent('Controller', 'PasswordController.php', 'return $presenter->present();')
        ];

        yield 'CheckChooseDomainAndCreateControllerWithExistingUseCaseAndExistingPresenter' => [
            MakerTestContent::create('Account')
                ->addContent('Controller', 'CreateRoleController.php', 'namespace App\\Account\\UserInterface\\Controller')
                ->addContent('Controller', 'CreateRoleController.php', 'use Symfony\\Component\\HttpFoundation\JsonResponse;')
                ->addContent('Controller', 'CreateRoleController.php', 'use App\\Account\\Domain\\UseCase\\CreateAccount;')
                ->addContent('Controller', 'CreateRoleController.php', 'use App\\Account\\UserInterface\\Presenter\\Json\\CreateAdminPresenterJSON;')
                ->addContent('Controller', 'CreateRoleController.php', 'class CreateRoleController extends AbstractController')
                ->addContent('Controller', 'CreateRoleController.php', '#[Route(\'/create/role\', name: \'app_create_role\')]')
                ->addContent('Controller', 'CreateRoleController.php', 'public function createRoleAction(CreateAccount $useCase, CreateAdminPresenterJSON $presenter): JsonResponse')
                ->addContent('Controller', 'CreateRoleController.php', '$useCase->execute();')
                ->addContent('Controller', 'CreateRoleController.php', 'return $presenter->present();')
        ];

        yield 'CheckConfigCreateDomain' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('Config', 'routes.yaml', 'createdomain.controller')
                ->addContent('Config', 'routes.yaml', 'resource: { path: ../src/CreateDomain/UserInterface/Controller/, namespace: App\CreateDomain\UserInterface\Controller }')
                ->addContent('Config', 'routes.yaml', 'type: attribute')
        ];

        yield 'CheckConfigSecurity' => [
            MakerTestContent::create('Security')
                ->addContent('Config', 'routes.yaml', 'security.controller')
                ->addContent('Config', 'routes.yaml', 'resource: { path: ../src/Security/UserInterface/Controller/, namespace: App\Security\UserInterface\Controller }')
                ->addContent('Config', 'routes.yaml', 'type: attribute')
        ];

        yield 'CheckConfigAccount' => [
            MakerTestContent::create('Account')
                ->addContent('Config', 'routes.yaml', 'account.controller')
                ->addContent('Config', 'routes.yaml', 'resource: { path: ../src/Account/UserInterface/Controller/, namespace: App\Account\UserInterface\Controller }')
                ->addContent('Config', 'routes.yaml', 'type: attribute')
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
        yield 'CreateControllerWithExistingFileThrowingException' => [
            MakerTestFailed::create()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'CreateAccount'])
                ->addInputs([0, 0]) // Don't Create UseCase & Presenter
                ->setException(FileAlreadyExistException::class, 'Account/UserInterface/Controller/CreateAccountController.php" is already exist.'),
        ];

        yield 'CreateControllerAndUseCaseWithExistingUseCaseFileThrowingException' => [
            MakerTestFailed::create()
                ->chooseDomain(0) // Account
                ->setArgs(['name' => 'CreateFailed'])
                ->addInputs([1, 'CreateUser'])
                ->addInput(0) // Don't Create Presenter
                ->setException(FileAlreadyExistException::class, 'Account/Domain/UseCase/CreateUser.php" is already exist.'),
        ];

        yield 'CreateControllerAndPresenterWithExistingPresenterFileThrowingException' => [
            MakerTestFailed::create()
                ->chooseDomain(2) // Security
                ->setArgs(['name' => 'CreateSecurity'])
                ->addInput(0) // Don't Create UseCase
                ->addInputs([1, 0, 'UpdatePassword']) // Create Presenter - Type HTML - Presenter Name
                ->setException(FileAlreadyExistException::class, 'Security/UserInterface/Presenter/Html/UpdatePasswordPresenterHTML.php" is already exist.'),
        ];

        yield 'CreateControllerAndChooseUseCaseInEmptyFolder' => [
            MakerTestFailed::create()
                ->createDomain('EmptyDomain')
                ->setArgs(['name' => 'Empty'])
                ->addInput(2) // Choose UseCase
                ->setException(NoItemToChooseException::class, 'EmptyDomain/Domain/UseCase')
        ];

        yield 'CreateControllerAndChoosePresenterInEmptyFolder' => [
            MakerTestFailed::create()
                ->createDomain('EmptyDomain')
                ->setArgs(['name' => 'NoPresenter'])
                ->addInput(0) // Don't Create UseCase
                ->addInputs([2, 1]) // Choose Presenter - JSON
                ->setException(NoItemToChooseException::class, 'EmptyDomain/UserInterface/Presenter/Json')
        ];
    }
}
