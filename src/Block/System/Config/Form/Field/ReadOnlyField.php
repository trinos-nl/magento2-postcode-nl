<?php
namespace Trinos\PostcodeNL\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ReadOnlyField extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('readonly', 1);
        return $element->getElementHtml();
    }
}
