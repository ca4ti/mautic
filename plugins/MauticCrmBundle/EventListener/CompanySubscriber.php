<?php

namespace MauticPlugin\MauticCrmBundle\EventListener;

use Mautic\LeadBundle\Event as Events;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticCrmBundle\Integration\Pipedrive\Export\CompanyExport;
use MauticPlugin\MauticCrmBundle\Integration\PipedriveIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CompanySubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var CompanyExport
     */
    private $companyExport;

    public function __construct(IntegrationHelper $integrationHelper, CompanyExport $companyExport)
    {
        $this->integrationHelper = $integrationHelper;
        $this->companyExport     = $companyExport;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::COMPANY_POST_SAVE  => ['onCompanyPostSave', 0],
            LeadEvents::COMPANY_PRE_DELETE => ['onCompanyPreDelete', 10],
        ];
    }

    /**
     * @param Events\LeadEvent $event
     */
    public function onCompanyPostSave(Events\CompanyEvent $event)
    {
        $company = $event->getCompany();
        if ($company->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }

        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        $operation         = $this->companyExport->getOperation($company);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive($operation)) {
            return;
        }

        $this->companyExport->setIntegration($integrationObject);
        $this->companyExport->pushCompany($company);
    }

    /**
     * @param Events\LeadEvent $event
     */
    public function onCompanyPreDelete(Events\CompanyEvent $event)
    {
        $company = $event->getCompany();
        if ($company->getEventData('pipedrive.webhook')) {
            // Don't export what was just imported
            return;
        }

        /** @var PipedriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        if (false === $integrationObject || !$integrationObject->shouldImportDataToPipedrive('update')) {
            return;
        }

        $this->companyExport->setIntegration($integrationObject);
        $this->companyExport->delete($company);
    }
}
