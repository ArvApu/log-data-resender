<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constant\Enum\ContentType;
use App\Constant\Enum\DateRangePreset;
use App\Constant\Enum\LogSource;
use App\Constant\Enum\ResendJobStatus;
use App\Data\DataTransferObject\ResendLogsPayload;
use App\Entity\ResendJob;
use App\Messenger\Message\ResendLogsMessage;
use App\Service\DataDog\DataDogFilterNormalizer;
use App\Service\LogModifier\LogModifierInterface;
use App\Service\LogParser\LogTypeParser\LogTypeParserInterface;
use App\Service\LogProvider\Source\LogsProviderSourceInterface;
use App\Service\Util\ResendLogViewDataProvider;
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

final class ResendLogsController extends AbstractController
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
        ResendLogViewDataProvider $resendLogViewDataProvider,
    ) {
        $this->modifiers = $resendLogViewDataProvider->buildModifiers($modifiersLocator);
        $this->sources = $resendLogViewDataProvider->buildSources($sourcesLocator);
        $this->parsers = $resendLogViewDataProvider->buildParsers($parsersLocator);
    }

    #[Route('/resend-logs', name: 'resend_logs.view', methods: [Request::METHOD_GET])]
    public function viewForm(): Response
    {
        return $this->renderFormPage();
    }

    #[Route('/resend-logs', name: 'resend_logs.run', methods: [Request::METHOD_POST])]
    public function resendLogs(
        EntityManagerInterface $entityManager,
        ResendJobStorage $storage,
        MessageBusInterface $bus,
        DataDogFilterNormalizer $dataDogFilterNormalizer,
        #[MapRequestPayload]
        ResendLogsPayload $payload,
        #[MapUploadedFile([
            new Assert\File(
                maxSize: '3M',
                mimeTypes: [ContentType::JSON->value],
            ),
        ])]
        ?UploadedFile $file = null,
    ): Response {
        $sourceInfo = $this->sources[$payload->source] ?? null;
        if ($sourceInfo === null) {
            return $this->renderFormPage('Unknown source selected.');
        }

        if ($sourceInfo['is_file'] && $file === null) {
            return $this->renderFormPage('File source requires a file upload.');
        }

        try {
            $filter = $this->resolveFilter($payload, $dataDogFilterNormalizer);
        } catch (\InvalidArgumentException $exception) {
            return $this->renderFormPage($exception->getMessage());
        }

        $job = new ResendJob()
            ->setStatus(ResendJobStatus::QUEUED)
            ->setSource($payload->source)
            ->setParser($payload->parser)
            ->setModifiers($payload->modifiers)
            ->setFilter($filter);

        $entityManager->persist($job);
        $entityManager->flush();

        if ($file !== null) {
            $job->setFilterFilePath($storage->storeUploadedFilter($file, $job->getId()));
        }

        $job->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $bus->dispatch(new ResendLogsMessage($job->getId()));

        return $this->redirectToRoute('resend_jobs.view', [
            'job' => $job->getId(),
        ]);
    }

    private function renderFormPage(?string $error = null): Response
    {
        return $this->render('resend_logs.html.twig', [
            'sources' => $this->sources,
            'parsers' => $this->parsers,
            'modifiers' => $this->modifiers,
            'date_presets' => DateRangePreset::cases(),
            'error' => $error,
        ]);
    }

    private function resolveFilter(
        ResendLogsPayload $payload,
        DataDogFilterNormalizer $dataDogFilterNormalizer,
    ): string {
        if ($payload->source !== LogSource::DATADOG->value) {
            return $payload->filter;
        }

        return $dataDogFilterNormalizer->normalize(
            $payload->filter,
            $payload->datePreset,
            $payload->dateFrom,
            $payload->dateTo,
        );
    }
}
