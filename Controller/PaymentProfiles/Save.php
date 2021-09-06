<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Controller\PaymentProfiles;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Payment;
use TNW\AuthorizeCim\Model\Customer\PaymentProfileManagementInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class Save - add payment card controller
 */
class Save implements AccountInterface, HttpPostActionInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressDataFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var CartInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var PaymentProfileManagementInterface
     */
    private $paymentProfileManagement;

    /**
     * @var QuoteAddressInterfaceFactory
     */
    private $quoteAddressFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * Save constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param FormFactory $formFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param RegionFactory $regionFactory
     * @param CartInterfaceFactory $quoteFactory
     * @param PaymentInterfaceFactory $paymentFactory
     * @param PaymentProfileManagementInterface $paymentProfileManagement
     * @param QuoteAddressInterfaceFactory $quoteAddressFactory
     * @param Session $session
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param UrlInterface $urlBuilder
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        FormFactory $formFactory,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        DataObjectHelper $dataObjectHelper,
        RegionFactory $regionFactory,
        CartInterfaceFactory $quoteFactory,
        PaymentInterfaceFactory $paymentFactory,
        PaymentProfileManagementInterface $paymentProfileManagement,
        QuoteAddressInterfaceFactory $quoteAddressFactory,
        Session $session,
        RequestInterface $request,
        ResultFactory $resultFactory,
        UrlInterface $urlBuilder,
        MessageManagerInterface $messageManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->formFactory = $formFactory;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->regionFactory = $regionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->paymentFactory = $paymentFactory;
        $this->paymentProfileManagement = $paymentProfileManagement;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->session = $session;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->urlBuilder = $urlBuilder;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $response = [
            'success' => false
        ];

        try {
            $customerId = $this->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);

            $addressForm = $this->formFactory->create(
                'customer_address',
                'customer_address_edit',
                []
            );
            $request = $addressForm->prepareRequest($this->request->getParam('billing'));
            $addressData = $addressForm->extractData($request);
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                throw new LocalizedException(__(implode(' ', $addressErrors)));
            }
            $attributeValues = $addressForm->compactData($addressData);
            $this->updateRegionData($attributeValues);

            $address = $this->addressDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $address,
                array_merge($attributeValues),
                AddressInterface::class
            );
            $address->setCustomerId($customer->getId());

            /** @var QuoteAddress $billingAddress */
            $billingAddress = $this->quoteAddressFactory->create();
            $billingAddress->importCustomerAddressData($address);

            $cardData = $this->request->getParam('payment');
            $cardData['method'] = $this->request->getParam('method');
            if (isset($cardData['cc_number'])) {
                $cardData['cc_last4'] = substr($cardData['cc_number'], -4);
            }

            /** @var Quote $quote */
            $quote = $this->quoteFactory->create();
            $quote->assignCustomerWithAddressChange($customer, $billingAddress);

            /** @var Payment $payment */
            $payment = $this->paymentFactory->create();
            $payment->setQuote($quote);
            $payment->importData($cardData);

            if ($this->paymentProfileManagement->save($payment, $customer)) {
                $this->addressRepository->save($address);
            }

            $response['success'] = true;
        } catch (Exception $exception) {
            $response['message'] = __($exception->getMessage());
        }

        if ($response['success'] !== true) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            return $resultJson->setData($response);
        }

        if ($this->request->isAjax()) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $response['url'] = $this->urlBuilder->getUrl('*/*');
            $this->messageManager->addSuccessMessage(__('Payment Data saved successfully'));
            return $resultJson->setData($response);
        }

        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('index');
        return $resultForward;
    }

    /**
     * Update region data
     *
     * @param array $attributeValues
     * @return void
     */
    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        $regionData = [
            RegionInterface::REGION_ID => !empty($attributeValues['region_id']) ? $attributeValues['region_id'] : null,
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null,
        ];

        $region = $this->regionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $region,
            $regionData,
            RegionInterface::class
        );
        $attributeValues['region'] = $region;
    }

    /**
     * @return int
     */
    private function getCustomerId()
    {
        return (int) $this->session->getCustomerId();
    }
}
