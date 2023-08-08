<?php

namespace TorqIT\RoleCreatorBundle\Service;

use Pimcore\Model\Document\Folder as AssetFolder;
use Pimcore\Model\Asset\Folder as DocumentFolder;
use Pimcore\Model\DataObject\Folder as DataObjectFolder;
use Pimcore\Model\Exception\NotFoundException;
use Pimcore\Model\User\Role;
use Pimcore\Model\User\Workspace;

class WorkspaceBuilder
{
    private const LIST = "list";
    private const VIEW = "view";
    private const SAVE = "save";
    private const PUBLISH = "publish";
    private const UNPUBLISH = "unpublish";
    private const DELETE = "delete";
    private const RENAME = "rename";
    private const CREATE = "create";
    private const SETTINGS = "settings";
    private const VERSIONS = "versions";
    private const PROPERTIES = "properties";

    /** @param string[] $permissions */
    public function buildObjectWorkspaceIntoRole(Role $role, string $folderName, array $permissions)
    {
        $folder = DataObjectFolder::getByPath($folderName);

        if(!$folder)
        {
            throw new NotFoundException("Could not find folder or data object with path '$folderName'");
        }

        /** @var Workspace\DataObject $workspace */
        $workspace = $this->findWorkspace($folder->getId(), $role->getWorkspacesObject());

        if(!$workspace)
        {
            $workspace = new Workspace\DataObject();
            $workspace->setCid($folder->getId());

            $role->setWorkspacesObject(array_merge($role->getWorkspacesObject(), [$workspace]));
        }

        $workspace->setList(in_array(self::LIST, $permissions));
        $workspace->setView(in_array(self::VIEW, $permissions));
        $workspace->setSave(in_array(self::SAVE, $permissions));
        $workspace->setPublish(in_array(self::PUBLISH, $permissions));
        $workspace->setUnpublish(in_array(self::UNPUBLISH, $permissions));
        $workspace->setDelete(in_array(self::DELETE, $permissions));
        $workspace->setRename(in_array(self::RENAME, $permissions));
        $workspace->setCreate(in_array(self::CREATE, $permissions));
        $workspace->setSettings(in_array(self::SETTINGS, $permissions));
        $workspace->setVersions(in_array(self::VERSIONS, $permissions));
        $workspace->setProperties(in_array(self::PROPERTIES, $permissions));
    }

    /** @param Workspace\AbstractWorkspace[] $workspaces */
    private function findWorkspace(int $folderId, array $workspaces)
    {
        foreach($workspaces as $workspace)
        {
            if($folderId == $workspace->getCid())
            {
                return $workspace;
            }
        }

        return null;
    }
}
