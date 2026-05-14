<?php

declare(strict_types=1);

namespace TorqIT\RoleCreatorBundle\Service;

use Pimcore\Model\User\UserRoleInterface;

interface UserPerspectiveServiceInterface
{
    public function updatePerspectives(array $perspectivesToSet, UserRoleInterface $user): void;

    /** @return array<object> */
    public function getConfigPerspectives(UserRoleInterface $user): array;
}
