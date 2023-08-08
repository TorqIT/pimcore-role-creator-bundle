<?php

namespace TorqIT\RoleCreatorBundle\Command;

use Pimcore\Config;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\User\Permission\Definition;
use Pimcore\Model\User\Role;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RoleCreatorCommand extends AbstractCommand
{
    private array $permissionKeys;

    protected function configure()
    {
        $this
            ->setName('torq:generate-roles')
            ->setDescription('Command for creating user roles in the pimcore admin interface.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->permissionKeys = array_map(fn(Definition $d) => $d->getKey(),(new Definition\Listing())->load());
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
        $role = Role::getByName($roleName);

        if (!$role) {
            $role = new Role();
        }

        $this->applyPermissions($role, $roleProperties);

        $role->setParentId(0);
        $role->setName($roleName);
        $role->save();
    }

    private function applyPermissions(Role $role, array $roleProperties)
    {
        if(key_exists("included_permissions", $roleProperties))
        {
            $nonExistentPermissions = array_diff($roleProperties["included_permissions"], $this->permissionKeys);

            if(!empty($nonExistentPermissions))
            {
                $unrecognizedPermissions = implode(", ", $nonExistentPermissions);
                $this->output->writeln("<comment>WARNING: Found unrecognized permissions ($unrecognizedPermissions)</comment>");
            }

            $role->setPermissions($roleProperties["included_permissions"]);
        }
        else if(key_exists("excluded_permissions", $roleProperties))
        {
            $targetPermissions = array_diff($this->permissionKeys, $roleProperties["excluded_permissions"]);
            $role->setPermissions($targetPermissions);
        }
        else if(key_exists("all_permissions", $roleProperties))
        {
            $role->setPermissions($this->permissionKeys);
        }
    }
}
