<?php

namespace Trinos\PostcodeNL\Model\Form\EntityField\EavEntityAddress;

use Hyva\Checkout\Magewire\Checkout\AddressView\MagewireAddressFormInterface as FormInterface;
use Hyva\Checkout\Model\Form\EntityField\EavAttributeField;
use Magento\Store\Model\ScopeInterface;

class HousenumberAttributeField extends EavAttributeField
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
        $this->addAttribute('address-line2', 'autocomplete');
        $this->addAttribute('street[1]', 'name');
        $this->addAttribute('numeric', 'inputmode');
        $this->addAttribute(
            FormInterface::ADDRESS_PROPERTY . '.street.1',
            'wire:model.defer'
        );
        $this->addAttribute('[0-9]+', 'pattern');
        $this->addAttribute(__('This field must be numeric.'), 'title');
        return parent::getAttributes();
    }

    public function getFrontendInput(): string
    {
        return 'text';
    }

    public function isAutoSave(): bool
    {
        return false;
    }
}
