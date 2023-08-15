<?php

namespace TorqIT\RoleCreatorBundle\Command;

use Pimcore\Config;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\User\Permission\Definition;
use Pimcore\Model\User\Role;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorqIT\RoleCreatorBundle\Service\WorkspaceBuilder;

class RoleCreatorCommand extends AbstractCommand
{
    private array $permissionKeys;

    public function __construct(
      private WorkspaceBuilder $workspaceBuilder
    ) {
        parent::__construct();
    }

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
                $this->createOrUpdateRole($roleName, $roleProperties);
            }
        }

        return 0;
    }

    private function createOrUpdateRole($roleName, $roleProperties)
    {
        $role = Role::getByName($roleName);

        if (!$role) {
            $this->output->writeln("Creating new role: $roleName");
            $role = new Role();
        }
        else {
            $this->output->writeln("Updating role: $roleName");
        }

        $this->applyPermissions($role, $roleProperties);
        $this->applyWorkspaces($role, $roleProperties);

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

    private function applyWorkspaces(Role $role, array $roleProperties)
    {
        if(!key_exists("workspaces", $roleProperties))
        {
            $role->setWorkspacesObject([]);
            $role->setWorkspacesAsset([]);
            $role->setWorkspacesDocument([]);
            return;
        }

        $workspaces = $roleProperties["workspaces"];

        $this->buildAndSetWorkspaces($workspaces, $role, "data_objects", "Object", "data object");
        $this->buildAndSetWorkspaces($workspaces, $role, "assets", "Asset", "asset");
        $this->buildAndSetWorkspaces($workspaces, $role, "documents", "Document", "document");
    }

    //A bit ugly, but it saves SO much room
    private function buildAndSetWorkspaces(array $workspaces, Role $role, string $propertyKey, string $workspaceType, string $workspacePrettyName)
    {
        $setFunc = "setWorkspaces{$workspaceType}";

        if(key_exists($propertyKey, $workspaces))
        {
            $builtWorkspaces = [];
            $buildFunc = "build{$workspaceType}Workspace";

            foreach($workspaces[$propertyKey] as $folder => $permissions)
            {
                $this->output->writeln("Configuring $workspacePrettyName workspace for '$folder'", OutputInterface::VERBOSITY_VERBOSE);
                $builtWorkspaces[] = $this->workspaceBuilder->$buildFunc($folder, $permissions);
            }

            $role->$setFunc($builtWorkspaces);
        }
        else {
            $role->$setFunc([]);
        }
    }
}
