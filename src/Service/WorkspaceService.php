<?php

namespace App\Service;

use App\Entity\Workspace;
use App\Repository\WorkspaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Http\Discovery\Exception\NotFoundException;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\String\Slugger\SluggerInterface;

class WorkspaceService
{
    private const SHAPE = ['name' => 'string'];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private WorkspaceRepository $repository,
        private SluggerInterface $slugger,
    ) {
    }

    public function get(int $id): Workspace
    {
        return $this->find($id);
    }

    private function find(int $id): Workspace
    {
        $workspace = $this->repository->find($id);
        if (null === $workspace) {
            throw new NotFoundException(sprintf('Workspace with id %d not found', $id));
        }

        return $workspace;
    }

    private function makeUniqueSlugFromName(string $name): string
    {
        $nameSlug = $this->slugger->slug($name);
        $generator = $this->getSlugGenerator((string) $nameSlug);
        foreach ($generator as $slug) {
            $slug = strtolower($this->slugger->slug($slug));
            if ($this->repository->isSlugUnique($slug)) {
                return $slug;
            }
        }
        throw new \LogicException('Generator can`t make unique slug');
    }

    private function getSlugGenerator(string $nameSlug): \Generator
    {
        yield $nameSlug;
        yield $nameSlug.'-'.bin2hex(random_bytes(10));
    }

    public function create(
        #[ArrayShape(self::SHAPE)]
        array $data
    ): Workspace {
        $workspace = new Workspace();
        $workspace->setName($data['name']);
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();

        return $workspace;
    }

    public function update(
        int $id,
        #[ArrayShape(self::SHAPE)]
        array $data
    ): void {
        $workspace = $this->find($id);
        $workspace->setName($data['name']);
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();
    }

    public function delete(int $id): void
    {
        $workspace = $this->find($id);
        $workspace->setDeletedAt((new \DateTime())/*->setTimezone(new \DateTimeZone(date_default_timezone_get()))*/);
        $this->entityManager->persist($workspace);
        $this->entityManager->flush();
    }
}
