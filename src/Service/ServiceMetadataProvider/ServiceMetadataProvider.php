<?php

declare(strict_types=1);

namespace App\Service\ServiceMetadataProvider;

use App\Attribute\ServiceMetadata;
use App\Data\ValueObject\ServiceMetadataInfo;
use ReflectionClass;

readonly class ServiceMetadataProvider
{
    public function getAttributeMetadata(object $service): ?ServiceMetadataInfo
    {
        $reflection = new ReflectionClass($service);

        $attributes = $reflection->getAttributes(ServiceMetadata::class);

        if (empty($attributes)) {
            return null;
        }

        /** @var ServiceMetadata $metadata */
        $metadata = $attributes[0]->newInstance();

        return new ServiceMetadataInfo($metadata->label, $metadata->description);
    }
}
