<?php
namespace Fooman\FixForFullTaxOnInvoicePdf\Model;

class PdfTax extends \Magento\Tax\Model\Sales\Pdf\Tax
{

 /**
 * Apply Fix for wrong full tax summary
 * @see https://github.com/magento/magento2/pull/9765
 */

 /**
 * Get array of arrays with tax information for display in PDF
 * array(
 *  $index => array(
 *      'amount'   => $amount,
 *      'label'    => $label,
 *      'font_size'=> $font_size
 *  )
 * )
 *
 * @return array
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
public function getFullTaxInfo()
{
    $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
    $taxClassAmount = $this->_taxHelper->getCalculatedTaxes($this->getSource());
    if (!empty($taxClassAmount)) {
        foreach ($taxClassAmount as &$tax) {
            $percent = $tax['percent'] ? ' (' . $tax['percent'] . '%)' : '';
            $tax['amount'] = $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($tax['tax_amount']);
            $tax['label'] = __($tax['title']) . $percent . ':';
            $tax['font_size'] = $fontSize;
        }
    } else {
        /** @var $orders \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\Collection */
        $orders = $this->_taxOrdersFactory->create();
        $rates = $orders->loadByOrder($this->getOrder())->toArray();
        $fullInfo = $this->_taxCalculation->reproduceProcess($rates['items']);
        $tax_info = [];

        if ($fullInfo) {
            foreach ($fullInfo as $info) {
                if (isset($info['hidden']) && $info['hidden']) {
                    continue;
                }

                $_amount = $info['amount'];

                foreach ($info['rates'] as $rate) {
                    $percent = $rate['percent'] ? ' (' . $rate['percent'] . '%)' : '';

                    $tax_info[] = [
                        'amount' => $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($_amount),
                        'label' => __($rate['title']) . $percent . ':',
                        'font_size' => $fontSize,
                    ];
                }
            }
        }
        $taxClassAmount = $tax_info;
    }

    return $taxClassAmount;
}
}
