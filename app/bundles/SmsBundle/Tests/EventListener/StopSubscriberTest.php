<?php

namespace Mautic\SmsBundle\Tests\EventListener;

use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\DoNotContact as DoNotContactModel;
use Mautic\SmsBundle\Event\ReplyEvent;
use Mautic\SmsBundle\EventListener\StopSubscriber;

class StopSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoNotContact
     */
    private $doNotContactModel;

    protected function setUp(): void
    {
        $this->doNotContactModel = $this->createMock(DoNotContactModel::class);
    }

    public function testLeadAddedToDNC()
    {
        $lead = new Lead();
        $lead->setId(1);
        $event = new ReplyEvent();
        $event->setMessage('stop');
        $event->setContact($lead);

        $this->doNotContactModel->expects($this->once())
        ->method('addDncForContact')
        ->with(1, 'sms', DoNotContact::UNSUBSCRIBED);

        $this->StopSubscriber()->onReply($event);
    }

    /**
     * @return StopSubscriber
     */
    private function StopSubscriber()
    {
        return new StopSubscriber($this->doNotContactModel);
    }
}
