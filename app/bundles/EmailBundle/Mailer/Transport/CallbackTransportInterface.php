<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Mailer\Transport;

use Symfony\Component\HttpFoundation\Request;

interface CallbackTransportInterface
{
    /**
     * Processes the response.
     */
    public function processCallbackRequest(Request $request): void;
}
