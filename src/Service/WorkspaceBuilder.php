<?php

namespace TorqIT\RoleCreatorBundle\Service;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Exception\NotFoundException;
use Pimcore\Model\User\Workspace;
use TorqIT\RoleCreatorBundle\Enum\WorkspaceBuilderEnum;

class WorkspaceBuilder
{
    public function buildObjectWorkspace(string $folderName, ?array $workspaceConfig)
    {
        $folder = DataObject::getByPath($folderName);
        $this->throwIfNull($folder, "data object", $folderName);

        $workspace = new Workspace\DataObject();
        $workspace->setCid($folder->getId());
        $workspace->setCpath($folder->getFullPath());

        $permissions = isset($workspaceConfig['permissions']) ? $workspaceConfig['permissions'] : false;
        $workspace->setSave($permissions === true || in_array(WorkspaceBuilderEnum::SAVE->value, $permissions));
        $workspace->setUnpublish($permissions === true || in_array(WorkspaceBuilderEnum::UNPUBLISH->value, $permissions));

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        $specialConfig = isset($workspaceConfig['special_configs']) ? $workspaceConfig['special_configs'] : false;
        $workspace->setLEdit(isset($specialConfig[WorkspaceBuilderEnum::OBJECT_LOCALIZED_EDIT->value]) ? $specialConfig[WorkspaceBuilderEnum::OBJECT_LOCALIZED_EDIT->value] : null);
        $workspace->setLView(isset($specialConfig[WorkspaceBuilderEnum::OBJECT_LOCALIZED_VIEW->value]) ? $specialConfig[WorkspaceBuilderEnum::OBJECT_LOCALIZED_VIEW->value] : null);
        $workspace->setLayouts(isset($specialConfig[WorkspaceBuilderEnum::OBJECT_CUSTOM_LAYOUTS->value]) ? $specialConfig[WorkspaceBuilderEnum::OBJECT_CUSTOM_LAYOUTS->value] : null);

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

        $workspace->setSave($permissions === true || in_array(WorkspaceBuilderEnum::SAVE->value, $permissions));
        $workspace->setUnpublish($permissions === true || in_array(WorkspaceBuilderEnum::UNPUBLISH->value, $permissions));

        $this->setCommonWorkspaceAttributes($workspace, $permissions);

        return $workspace;
    }

    /** @param string[] $permissions */
    public function setCommonWorkspaceAttributes(Workspace\AbstractWorkspace $workspace, array|bool $permissions)
    {
        $workspace->setList($permissions === true || in_array(WorkspaceBuilderEnum::LIST->value, $permissions));
        $workspace->setView($permissions === true || in_array(WorkspaceBuilderEnum::VIEW->value, $permissions));
        $workspace->setPublish($permissions === true || in_array(WorkspaceBuilderEnum::PUBLISH->value, $permissions));
        $workspace->setDelete($permissions === true || in_array(WorkspaceBuilderEnum::DELETE->value, $permissions));
        $workspace->setRename($permissions === true || in_array(WorkspaceBuilderEnum::RENAME->value, $permissions));
        $workspace->setCreate($permissions === true || in_array(WorkspaceBuilderEnum::CREATE->value, $permissions));
        $workspace->setSettings($permissions === true || in_array(WorkspaceBuilderEnum::SETTINGS->value, $permissions));
        $workspace->setVersions($permissions === true || in_array(WorkspaceBuilderEnum::VERSIONS->value, $permissions));
        $workspace->setProperties($permissions === true || in_array(WorkspaceBuilderEnum::PROPERTIES->value, $permissions));
    }

    private function throwIfNull($folder, $type, $path)
    {
        if (!$folder) {
            throw new NotFoundException("Could not find $type path '$path'");
        }
    }
}
