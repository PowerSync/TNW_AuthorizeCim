<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Helper;

use Magento\Payment\Gateway\Helper;

class SubjectReader
{
    /**
     * @param array $subject
     * @return mixed
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);

        if (!isset($response['object']) || !\is_object($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist.');
        }

        return $response['object'];
    }

    /**
     * @param array $subject
     * @return \Magento\Payment\Gateway\Data\PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * @param array $subject
     * @return \net\authorize\api\contract\v1\AnetApiResponseType
     */
    public function readTransaction(array $subject)
    {
        if (!isset($subject['object']) || !\is_object($subject['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        return $subject['object'];
    }

    /**
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function readCustomerData(array $data)
    {
        $result = new \Magento\Framework\DataObject();
        if (is_array($data) && array_key_exists('customer', $data)) {
            $customerObject = $data['customer'];
            $result->setEmail($customerObject->getEmail());
            $result->setCustomerId($customerObject->getId());
            if (method_exists($customerObject, 'getCustomAttribute')
                && $customerObject->getCustomAttribute('customer_profile_id')
            ) {
                $result->setCustomerProfileId($customerObject->getCustomAttribute('customer_profile_id')->getValue());
            }
        } elseif (is_array($data) && array_key_exists('payment', $data)) {
            $paymentDO = $this->readPayment($data);
            $order = $paymentDO->getOrder();
            $billingAddress = $order->getBillingAddress();
            $result->setEmail($billingAddress->getEmail());
            $result->setCustomerId($order->getCustomerId());
        }
        return $result;
    }
}
