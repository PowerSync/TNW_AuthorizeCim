<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ProfileUpdateCommand - update customer profile command
 */
class ProfileUpdateCommand implements CommandInterface
{
    const CUSTOMER_UPDATE = 'customer_update';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProfileUpdateCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        LoggerInterface $logger
    ) {
        $this->commandPool = $commandPool;
        $this->logger = $logger;
    }

    /**
     * @param array $commandSubject
     * @return \Magento\Payment\Gateway\Command\ResultInterface|void|null
     */
    public function execute(array $commandSubject)
    {
        try {
            $this->commandPool->get(self::CUSTOMER_UPDATE)->execute($commandSubject);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
