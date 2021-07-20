<?php
/**
 * Copyright © 2017 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class CustomerUpdateHandler - customer update command handler
 */
class CustomerUpdateHandler implements HandlerInterface
{
    /**
     * @param array $subject
     * @param array $response
     * @return $this|void
     */
    public function handle(array $subject, array $response)
    {
        //TODO: no actual data to handle in response on success
        return $this;
    }
}
