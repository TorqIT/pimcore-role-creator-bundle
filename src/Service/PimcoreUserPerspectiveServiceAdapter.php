<?php

declare(strict_types=1);

namespace TorqIT\RoleCreatorBundle\Service;

use Pimcore\Bundle\StudioBackendBundle\User\Service\UserPerspectiveServiceInterface as PimcoreUserPerspectiveServiceInterface;
use Pimcore\Model\User\UserRoleInterface;

class PimcoreUserPerspectiveServiceAdapter implements UserPerspectiveServiceInterface
{
    public function __construct(
        private PimcoreUserPerspectiveServiceInterface $inner,
    ) {}

    public function updatePerspectives(array $perspectivesToSet, UserRoleInterface $user): void
    {
        $this->inner->updatePerspectives($perspectivesToSet, $user);
    }

    public function getConfigPerspectives(UserRoleInterface $user): array
    {
        return $this->inner->getConfigPerspectives($user);
    }
}
