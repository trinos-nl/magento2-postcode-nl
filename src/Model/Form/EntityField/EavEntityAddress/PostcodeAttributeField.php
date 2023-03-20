<?php

namespace Trinos\PostcodeNL\Model\Form\EntityField\EavEntityAddress;

use Hyva\Checkout\Model\Form\EntityField\EavEntityAddress\PostcodeAttributeField as CorePostcodeAttributeField;

class PostcodeAttributeField extends CorePostcodeAttributeField
{
    public function getAttributes(): array
    {
        $this->addAttribute("getPostcodeInformation()", '@change.prevent');
        return parent::getAttributes();
    }

    public function isAutoSave(): bool
    {
        return false;
    }
}
