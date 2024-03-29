<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Request\CaptureDataBuilder;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptureDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCcTransId'])
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->builder = new CaptureDataBuilder(new SubjectReader());
    }

    public function testBuildWithException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage("No authorization transaction to proceed capture.");

        $buildSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->method('getCcTransId')
            ->willReturn('');

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $transactionId = 'b3b99d';

        $expected = [
            'transaction_request' => [
                'ref_trans_id' => $transactionId
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment->method('getCcTransId')
            ->willReturn($transactionId);

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
