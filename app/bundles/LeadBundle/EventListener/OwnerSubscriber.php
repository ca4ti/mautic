<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\Helper\ArrayHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\SmsBundle\Event\TokensBuildEvent;
use Mautic\SmsBundle\SmsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class OwnerSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $ownerFieldSprintf = '{ownerfield=%s}';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var array
     */
    private $owners;

    /**
     * OwnerSubscriber constructor.
     */
    public function __construct(LeadModel $leadModel, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->leadModel  = $leadModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_BUILD    => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND     => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY  => ['onEmailDisplay', 0],
            SmsEvents::ON_SMS_TOKENS_BUILD => ['onSmsTokensBuild', 0],
            SmsEvents::TOKEN_REPLACEMENT   => ['onSmsTokenReplacement', 0],
        ];
    }

    public function onEmailBuild(EmailBuilderEvent $event)
    {
        $event->addTokens($this->getTokens());
    }

    public function onEmailDisplay(EmailSendEvent $event)
    {
        $this->onEmailGenerate($event);
    }

    public function onEmailGenerate(EmailSendEvent $event)
    {
        $event->addTokens($this->getGeneratedTokens($event));
    }

    public function onSmsTokensBuild(TokensBuildEvent $event): void
    {
        $tokens = array_merge($event->getTokens(), $this->getTokens());
        $event->setTokens($tokens);
    }

    public function onSmsTokenReplacement(TokenReplacementEvent $event): void
    {
        $contact             = $event->getLead()->getProfileFields();
        $contact['owner_id'] = $event->getLead()->getOwner() ? $event->getLead()->getOwner()->getId() : null;
        if (empty($contact['id']) && $event->getEntity()) {
            return;
        }
        $ownerTokens = $this->getOwnerTokens($contact);
        $content     = str_replace(array_keys($ownerTokens), $ownerTokens, $event->getContent());
        $event->setContent($content);
    }

    /**
     * Generates an array of tokens based on the given token.
     *
     * * If contact[owner_id] === 0, then we need fake data
     * * If contact[owner_id] === null, then we should blank out tokens
     * * If contact[owner_id] > 0 AND User exists, then we should fill in tokens
     *
     * @return array
     */
    private function getGeneratedTokens(EmailSendEvent $event)
    {
        if ($event->isInternalSend()) {
            return $this->getFakeTokens();
        }

        $contact = $event->getLead();

        return $this->getOwnerTokens($contact);
    }

    /**
     * Used to replace all owner tokens with emptiness so not to email out tokens.
     *
     * @return array
     */
    private function getEmptyTokens()
    {
        return [
            $this->buildToken('email')       => '',
            $this->buildToken('firstname')   => '',
            $this->buildToken('lastname')    => '',
            $this->buildToken('position')    => '',
            $this->buildToken('signature')   => '',
        ];
    }

    /**
     * Used to replace all owner tokens with emptiness so not to email out tokens.
     *
     * @return array
     */
    private function getFakeTokens()
    {
        return [
            $this->buildToken('email')       => '['.$this->buildLabel('email').']',
            $this->buildToken('firstname')   => '['.$this->buildLabel('firstname').']',
            $this->buildToken('lastname')    => '['.$this->buildLabel('lastname').']',
            $this->buildToken('position')    => '['.$this->buildLabel('position').']',
            $this->buildToken('signature')   => '['.$this->buildLabel('signature').']',
        ];
    }

    /**
     * Creates a token using defined pattern.
     *
     * @param string $field
     *
     * @return string
     */
    private function buildToken($field)
    {
        return sprintf($this->ownerFieldSprintf, $field);
    }

    /**
     * Creates translation ready label Owner Firstname etc.
     *
     * @param string $field
     *
     * @return string
     */
    private function buildLabel($field)
    {
        return sprintf(
            '%s %s',
            $this->translator->trans('mautic.lead.list.filter.owner'),
            $this->translator->trans('mautic.core.'.$field)
        );
    }

    /**
     * @param $ownerId
     *
     * @return array|null
     */
    private function getOwner($ownerId)
    {
        if (!isset($this->owners[$ownerId])) {
            $this->owners[$ownerId] = $this->leadModel->getRepository()->getLeadOwner($ownerId);
        }

        return $this->owners[$ownerId];
    }

    /**
     * @param array<int|string> $contact
     *
     * @return array|string[]
     */
    private function getOwnerTokens($contact): array
    {
        if (empty($contact['owner_id'])) {
            return $this->getEmptyTokens();
        }

        $owner = $this->getOwner($contact['owner_id']);

        if (!$owner) {
            return $this->getEmptyTokens();
        }

        return [
            $this->buildToken('email')     => ArrayHelper::getValue('email', $owner),
            $this->buildToken('firstname') => ArrayHelper::getValue('first_name', $owner),
            $this->buildToken('lastname')  => ArrayHelper::getValue('last_name', $owner),
            $this->buildToken('position')  => ArrayHelper::getValue('position', $owner),
            $this->buildToken('signature') => nl2br(ArrayHelper::getValue('signature', $owner)),
        ];
    }

    /**
     * @return array<string>
     */
    private function getTokens(): array
    {
        return [
            $this->buildToken('email')     => $this->buildLabel('email'),
            $this->buildToken('firstname') => $this->buildLabel('firstname'),
            $this->buildToken('lastname')  => $this->buildLabel('lastname'),
            $this->buildToken('position')  => $this->buildLabel('position'),
            $this->buildToken('signature') => $this->buildLabel('signature'),
        ];
    }
}
