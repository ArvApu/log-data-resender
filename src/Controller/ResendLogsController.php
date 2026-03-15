<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constant\Enum\ContentTypeEnum;
use App\Constant\Enum\ResendJobStatus;
use App\Data\DataTransferObject\ResendLogsPayload;
use App\Entity\ResendJob;
use App\Messenger\Message\ResendLogsMessage;
use App\Service\LogModifier\LogModifierInterface;
use App\Service\LogParser\LogTypeParser\LogTypeParserInterface;
use App\Service\LogProvider\Source\LogsProviderFileSourceInterface;
use App\Service\LogProvider\Source\LogsProviderSourceInterface;
use App\Service\ServiceMetadataProvider\ServiceMetadataProvider;
use App\Storage\ResendJobStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

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
            'error' => null,
        ]);
    }

    #[Route('/resend-logs', name: 'resend_logs.run', methods: [Request::METHOD_POST])]
    public function resendLogs(
        EntityManagerInterface $entityManager,
        ResendJobStorage $storage,
        MessageBusInterface $bus,
        #[MapRequestPayload]
        ResendLogsPayload $payload,
        #[MapUploadedFile([
            new Assert\File(
                maxSize: '3M',
                mimeTypes: [
                    ContentTypeEnum::JSON->value,
                ],
            ),
        ])]
        ?UploadedFile $file = null,
    ): Response {
        $sourceInfo = $this->sources[$payload->source] ?? null;

        if ($sourceInfo === null) {
            return $this->render('resend_logs.html.twig', [
                'sources' => $this->sources,
                'parsers' => $this->parsers,
                'modifiers' => $this->modifiers,
                'error' => 'Unknown source selected.',
            ]);
        }

        if ($sourceInfo->is_file && $file === null) {
            return $this->render('resend_logs.html.twig', [
                'sources' => $this->sources,
                'parsers' => $this->parsers,
                'modifiers' => $this->modifiers,
                'error' => 'File source requires a file upload.',
            ]);
        }

        $job = new ResendJob()
            ->setStatus(ResendJobStatus::QUEUED)
            ->setSource($payload->source)
            ->setParser($payload->parser)
            ->setModifiers($payload->modifiers)
            ->setFilter($payload->filter);

        $entityManager->persist($job);
        $entityManager->flush();

        if ($file !== null) {
            $filterPath = $storage->storeUploadedFilter($file, $job->getId());
            $job->setFilterFilePath($filterPath);
        }

        $job->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $bus->dispatch(new ResendLogsMessage($job->getId()));

        return $this->redirectToRoute('resend_jobs.view', [
            'job' => $job->getId(),
        ]);
    }
}
