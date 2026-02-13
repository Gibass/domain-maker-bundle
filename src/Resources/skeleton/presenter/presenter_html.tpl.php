<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $useStatements; ?>

class <?php echo $className; ?> extends AbstractWebPresenter<?php echo "\n"; ?>
{
    public function present(): Response<?php echo "\n"; ?>
    {
        return $this->render('<?php echo $template; ?>', []);
    }
}
