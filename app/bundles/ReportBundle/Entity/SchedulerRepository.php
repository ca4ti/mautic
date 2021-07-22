<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ReportBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Mautic\ReportBundle\Scheduler\Option\ExportOption;

/**
 * SchedulerRepository.
 */
class SchedulerRepository extends EntityRepository
{
    /**
     * @deprecated not used anymore
     *
     * @return Report|null
     */
    public function getSchedulerByReport(Report $report)
    {
        return $this->findOneBy(['report' => $report]);
    }

    /**
     * @return array|Scheduler[]
     */
    public function getScheduledReportsForExport(ExportOption $exportOption)
    {
        $qb = $this->createQueryBuilder('scheduler');
        $qb->addSelect('report')
            ->leftJoin('scheduler.report', 'report');

        $date = new \DateTime();
        $qb->andWhere('scheduler.scheduleDate <= :scheduleDate')
            ->setParameter('scheduleDate', $date);

        if ($exportOption->getReportId()) {
            $qb->andWhere('report.id = :id')
                ->setParameter('id', $exportOption->getReportId());
        }

        return $qb->getQuery()->getResult();
    }
}
