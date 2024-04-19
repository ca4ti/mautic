<?php

namespace Mautic\ReportBundle\Tests\Model;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\ReportBundle\Crate\ReportDataResult;
use Mautic\ReportBundle\Entity\Report;
use Mautic\ReportBundle\Entity\Scheduler;
use Mautic\ReportBundle\Model\CsvExporter;
use Mautic\ReportBundle\Model\ExcelExporter;
use Mautic\ReportBundle\Model\ExportHandler;
use Mautic\ReportBundle\Model\ReportExportOptions;
use Mautic\ReportBundle\Model\ReportFileWriter;
use Mautic\ReportBundle\Tests\Fixtures;

class ReportFileWriterTest extends \PHPUnit\Framework\TestCase
{
    public function testWriteReportDataAsXlsx(): void
    {
        $excelExporter = $this->getMockBuilder(ExcelExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $csvExporter = $this->getMockBuilder(CsvExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exportHandler = $this->getMockBuilder(ExportHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = 'Handler';

        $report    = new Report();
        $report->setScheduleFormat('xlsx');
        $report->setName('test');

        $scheduler = new Scheduler($report, new \DateTime());

        $reportDataResult = new ReportDataResult(Fixtures::getValidReportResult());

        $coreParametersHelper = $this->getMockBuilder(CoreParametersHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $coreParametersHelper->expects($this->once())
            ->method('get')
            ->with('report_export_batch_size')
            ->willReturn(3);

        $reportExportOptions = new ReportExportOptions($coreParametersHelper);

        $exportHandler->expects($this->once())
            ->method('getPath')
            ->willReturn('tmp/test.xlsx');

        $excelExporter->expects($this->once())
            ->method('export')
            ->with($reportDataResult, (new \DateTime())->format('Y-m-d').'_test', 'tmp/test.xlsx')
            ->willReturn($handler);

        $reportFileWriter = new ReportFileWriter($csvExporter, $excelExporter, $exportHandler);

        $reportFileWriter->writeReportData($scheduler, $reportDataResult, $reportExportOptions);
    }

    public function testWriteReportDataAsCsv(): void
    {
        $excelExporter = $this->getMockBuilder(ExcelExporter::class)
      ->disableOriginalConstructor()
      ->getMock();

        $csvExporter = $this->getMockBuilder(CsvExporter::class)
      ->disableOriginalConstructor()
      ->getMock();

        $exportHandler = $this->getMockBuilder(ExportHandler::class)
      ->disableOriginalConstructor()
      ->getMock();

        $handler = 'Handler';

        $report    = new Report();
        $report->setScheduleFormat('csv');

        $scheduler = new Scheduler($report, new \DateTime());

        $reportDataResult = new ReportDataResult(Fixtures::getValidReportResult());

        $coreParametersHelper = $this->getMockBuilder(CoreParametersHelper::class)
      ->disableOriginalConstructor()
      ->getMock();

        $coreParametersHelper->expects($this->once())
      ->method('get')
      ->with('report_export_batch_size')
      ->willReturn(3);

        $reportExportOptions = new ReportExportOptions($coreParametersHelper);

        $exportHandler->expects($this->once())
      ->method('getHandler')
      ->willReturn($handler);

        $csvExporter->expects($this->once())
      ->method('export')
      ->with($reportDataResult, $handler, 1)
      ->willReturn($handler);

        $exportHandler->expects($this->once())
      ->method('closeHandler')
      ->willReturn($handler);

        $reportFileWriter = new ReportFileWriter($csvExporter, $excelExporter, $exportHandler);

        $reportFileWriter->writeReportData($scheduler, $reportDataResult, $reportExportOptions);
    }

    public function testClear(): void
    {
        $excelExporter = $this->getMockBuilder(ExcelExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $csvExporter = $this->getMockBuilder(CsvExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exportHandler = $this->getMockBuilder(ExportHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $report    = new Report();
        $scheduler = new Scheduler($report, new \DateTime());

        $exportHandler->expects($this->once())
            ->method('removeFile');

        $reportFileWriter = new ReportFileWriter($csvExporter, $excelExporter, $exportHandler);

        $reportFileWriter->clear($scheduler);
    }

    public function testGetFilePath(): void
    {
        $excelExporter = $this->getMockBuilder(ExcelExporter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $csvExporter = $this->getMockBuilder(CsvExporter::class)
          ->disableOriginalConstructor()
          ->getMock();

        $exportHandler = $this->getMockBuilder(ExportHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $report    = new Report();
        $report->setName('my report');
        $report->setScheduleFormat('xlsx');
        $date      = new \DateTime();
        $scheduler = new Scheduler($report, $date);

        $exportHandler->expects($this->once())
            ->method('getPath')
            ->willReturn("/tmp/{$date->format('Y-m-d')}_my-report.csv");

        $reportFileWriter = new ReportFileWriter($csvExporter, $excelExporter, $exportHandler);

        $reportFileWriter->getFilePath($scheduler);
    }
}
