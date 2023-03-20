<?php

namespace Trinos\PostcodeNL\Model\Form\EntityField\EavEntityAddress;

use Hyva\Checkout\Model\Form\EntityField\EavAttributeField;

class ManualModeAttributeField extends EavAttributeField
{
    public function getFrontendInput(): string
    {
        return 'checkbox';
    }
}
