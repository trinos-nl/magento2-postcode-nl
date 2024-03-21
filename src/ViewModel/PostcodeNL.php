<?php

namespace Trinos\PostcodeNL\ViewModel;

use Trinos\PostcodeNL\Model\Config\PostcodeNL as PostcodeNLConfig;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\HTTP\Client\Curl;

class PostcodeNL implements ArgumentInterface
{
    public function __construct(
        private readonly PostcodeNLConfig $postcodeNLConfig,
    ) {
    }

    public function getApiKey(): string
    {
        return $this->postcodeNLConfig->getApiKey() ?? '';
    }

    public function getApiSecret(): string
    {
        return $this->postcodeNLConfig->getApiKey() ?? '';
    }
}
