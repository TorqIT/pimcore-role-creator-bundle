<?php

namespace TorqIT\RoleCreatorBundle\Command;

use Pimcore\Config;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\User\Role;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RoleCreatorCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('torq:generate-roles')
            ->setDescription('Command for creating user roles in the pimcore admin interface.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roleFileLocation = PIMCORE_PROJECT_ROOT . '/config/roles.yml';
        $myConfig = new Config();
        $roleStructureArray = $myConfig->getConfigInstance($roleFileLocation, true);

        if ($roleStructureArray["system_roles"]) {
            $systemRoles = $roleStructureArray["system_roles"];
            foreach ($systemRoles as $roleName => $roleProperties) {
                $this->createRole($roleName, $roleProperties);
            }
        }

        return 0;
    }

    private function createRole($roleName, $roleProperties)
    {
        $roleExists = Role::getByName($roleName);
        if ($roleExists) {
            return;
        }

        $newRole = new Role();
        $newRole->setParentId(0);
        $newRole->setName($roleName);
        $newRole->save();
    }
}
