<?php

declare(strict_types=1);

namespace App\Service\LogProvider;

use App\Service\LogProvider\Source\LogsProviderSourceInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

readonly class LogsProvider
{
    public function __construct(
        #[AutowireLocator(LogsProviderSourceInterface::class)]
        private ServiceLocator $serviceLocator
    ) {
    }

    public function getLogs(string $source, string $filter): iterable
    {
        /** @var LogsProviderSourceInterface $logsProvider */
        $logsProvider = $this->serviceLocator->get($source);

        return $logsProvider->getLogs($filter);
    }
}
