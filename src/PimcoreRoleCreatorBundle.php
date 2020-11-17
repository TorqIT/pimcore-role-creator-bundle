<?php

namespace TorqIT\PimcoreRoleCreatorBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class PimcoreRoleCreatorBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/pimcorerolecreator/js/pimcore/startup.js'
        ];
    }
}