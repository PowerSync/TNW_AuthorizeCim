<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Test\Unit\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use TNW\AuthorizeCim\Gateway\Command\AuthorizeStrategyCommand;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class AuthorizeStrategyCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CommandPoolInterface|MockObject
     */
    private $commandPool;

    /**
     * @var GatewayCommand|MockObject
     */
    private $command;

    /**
     * @var PaymentDataObject|MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var AuthorizeStrategyCommand
     */
    private $strategyCommand;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->commandPool = $this->getMockBuilder(CommandPoolInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $this->command = $this->getMockBuilder(GatewayCommand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();

        $this->paymentDO = $this->getMockBuilder(PaymentDataObject::class)
            ->onlyMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAdditionalInformation'])
            ->getMock();

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        /** @var \Psr\Log\LoggerInterface|MockObject $logger */
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->strategyCommand = new AuthorizeStrategyCommand(
            $this->commandPool,
            new SubjectReader(),
            $logger
        );
    }

    public function testAuthorizeExecute()
    {
        $commandSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment
            ->method('getAdditionalInformation')
            ->with('is_active_payment_token_enabler')
            ->willReturn(null);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(AuthorizeStrategyCommand::AUTHORIZE)
            ->willReturn($this->command);

        $this->command->method('execute')
            ->with($commandSubject)
            ->willReturn([]);

        $this->strategyCommand->execute($commandSubject);
    }

    public function testCustomerExecute()
    {
        $commandSubject = [
            'payment' => $this->paymentDO
        ];

        $this->payment
            ->method('getAdditionalInformation')
            ->with('is_active_payment_token_enabler')
            ->willReturn(true);

        $this->commandPool->expects(static::at(0))
            ->method('get')
            ->with(AuthorizeStrategyCommand::AUTHORIZE)
            ->willReturn($this->command);

        $this->commandPool->expects(static::at(1))
            ->method('get')
            ->with(AuthorizeStrategyCommand::CUSTOMER)
            ->willReturn($this->command);

        $this->command->method('execute')
            ->with($commandSubject)
            ->willReturn([]);

        $this->strategyCommand->execute($commandSubject);
    }
}
