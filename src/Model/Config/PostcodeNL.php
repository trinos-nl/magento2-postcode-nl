<?php

declare(strict_types=1);

namespace Trinos\PostcodeNL\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class PostcodeNL
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/general/api_key',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function isValidApiKey()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/general/api_key_is_valid',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getApiSecret(): string
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/general/api_secret',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
