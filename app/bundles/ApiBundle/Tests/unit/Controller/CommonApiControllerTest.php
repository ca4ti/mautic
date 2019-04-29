<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ApiBundle\Tests\Controller;

use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\CoreBundle\Test\AbstractMauticTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommonApiControllerTest.
 *
 * @group Controller
 */
class CommonApiControllerTest extends AbstractMauticTestCase
{
    public function testAddAliasIfNotPresentWithOneColumnWithoutAlias()
    {
        $result = $this->getResultFromProtectedMethod('addAliasIfNotPresent', ['dateAdded', 'f']);

        $this->assertEquals('f.dateAdded', $result);
    }

    public function testAddAliasIfNotPresentWithOneColumnWithAlias()
    {
        $result = $this->getResultFromProtectedMethod('addAliasIfNotPresent', ['f.dateAdded', 'f']);

        $this->assertEquals('f.dateAdded', $result);
    }

    public function testAddAliasIfNotPresentWithTwoColumnsWithAlias()
    {
        $result = $this->getResultFromProtectedMethod('addAliasIfNotPresent', ['f.dateAdded, f.dateModified', 'f']);

        $this->assertEquals('f.dateAdded,f.dateModified', $result);
    }

    public function testAddAliasIfNotPresentWithTwoColumnsWithoutAlias()
    {
        $result = $this->getResultFromProtectedMethod('addAliasIfNotPresent', ['dateAdded, dateModified', 'f']);

        $this->assertEquals('f.dateAdded,f.dateModified', $result);
    }

    public function testGetWhereFromRequestWithNoWhere()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->getResultFromProtectedMethod('getWhereFromRequest', [], $request);

        $this->assertEquals([], $result);
    }

    public function testGetWhereFromRequestWithSomeWhere()
    {
        $where = [
            [
                'col'  => 'id',
                'expr' => 'eq',
                'val'  => 5,
            ],
        ];

        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $request->method('get')
            ->willReturn($where);

        $result = $this->getResultFromProtectedMethod('getWhereFromRequest', [], $request);

        $this->assertEquals($where, $result);
    }

    /**
     * @param $method
     * @param array        $args
     * @param Request|null $request
     *
     * @return mixed
     */
    protected function getResultFromProtectedMethod($method, array $args, Request $request = null)
    {
        $controller = new CommonApiController();

        if ($request) {
            $controller->setRequest($request);
        }

        $controllerReflection = new \ReflectionClass(CommonApiController::class);
        $method               = $controllerReflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($controller, $args);
    }
}
