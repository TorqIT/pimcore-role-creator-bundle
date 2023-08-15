<?php

namespace TorqIT\RoleCreatorBundle\Service;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
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
    public function buildObjectWorkspace(string $folderName, array $permissions)
    {
        $folder = DataObject::getByPath($folderName);
        $this->throwIfNull($folder, "data object", $folderName);

        $workspace = new Workspace\DataObject();
        $workspace->setCid($folder->getId());

        $workspace->setSave(in_array(self::SAVE, $permissions));
        $workspace->setUnpublish(in_array(self::UNPUBLISH, $permissions));

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        return $workspace;
    }

    /** @param string[] $permissions */
    public function buildAssetWorkspace(string $folderName, array $permissions)
    {
        $folder = Asset::getByPath($folderName);
        $this->throwIfNull($folder, "asset", $folderName);

        $workspace = new Workspace\Asset();
        $workspace->setCid($folder->getId());

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        return $workspace;
    }

    /** @param string[] $permissions */
    public function buildDocumentWorkspace(string $folderName, array $permissions)
    {
        $folder = Document::getByPath($folderName);
        $this->throwIfNull($folder, "document", $folderName);

        $workspace = new Workspace\Document();
        $workspace->setCid($folder->getId());

        $workspace->setSave(in_array(self::SAVE, $permissions));
        $workspace->setUnpublish(in_array(self::UNPUBLISH, $permissions));

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        return $workspace;
    }

    /** @param string[] $permissions */
    public function setCommonWorkspaceAttributes(Workspace\AbstractWorkspace $workspace, array $permissions)
    {
        $workspace->setList(in_array(self::LIST, $permissions));
        $workspace->setView(in_array(self::VIEW, $permissions));
        $workspace->setPublish(in_array(self::PUBLISH, $permissions));
        $workspace->setDelete(in_array(self::DELETE, $permissions));
        $workspace->setRename(in_array(self::RENAME, $permissions));
        $workspace->setCreate(in_array(self::CREATE, $permissions));
        $workspace->setSettings(in_array(self::SETTINGS, $permissions));
        $workspace->setVersions(in_array(self::VERSIONS, $permissions));
        $workspace->setProperties(in_array(self::PROPERTIES, $permissions));
    }

    private function throwIfNull($folder, $type, $path)
    {
        if(!$folder)
        {
            throw new NotFoundException("Could not $type path '$path'");
        }
    }
}
