<?php

namespace TorqIT\RoleCreatorBundle\EventListener;

use Pimcore\Event\Model\UserRoleEvent;
use Pimcore\Model\User\Role;
use Pimcore\Config;
use Symfony\Component\Yaml\Yaml;
use Pimcore\Model\User\Workspace;
use TorqIT\RoleCreatorBundle\Enum\WorkspaceBuilderEnum;
use TorqIT\RoleCreatorBundle\Service\RoleConfigService;

class RoleListener
{
    public function __construct(private RoleConfigService $roleConfigService) {}

    public function onPostRoleUpdate(UserRoleEvent $event): void
    {
        $userRole = $event->getUserRole();
        if ($userRole instanceof Role) {
            $this->updateRoleInYaml($userRole);
        }
    }

    private function updateRoleInYaml(Role $role): void
    {
        $roleName = $role->getName();

        $roleFileLocation = $this->roleConfigService->getRolesFilePath();
        $config = new Config();
        try {
            $roleStructureArray = $config->getConfigInstance($roleFileLocation, true);
        } catch (\Exception $e) {
            // File not found or invalid YAML. Will create a new one.
            $roleStructureArray = [
                'system_roles' => []
            ];
        }


        // Setup initial structure for role
        $newRoleData = [
            'included_permissions' => $role->getPermissions(),
            'perspectives' => $role->getPerspectives(),
            'allowedTypes' => [
                'classes' => $role->getClasses(),
                'document_types' => $role->getDocTypes()
            ]
        ];

        foreach ($role->getWorkspacesDocument() as $documentWorkspace) {
            if ($documentWorkspace instanceof \Pimcore\Model\User\Workspace\Document) {
                $permissions = $this->getCommonPermissionListFromWorkspace($documentWorkspace);
                $documentWorkspace->getUnpublish() && $permissions[] = WorkspaceBuilderEnum::UNPUBLISH->value;
                $documentWorkspace->getSave() && $permissions[] = WorkspaceBuilderEnum::SAVE->value;

                $newRoleData['workspaces']['documents'][$documentWorkspace->getCpath()] = $permissions;
            }
        }

        foreach ($role->getWorkspacesAsset() as $assetWorkspace) {
            if ($assetWorkspace instanceof \Pimcore\Model\User\Workspace\Asset) {
                $newRoleData['workspaces']['assets'][$assetWorkspace->getCpath()] = $this->getCommonPermissionListFromWorkspace($assetWorkspace);
            }
        }

        foreach ($role->getWorkspacesObject() as $objectWorkspace) {
            if ($objectWorkspace instanceof \Pimcore\Model\User\Workspace\DataObject) {
                $permissions = $this->getCommonPermissionListFromWorkspace($objectWorkspace);
                $objectWorkspace->getUnpublish() && $permissions[] = WorkspaceBuilderEnum::UNPUBLISH->value;
                $objectWorkspace->getSave() && $permissions[] = WorkspaceBuilderEnum::SAVE->value;

                $newRoleData['workspaces']['data_objects'][$objectWorkspace->getCpath()] = [
                    'permissions' => $permissions,
                    'special_configs' => [
                        WorkspaceBuilderEnum::OBJECT_LOCALIZED_EDIT->value => $objectWorkspace->getLEdit(),
                        WorkspaceBuilderEnum::OBJECT_LOCALIZED_VIEW->value => $objectWorkspace->getLView(),
                        WorkspaceBuilderEnum::OBJECT_CUSTOM_LAYOUTS->value => $objectWorkspace->getLayouts()
                    ],
                ];
            }
        }

        // If excluded_permissions are set, remove included_permissions
        if (isset($roleStructureArray['system_roles'][$roleName]['excluded_permissions'])) {
            unset($newRoleData['included_permissions']);
            $newRoleData['excluded_permissions'] = $roleStructureArray['system_roles'][$roleName]['excluded_permissions'];
        }

        // Update main system_roles array
        $roleStructureArray['system_roles'][$roleName] = $newRoleData;

        // Update roles.yaml file
        $yamlOutput = Yaml::dump($roleStructureArray, 6);

        file_put_contents($roleFileLocation, $yamlOutput);
    }

    private function getCommonPermissionListFromWorkspace(Workspace\AbstractWorkspace $workspace)
    {
        $permissions = [];

        $workspace->getList() && $permissions[] = WorkspaceBuilderEnum::LIST->value;
        $workspace->getView() && $permissions[] = WorkspaceBuilderEnum::VIEW->value;
        $workspace->getPublish() && $permissions[] = WorkspaceBuilderEnum::PUBLISH->value;
        $workspace->getDelete() && $permissions[] = WorkspaceBuilderEnum::DELETE->value;
        $workspace->getRename() && $permissions[] = WorkspaceBuilderEnum::RENAME->value;
        $workspace->getCreate() && $permissions[] = WorkspaceBuilderEnum::CREATE->value;
        $workspace->getSettings() && $permissions[] = WorkspaceBuilderEnum::SETTINGS->value;
        $workspace->getVersions() && $permissions[] = WorkspaceBuilderEnum::VERSIONS->value;
        $workspace->getProperties() && $permissions[] = WorkspaceBuilderEnum::PROPERTIES->value;

        return $permissions;
    }
}
