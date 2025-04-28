<?php

namespace TorqIT\RoleCreatorBundle\Service;

class RoleConfigService
{
    public function getRolesFilePath(): string
    {
        return PIMCORE_PROJECT_ROOT . '/config/pimcore/roles.yaml';
    }
}
