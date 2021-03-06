<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_Pdfgenerator
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\Pdfgenerator\Test\Unit\Helper;

use Ecomteck\Pdfgenerator\Helper\Data as DataHelper;
use Ecomteck\Pdfgenerator\Helper\Data;
use Ecomteck\Pdfgenerator\Model\Pdfgenerator;
use Ecomteck\Pdfgenerator\Model\ResourceModel\Pdfgenerator\CollectionFactory as PdfGeneratorCollectionFactory;
use Ecomteck\Pdfgenerator\Model\ResourceModel\Pdfgenerator\Collection as PdfGeneratorCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Mpdf\Mpdf;

/**
 * Class Test
 * @package Ecomteck\Pdfgenerator\Test\Unit\Helper
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigInterface;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;
    /**
     * @var PdfGeneratorCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pdfGeneratorCollectionFactory;

    /**
     * @var PdfGeneratorCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pdfGeneratorCollection;

    /**
     * @var DataHelper
     */
    private $subject;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->setMethods(['getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMockForAbstractClass();

        $this->context
            ->expects($this->atLeastOnce())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfigInterface));

        $this->pdfGeneratorCollectionFactory = $this->getMockBuilder(PdfGeneratorCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->pdfGeneratorCollection = $this->getMockBuilder(PdfGeneratorCollection::class)
            ->setMethods(['getSize', 'addStoreFilter', 'addFieldToFilter', 'getLastItem'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->pdfGeneratorCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->pdfGeneratorCollection));

        $this->subject = new DataHelper(
            $this->context,
            $this->pdfGeneratorCollectionFactory
        );
    }

    public function testIsNotEnable()
    {
        if (!class_exists(Mpdf::class)) {
            $this->fail('The class mMPDF must be available');
        }

        $this->pdfGeneratorCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));

        $this->assertFalse($this->subject->isEnable());
    }

    public function testIsEnable()
    {
        if (!class_exists(Mpdf::class)) {
            $this->fail('The class mMPDF must be available');
        }

        $this->pdfGeneratorCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));

        $this->scopeConfigInterface->expects($this->once())
            ->method('getValue')->willReturn(true);

        $this->assertTrue($this->subject->isEnable());
    }

    public function testGetTemplateStatus()
    {

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();

        $invoiceMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $pdfGeenratorMock = $this->getMockBuilder(Pdfgenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pdfGeneratorCollection->expects($this->once())
            ->method('addStoreFilter')
            ->willReturn($pdfGeenratorMock);

        $this->pdfGeneratorCollection->expects($this->once())
            ->method('getLastItem')
            ->willReturn($pdfGeenratorMock);

        $this->pdfGeneratorCollection->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->willReturn($pdfGeenratorMock);

        $result = $this->subject->getTemplateStatus($invoiceMock);

        $this->assertInstanceOf(Pdfgenerator::class, $result);
    }

    public function testIsEmail()
    {
        $this->pdfGeneratorCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));

        $scope = $this->scopeConfigInterface;

        $scope->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn(true);

        $this->assertTrue($this->subject->isEmail());
    }
}
