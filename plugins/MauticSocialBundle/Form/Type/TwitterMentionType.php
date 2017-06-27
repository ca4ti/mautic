<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticSocialBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class TwitterMentionType extends TwitterAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('handle', 'text', [
            'label'      => 'mautic.social.monitoring.twitter.handle',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control',
                'tooltip'  => 'mautic.social.monitoring.twitter.handle.tooltip',
                'preaddon' => 'fa fa-at',
            ],
        ]);

        $builder->add('checknames', 'choice', [
            'choices' => [
                '0' => 'No',
                '1' => 'Yes',
            ],
            'label'       => 'mautic.social.monitoring.twitter.checknames',
            'required'    => false,
            'empty_value' => false,
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => [
                'class'   => 'form-control',
                'tooltip' => 'mautic.social.monitoring.twitter.checknames.tooltip', ],
        ]);

        // pull in the parent type's form builder
        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'twitter_handle';
    }
}
