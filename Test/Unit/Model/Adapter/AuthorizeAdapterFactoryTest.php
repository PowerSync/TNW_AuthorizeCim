<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Test\Unit\Model\Adapter;

use Magento\Framework\ObjectManagerInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapter;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class AuthorizeAdapterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var AuthorizeAdapterFactory
     */
    private $adapterFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getApiLoginId', 'getTransactionKey', 'isSandboxMode'])
            ->getMock();

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->adapterFactory = new AuthorizeAdapterFactory(
            $this->objectManager,
            $this->config
        );
    }

    /**
     * @covers \TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory::create()
     */
    public function testCreate()
    {
        $expected = $this->createMock(AuthorizeAdapter::class);

        $this->objectManager
            ->method('create')
            ->with(
                AuthorizeAdapter::class,
                [
                    'apiLoginId' => 'api_login_id',
                    'transactionKey' => 'transaction_key',
                    'sandboxMode' => true,
                ]
            )
            ->willReturn($expected);

        $storeId = 5;
        $this->config
            ->method('getApiLoginId')
            ->with($storeId)
            ->willReturn('api_login_id');

        $this->config
            ->method('getTransactionKey')
            ->with($storeId)
            ->willReturn('transaction_key');

        $this->config
            ->method('isSandboxMode')
            ->with($storeId)
            ->willReturn(true);

        self::assertEquals($expected, $this->adapterFactory->create($storeId));
    }
}
