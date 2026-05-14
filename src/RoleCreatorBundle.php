<?php

declare(strict_types=1);

namespace TorqIT\RoleCreatorBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TorqIT\RoleCreatorBundle\DependencyInjection\Compiler\UserPerspectiveServicePass;

class RoleCreatorBundle extends AbstractPimcoreBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new UserPerspectiveServicePass());
    }
}
