<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Tests\Entity;

use Mautic\FormBundle\Entity\Field;
use Mautic\FormBundle\Entity\Form;

final class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testShowForConditionalFieldWithNoParent()
    {
        $field = new Field();
        $this->assertTrue($field->showForConditionalField([]));
    }

    public function testShowForConditionalFieldWithParentButNoAlias()
    {
        $parentFieldId = '55';
        $field         = new Field();
        $parentField   = $this->createMock(Field::class);
        $form          = new Form();
        $form->addField(0, $parentField);
        $field->setForm($form);
        $field->setParent($parentFieldId);
        $parentField->method('getId')->willReturn($parentFieldId);

        $this->assertFalse($field->showForConditionalField([]));
    }

    public function testShowForConditionalFieldWithParentAndAliasAndNotInConditionAndBadValue()
    {
        $parentFieldId    = '55';
        $parentFieldAlias = 'field_a';
        $field            = new Field();
        $parentField      = $this->createMock(Field::class);
        $form             = new Form();
        $form->addField(0, $parentField);
        $field->setForm($form);
        $field->setParent($parentFieldId);
        $field->setConditions(['expr' => 'notIn', 'values' => []]);
        $parentField->method('getId')->willReturn($parentFieldId);
        $parentField->method('getAlias')->willReturn($parentFieldAlias);
        $data = [$parentFieldAlias => 'value A'];

        $this->assertTrue($field->showForConditionalField($data));
    }

    public function testShowForConditionalFieldWithParentAndAliasAndNotInConditionAndMatchingValue()
    {
        $parentFieldId    = '55';
        $parentFieldAlias = 'field_a';
        $field            = new Field();
        $parentField      = $this->createMock(Field::class);
        $form             = new Form();
        $form->addField(0, $parentField);
        $field->setForm($form);
        $field->setParent($parentFieldId);
        $field->setConditions(['expr' => 'notIn', 'values' => ['value A']]);
        $parentField->method('getId')->willReturn($parentFieldId);
        $parentField->method('getAlias')->willReturn($parentFieldAlias);
        $data = [$parentFieldAlias => 'value A'];

        $this->assertFalse($field->showForConditionalField($data));
    }

    public function testShowForConditionalFieldWithParentAndAliasAndAnyValue()
    {
        $parentFieldId    = '55';
        $parentFieldAlias = 'field_a';
        $field            = new Field();
        $parentField      = $this->createMock(Field::class);
        $form             = new Form();
        $form->addField(0, $parentField);
        $field->setForm($form);
        $field->setParent($parentFieldId);
        $field->setConditions(['expr' => '', 'any' => true, 'values' => ['value A']]);
        $parentField->method('getId')->willReturn($parentFieldId);
        $parentField->method('getAlias')->willReturn($parentFieldAlias);
        $data = [$parentFieldAlias => 'value A'];

        $this->assertTrue($field->showForConditionalField($data));
    }

    public function testShowForConditionalFieldWithParentAndAliasAndInValueMatches()
    {
        $parentFieldId    = '55';
        $parentFieldAlias = 'field_a';
        $field            = new Field();
        $parentField      = $this->createMock(Field::class);
        $form             = new Form();
        $form->addField(0, $parentField);
        $field->setForm($form);
        $field->setParent($parentFieldId);
        $field->setConditions(['expr' => 'in', 'values' => ['value A']]);
        $parentField->method('getId')->willReturn($parentFieldId);
        $parentField->method('getAlias')->willReturn($parentFieldAlias);
        $data = [$parentFieldAlias => ['value A']];

        $this->assertTrue($field->showForConditionalField($data));
    }

    public function testShowForConditionalFieldWithParentAndAliasAndInValueDoesNotMatch()
    {
        $parentFieldId    = '55';
        $parentFieldAlias = 'field_a';
        $field            = new Field();
        $parentField      = $this->createMock(Field::class);
        $form             = new Form();
        $form->addField(0, $parentField);
        $field->setForm($form);
        $field->setParent($parentFieldId);
        $field->setConditions(['expr' => 'in', 'values' => ['value B']]);
        $parentField->method('getId')->willReturn($parentFieldId);
        $parentField->method('getAlias')->willReturn($parentFieldAlias);
        $data = [$parentFieldAlias => ['value A']];

        $this->assertFalse($field->showForConditionalField($data));
    }
}
