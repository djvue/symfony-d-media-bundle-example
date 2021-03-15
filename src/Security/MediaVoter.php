<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Workspace;
use App\Entity\WorkspaceUser;
use App\Repository\WorkspaceUserRepository;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Security\MediaPermissions;
use Djvue\DMediaBundle\Service\MediaEntityService;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MediaVoter extends Voter
{
    public function __construct(
        private MediaEntityService $mediaEntityService,
    ) {
    }

    #[Pure]
    protected function supports(
        string $attribute,
        $subject
    ): bool {
        $types = [MediaPermissions::VIEW, MediaPermissions::EDIT, MediaPermissions::DELETE, MediaPermissions::UPLOAD];
        if (!in_array($attribute, $types,true)) {
            return false;
        }

        if (!$subject instanceof Media) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Media $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::VIEW:
                return true;
            case self::EDIT:
            case self::DELETE:
                $entityHasMedias = $this->mediaEntityService->getEntitiesOfType($subject, 'workspace');
                foreach ($entityHasMedias as $entityHasMedia) {
                    if ($entityHasMedia->getEntityId() === 1) {
                        return false;
                    }
                }
                return true;
        }

        throw new \LogicException('This code should not be reached!');
    }
}
