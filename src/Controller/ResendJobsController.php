<?php

declare(strict_types=1);

namespace App\Controller;

use App\Data\ValueObject\ServiceMetadataInfo;
use App\Entity\ResendJob;
use App\Service\LogModifier\LogModifierInterface;
use App\Service\LogParser\LogTypeParser\LogTypeParserInterface;
use App\Service\LogProvider\Source\LogsProviderSourceInterface;
use App\Repository\ResendJobRepository;
use App\Constant\Enum\ResendJobStatus;
use App\Service\Util\ResendLogViewDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResendJobsController extends AbstractController
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
        ResendLogViewDataProvider $resendLogViewDataProvider,
        private readonly ResendJobRepository $resendJobRepository,
    ) {
        $this->sourceLabels = array_map(
            /** @param array{metadata: ServiceMetadataInfo} $source */
            static fn (array $source): string => $source['metadata']->getLabel(),
            $resendLogViewDataProvider->buildSources($sourcesLocator),
        );

        $this->parserLabels = array_map(
            static fn (ServiceMetadataInfo $metadata): string => $metadata->getLabel(),
            $resendLogViewDataProvider->buildParsers($parsersLocator),
        );

        $this->modifierLabels = array_map(
            static fn (ServiceMetadataInfo $metadata): string => $metadata->getLabel(),
            $resendLogViewDataProvider->buildModifiers($modifiersLocator),
        );
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

    #[Route('/resend-jobs/{id}/cancel', name: 'resend_jobs.cancel', methods: [Request::METHOD_POST])]
    public function cancel(ResendJob $job, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($job->getStatus() === ResendJobStatus::CANCELLED) {
            return $this->json(['status' => $job->getStatusValue()]);
        }

        if ($job->getStatus() === ResendJobStatus::COMPLETED || $job->getStatus() === ResendJobStatus::FAILED) {
            return $this->json(['status' => $job->getStatusValue()]);
        }

        $job->setStatus(ResendJobStatus::CANCELLED)
            ->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
