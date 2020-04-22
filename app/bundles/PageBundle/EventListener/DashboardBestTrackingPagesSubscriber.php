<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\EventListener;

use Mautic\DashboardBundle\Event\WidgetDetailEvent;
use Mautic\DashboardBundle\EventListener\DashboardSubscriber as MainDashboardSubscriber;
use Mautic\PageBundle\Form\Type\DashboardBestTrackingPagesType;
use Mautic\PageBundle\Model\PageModel;

class DashboardBestTrackingPagesSubscriber extends MainDashboardSubscriber
{
    /**
     * Define the name of the bundle/category of the widget(s).
     *
     * @var string
     */
    protected $bundle = 'page';

    /**
     * Define the widget(s).
     *
     * @var string
     */
    protected $types = [
        'best.tracking.pages' => [
            'formAlias' => DashboardBestTrackingPagesType::class,
        ],
    ];

    /**
     * Define permissions to see those widgets.
     *
     * @var array
     */
    protected $permissions = [
        'page:pages:viewown',
        'page:pages:viewother',
    ];

    /**
     * @var PageModel
     */
    protected $pageModel;

    /**
     * DashboardSubscriber constructor.
     */
    public function __construct(PageModel $pageModel)
    {
        $this->pageModel = $pageModel;
    }

    /**
     * Set a widget detail when needed.
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event)
    {
        $this->checkPermissions($event);
        $canViewOthers = $event->hasPermission('page:pages:viewother');

        if ('best.tracking.pages' == $event->getType()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();

            if (!$event->isCached()) {
                $items = [];
                $pages = $this->pageModel->getPopularTrackedPages($widget->getLimitCalcByWeight(), $params['dateFrom'], $params['dateTo'], $params, $canViewOthers);
                // Build table rows with links
                if ($pages) {
                    foreach ($pages as $page) {
                        $row     = [
                            [
                                'value'     => $page['url_title'],
                                'type'      => 'link',
                                'external'  => true,
                                'link'      => $page['url'],
                            ],
                            [
                                'value' => $page['hits'],
                            ],
                        ];
                        $items[] = $row;
                    }
                }
                $event->setTemplateData([
                    'headItems' => [
                        'mautic.dashboard.label.title',
                        'mautic.dashboard.label.hits',
                    ],
                    'bodyItems' => $items,
                ]);
            }

            $event->setTemplate('MauticCoreBundle:Helper:table.html.php');
            $event->stopPropagation();
        }
    }
}
