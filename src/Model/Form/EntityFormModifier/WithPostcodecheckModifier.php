<?php

namespace Trinos\PostcodeNL\Model\Form\EntityFormModifier;

use Hyva\Checkout\Magewire\Checkout\AddressView\AbstractMagewireAddressForm;
use Hyva\Checkout\Magewire\Checkout\AddressView\MagewireAddressFormInterface;
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
            'manualModeUpdated',
            'form:build:magewire',
            [$this, 'validatePostcode']
        );

        $form->registerModificationListener(
            'postcodenlShippingUpdated',
            'form:updated',
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
        $houseNumber = $street->getRelatives()[1] ?? null;
        $addition = $street->getRelatives()[2] ?? null;
        $city = $form->getField(AddressInterface::KEY_CITY);

        $houseNumber?->setAttribute('autocomplete', 'address-line2');
        $addition?->setAttribute('autocomplete', 'address-line3');

        if ($country !== 'NL') {
            if ($manualMode) {
                $form->removeField($manualMode);
            }
            $street->enable();
            $city->enable();
        } else {
            $this->onManualModeUpdated($form);
        }
    }

    public function validatePostcode(EntityFormInterface $form): ?array
    {
        $manualMode = $form->getField(self::KEY_MANUAL_MODE);
        if (!$manualMode || $manualMode->getValue()) {
            return null;
        }

        $countryId = $form->getField(AddressInterface::KEY_COUNTRY_ID);
        if ($countryId->getValue() !== 'NL') {
            return null;
        }

        $postcode = $form->getField(AddressInterface::KEY_POSTCODE);
        $street = $form->getField(AddressInterface::KEY_STREET);
        $housenumber = $street->getRelatives()[1] ?? null;
        $addition = $street->getRelatives()[2] ?? null;

        $response = json_decode($this->postcodeManagement->getPostcodeInformation(
            $postcode->getValue() ?? '',
            $housenumber?->getValue() ?? '',
            $addition?->getValue() ?? '',
        ), true);

        if (isset($response['exception'])) {
            $manualMode->setAttribute('data-msg-magewire', $response['exception']);
            $manualMode->setAttribute('data-magewire-is-valid', '0');
            return $response;
        }

        if (count($response['houseNumberAdditions']) > 1) {
            // The option key should be the same as the label.
            $options = array_combine($response['houseNumberAdditions'], $response['houseNumberAdditions']);
            $addition?->setOptions($options);
        }

        return $response;
    }

    public function onManualModeUpdated(EntityFormInterface $form): void
    {
        $manualMode = $form->getField(self::KEY_MANUAL_MODE);
        $postcode = $form->getField(AddressInterface::KEY_POSTCODE);
        $street = $form->getField(AddressInterface::KEY_STREET);
        $housenumber = $street->getRelatives()[1] ?? null;
        $addition = $street->getRelatives()[2] ?? null;
        $city = $form->getField(AddressInterface::KEY_CITY);

        $postcode?->setValidationRule('postcode');

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
        $housenumber = $street->getRelatives()[1] ?? null;
        $addition = $street->getRelatives()[2] ?? null;

        foreach ([$postcode, $housenumber, $addition, $manualMode] as $field) {
            if ($field) {
                $field->removeAttribute('data-autosave');
            }
        }
    }

    public function postcodeCheck(EntityFormInterface $form, MagewireAddressFormInterface $formComponent): void
    {
        $manualMode = $form->getField(self::KEY_MANUAL_MODE);
        if ($manualMode && $manualMode->getValue()) {
            return;
        }

        $countryId = $form->getField(AddressInterface::KEY_COUNTRY_ID);
        if ($countryId->getValue() !== 'NL') {
            return;
        }

        $response = $this->validatePostcode($form);

        $postcode = $form->getField(AddressInterface::KEY_POSTCODE);
        $street = $form->getField(AddressInterface::KEY_STREET);
        $city = $form->getField(AddressInterface::KEY_CITY);
        $housenumber = $street->getRelatives()[1] ?? null;
        $addition = $street->getRelatives()[2] ?? null;

        if (!$response || isset($response['exception'])) {
            $street->setValue('');
            $city->setValue('');
            return;
        }

        $postcode->setValue($response['postcode']);
        $street->setValue($response['street']);
        $city->setValue($response['city']);
        $housenumber?->setValue($response['houseNumber']);
        $addition?->setValue($response['houseNumberAddition']);
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
