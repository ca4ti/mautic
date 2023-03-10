<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCrmBundle\EventListener;

use Mautic\LeadBundle\Event as Events;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var LeadExport
     */
    private $leadExport;

    public function __construct(IntegrationHelper $integrationHelper, LeadExport $leadExport = null)
    {
        $this->integrationHelper = $integrationHelper;
        $this->leadExport        = $leadExport;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_POST_SAVE      => ['onLeadPostSave', 0],
            LeadEvents::LEAD_PRE_DELETE     => ['onLeadPostDelete', 255],
            LeadEvents::LEAD_COMPANY_CHANGE => ['onLeadCompanyChange', 0],
        ];
    }

    public function onLeadPostSave(Events\LeadEvent $event)
    {
        $lead = $event->getLead();
        if ($lead->isAnonymous()) {
            // Ignore this contact
            return;
        }

        $changes = $lead->getChanges(true);
        if (!empty($changes['dateIdentified'])) {
            $this->leadExport->create($lead);
        } else {
            $this->leadExport->update($lead);
        }
    }

    public function onLeadPostDelete(Events\LeadEvent $event)
    {
        $lead = $event->getLead();
        $this->leadExport->delete($lead);
    }

    public function onLeadCompanyChange(Events\LeadChangeCompanyEvent $event)
    {
        $lead = $event->getLead();
        $this->leadExport->update($lead);
    }
}
