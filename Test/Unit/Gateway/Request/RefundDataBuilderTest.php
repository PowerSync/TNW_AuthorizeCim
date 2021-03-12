<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\RefundDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class RefundDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var RefundDataBuilder
     */
    private $builder;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->builder = new RefundDataBuilder(new SubjectReader());
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
                'ref_trans_id' => 'xsd7n',
                'payment' => [
                    'credit_card' => [
                        'card_number' => '0001',
                        'expiration_date' => 'XXXX',
                    ]
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => 10.4580
        ];

        $this->payment->method('getParentTransactionId')
            ->willReturn('xsd7n');

        $this->payment->method('getCcLast4')
            ->willReturn('0001');

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
