<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Workspace;
use App\Entity\WorkspaceUser;
use Doctrine\Persistence\ObjectManager;

class WorkspaceFixtures extends BaseFixture
{
    public const TEST_WORKSPACE_REFERENCE = 'test-workspace';

    public function loadData(ObjectManager $manager): void
    {
        /** @var User $testUser */
        $testUser = $this->getReference(TestUserFixture::TEST_USER_REFERENCE);
        for ($i = 0; $i < 3; ++$i) {
            $workspace = new Workspace();
            $workspace->setName($this->faker->name);
            $workspace->setSlug($this->faker->unique()->slug);
            $manager->persist($workspace);

            $workspaceUser = new WorkspaceUser();
            $workspaceUser->setRole(WorkspaceUser::ROLE_OWNER);
            $workspaceUser->setListOrder(random_int(1, 100) * 100);
            $workspaceUser->setUser($testUser);
            $workspaceUser->setWorkspace($workspace);
            $manager->persist($workspaceUser);

            if (0 === $i) {
                $this->addReference(self::TEST_WORKSPACE_REFERENCE, $workspace);
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TestUserFixture::class,
        ];
    }
}
