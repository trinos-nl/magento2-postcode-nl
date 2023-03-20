<?php

namespace Trinos\PostcodeNL\Model\Form\EntityFormModifier;

use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;
use Magento\Quote\Api\Data\AddressInterface;

class WithPostcodecheckModifier implements EntityFormModifierInterface
{

    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        foreach ($form->getFields() as $field) {
            $field->addAttribute($field->getId(), 'x-ref');
            $field->addAttribute('onChange', '@blur');

            if ($field->isRequired()
                && $field->getFrontendInput() === 'text'
                && !isset($field->getAttributes()['pattern'])) {
                $field->addAttribute('.*\S+.*', 'pattern');
                $field->addAttribute(__('This is a required field.'), 'title');
            }
        }

        $street = $form->getField(AddressInterface::KEY_STREET);
        $city = $form->getField(AddressInterface::KEY_CITY);

        $countryId = $form->getField(AddressInterface::KEY_COUNTRY_ID);
        $manualMode = $form->getField('postcodenl_manual_mode');
        if ($countryId->getValue() === 'NL' && !$manualMode->getValue()) {
            $street->addAttribute(true, 'disabled');
            $city->addAttribute(true, 'disabled');
        } else {
            $street->removeAttribute('disabled');
            $city->removeAttribute('disabled');
        }

        return $form;
    }
}
