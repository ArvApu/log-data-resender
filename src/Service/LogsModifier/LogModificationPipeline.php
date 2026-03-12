<?php

namespace App\Service\LogsModifier;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class LogModificationPipeline
{
    private array $enabledModifiers = [];

    public function __construct(
        /** @var LogModifierInterface[] */
        #[AutowireIterator(LogModifierInterface::class)]
        private readonly iterable $modifiers
    ) {
    }

    public function process(array $log): array
    {
        if (empty($this->enabledModifiers)) {
            return $log;
        }

        foreach ($this->modifiers as $modifier) {
            if (in_array($modifier->getId(), $this->enabledModifiers, true)) {
                $log = $modifier->modify($log);
            }
        }

        return $log;
    }

    public function setEnabledModifiers(array $modifierIds): void
    {
        $this->enabledModifiers = $modifierIds;
    }
}

