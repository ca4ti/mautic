<?php

declare(strict_types=1);

namespace Mautic\CoreBundle\Tests\Unit\Form\Type;

use Mautic\CoreBundle\Form\Type\ContentPreviewSettingsType;
use Mautic\CoreBundle\Form\Type\LookupType;
use Mautic\EmailBundle\Entity\Email;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ContentPreviewSettingsTypeTest extends TestCase
{
    /**
     * @var ContentPreviewSettingsType|MockObject
     */
    private $form;

    /**
     * @var MockObject|TranslatorInterface
     */
    private $translator;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->form       = new ContentPreviewSettingsType($this->translator);

        parent::setUp();
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects(self::once())
            ->method('setDefaults')
            ->with(
                [
                    'type'         => null,
                    'objectId'     => null,
                    'translations' => null,
                    'variants'     => null,
                ]
            );

        $this->form->configureOptions($resolver);
    }

    public function testGetBlockPrefix(): void
    {
        self::assertSame('content_preview_settings', $this->form->getBlockPrefix());
    }

    public function testBuildFormWithTranslationAndVariantFieldNotAvailable(): void
    {
        $objectId = 1;
        $options  = [
            'objectId'      => $objectId,
            'translations'  => [
                'children' => [],
            ],
            'variants'     => [
                'children' => [],
            ],
        ];

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('add')
            ->withConsecutive(
                [
                    'contact',
                    LookupType::class,
                    [
                        'attr' => [
                            'class'                   => 'form-control',
                            'onChange'                => '',
                            'data-callback'           => 'activateContactLookupField',
                            'data-toggle'             => 'field-lookup',
                            'data-lookup-callback'    => 'updateContactLookupListFilter',
                            'data-chosen-lookup'      => 'lead:contactList',
                            'placeholder'             => $this->translator->trans(
                                'mautic.lead.list.form.startTyping'
                            ),
                            'data-no-record-message'=> $this->translator->trans(
                                'mautic.core.form.nomatches'
                            ),
                        ],
                    ],
                ]
            );

        $this->form->buildForm($builder, $options);
    }

    public function testBuildFormWithTranslationAndVariantFieldAvailable(): void
    {
        $parentEmailId = 1;
        $parentEmail   = new Email();
        $parentEmail->setId($parentEmailId);
        $parentEmail->setName('Parent');
        $parentEmail->setLanguage('en');

        $translationEmail1 = new Email();
        $translationEmail1->setId(2);
        $translationEmail1->setName('Translation 1');
        $translationEmail1->setLanguage('cs_CZ');

        $translationEmail2 = new Email();
        $translationEmail2->setId(3);
        $translationEmail2->setName('Translation 2');
        $translationEmail2->setLanguage('dz_BT');

        $expectedTranslationChoices = [
            'Parent - English - ID 1'                  => 1,
            'Translation 1 - Czech (Czechia) - ID 2'   => 2,
            'Translation 2 - Dzongkha (Bhutan) - ID 3' => 3,
        ];

        $variantEmail1 = new Email();
        $variantEmail1->setId(2);
        $variantEmail1->setName('Variant 1');

        $variantEmail2 = new Email();
        $variantEmail2->setId(3);
        $variantEmail2->setName('Variant 2');

        $expectedVariantChoices = [
            'Parent - ID 1'    => 1,
            'Variant 1 - ID 2' => 2,
            'Variant 2 - ID 3' => 3,
        ];

        $formOptions = [
            'objectId'      => $parentEmailId,
            'translations'  => [
                'parent'   => $parentEmail,
                'children' => [
                    $translationEmail1,
                    $translationEmail2,
                ],
            ],
            'variants' => [
                'parent'   => $parentEmail,
                'children' => [
                    $variantEmail1,
                    $variantEmail2,
                ],
            ],
        ];

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->expects(self::exactly(3))
            ->method('add')
            ->withConsecutive(
                [
                    'translation',
                    ChoiceType::class,
                    [
                        'choices' => $expectedTranslationChoices,
                        'attr'    => [
                            'onChange' => "Mautic.contentPreviewUrlGenerator.regenerateUrl({$parentEmailId}, this)",
                        ],
                        'placeholder'  => $this->translator->trans('mautic.core.form.chooseone'),
                        'data'         => (string) $parentEmailId,
                    ],
                ],
                [
                    'variant',
                    ChoiceType::class,
                    [
                        'choices' => $expectedVariantChoices,
                        'attr'    => [
                            'onChange' => "Mautic.contentPreviewUrlGenerator.regenerateUrl({$parentEmailId}, this)",
                        ],
                        'placeholder'  => $this->translator->trans('mautic.core.form.chooseone'),
                        'data'         => (string) $parentEmailId,
                    ],
                ],
                [
                    'contact',
                    LookupType::class,
                    [
                        'attr' => [
                            'class'                   => 'form-control',
                            'onChange'                => '',
                            'data-callback'           => 'activateContactLookupField',
                            'data-toggle'             => 'field-lookup',
                            'data-lookup-callback'    => 'updateContactLookupListFilter',
                            'data-chosen-lookup'      => 'lead:contactList',
                            'placeholder'             => $this->translator->trans(
                                'mautic.lead.list.form.startTyping'
                            ),
                            'data-no-record-message'=> $this->translator->trans(
                                'mautic.core.form.nomatches'
                            ),
                        ],
                    ],
                ]
            );

        $this->form->buildForm($formBuilder, $formOptions);
    }
}
