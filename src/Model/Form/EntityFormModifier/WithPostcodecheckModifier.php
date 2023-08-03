<?php

namespace Trinos\PostcodeNL\Model\Form\EntityFormModifier;

use Hyva\Checkout\Magewire\Checkout\AddressView\MagewireAddressFormInterface;
use Hyva\Checkout\Model\Form\EntityFieldInterface;
use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Trinos\PostcodeNL\Model\PostcodeManagement;

class WithPostcodecheckModifier implements EntityFormModifierInterface
{
    private const KEY_MANUAL_MODE = 'postcodenl_manual_mode';

    public function __construct(
        protected PostcodeManagement $postcodeManagement,
    ) {
    }

    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        $form->registerModificationListener(
            'explodeStreetRows',
            'form:build',
            [$this, 'explodeStreetRows']
        );
        $form->registerModificationListener(
            'initPostcodeCheckFields',
            'form:build',
            [$this, 'initPostcodeCheckFields']
        );

        $form->registerModificationListener(
            'postcodenlShippingUpdated',
            'form:shipping:updated',
            [$this, 'postcodeCheck']
        );
        $form->registerModificationListener(
            'postcodenBillingUpdated',
            'form:billing:updated',
            [$this, 'postcodeCheck']
        );

        $form->registerModificationListener(
            'removeAutoSave',
            'form:build:magewire',
            [$this, 'removeAutoSave']
        );

        return $form;
    }

    public function initPostcodeCheckFields(EntityFormInterface $form): void
    {
        $country = $form->getField(AddressInterface::KEY_COUNTRY_ID)->getValue();
        $manualMode = $form->getField(self::KEY_MANUAL_MODE);
        $street = $form->getField(AddressInterface::KEY_STREET);
        $houseNumber = $street->getRelatives()[1];
        $addition = $street->getRelatives()[2];
        $city = $form->getField(AddressInterface::KEY_CITY);

        $houseNumber->setAttribute('autocomplete', 'address-line2');
        $addition->setAttribute('autocomplete', 'address-line3');

        if ($country !== 'NL' && $manualMode) {
            $form->removeField($manualMode);
            $street->enable();
            $city->enable();
        } else {
            $this->onManualModeUpdated($form);
        }
    }

    public function onManualModeUpdated(EntityFormInterface $form): void
    {
        $manualMode = $form->getField(self::KEY_MANUAL_MODE);
        $postcode = $form->getField(AddressInterface::KEY_POSTCODE);
        $street = $form->getField(AddressInterface::KEY_STREET);
        $housenumber = $street->getRelatives()[1];
        $addition = $street->getRelatives()[2];
        $city = $form->getField(AddressInterface::KEY_CITY);

        if ($manualMode && $manualMode->getValue()) {
            $street->enable();
            $city->enable();
            $addition?->unsetData('options');
        } else {
            $street->disable();
            $city->disable();
        }

        foreach ([$postcode, $housenumber, $addition, $manualMode] as $field) {
            if ($field) {
                $field->setAttribute('@change.capture', '$wire.save()');
            }
        }
    }

    public function removeAutoSave(EntityFormInterface $form): void
    {
        $manualMode = $form->getField(self::KEY_MANUAL_MODE);
        $postcode = $form->getField(AddressInterface::KEY_POSTCODE);
        $street = $form->getField(AddressInterface::KEY_STREET);
        $housenumber = $street->getRelatives()[1];
        $addition = $street->getRelatives()[2];

        foreach ([$postcode, $housenumber, $addition, $manualMode] as $field) {
            if ($field) {
                $field->removeAttribute('data-autosave');
            }
        }
    }

    public function postcodeCheck(EntityFormInterface $form, EntityFieldInterface $field, MagewireAddressFormInterface $formComponent): void
    {
        $address = $formComponent->getAddress();
        $manualMode = $form->getField(self::KEY_MANUAL_MODE);
        if (!$manualMode || $address[self::KEY_MANUAL_MODE]) {
            return;
        }

        $postcode = $form->getField(AddressInterface::KEY_POSTCODE);
        $city = $form->getField(AddressInterface::KEY_CITY);
        $street = $form->getField(AddressInterface::KEY_STREET);
        $housenumber = $street->getRelatives()[1];
        $addition = $street->getRelatives()[2];

        if (!$postcode || !$postcode->getValue() || !$housenumber || !$housenumber->getValue()) {
            return;
        }

        $response = json_decode($this->postcodeManagement->getPostcodeInformation(
            $postcode->getValue(),
            $housenumber->getValue(),
            $addition->getValue(),
        ), true);

        if (isset($response['exception'])) {
            $formComponent->error(self::KEY_MANUAL_MODE, $response['exception']);
            return;
        }
        $formComponent->clearErrors();
        $postcode->setValue($response['postcode']);
        $street->setValue($response['street']);
        $city->setValue($response['city']);
        $housenumber->setValue($response['houseNumber']);
        $addition->setValue($response['houseNumberAddition']);

        if (count($response['houseNumberAdditions']) > 1) {
            $addition->setOptions($response['houseNumberAdditions']);
        }

        $address[AddressInterface::KEY_POSTCODE] = $response['postcode'];
        $address[AddressInterface::KEY_STREET] = [
            $response['street'],
            $response['houseNumber'],
            $response['houseNumberAddition'],
        ];
        $address[AddressInterface::KEY_CITY] = $response['city'];
        $formComponent->address = $address;
    }

    public function explodeStreetRows(EntityFormInterface $form): void
    {
        $streetField = $form->getField(AddressInterface::KEY_STREET);
        foreach ($streetField->getRelatives() as $relative) {
            // Change id so the field can coexist with the original street field
            $relative->setData('id', "{$streetField->getName()}.{$relative->getPosition()}");
            $form->addField($relative);
        }
    }
}
