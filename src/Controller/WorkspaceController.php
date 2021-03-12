<?php

namespace App\Controller;

use App\Controller\BaseApiController;
use App\Entity\User;
use App\Service\WorkspaceService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/board/workspace', name: 'board_workspace')]
class WorkspaceController extends BaseApiController
{
    protected SerializerInterface $serializer;

    public function __construct(
        private WorkspaceService $workspaceService
    ) {
    }

    #[Route('', name: 'all', methods: ['GET', 'HEAD'])]
    public function filter(Request $request, UserInterface $user): JsonResponse
    {
        /** @var User $user */
        $workspaces = $this->workspaceService->allForUser($user->getId());

        return $this->ok([
            'workspaces' => $workspaces,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $name = $request->get('name', '');
        if ('' === $name) {
            $this->badInput('Name is required');
        }
        try {
            $this->workspaceService->update($id, [
                'name' => $name,
            ]);
        } catch (EntityNotFoundException $exception) {
            $this->unprocessable($exception->getMessage());
        }

        return $this->ok();
    }

    #[Route('/{id<\d+>}', name: 'get', methods: ['GET', 'HEAD'])]
    public function get(int $id): JsonResponse
    {
        try {
            $workspace = $this->workspaceService->get($id);
        } catch (EntityNotFoundException $exception) {
            $this->unprocessable($exception->getMessage());
        }

        return $this->created(['workspace' => $workspace]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserInterface $user): JsonResponse
    {
        /** @var User $user */
        $name = $request->get('name', '');
        if ('' === $name) {
            $this->badInput('Name is required');
        }
        $workspace = $this->workspaceService->createForUser($user, [
            'name' => $name,
        ]);

        return $this->created(['workspace' => $workspace]);
    }

    #[Route('/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->workspaceService->delete($id);
        } catch (EntityNotFoundException $exception) {
            $this->unprocessable($exception->getMessage());
        }

        return $this->ok();
    }

    #[Route('/{id<\d+>}/set-order', name: 'set_order', methods: ['PUT'])]
    public function setOrder(int $id, UserInterface $user, Request $request): JsonResponse
    {
        $order = $request->get('order', null);
        if (null === $order) {
            $this->badInput('Order is required');
        }
        $order = (int) $order;
        try {
            /* @var User $user */
            $this->workspaceService->setOrder($id, $user->getId(), $order);
        } catch (EntityNotFoundException $exception) {
            $this->unprocessable($exception->getMessage());
        }

        return $this->ok();
    }
}
