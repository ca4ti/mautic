<?php

namespace Mautic\CoreBundle\Form\Type;

use Mautic\CoreBundle\Helper\ThemeHelperInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class ThemeListType extends AbstractType
{
    public function __construct(
        private ThemeHelperInterface $themeHelper
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices'           => function (Options $options): array {
                    $themes                     = $this->themeHelper->getInstalledThemes($options['feature'], false, true);
                    $themes['mautic_code_mode'] = 'Code Mode';

                    // Hide themes that are marked as hidden
                    foreach ($this->themeHelper->getInstalledThemes($options['feature'], true, true) as $themeKey => $themeInfo) {
                        if (in_array('hide', $themeInfo['config']) && true === $themeInfo['config']['hide']) {
                            unset($themes[$themeKey]);
                        }
                    }

                    return array_flip($themes);
                },
                'expanded'          => false,
                'multiple'          => false,
                'label'             => 'mautic.core.form.theme',
                'label_attr'        => ['class' => 'control-label'],
                'placeholder'       => false,
                'required'          => false,
                'attr'              => [
                    'class' => 'form-control',
                ],
                'feature'           => 'all',
            ]
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
