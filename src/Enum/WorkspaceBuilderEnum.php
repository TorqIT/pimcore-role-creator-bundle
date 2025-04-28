<?php

namespace TorqIT\RoleCreatorBundle\Enum;

enum WorkspaceBuilderEnum: string
{
    case LIST = 'list';
    case VIEW = 'view';
    case SAVE = 'save';
    case PUBLISH = 'publish';
    case UNPUBLISH = 'unpublish';
    case DELETE = 'delete';
    case RENAME = 'rename';
    case CREATE = 'create';
    case SETTINGS = 'settings';
    case VERSIONS = 'versions';
    case PROPERTIES = 'properties';

    case OBJECT_LOCALIZED_EDIT = 'localized_edit';
    case OBJECT_LOCALIZED_VIEW = 'localized_view';
    case OBJECT_CUSTOM_LAYOUTS = 'custom_layouts';
}
