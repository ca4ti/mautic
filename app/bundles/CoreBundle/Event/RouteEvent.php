<?php

namespace Mautic\CoreBundle\Event;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\EventDispatcher\Event;

class RouteEvent extends Event
{
    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var RouteCollection
     */
    protected $collection;

    /**
     * @param string $type
     */
    public function __construct(Loader $loader, protected $type = 'main')
    {
        $this->loader     = $loader;
        $this->collection = new RouteCollection();
    }

    /**
     * @param string $path
     */
    public function addRoutes($path)
    {
        $this->collection->addCollection($this->loader->import($path));
    }

    /**
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
