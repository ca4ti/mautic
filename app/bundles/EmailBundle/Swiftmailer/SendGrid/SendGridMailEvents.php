<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Swiftmailer\SendGrid;

final class SendGridMailEvents
{
    /**
     * The mautic.email.swiftmailer.sendgrid_api.get_mail_message event is
     * dispatched by the mautic.transport.sendgrid_api.facade service
     * immediately after obtaining a SendGrid\Mail instance from the
     * mautic.transport.sendgrid_api.message service.
     *
     * The event listener receives an instance of
     * \Mautic\EmailBundle\Swiftmailer\SendGrid\Event\GetMailMessageEvent.
     *
     * @var string
     */
    const GET_MAIL_MESSAGE = 'mautic.email.swiftmailer.sendgrid_api.get_mail_message';

    /**
     * The mautic.email.swiftmailer.sendgrid_api.mail_send_response event is
     * dispatched by the mautic.transport.sendgrid_api.facade service
     * after verifying the response from sending a SendGrid\Mail object via
     * the mautic.transport.sendgrid_api.sendgrid_wrapper service.
     *
     * The event listener receives an instance of
     * \Mautic\EmailBundle\Swiftmailer\SendGrid\Event\MailSendResponseEvent.
     *
     * @var string
     */
    const MAIL_SEND_RESPONSE = 'mautic.email.swiftmailer.sendgrid_api.mail_send_response';
}
