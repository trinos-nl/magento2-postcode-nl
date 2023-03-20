<?php

namespace Trinos\PostcodeNL\Model\Form\EntityField\EavEntityAddress;

use Hyva\Checkout\Magewire\Checkout\AddressView\MagewireAddressFormInterface as FormInterface;
use Hyva\Checkout\Model\Form\EntityField\EavAttributeField;
use Magento\Store\Model\ScopeInterface;

class HousenumberAdditionAttributeField extends EavAttributeField
{
    public function getWrapperClasses(array $combineWith = []): array
    {
        $combineWith = array_diff($combineWith, ['col-span-12']);
        return parent::getWrapperClasses(['col-span-6'] + $combineWith);
    }

    public function canRender(): bool
    {
        return $this->scopeConfig->getValue('postcodenl_api/advanced_config/explode_address', ScopeInterface::SCOPE_STORE) ?? false;
    }

    public function getAttributes(): array
    {
        $this->addAttribute("getPostcodeInformation()", '@change.prevent');
        $this->addAttribute('address-line3', 'autocomplete');
        $this->addAttribute('street[2]', 'name');
        $this->addAttribute(
            FormInterface::ADDRESS_PROPERTY . '.street.2',
            'wire:model.defer'
        );
        return parent::getAttributes();
    }
}
