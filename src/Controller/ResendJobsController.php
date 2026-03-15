<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ResendJob;
use App\Service\LogModifier\LogModifierInterface;
use App\Service\LogParser\LogTypeParser\LogTypeParserInterface;
use App\Service\LogProvider\Source\LogsProviderSourceInterface;
use App\Repository\ResendJobRepository;
use App\Service\ServiceMetadataProvider\ServiceMetadataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ResendJobsController extends AbstractController
{
    private readonly array $modifierLabels;
    private readonly array $parserLabels;
    private readonly array $sourceLabels;

    public function __construct(
        #[AutowireLocator(LogsProviderSourceInterface::class)]
        ServiceLocator $sourcesLocator,
        #[AutowireLocator(LogTypeParserInterface::class)]
        ServiceLocator $parsersLocator,
        #[AutowireIterator(LogModifierInterface::class)]
        iterable $modifiersLocator,
        ServiceMetadataProvider $serviceMetadataProvider,
        private readonly ResendJobRepository $resendJobRepository,
    ) {
        $modifiers = [];
        foreach ($modifiersLocator as $modifier) {
            $modifiers[$modifier->getId()] = $serviceMetadataProvider->getAttributeMetadata($modifier)->getLabel();
        }

        $sources = [];
        foreach ($sourcesLocator->getIterator() as $id => $source) {
            $sources[$id] = $serviceMetadataProvider->getAttributeMetadata($source)->getLabel();
        }

        $parsers = [];
        foreach ($parsersLocator->getIterator() as $id => $parser) {
            $parsers[$id] = $serviceMetadataProvider->getAttributeMetadata($parser)->getLabel();
        }

        $this->modifierLabels = $modifiers;
        $this->sourceLabels = $sources;
        $this->parserLabels = $parsers;
    }

    #[Route('/resend-jobs', name: 'resend_jobs.view', methods: [Request::METHOD_GET])]
    public function view(Request $request): Response
    {
        $highlightJobId = $request->query->getInt('job');

        return $this->render('resend_jobs.html.twig', [
            'jobs' => $this->resendJobRepository->findLatest(),
            'highlight_job_id' => $highlightJobId > 0 ? $highlightJobId : null,
            'source_labels' => $this->sourceLabels,
            'parser_labels' => $this->parserLabels,
            'modifier_labels' => $this->modifierLabels,
        ]);
    }

    #[Route('/resend-jobs/status', name: 'resend_jobs.status', methods: [Request::METHOD_GET])]
    public function status(): JsonResponse
    {
        $jobs = $this->resendJobRepository->findLatest();

        // TODO: DTO maybe?
        $payload = array_map(
            static fn (ResendJob $job): array => [
                'id' => $job->getId(),
                'status' => $job->getStatusValue(),
                'source' => $job->getSource(),
                'parser' => $job->getParser(),
                'modifiers' => $job->getModifiers(),
                'filter' => $job->getFilter(),
                'filterFilePath' => $job->getFilterFilePath(),
                'processedCount' => $job->getProcessedCount(),
                'totalCount' => $job->getTotalCount(),
                'counts' => $job->getCounts(),
                'errorMessage' => $job->getErrorMessage(),
                'createdAt' => $job->getCreatedAt()->format(DATE_ATOM),
                'startedAt' => $job->getStartedAt()?->format(DATE_ATOM),
                'finishedAt' => $job->getFinishedAt()?->format(DATE_ATOM),
                'updatedAt' => $job->getUpdatedAt()->format(DATE_ATOM),
            ],
            $jobs,
        );

        return $this->json([
            'jobs' => $payload,
        ]);
    }
}
