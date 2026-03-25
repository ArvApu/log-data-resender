<?php

declare(strict_types=1);

namespace App\Service\Util;

use App\Data\ValueObject\ServiceMetadataInfo;
use App\Service\LogModifier\LogModifierInterface;
use App\Service\LogParser\LogTypeParser\LogTypeParserInterface;
use App\Service\LogProvider\Source\LogsProviderFileSourceInterface;
use App\Service\ServiceMetadataProvider\ServiceMetadataProvider;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class ResendLogViewDataProvider
{
    public function __construct(
        private ServiceMetadataProvider $serviceMetadataProvider,
    ) {
    }

    /**
     * @return ServiceMetadataInfo[]
     * @throws \Exception
     */
    public function buildModifiers(iterable $modifiers): array
    {
        $result = [];

        foreach ($modifiers as $modifier) {
            if (!$modifier instanceof LogModifierInterface) {
                continue;
            }

            $metadata = $this->serviceMetadataProvider->getAttributeMetadata($modifier);

            if ($metadata === null) {
                continue;
            }

            $result[$modifier->getId()] = $metadata;
        }

        return $result;
    }

    /**
     * @return array<string, array{is_file: bool, metadata: ServiceMetadataInfo}>
     * @throws \Exception
     */
    public function buildSources(ServiceLocator $sources): array
    {
        $result = [];

        foreach ($sources->getIterator() as $id => $source) {
            $metadata = $this->serviceMetadataProvider->getAttributeMetadata($source);

            if ($metadata === null) {
                continue;
            }

            $result[$id] = [
                'is_file' => $source instanceof LogsProviderFileSourceInterface,
                'metadata' => $metadata,
            ];
        }

        return $result;
    }

    /**
     * @return ServiceMetadataInfo[]
     * @throws \Exception
     */
    public function buildParsers(ServiceLocator $parsers): array
    {
        $result = [];

        foreach ($parsers->getIterator() as $id => $parser) {
            if (!$parser instanceof LogTypeParserInterface) {
                continue;
            }

            $metadata = $this->serviceMetadataProvider->getAttributeMetadata($parser);

            if ($metadata === null) {
                continue;
            }

            $result[$id] = $metadata;
        }

        return $result;
    }
}
