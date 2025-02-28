<?php

declare(strict_types=1);

namespace Icepay\Payment\Block\Adminhtml\Configuration;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ValidateMerchantKeys extends Field
{
    protected $_template = 'Icepay_Payment::system/configuration/validate-merchant-keys.phtml';

    public function render(AbstractElement $element): string
    {
        return $this->_decorateRowHtml($element, '
            <td class="label"></td>
            <td>' . $this->_toHtml() . '</td>
        ');
    }
}
