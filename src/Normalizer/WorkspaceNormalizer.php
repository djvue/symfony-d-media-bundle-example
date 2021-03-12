<?php

namespace App\Normalizer;

use App\Entity\Workspace;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WorkspaceNormalizer implements NormalizerInterface
{
    public function __construct()
    {
    }

    /**
     * @param Workspace $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'slug' => $object->getSlug(),
            'createdAt' => $object->getCreatedAt()->format('H:i d.m.Y'),
            'updatedAt' => $object->getUpdatedAt()->format('H:i d.m.Y'),
        ];
    }

    public function supportsNormalization($object, string $format = null): bool
    {
        return $object instanceof Workspace;
    }
}
