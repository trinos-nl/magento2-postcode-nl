<?php

namespace Trinos\PostcodeNL\Plugin;

use Hyva\Checkout\Model\ConfigData\HyvaThemes\Checkout;

class SortFormFieldsMapping
{
    /**
     * The reason for sorting is so that street.0 is added before street.1, etc.
     * This way we force street.0 to always be the ancestor
     *
     * @param Checkout $subject
     * @param array $result
     * @param string $by
     * @return array
     */
    public function afterGetShippingEavAttributeFormFieldsMapping(Checkout $subject, array $result, string $by = 'attribute_code'): array
    {
        ksort($result);
        return $result;
    }
}
