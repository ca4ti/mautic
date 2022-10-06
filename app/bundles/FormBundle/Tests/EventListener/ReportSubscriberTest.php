<?php

namespace Mautic\FormBundle\Tests\EventListener;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\ChannelBundle\Helper\ChannelListHelper;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\FormBundle\Entity\SubmissionRepository;
use Mautic\FormBundle\EventListener\ReportSubscriber;
use Mautic\LeadBundle\Model\CompanyReportData;
use Mautic\ReportBundle\Entity\Report;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\Event\ReportGraphEvent;
use PHPUnit\Framework\TestCase;

class ReportSubscriberTest extends TestCase
{
    /**
     * @var SubmissionRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $submissionRepository;

    /**
     * @var CompanyReportData|\PHPUnit\Framework\MockObject\MockObject
     */
    private $companyReportData;

    /**
     * @var ReportSubscriber
     */
    private $subscriber;

    /**
     * @var \Mautic\LeadBundle\Segment\Query\QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $queryBuilder;

    /**
     * @var ChannelListHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $channelListHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->companyReportData    = $this->createMock(CompanyReportData::class);
        $this->submissionRepository = $this->createMock(SubmissionRepository::class);
        $this->subscriber           = new ReportSubscriber($this->companyReportData, $this->submissionRepository);
        $this->queryBuilder         = $this->createMock(\Mautic\LeadBundle\Segment\Query\QueryBuilder::class);
        $this->channelListHelper    = $this->createMock(ChannelListHelper::class);
    }

    public function testOnReportBuilderAddsFormAndFormSubmissionReports(): void
    {
        $mockEvent = $this->getMockBuilder(ReportBuilderEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'checkContext',
                'addGraph',
                'getStandardColumns',
                'getCategoryColumns',
                'getCampaignByChannelColumns',
                'getLeadColumns',
                'getIpColumn',
                'addTable',
            ])
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('getStandardColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getCategoryColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getCampaignByChannelColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getLeadColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getIpColumn')
            ->willReturn([]);

        $mockEvent->expects($this->exactly(2))
            ->method('checkContext')
            ->willReturn(true);

        $setTables = [];
        $setGraphs = [];

        $mockEvent->expects($this->exactly(2))
            ->method('addTable')
            ->willReturnCallback(function () use (&$setTables) {
                $args = func_get_args();

                $setTables[] = $args;
            });

        $mockEvent->expects($this->exactly(3))
            ->method('addGraph')
            ->willReturnCallback(function () use (&$setGraphs) {
                $args = func_get_args();

                $setGraphs[] = $args;
            });

        $this->companyReportData->expects($this->once())
            ->method('getCompanyData')
            ->with()
            ->willReturn([]);

        $this->subscriber->onReportBuilder($mockEvent);

        $this->assertCount(2, $setTables);
        $this->assertCount(3, $setGraphs);
    }

    public function testOnReportGenerateFormsContext(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockEvent        = $this->getMockBuilder(ReportGeneratorEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getContext',
                'getQueryBuilder',
                'addCategoryLeftJoin',
                'setQueryBuilder',
            ])
            ->getMock();

        $mockQueryBuilder->expects($this->once())
            ->method('from')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getContext')
            ->willReturn('forms');

        $this->subscriber->onReportGenerate($mockEvent);
    }

    public function testOnReportGenerateFormSubmissionContext(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockEvent        = $this->getMockBuilder(ReportGeneratorEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getContext',
                'getQueryBuilder',
                'addCategoryLeftJoin',
                'addIpAddressLeftJoin',
                'addLeadLeftJoin',
                'addCampaignByChannelJoin',
                'applyDateFilters',
                'setQueryBuilder',
                'getReport',
            ])
            ->getMock();

        $mockQueryBuilder->expects($this->once())
            ->method('from')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects($this->exactly(2))
            ->method('leftJoin')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getContext')
            ->willReturn('form.submissions');

        $mockEvent->expects($this->once())
            ->method('getReport')
            ->willReturn(new Report());

        $this->subscriber->onReportGenerate($mockEvent);
    }

    public function testOnReportGraphGenerateBadContextWillReturn(): void
    {
        $mockEvent = $this->createMock(ReportGraphEvent::class);

        $mockEvent->expects($this->once())
            ->method('checkContext')
            ->willReturn(false);

        $mockEvent->expects($this->never())
            ->method('getRequestedGraphs');

        $this->subscriber->onReportGraphGenerate($mockEvent);
    }

    public function testOnReportGraphGenerate(): void
    {
        $mockEvent        = $this->createMock(ReportGraphEvent::class);
        $mockTrans        = $this->createMock(Translator::class);
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockChartQuery   = $this->createMock(ChartQuery::class);

        $mockTrans->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        $mockEvent->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockChartQuery->expects($this->any())
            ->method('loadAndBuildTimeData')
            ->willReturn(['a', 'b', 'c']);

        $mockChartQuery->expects($this->any())
            ->method('fetchCount')
            ->willReturn(2);

        $mockChartQuery->expects($this->any())
            ->method('fetchCountDateDiff')
            ->willReturn(2);

        $graphOptions = [
            'chartQuery' => $mockChartQuery,
            'translator' => $mockTrans,
            'dateFrom'   => new \DateTime(),
            'dateTo'     => new \DateTime(),
        ];

        $mockEvent->expects($this->once())
            ->method('checkContext')
            ->willReturn(true);

        $mockEvent->expects($this->any())
            ->method('getOptions')
            ->willReturn($graphOptions);

        $mockEvent->expects($this->once())
            ->method('getRequestedGraphs')
            ->willReturn(
                [
                    'mautic.form.graph.line.submissions',
                    'mautic.form.table.top.referrers',
                    'mautic.form.table.most.submitted',
                ]
            );

        $this->submissionRepository->expects($this->once())
            ->method('getTopReferrers')
            ->willReturn(['a', 'b', 'c']);

        $this->submissionRepository->expects($this->once())
            ->method('getMostSubmitted')
            ->willReturn(['a', 'b', 'c']);

        $this->subscriber->onReportGraphGenerate($mockEvent);
    }

    public function testGroupByDefaultConfigured(): void
    {
        $report             = new Report();
        $report->setSource(ReportSubscriber::CONTEXT_FORM_SUBMISSION);
        $event              = new ReportGeneratorEvent($report, [], $this->queryBuilder, $this->channelListHelper);
        $subscriber         = new ReportSubscriber($this->companyReportData, $this->submissionRepository);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $this->queryBuilder->method('leftJoin')->willReturn($this->queryBuilder);
        $this->assertFalse($event->hasGroupBy());

        $subscriber->onReportGenerate($event);
    }
}
