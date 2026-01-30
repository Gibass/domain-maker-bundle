<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $useStatements; ?>

readonly class <?php echo $className; ?><?php echo "\n"; ?>
{
    public function present(): Response<?php echo "\n"; ?>
    {
        return new Response();
    }
}
