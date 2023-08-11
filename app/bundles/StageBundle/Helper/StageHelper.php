<?php

namespace Mautic\StageBundle\Helper;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\StageBundle\Entity\Stage;
use Mautic\StageBundle\Model\StageModel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StageHelper
{
    public function __construct(
        private LeadModel $leadModel,
        private StageModel $stageModel,
        private LoggerInterface $logger,
        private TranslatorInterface $translator
    )
    {
    }

    public function changeStage(Lead $lead, Stage $stage, string $origin): void
    {
        // Get the current stage and validate it vs the new one
        $currentStage = $lead->getStage();
        if ($currentStage) {
            if ($currentStage->getId() === $stage->getId()) {
                throw new \UnexpectedValueException($this->translator->trans('mautic.stage.campaign.event.already_in_stage'));
            }

            if ($currentStage->getWeight() > $stage->getWeight()) {
                throw new \UnexpectedValueException($this->translator->trans('mautic.stage.campaign.event.stage_invalid'));
            }
        }

        $this->leadModel->addToStage($lead, $stage, $origin);
        $this->leadModel->saveEntity($lead);

        $this->logger->info(
            sprintf(
                'StageBundle: Lead %s changed stage from %s (%s) to %s (%s) by %s',
                $lead->getId(),
                $currentStage ? $currentStage->getName() : null,
                $currentStage ? $currentStage->getId() : null,
                $stage->getName(),
                $stage->getId(),
                $origin
            )
        );
    }

    /**
     * Takes a stage ID and returns a Stage object.
     *
     * @return Stage|null
     */
    public function getStage(int $stageId)
    {
        return $this->stageModel->getEntity($stageId);
    }
}
