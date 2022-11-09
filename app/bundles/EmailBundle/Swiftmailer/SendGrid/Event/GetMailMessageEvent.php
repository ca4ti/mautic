<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Swiftmailer\SendGrid\Event;

use SendGrid\Mail;
use Swift_Mime_SimpleMessage;
use Symfony\Component\EventDispatcher\Event;

class GetMailMessageEvent extends Event
{
    /** @var Mail */
    private $mail;

    /** @var Swift_Mime_SimpleMessage */
    private $message;

    /**
     * Constructor.
     */
    public function __construct(Mail $mail, Swift_Mime_SimpleMessage $message)
    {
        $this->mail    = $mail;
        $this->message = $message;
    }

    /**
     * Get mail.
     *
     * @return Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Get message.
     *
     * @return Swift_Mime_SimpleMessage
     */
    public function getMessage()
    {
        return $this->message;
    }
}
