<?php
/**
 * Copyright © 2021 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Block\Customer\CreditCard;

use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Customer\Model\Session;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;

/**
 * Class Form
 */
class Form extends Template
{
    /**
     * @var Data
     */
    private $paymentHelper;

    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var BackendQuote
     */
    private $sessionQuote;

    /**
     * @var MethodInterface
     */
    private $currentMethod;

    /**
     * @var Session
     */
    private $session;

    /**
     * Form constructor.
     * @param Template\Context $context
     * @param Data $paymentHelper
     * @param PaymentMethodListInterface $paymentMethodList
     * @param CustomerRepositoryInterface $customerRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param BackendQuote $sessionQuote
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $paymentHelper,
        PaymentMethodListInterface $paymentMethodList,
        CustomerRepositoryInterface $customerRepository,
        WebsiteRepositoryInterface $websiteRepository,
        BackendQuote $sessionQuote,
        Session $session,
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->paymentMethodList = $paymentMethodList;
        $this->customerRepository = $customerRepository;
        $this->websiteRepository = $websiteRepository;
        $this->sessionQuote = $sessionQuote;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $method = $this->getActiveMethods()[ConfigProvider::CODE] ?? null;
        if ($method) {
            $this->setCurrentMethod($method);
        }
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getMethodFormBlockHtml()
    {
        $result = '';
        $methods = $this->getActiveMethods();
        $method = $methods[ConfigProvider::CODE] ?? null;
        if ($method) {
            try {
                $block = $this->paymentHelper->getMethodFormBlock($method, $this->getLayout());
                $methodFormTemplate = $this->getData('method_form_template');
                if ($methodFormTemplate) {
                    $block->setTemplate($methodFormTemplate);
                }
                $result = $block->toHtml();
            } catch (LocalizedException $exception) {
                $result = '';
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getActiveMethods()
    {
        $methods = $this->getMethods();
        foreach ($this->getMethods() as $key => $methodInstance) {
            if ($methodInstance->isAvailable()) {
                $methodInstance->setInfoInstance($this->getQuote()->getPayment());
            } else {
                unset($methods[$key]);
            }
        }
        return $methods;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $methods = [];
            $storeId = (int) $this->getStoreId();
            foreach ($this->paymentMethodList->getList($storeId) as $method) {
                if (in_array($method->getCode(), [ConfigProvider::CODE], true)) {
                    try {
                        $methods[$method->getCode()] = $this->paymentHelper->getMethodInstance($method->getCode());
                    } catch (LocalizedException $e) {
                        continue;
                    }
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }

    /**
     * @return MethodInterface
     */
    public function getCurrentMethod()
    {
        return $this->currentMethod;
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('customer/paymentProfiles/save', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'customer/paymentProfiles/index'
        );
    }


    /**
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param MethodInterface $method
     */
    private function setCurrentMethod(MethodInterface $method)
    {
        $this->currentMethod = $method;
    }

    /**
     * @return int|null
     */
    private function getStoreId()
    {
        $customerId = $this->getCustomerId();
        try {
            $customer = $this->customerRepository->getById($customerId);
            $storeId = $customer->getStoreId();
            if (!$storeId) {
                $websiteId = $customer->getWebsiteId();
                $website = $this->websiteRepository->getById($websiteId);
                $store = $website->getDefaultStore();
                if ($store instanceof Store) {
                    $storeId = $store->getId();
                }
            }
            return $storeId;
        } catch (NoSuchEntityException | LocalizedException $exception) {
            return null;
        }
    }

    /**
     * @return int
     */
    private function getCustomerId()
    {
        return (int) $this->session->getCustomerId();
    }

    /**
     * @return Quote
     */
    private function getQuote()
    {
        return $this->sessionQuote->getQuote();
    }
}
