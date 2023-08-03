<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Trinos\PostcodeNL\Model\Form\EntityField\EavEntityAddress;

use Hyva\Checkout\Model\Form\EntityField\EavEntityAddress\StreetAttributeField as CoreStreetAttributeField;

class StreetAttributeField extends CoreStreetAttributeField
{
    /**
     * Allow custom field id. This is needed to make sure the street field is
     * rendered as a multiple fields instead of a single field.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getData('id') ?? parent::getId();
    }

    /**
     * Get sort order from config instead of position.
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->getConfig()->getSortOrder();
    }
}
