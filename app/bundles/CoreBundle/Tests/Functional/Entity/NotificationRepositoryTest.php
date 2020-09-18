<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Tests\Functional\Entity;

use DateTime;
use Mautic\CoreBundle\Entity\Notification;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\UserBundle\Entity\User;
use PHPUnit\Framework\Assert;

class NotificationRepositoryTest extends MauticMysqlTestCase
{
    public function testIsNotificationDuplicate(): void
    {
        $this->createNotification(2, 'dup1', new DateTime('-1 day'));
        $this->createNotification(1, 'dup2', new DateTime('-1 day'));
        $this->em->flush();

        $this->assertDuplicate(true, 2, 'dup1', new DateTime('-1 day'));
        $this->assertDuplicate(true, 2, 'dup1', new DateTime('-25 hour'));
        $this->assertDuplicate(false, 2, 'dup1', new DateTime('-12 hour'));
        $this->assertDuplicate(true, 1, 'dup2', new DateTime('-1 day'));
        $this->assertDuplicate(false, 1, 'dup1', new DateTime('-1 day'));
    }

    private function assertDuplicate(bool $expectedIsDuplicate, int $userId, string $deduplicate, DateTime $from): void
    {
        $isDuplicate = $this->em->getRepository(Notification::class)
            ->isDuplicate($userId, md5($deduplicate), $from);

        Assert::assertSame($expectedIsDuplicate, $isDuplicate);
    }

    private function createNotification(int $userId, string $deduplicate, DateTime $datetime): Notification
    {
        $notification = new Notification();
        $notification->setType('notice');
        $notification->setMessage('Some message');
        $notification->setUser($this->em->getReference(User::class, $userId));
        $notification->setDateAdded($datetime);
        $notification->setDeduplicate(md5($deduplicate));
        $this->em->persist($notification);

        return $notification;
    }
}
