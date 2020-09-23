<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\MonitoredEmail\Processor;

use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\EmailBundle\Entity\StatRepository;
use Mautic\EmailBundle\MonitoredEmail\Exception\BounceNotFound;
use Mautic\EmailBundle\MonitoredEmail\Message;
use Mautic\EmailBundle\MonitoredEmail\Processor\Bounce\BouncedEmail;
use Mautic\EmailBundle\MonitoredEmail\Processor\Bounce\Parser;
use Mautic\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Mautic\EmailBundle\Swiftmailer\Transport\BounceProcessorInterface;
use Mautic\LeadBundle\Model\DoNotContact;
use Mautic\LeadBundle\Model\LeadModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Mautic\LeadBundle\Entity\DoNotContact as DNC;

class Bounce implements ProcessorInterface
{
    /**
     * @var \Swift_Transport
     */
    protected $transport;

    /**
     * @var ContactFinder
     */
    protected $contactFinder;

    /**
     * @var StatRepository
     */
    protected $statRepository;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $bouncerAddress;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var DoNotContact
     */
    protected $doNotContact;

    /**
     * Bounce constructor.
     */
    public function __construct(
        \Swift_Transport $transport,
        ContactFinder $contactFinder,
        StatRepository $statRepository,
        LeadModel $leadModel,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        DoNotContact $doNotContact
    ) {
        $this->transport      = $transport;
        $this->contactFinder  = $contactFinder;
        $this->statRepository = $statRepository;
        $this->leadModel      = $leadModel;
        $this->translator     = $translator;
        $this->logger         = $logger;
        $this->doNotContact   = $doNotContact;
    }

    /**
     * @return bool
     */
    public function process(Message $message)
    {
        $this->message = $message;
        $bounce        = false;

        $this->logger->debug('MONITORED EMAIL: Processing message ID '.$this->message->id.' for a bounce');

        // Does the transport have special handling such as Amazon SNS?
        if ($this->transport instanceof BounceProcessorInterface) {
            try {
                $bounce = $this->transport->processBounce($this->message);
            } catch (BounceNotFound $exception) {
                // Attempt to parse a bounce the standard way
            }
        }

        if (!$bounce) {
            try {
                $bounce = (new Parser($this->message))->parse();
            } catch (BounceNotFound $exception) {
                return false;
            }
        }

        $searchResult = $this->contactFinder->find($bounce->getContactEmail(), $bounce->getBounceAddress());
        if (!$contacts = $searchResult->getContacts()) {
            // No contacts found so bail
            return false;
        }

        $stat    = $searchResult->getStat();
        $channel = 'email';
        $retryCount = 0;
        $globalRetryCount = 0;

        if ($stat) {
            // Update stat entry
            $this->updateStat($stat, $bounce);

            if ($stat->getEmail() instanceof Email) {
                // We know the email ID so set it to append to the the DNC record
                $channel = ['email' => $stat->getEmail()->getId()];
                $retryCount = $stat->getRetryCount();
                $globalRetryCount=$this->contactFinder->findSoftbyLead($stat->getLead()->getId());
            }
        }

        $comments = $this->translator->trans('mautic.email.bounce.reason.'.$bounce->getRuleCategory());
        if ($fail = $bounce->isFinal() || $retryCount >= 5 || $globalRetryCount >= 5) {
            foreach ($contacts as $contact) {
                $this->doNotContact->addDncForContact($contact->getId(), $channel, DNC::BOUNCED, $comments);
            }
        }

        return true;
    }

    protected function updateStat(Stat $stat, BouncedEmail $bouncedEmail)
    {
        $dtHelper    = new DateTimeHelper();
        $openDetails = $stat->getOpenDetails();

        if (!isset($openDetails['bounces'])) {
            $openDetails['bounces'] = [];
        }

        $openDetails['bounces'][] = [
            'datetime' => $dtHelper->toUtcString(),
            'reason'   => $bouncedEmail->getRuleCategory(),
            'code'     => $bouncedEmail->getRuleNumber(),
            'type'     => $bouncedEmail->getType(),
        ];

        $stat->setOpenDetails($openDetails);

        $retryCount = $stat->getRetryCount();
        ++$retryCount;
        $stat->setRetryCount($retryCount);

        if ($fail = $bouncedEmail->isFinal() || $retryCount >= 5) {
            $stat->setIsFailed(true);
        } else {
            $stat->setIsSoft(true);
        }

        $this->statRepository->saveEntity($stat);
    }
}
