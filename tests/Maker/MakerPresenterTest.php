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

class MakerPresenterTest extends MakerTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $application = new Application($kernel);
        $command = $application->find('maker:presenter');
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
        yield 'CreateDomainAndHtmlPresenter' => [
            MakerTestGenerate::create()
                ->createDomain('CreateDomain')
                ->setArgs(['name' => 'TestUseCase'])
                ->addInput(0) // Html
                ->setFiles(['CreateDomain/UserInterface/Presenter/Html/TestUseCasePresenterHTML.php'])
        ];

        yield 'ChooseExistingDomainAndCreateJsonPresenter' => [
            MakerTestGenerate::create()
                ->chooseDomain(0)
                ->setArgs(['name' => 'NewUseCase'])
                ->addInput(1) // Json
                ->setFiles(['CreateDomain/UserInterface/Presenter/Json/NewUseCasePresenterJSON.php'])
        ];

        yield 'CreateDomainAndHtmlPresenterWithSuffix' => [
            MakerTestGenerate::create()
                ->createDomain('NewDomain')
                ->setArgs(['name' => 'TestWithSuffixPresenterHTML'])
                ->addInput(0) // Html
                ->setFiles(['NewDomain/UserInterface/Presenter/Html/TestWithSuffixPresenterHTML.php'])
        ];
    }

    #[DataProvider('dataTestContentProvider')]
    public function testContentSuccessful(MakerTestContent $content): void
    {
        $this->assertFileContent($content);
    }

    public static function dataTestContentProvider(): \Generator
    {
        yield 'CheckCreatedHtmlPresenterContentWithCreatedDomain' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('Presenter', 'Html/TestUseCasePresenterHTML.php', 'namespace App\\CreateDomain\\UserInterface\\Presenter\\Html')
                ->addContent('Presenter', 'Html/TestUseCasePresenterHTML.php', 'use Gibass\\Domain\Core\\UserInterface\\Presenter\\AbstractWebPresenter;')
                ->addContent('Presenter', 'Html/TestUseCasePresenterHTML.php', 'use Symfony\\Component\\HttpFoundation\\Response;')
                ->addContent('Presenter', 'Html/TestUseCasePresenterHTML.php', 'class TestUseCasePresenterHTML extends AbstractWebPresenter')
                ->addContent('Presenter', 'Html/TestUseCasePresenterHTML.php', 'public function present(): Response')
                ->addContent('Presenter', 'Html/TestUseCasePresenterHTML.php', 'return $this->render(\'pages/create-domain/index/index.html.twig\', []);')
        ];

        yield 'CheckCreatedJsonPresenterContentWithChosenDomain' => [
            MakerTestContent::create('CreateDomain')
                ->addContent('Presenter', 'Json/NewUseCasePresenterJSON.php', 'namespace App\\CreateDomain\\UserInterface\\Presenter\\Json')
                ->addContent('Presenter', 'Json/NewUseCasePresenterJSON.php', 'use Gibass\\Domain\Core\\UserInterface\\Presenter\\AbstractJsonPresenter;')
                ->addContent('Presenter', 'Json/NewUseCasePresenterJSON.php', 'use Symfony\\Component\\HttpFoundation\\JsonResponse;')
                ->addContent('Presenter', 'Json/NewUseCasePresenterJSON.php', 'class NewUseCasePresenterJSON extends AbstractJsonPresenter')
                ->addContent('Presenter', 'Json/NewUseCasePresenterJSON.php', 'public function present(): JsonResponse')
                ->addContent('Presenter', 'Json/NewUseCasePresenterJSON.php', 'return $this->json([]);')
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
        yield 'CreatePresenterWithExistingFileThrowingException' => [
            MakerTestFailed::create()
                ->chooseDomain(0)
                ->setArgs(['name' => 'NewUseCase'])
                ->addInput(1) // Json
                ->setException(FileAlreadyExistException::class, 'CreateDomain/UserInterface/Presenter/Json/NewUseCasePresenterJSON.php" is already exist.'),
        ];
    }
}
