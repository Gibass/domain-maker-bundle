<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $useStatements; ?>
use Symfony\Component\HttpFoundation\<?php echo $presenter['response']; ?>;
use <?php echo $presenter['namespace'] . '\\' . $presenter['className']; ?>;

class <?php echo $className; ?> extends AbstractController
{
    #[Route('<?php echo $routePath; ?>', name: '<?php echo $routeName; ?>')]
    public function <?php echo $method; ?>Action(<?php echo $presenter['className']?> $presenter): <?php echo $presenter['response']; ?>
    {
        return $presenter->present();
    }
}
