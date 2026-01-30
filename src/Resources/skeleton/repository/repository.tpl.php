<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $useStatements; ?>
use <?php echo $gateway['namespace'] . '\\' . $gateway['className']; ?>;
use <?php echo $entity['namespace'] . '\\' . $entity['className']; ?>;

/**
 * @extends ServiceEntityRepository<<?php echo $entity['className']?>>
 */
class <?php echo $className; ?> extends ServiceEntityRepository implements <?php echo $gateway['className']?><?php echo "\n"; ?>
{
    public function __construct(ManagerRegistry $registry)<?php echo "\n"; ?>
    {
        parent::__construct($registry, <?php echo $entity['className']?>::class);
    }
}
