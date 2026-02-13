<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use <?php echo $repository['namespace'] . '\\' . $repository['className']; ?>;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: <?php echo $repository['className'] ?>::class)]<?php echo "\n"; ?>
class <?php echo $className; ?><?php echo "\n"; ?>
{

}
