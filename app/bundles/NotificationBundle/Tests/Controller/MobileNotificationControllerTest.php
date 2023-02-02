<?php

declare(strict_types=1);

namespace Mautic\NotificationBundle\Tests\Controller;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NotificationControllerTest extends MauticMysqlTestCase
{
    /**
     * Smoke test to ensure the '/s/mobile_notifications' route loads.
     */
    public function testIndexRouteSuccessfullyLoads(): void
    {
        $this->client->request(Request::METHOD_GET, '/s/mobile_notifications');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Smoke test to ensure the '/s/mobile_notifications/new' route loads.
     */
    public function testNewRouteSuccessfullyLoads(): void
    {
        $this->client->request(Request::METHOD_GET, '/s/mobile_notifications/new');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
