<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class BaseFixture extends Fixture
{
    private ObjectManager $manager;
    protected Generator $faker;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->loadFaker();
        $this->loadData($manager);
    }

    protected function loadFaker(): void
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create();
        }
    }

    abstract protected function loadData(ObjectManager $manager): void;

    protected function createMany(string $className, int $count, callable $factory): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $entity = new $className();
            $factory($entity, $i);
            $this->manager->persist($entity);
            // store for usage later as App\Entity\ClassName_#COUNT#
            $this->addReference($className.'_'.$i, $entity);
        }
    }
}
