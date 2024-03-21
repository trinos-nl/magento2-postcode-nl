<?php

namespace Trinos\PostcodeNL\Model\System\Message;

use Experius\Postcode\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Store\Model\ScopeInterface;
use Trinos\PostcodeNL\Model\Config\PostcodeNL as PostcodeNLConfig;

class LicenceCheck implements MessageInterface
{
    const MESSAGE_IDENTITY = 'postcodenl_system_message';

    public function __construct(
        protected PostcodeNLConfig $postcodeNLConfig
    ) {
    }

    public function getIdentity(): string
    {
        return self::MESSAGE_IDENTITY;
    }

    public function isDisplayed(): bool
    {
        $keyIsValid = $this->postcodeNLConfig->isValidApiKey();
        return !($keyIsValid == 'yes');
    }

    public function getText(): string
    {
        return __('Your Postcode.nl API licence is invalid');
    }

    public function getSeverity(): int
    {
        return self::SEVERITY_MAJOR;
    }
}
