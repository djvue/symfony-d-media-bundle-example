<?php

declare(strict_types=1);

namespace App\Service;

use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Service\MediaEntityService;
use Djvue\DMediaBundle\Service\MediaService;

class DemoMediasService
{
    public const DEMO_ENTITY_TYPE = 'workspace';
    public const DEMO_ENTITY_ID = 1;

    public function __construct(
        private MediaService $mediaService,
        private MediaEntityService $mediaEntityService,
    )
    {
    }

    public function getEntities(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Workspace',
                'type' => 'workspace',
                'options' => [
                    [ 'id' => 1, 'name' => 'Google' ],
                    [ 'id' => 2, 'name' => 'Yandex' ],
                    [ 'id' => 3, 'name' => 'Mail' ],
                    [ 'id' => 4, 'name' => 'Amazon' ],
                ],
                'allowMultiple' => false
            ]
        ];
    }

    /**
     * @return Media[]
     */
    public function getSingleMedias(): array
    {
        $media = $this->mediaEntityService->getOneMedia(self::DEMO_ENTITY_TYPE, self::DEMO_ENTITY_ID);
        return $media === null ? [] : [$media];
    }

    /**
     * @return Media[]
     */
    public function getMultiMedias(): array
    {
        return $this->mediaEntityService->getMedias(self::DEMO_ENTITY_TYPE, self::DEMO_ENTITY_ID);
    }
}
