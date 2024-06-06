<?php

namespace TorqIT\RoleCreatorBundle\Service;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Exception\NotFoundException;
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

    private const OBJECT_LOCALIZED_EDIT = "localized_edit";
    private const OBJECT_LOCALIZED_VIEW = "localized_view";
    private const OBJECT_CUSTOM_LAYOUTS = "custom_layouts";

    /** @param string[] $permissions */
    public function buildObjectWorkspace(string $folderName, array|bool $permissions)
    {
        $folder = DataObject::getByPath($folderName);
        $this->throwIfNull($folder, "data object", $folderName);

        $workspace = new Workspace\DataObject();
        $workspace->setCid($folder->getId());
        $workspace->setCpath($folder->getFullPath());

        $permissions = isset($workspaceConfig['permissions']) ? $workspaceConfig['permissions'] : false;
        $workspace->setSave($permissions === true || in_array(self::SAVE, $permissions));
        $workspace->setUnpublish($permissions === true || in_array(self::UNPUBLISH, $permissions));

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        $specialConfig = isset($workspaceConfig['special_configs']) ? $workspaceConfig['special_configs'] : false;
        $workspace->setLEdit(isset($specialConfig[self::OBJECT_LOCALIZED_EDIT]) ? $specialConfig[self::OBJECT_LOCALIZED_EDIT] : null);
        $workspace->setLView(isset($specialConfig[self::OBJECT_LOCALIZED_VIEW]) ? $specialConfig[self::OBJECT_LOCALIZED_VIEW] : null);
        $workspace->setLayouts(isset($specialConfig[self::OBJECT_CUSTOM_LAYOUTS]) ? $specialConfig[self::OBJECT_CUSTOM_LAYOUTS] : null);

        return $workspace;
    }

    /** @param string[] $permissions */
    public function buildAssetWorkspace(string $folderName, array|bool $permissions)
    {
        $folder = Asset::getByPath($folderName);
        $this->throwIfNull($folder, "asset", $folderName);

        $workspace = new Workspace\Asset();
        $workspace->setCid($folder->getId());
        $workspace->setCpath($folder->getFullPath());

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        return $workspace;
    }

    /** @param string[] $permissions */
    public function buildDocumentWorkspace(string $folderName, array|bool $permissions)
    {
        $folder = Document::getByPath($folderName);
        $this->throwIfNull($folder, "document", $folderName);

        $workspace = new Workspace\Document();
        $workspace->setCid($folder->getId());
        $workspace->setCpath($folder->getFullPath());

        $workspace->setSave($permissions === true || in_array(self::SAVE, $permissions));
        $workspace->setUnpublish($permissions === true || in_array(self::UNPUBLISH, $permissions));

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        return $workspace;
    }

    /** @param string[] $permissions */
    public function setCommonWorkspaceAttributes(Workspace\AbstractWorkspace $workspace, array|bool $permissions)
    {
        $workspace->setList($permissions === true || in_array(self::LIST, $permissions));
        $workspace->setView($permissions === true || in_array(self::VIEW, $permissions));
        $workspace->setPublish($permissions === true || in_array(self::PUBLISH, $permissions));
        $workspace->setDelete($permissions === true || in_array(self::DELETE, $permissions));
        $workspace->setRename($permissions === true || in_array(self::RENAME, $permissions));
        $workspace->setCreate($permissions === true || in_array(self::CREATE, $permissions));
        $workspace->setSettings($permissions === true || in_array(self::SETTINGS, $permissions));
        $workspace->setVersions($permissions === true || in_array(self::VERSIONS, $permissions));
        $workspace->setProperties($permissions === true || in_array(self::PROPERTIES, $permissions));
    }

    private function throwIfNull($folder, $type, $path)
    {
        if (!$folder) {
            throw new NotFoundException("Could not find $type path '$path'");
        }
    }
}
