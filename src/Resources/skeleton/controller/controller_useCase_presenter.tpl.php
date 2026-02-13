<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $useStatements; ?>
use Symfony\Component\HttpFoundation\<?php echo $presenter['response']; ?>;
use <?php echo $presenter['namespace'] . '\\' . $presenter['className']; ?>;
use <?php echo $useCase['namespace'] . '\\' . $useCase['className']; ?>;

class <?php echo $className; ?> extends AbstractController
{
    #[Route('<?php echo $routePath; ?>', name: '<?php echo $routeName; ?>')]
    public function <?php echo $method; ?>Action(<?php echo $useCase['className']?> $useCase, <?php echo $presenter['className']?> $presenter): <?php echo $presenter['response']; ?>
    {
        $useCase->execute();
        return $presenter->present();
    }
}
