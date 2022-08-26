<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace TNW\AuthorizeCim\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config as MagentoGatewayConfig;
use Magento\Store\Model\ScopeInterface;
use TNW\AuthorizeCim\Model\Adminhtml\Source\Environment;

/**
 * Config for payment config values
 */
class Config extends MagentoGatewayConfig
{
    /**
     * Is method active field name
     *
     * @var string
     */
    const ACTIVE = 'active';

    /**
     * Is need use CCV field name
     *
     * @var string
     */
    const USE_CCV = 'useccv';

    /**
     * API login id field name
     *
     * @var string
     */
    const LOGIN = 'login';

    /**
     * API transaction key field name
     *
     * @var string
     */
    const TRANSACTION_KEY = 'trans_key';

    /**
     * API client key field name
     *
     * @var string
     */
    const CLIENT_KEY = 'client_key';

    /**
     * Payment mode field name
     *
     * @var string
     */
    const ENVIRONMENT = 'environment';

    /**
     * Currency code field name
     *
     * @var string
     */
    const CURRENCY = 'currency';

    /**
     * Validation mode field name
     *
     * @var string
     */
    const VALIDATION_MODE = 'validation_mode';

    /**
     * JS sdk url
     *
     * @var string
     */
    const SDK_URL = 'sdk_url';

    /**
     * Test JS sdk url
     *
     * @var string
     */
    const SDK_URL_TEST = 'sdk_url_test_mode';

    /**
     * @var string
     */
    const CC_TYPES_MAPPER = 'cctypes_mapper';

    /**
     * @var string
     */
    const CC_TYPES = 'cctypes';

    /**
     * @var string
     */
    const VERIFY_3DSECURE = 'verify_3dsecure';

    /**
     * @var string
     */
    const VERIFY_API_IDENTIFIER = 'verify_api_identifier';

    /**
     * @var string
     */
    const VERIFY_ORG_UNIT_ID = 'verify_org_unit_id';

    /**
     * @var string
     */
    const VERIFY_API_KEY = 'verify_api_key';

    /**
     * @var string
     */
    const VERIFY_SDK_URL = 'verify_sdk_url';

    /**
     * @var string
     */
    const THRESHOLD_AMOUNT = 'threshold_amount';

    /**
     * @var string
     */
    const VALUE_3DSECURE_ALL = 0;

    /**
     * @var string
     */
    const VERIFY_ALLOW_SPECIFIC = 'verify_all_countries';

    /**
     * @var string
     */
    const VERIFY_SPECIFIC = 'verify_specific_countries';

    /**
     * @var string
     */
    const DEBUG = 'debug';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Can method is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null): bool
    {
        return (bool) $this->getValue(self::ACTIVE, $storeId);
    }

    /**
     * Is need enter CVV code (for vault)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCcvEnabled($storeId = null): bool
    {
        return (bool) $this->getValue(self::USE_CCV, $storeId);
    }

    /**
     * Get API login
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getApiLoginId($storeId = null): ?string
    {
        return $this->getValue(self::LOGIN, $storeId);
    }

    /**
     * Get API transaction key
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getTransactionKey($storeId = null): ?string
    {
        return $this->getValue(self::TRANSACTION_KEY, $storeId);
    }

    /**
     * Get API client key
     *
     * @param int|null $storeId
     * @return null|string
     */
    public function getClientKey($storeId = null): ?string
    {
        return $this->getValue(self::CLIENT_KEY, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null): string
    {
        return (string) $this->getValue(self::ENVIRONMENT, $storeId);
    }

    /**
     * Get in what mode is the payment method (test or live modes)
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSandboxMode($storeId = null): bool
    {
        return $this->getEnvironment($storeId) == Environment::ENVIRONMENT_SANDBOX;
    }

    /**
     * Get validation mode
     *
     * @param int|null $storeId
     * @return string
     */
    public function getValidationMode($storeId = null): string
    {
        return (string)$this->getValue(self::VALIDATION_MODE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getSdkUrl($storeId = null): string
    {
        if ($this->isSandboxMode($storeId)) {
            return (string)$this->getValue(self::SDK_URL_TEST, $storeId);
        }

        return (string)$this->getValue(self::SDK_URL, $storeId);
    }

    /**
     * Retrieve available credit card types
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAvailableCardTypes($storeId = null): array
    {
        $ccTypes = $this->getValue(self::CC_TYPES, $storeId);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Retrieve mapper between Magento and Authorize.Net card types
     *
     * @return array
     */
    public function getCcTypesMapper(): array
    {
        $result = json_decode(
            $this->getValue(self::CC_TYPES_MAPPER),
            true
        );

        return is_array($result) ? $result : [];
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isVerify3dSecure($storeId = null)
    {
        return $this->getValue(self::VERIFY_3DSECURE, $storeId);
    }

    /**
     * Gets threshold amount for 3d secure.
     *
     * @param int|null $storeId
     * @return float
     */
    public function getThresholdAmount($storeId = null)
    {
        return (double) $this->getValue(self::THRESHOLD_AMOUNT, $storeId);
    }

    /**
     * Gets list of specific countries for 3d secure.
     *
     * @param int|null $storeId
     * @return array
     */
    public function get3DSecureSpecificCountries($storeId = null): array
    {
        if ((int) $this->getValue(self::VERIFY_ALLOW_SPECIFIC, $storeId) == self::VALUE_3DSECURE_ALL) {
            return [];
        }

        return explode(',', $this->getValue(self::VERIFY_SPECIFIC, $storeId));
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getVerifyApiIdentifier($storeId = null)
    {
        return $this->getValue(self::VERIFY_API_IDENTIFIER, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getVerifyOrgUnitId($storeId = null)
    {
        return $this->getValue(self::VERIFY_ORG_UNIT_ID, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getVerifyApiKey($storeId = null)
    {
        return $this->getValue(self::VERIFY_API_KEY, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getVerifySdkUrl($storeId = null)
    {
        return $this->getValue(self::VERIFY_SDK_URL, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isCIMEnabled($storeId = null)
    {
        return $this->scopeConfig
            ->getValue('payment/tnw_authorize_cim_vault/active', ScopeInterface::SCOPE_STORE, $storeId);
    }
}
