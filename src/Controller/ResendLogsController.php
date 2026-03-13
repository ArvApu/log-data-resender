<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constant\Enum\ContentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;
use App\Data\DataTransferObject\ResendLogsPayload;
use App\Service\LogModifier\LogModifierInterface;
use App\Service\LogParser\LogTypeParser\LogTypeParserInterface;
use App\Service\LogProvider\Source\LogsProviderFileSourceInterface;
use App\Service\LogProvider\Source\LogsProviderSourceInterface;
use App\Service\LogResender\LogResender;
use App\Service\ServiceMetadataProvider\ServiceMetadataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;

class ResendLogsController extends AbstractController
{
    private readonly array $modifiers;
    private readonly array $sources;
    private readonly array $parsers;

    public function __construct(
        #[AutowireLocator(LogsProviderSourceInterface::class)]
        ServiceLocator $sourcesLocator,
        #[AutowireLocator(LogTypeParserInterface::class)]
        ServiceLocator $parsersLocator,
        #[AutowireIterator(LogModifierInterface::class)]
        iterable $modifiersLocator,
        ServiceMetadataProvider $serviceMetadataProvider,
    ) {
        $modifiers = [];
        foreach ($modifiersLocator as $modifier) {
            $modifiers[$modifier->getId()] = $serviceMetadataProvider->getAttributeMetadata($modifier);
        }

        $sources = [];
        foreach ($sourcesLocator->getIterator() as $id => $source) {
            $sources[$id] = (object) [
                'is_file' => $source instanceof LogsProviderFileSourceInterface,
                'metadata' => $serviceMetadataProvider->getAttributeMetadata($source)
            ];
        }

        $parsers = [];
        foreach ($parsersLocator->getIterator() as $id => $parser) {
            $parsers[$id] = $serviceMetadataProvider->getAttributeMetadata($parser);
        }

        $this->modifiers = $modifiers;
        $this->sources = $sources;
        $this->parsers = $parsers;
    }

    #[Route('/resend-logs', name: 'resend_logs.view', methods: [Request::METHOD_GET])]
    public function viewForm(): Response {
        return $this->render('resend_logs.html.twig', [
            'sources' => $this->sources,
            'parsers' => $this->parsers,
            'modifiers' => $this->modifiers,
            'result' => null,
            'error' => null,
        ]);
    }

    #[Route('/resend-logs', name: 'resend_logs.run', methods: [Request::METHOD_POST])]
    public function resendLogs(
        #[MapRequestPayload]
        ResendLogsPayload $payload,
        LogResender $logResender,
        #[MapUploadedFile([
            new Assert\File(
                maxSize: '3M',
                mimeTypes: [
                    ContentTypeEnum::JSON->value,
                    ContentTypeEnum::PDF->value,
                ],
            ),
        ])]
        ?UploadedFile $file = null,
    ): Response {
        /*
         * TODO:
         *  Make this as queue job, and have some progress indicator on the frontend if the job is still running,
         *  and show the result once it's done. While sender is running, we could update some kind of row in database
         *  with progress, and UI could fetch this progress with ajax call every X seconds, and show it on the frontend.
         *  This way we can handle large files or filters without hitting request timeout.
         */
        $result = [];

        $filterValue = $file === null ? $payload->filter : $file->getPathname();

        $results = $logResender->resend($payload->source, $filterValue, $payload->parser, $payload->modifiers);

        $result[] = (object) [
            'filter' => $filterValue ?? null,
            'counts' => $results->getCounts(),
        ];

        return $this->render('resend_logs.html.twig', [
            'sources' => $this->sources,
            'parsers' => $this->parsers,
            'modifiers' => $this->modifiers,
            'result' => $result,
            'error' => $results?->getException()?->getMessage(),
        ]);
    }
}
