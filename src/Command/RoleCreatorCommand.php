<?php

namespace TorqIT\RoleCreatorBundle\Command;

use Pimcore\Config;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\Document\DocType;
use Pimcore\Model\User\Permission\Definition;
use Pimcore\Model\User\Role;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Config\Pimcore\ConfigLocation\DocumentTypesConfig;
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
            return;
        }

        $workspaces = $roleProperties["workspaces"];

        if(key_exists("data_objects", $workspaces))
        {
            $objectWorkspaces = [];

            foreach($workspaces["data_objects"] as $folder => $permissions)
            {
                $this->output->writeln("Configuring data object workspace for '$folder'", OutputInterface::VERBOSITY_VERBOSE);
                $objectWorkspaces[] = $this->workspaceBuilder->buildObjectWorkspace($folder, $permissions);
            }

            $role->setWorkspacesObject($objectWorkspaces);
        }

        if(key_exists("assets", $workspaces))
        {
            $assetWorkspaces = [];

            foreach($workspaces["assets"] as $folder => $permissions)
            {
                $this->output->writeln("Configuring asset workspace for '$folder'", OutputInterface::VERBOSITY_VERBOSE);
                $assetWorkspaces[] = $this->workspaceBuilder->buildAssetWorkspace($folder, $permissions);
            }

            $role->setWorkspacesAsset($assetWorkspaces);
        }

        if(key_exists("documents", $workspaces))
        {
            $documentWorkspaces = [];

            foreach($workspaces["documents"] as $folder => $permissions)
            {
                $this->output->writeln("Configuring document workspace for '$folder'", OutputInterface::VERBOSITY_VERBOSE);
                $documentWorkspaces[] = $this->workspaceBuilder->buildDocumentWorkspace($folder, $permissions);
            }

            $role->setWorkspacesDocument($documentWorkspaces);
        }
    }

    private function applyAllowedTypes(Role $role, array $roleProperties)
    {
        if(!key_exists("allowedTypes", $roleProperties))
        {
            return;
        }

        $allowedTypes = $roleProperties["allowedTypes"];

        if(key_exists("classes", $allowedTypes) && is_array($allowedTypes["classes"]))
        {
            $allowedClasses = [];

            foreach($allowedTypes["classes"] as $className)
            {
                $classDef = ClassDefinition::getByName($className);

                if($classDef)
                {
                    $allowedClasses[] = $classDef->getId();
                }
            }

            $role->setClasses($allowedClasses);
        }

        if(key_exists("document_types", $allowedTypes) && is_array($allowedTypes["document_types"]))
        {
            $allowedDocs = [];
            $docTypes = (new DocType\Listing())->load();

            foreach($allowedTypes["document_types"] as $docName)
            {
                $docType = $this->findDocWithName($docName, $docTypes);

                if($docType)
                {
                    $allowedDocs[] = $docType->getId();
                }
            }

            $role->setDocTypes($allowedDocs);
        }
    }

    /** @param DocType[] $docTypes */
    private function findDocWithName(string $name, array $docTypes)
    {
        foreach($docTypes as $docType)
        {
            if($docType->getName() == $name)
            {
                return $docType;
            }
        }

        return null;
    }
}
