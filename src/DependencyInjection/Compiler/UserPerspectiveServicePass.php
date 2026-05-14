<?php

declare(strict_types=1);

namespace TorqIT\RoleCreatorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TorqIT\RoleCreatorBundle\Command\RoleCreatorCommand;
use TorqIT\RoleCreatorBundle\EventListener\RoleListener;

class UserPerspectiveServicePass implements CompilerPassInterface
{
    private const PIMCORE_SERVICE_ID = 'Pimcore\Bundle\StudioBackendBundle\User\Service\UserPerspectiveServiceInterface';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::PIMCORE_SERVICE_ID)) {
            return;
        }

        $ref = new Reference(self::PIMCORE_SERVICE_ID);

        foreach ([RoleCreatorCommand::class, RoleListener::class] as $serviceId) {
            if ($container->has($serviceId)) {
                $container->getDefinition($serviceId)
                    ->addMethodCall('setUserPerspectiveService', [$ref]);
            }
        }
    }
}
