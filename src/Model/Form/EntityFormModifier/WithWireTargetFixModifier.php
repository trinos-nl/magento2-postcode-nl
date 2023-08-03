<?php

namespace Trinos\PostcodeNL\Model\Form\EntityFormModifier;

use Hyva\Checkout\Magewire\Checkout\AddressView\AbstractMagewireAddressForm;
use Hyva\Checkout\Magewire\Checkout\AddressView\MagewireAddressFormInterface as FormInterface;
use Hyva\Checkout\Model\Form\EntityFieldInterface;
use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;
use RuntimeException;

class WithWireTargetFixModifier implements EntityFormModifierInterface
{

    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        $form->registerModificationListener(
            'fixWireTarget',
            'form:build:magewire',
            fn (EntityFormInterface $form, AbstractMagewireAddressForm $component) => $this->fixWireTargetAttribute($component, $form)
        );

        return $form;
    }

    /**
     * Fix the wire:target attribute for the given component.
     * This properly sets the loading class to the fields
     *
     * @param AbstractMagewireAddressForm $component
     * @param EntityFormInterface $form
     * @param EntityFieldInterface|null $ancestor
     * @param EntityFieldInterface|null $root
     * @return void
     */
    public function fixWireTargetAttribute(
        AbstractMagewireAddressForm $component,
        EntityFormInterface         $form,
        EntityFieldInterface        $ancestor = null,
        EntityFieldInterface        $root = null
    ): void {
        if ($ancestor === null && $root === null) {
            if (!array_key_exists(FormInterface::ADDRESS_PROPERTY, $component->getPublicProperties())) {
                throw new RuntimeException(
                    sprintf('Public property %s is required for address binding purposes.', FormInterface::ADDRESS_PROPERTY)
                );
            }
        }

        // Grab the relatives from its ancestor if available.
        $fields = $ancestor ? $ancestor->getRelatives() : $form->getFields();
        foreach ($fields as $field) {
            $addressProperty = FormInterface::ADDRESS_PROPERTY . '.' . $field->getId();

            if ($field->hasRelatives()) {
                $this->fixWireTargetAttribute($component, $form, $field, $root ?? $field);
            }
            if ($field->hasNamesakeAncestor() || $field->hasNamesakeRelatives()) {
                $addressProperty .= '.' . $field->getPosition();
            }

            $field->setAttribute('wire:target', $addressProperty);
        }
    }
}

