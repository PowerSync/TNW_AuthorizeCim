<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\PaymentDataBuilder;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataBuilder
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
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->order = $this->createMock(OrderAdapterInterface::class);

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->builder = new PaymentDataBuilder(new SubjectReader());
    }

    public function testBuildReadPaymentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $buildSubject = [];

        $this->builder->build($buildSubject);
    }

    public function testBuildReadAmountException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => null
        ];

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $expected = [
            'transaction_request' => [
                'amount' => '10.46',
                'currency_code' => 'USD',
                'po_number' => '000000101',
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => 10.4580
        ];

        $this->order->method('getOrderIncrementId')
            ->willReturn('000000101');

        $this->order->method('getCurrencyCode')
            ->willReturn('USD');

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
