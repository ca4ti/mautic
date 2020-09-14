<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Tests\Field;

use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Field\SchemaDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class SchemaDefinitionTest extends TestCase
{
    /**
     * @dataProvider dataGetSchemaDefinition
     */
    public function testGetSchemaDefinition(string $alias, string $type, bool $isUnique, ?int $length, array $expected): void
    {
        Assert::assertSame($expected, SchemaDefinition::getSchemaDefinition($alias, $type, $isUnique, $length));
        Assert::assertSame($expected, (new SchemaDefinition())->getSchemaDefinitionNonStatic($alias, $type, $isUnique, $length));
    }

    public function dataGetSchemaDefinition(): iterable
    {
        foreach (['datetime', 'date', 'time', 'boolean'] as $type) {
            yield [
                'some',
                $type,
                false,
                80,
                [
                    'name'    => 'some',
                    'type'    => $type,
                    'options' => ['notnull' => false],
                ],
            ];
        }

        yield [
            'some',
            'number',
            false,
            70,
            [
                'name'    => 'some',
                'type'    => 'float',
                'options' => [
                    'notnull' => false,
                ],
            ],
        ];

        foreach (['timezone', 'locale', 'country', 'email', 'lookup', 'select', 'region', 'tel', 'text'] as $type) {
            foreach ([75, null] as $length) {
                yield [
                    'some',
                    $type,
                    false,
                    $length,
                    [
                        'name'    => 'some',
                        'type'    => 'string',
                        'options' => [
                            'notnull' => false,
                            'length'  => $length,
                        ],
                    ],
                ];
            }
        }

        foreach (['description' => 'text', 'articleDescription' => 'string', 'descriptionOfArticle' => 'text'] as $alias => $type) {
            yield [
                $alias,
                'text',
                false,
                null,
                [
                    'name'    => $alias,
                    'type'    => $type,
                    'options' => [
                        'notnull' => false,
                        'length'  => null,
                    ],
                ],
            ];
        }

        foreach (['multiselect', 'html', 'unknown'] as $type) {
            yield [
                'some',
                $type,
                false,
                80,
                [
                    'name'    => 'some',
                    'type'    => 'text',
                    'options' => ['notnull' => false],
                ],
            ];
        }

        $allTypes = [
            'datetime',
            'date',
            'time',
            'boolean',
            'number',
            'timezone',
            'locale',
            'country',
            'email',
            'lookup',
            'select',
            'region',
            'tel',
            'text',
            'multiselect',
            'html',
            'unknown',
        ];

        foreach ($allTypes as $type) {
            yield [
                'some',
                $type,
                true,
                80,
                [
                    'name'    => 'some',
                    'type'    => 'string',
                    'options' => ['notnull' => false],
                ],
            ];
        }
    }

    /**
     * @dataProvider dataGetFieldCharLengthLimit
     */
    public function testGetFieldCharLengthLimit(array $schemaDefinition, ?int $expected): void
    {
        Assert::assertSame($expected, SchemaDefinition::getFieldCharLengthLimit($schemaDefinition));
    }

    public function dataGetFieldCharLengthLimit(): iterable
    {
        yield [
            [
                'type'    => 'string',
                'options' => [
                    'length' => 50,
                ],
            ],
            50,
        ];

        yield [
            [
                'type'    => 'string',
            ],
            ClassMetadataBuilder::MAX_VARCHAR_INDEXED_LENGTH,
        ];

        yield [
            [
                'type'    => 'text',
                'options' => [
                    'length' => 60,
                ],
            ],
            60,
        ];

        foreach (['text', 'datetime', 'date', 'time', 'boolean', 'float'] as $type) {
            yield [
                [
                    'type'    => $type,
                ],
                null,
            ];
        }
    }
}
