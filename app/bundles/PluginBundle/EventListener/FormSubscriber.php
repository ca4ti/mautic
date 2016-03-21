<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\FormEvents;

/**
 * Class FormSubscriber
 */
class FormSubscriber extends CommonSubscriber
{

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            FormEvents::FORM_ON_BUILD => array('onFormBuild', 0)
        );
    }

    /**
     * @param FormBuilderEvent $event
     */
    public function onFormBuild (FormBuilderEvent $event)
    {
        $action = array(
            'group'       => 'mautic.plugin.actions',
            'description' => 'mautic.plugin.actions.tooltip',
            'label'       => 'mautic.plugin.actions.push_lead',
            'formType'    => 'integration_list',
            'formTheme'   => 'MauticPluginBundle:FormTheme\Integration',
            'callback'    => array('\\Mautic\\PluginBundle\\Helper\\EventHelper', 'pushLead')
        );

        $event->addSubmitAction('plugin.leadpush', $action);
    }
}