<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Workspace;
use App\Entity\WorkspaceUser;
use App\Repository\WorkspaceUserRepository;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkspaceVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const MANAGE_MEMBERS = 'manage_members';
    public const DELETE = 'delete';
    public const CHANGE_OWNER = 'change_owner';

    public function __construct(
        private WorkspaceUserRepository $workspaceUserRepository,
    ) {
    }

    #[Pure]
    protected function supports(
        string $attribute,
        $subject
    ): bool {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::MANAGE_MEMBERS, self::CHANGE_OWNER])) {
            return false;
        }

        if (!$subject instanceof Workspace) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Workspace $subject */
        $workspace = $subject;
        $role = $this->getRole($workspace, $user);

        switch ($attribute) {
            case self::VIEW:
                return null !== $role;
            case self::EDIT:
                return in_array($role, [WorkspaceUser::ROLE_EDITOR, WorkspaceUser::ROLE_MAINTAINER, WorkspaceUser::ROLE_OWNER], true);
            case self::MANAGE_MEMBERS:
                return in_array($role, [WorkspaceUser::ROLE_MAINTAINER, WorkspaceUser::ROLE_OWNER], true);
            case self::DELETE:
            case self::CHANGE_OWNER:
            return WorkspaceUser::ROLE_OWNER === $role;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function getRole(Workspace $workspace, User $user): ?string
    {
        return $this->workspaceUserRepository
            ->findOneBy([
                'user' => $user,
                'workspace' => $workspace,
            ])
            ?->getRole()
        ;
    }
}
