<?php

namespace App\Service\LogModifier\Modifier;

use App\Service\LogModifier\LogModifierInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AddHostLogModifier implements LogModifierInterface
{
    public function __construct(
        #[Autowire(param: 'app.webservices.host')]
        private string $host
    ) {
    }

    public function getId(): string
    {
        return 'add_host';
    }

    /**
     * @throws \Exception
     */
    public function modify(array $log): array
    {
       $log['_host'] = $this->host;

       return $log;
    }
}

