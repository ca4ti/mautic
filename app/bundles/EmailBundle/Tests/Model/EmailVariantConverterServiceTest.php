<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Tests;

use Mautic\CoreBundle\Model\AbTest\VariantConverterService;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Model\AbTest\EmailVariantConverterService;

class EmailVariantConverterServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests if variant is converted properly into a parent one.
     */
    public function testConvertWinnerVariant()
    {
        $converter      = new VariantConverterService();
        $emailConverter = new EmailVariantConverterService($converter);

        $winnerCriteria  = 'email.openrate';
        $sendWinnerDelay = 2;

        $parent = $this->getMockBuilder(Email::class)
        ->setMethods(['getId'])
        ->getMock();
        $parent->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $parent->setVariantSettings([
            'totalWeight'     => '50',
            'winnerCriteria'  => $winnerCriteria,
            'sendWinnerDelay' => $sendWinnerDelay, ]);
        $parent->setIsPublished(true);

        $winner = $this->getMockBuilder(Email::class)
            ->setMethods(['getId'])
            ->getMock();
        $winner->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));
        $winner->setIsPublished(true);

        $parent->addVariantChild($winner);
        $winner->setVariantParent($parent);

        $variant = $this->getMockBuilder(Email::class)
            ->setMethods(['getId'])
            ->getMock();
        $variant->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(3));
        $variant->setIsPublished(true);

        $parent->addVariantChild($variant);
        $variant->setVariantParent($parent);

        $emailConverter->convertWinnerVariant($winner);

        $this->assertEquals($winner, $parent->getVariantParent());
        $this->assertEquals($winner, $variant->getVariantParent());
        $this->assertNull($winner->getVariantParent());

        $this->assertTrue($winner->isPublished());
        $this->assertFalse($parent->isPublished());
        $this->assertFalse($variant->isPublished());

        $winnerVariantSettings = $winner->getVariantSettings();
        $this->assertEquals(100, $winnerVariantSettings['totalWeight']);
        $this->assertEquals($sendWinnerDelay, $winnerVariantSettings['sendWinnerDelay']);
        $this->assertEquals($winnerCriteria, $winnerVariantSettings['winnerCriteria']);
    }

    /**
     * Tests if variants are converted properly if the winner variant is already the parent one.
     */
    public function testConvertAlreadyParentVariant()
    {
        $converter      = new VariantConverterService();
        $emailConverter = new EmailVariantConverterService($converter);

        $winnerCriteria  = 'email.openrate';
        $sendWinnerDelay = 2;

        $winner = $this->getMockBuilder(Email::class)
            ->setMethods(['getId'])
            ->getMock();
        $winner->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $winner->setVariantSettings([
            'totalWeight'     => '50',
            'winnerCriteria'  => 'email.openrate',
            'sendWinnerDelay' => 2, ]);
        $winner->setIsPublished(true);

        $variant = $this->getMockBuilder(Email::class)
            ->setMethods(['getId'])
            ->getMock();
        $variant->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(3));
        $variant->setVariantSettings(['weight' => '25']);
        $variant->setIsPublished(true);

        $winner->addVariantChild($variant);
        $variant->setVariantParent($winner);

        $emailConverter->convertWinnerVariant($winner);

        $this->assertEquals($winner, $variant->getVariantParent());
        $this->assertNull($winner->getVariantParent());

        $this->assertTrue($winner->isPublished());
        $this->assertFalse($variant->isPublished());

        $winnerVariantSettings = $winner->getVariantSettings();
        $this->assertEquals(100, $winnerVariantSettings['totalWeight']);
        $this->assertEquals($sendWinnerDelay, $winnerVariantSettings['sendWinnerDelay']);
        $this->assertEquals($winnerCriteria, $winnerVariantSettings['winnerCriteria']);
    }
}
