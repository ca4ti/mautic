<?php

namespace MauticPlugin\MauticSocialBundle\EventListener;

use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\FormEvents;
use MauticPlugin\MauticSocialBundle\Form\Type\SocialLoginType;
use MauticPlugin\MauticSocialBundle\Integration\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FormSubscriber implements EventSubscriberInterface
{
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD => ['onFormBuild', 0],
        ];
    }

    public function onFormBuild(FormBuilderEvent $event)
    {
        if (!$this->config->isPublished()) {
            return;
        }
        $action = [
            'label'          => 'mautic.plugin.actions.socialLogin',
            'formType'       => SocialLoginType::class,
            'template'       => '@MauticSocial/Integration/login.html.twig',
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired'    => false,
                'addDefaultValue'  => false,
                'addSaveResult'    => false,
            ],
        ];

        $event->addFormField('plugin.loginSocial', $action);
    }
}
