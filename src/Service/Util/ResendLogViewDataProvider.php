<?php

declare(strict_types=1);

namespace App\Service\Util;

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

    public function buildModifiers(iterable $modifiers): array
    {
        $result = [];

        foreach ($modifiers as $modifier) {
            if (!$modifier instanceof LogModifierInterface) {
                continue;
            }

            $result[$modifier->getId()] = $this->serviceMetadataProvider->getAttributeMetadata($modifier);
        }

        return $result;
    }

    public function buildSources(ServiceLocator $sources): array
    {
        $result = [];

        foreach ($sources->getIterator() as $id => $source) {
            $result[$id] = [
                'is_file' => $source instanceof LogsProviderFileSourceInterface,
                'metadata' => $this->serviceMetadataProvider->getAttributeMetadata($source),
            ];
        }

        return $result;
    }

    public function buildParsers(ServiceLocator $parsers): array
    {
        $result = [];

        foreach ($parsers->getIterator() as $id => $parser) {
            if (!$parser instanceof LogTypeParserInterface) {
                continue;
            }

            $result[$id] = $this->serviceMetadataProvider->getAttributeMetadata($parser);
        }

        return $result;
    }
}
