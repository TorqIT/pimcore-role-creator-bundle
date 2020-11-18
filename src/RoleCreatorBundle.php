<?php

namespace TorqIT\RoleCreatorBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class RoleCreatorBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/pimcorerolecreator/js/pimcore/startup.js'
        ];
    }
}