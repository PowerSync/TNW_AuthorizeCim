<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Class for build request address data
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var string
     */
    private $requestedDataBlockName;

    /**
     * @var string
     */
    private $addressType;

    /**
     * AddressDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param string $requestedDataBlockName
     * @param string $addressType
     */
    public function __construct(
        SubjectReader $subjectReader,
        $requestedDataBlockName = 'transaction_request',
        $addressType = 'both'
    ) {
        $this->addressType = $addressType;
        $this->requestedDataBlockName = $requestedDataBlockName;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Build address data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $result = [];

        switch ($this->addressType) {
            case 'shipping':
                $this->populateAddress($order->getShippingAddress(), $result);
                break;
            case 'billing':
                $this->populateAddress($order->getBillingAddress(), $result, 'bill_to');
                break;
            default:
                $this->populateAddress($order->getShippingAddress(), $result, 'ship_to');
                $this->populateAddress($order->getBillingAddress(), $result, 'bill_to');
                break;
        }
        return $result;
    }

    /**
     * @param $address
     * @param $result
     * @param string $target
     */
    private function populateAddress($address, &$result, $target = '')
    {
        if ($address) {
            $addressData = [
                'first_name' => $address->getFirstname(),
                'last_name' => $address->getLastname(),
                'company' => $address->getCompany(),
                'address' => $address->getStreetLine1(),
                'city' => $address->getCity(),
                'state' => $address->getRegionCode(),
                'zip' => $address->getPostcode(),
                'country' => $address->getCountryId(),
            ];
            if ($target) {
                $result[$this->requestedDataBlockName][$target] = $addressData;
            } else {
                $result[$this->requestedDataBlockName] = $addressData;
            }
        }
    }
}
