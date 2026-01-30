<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $rootNamespace; ?>\Core\UserInterface\Presenter\AbstractJsonPresenter;
<?php echo $useStatements; ?>

class <?php echo $className; ?> extends AbstractJsonPresenter<?php echo "\n"; ?>
{
    public function present(): JsonResponse<?php echo "\n"; ?>
    {
        return $this->json([]);
    }
}
