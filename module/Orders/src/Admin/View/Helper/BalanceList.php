<?php
namespace Orders\Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;

class BalanceList extends AbstractHelper
{
    public function __invoke($items, $type)
    {
        if(!$items->count()) {
            return 'Нет задолженностей';
        }

        $html =
            '<div class="balances-list">';

        $view = $this->getView();
        foreach ($items as $item) {
            $html .=
                '<div class="agent">'.
                    '<div class="row header">'.
                        '<div class="cell name">' . $item->get('name') . ' (' . $item->get('phone') . ')</div>'.
                        '<div class="cell balance">' . $item->get('balance') . '</div>'.
                    '</div>';

            foreach ($item->getDebts() as $balance) {
                $html .=
                    '<div class="row order" data-balance="' . $balance['balance'] . '">'.
                        '<div class="cb">'.
                            '<input type="checkbox" name="odt[]" value="' . $balance['id'] . '">'.
                        '</div>'.
                        '<div class="cell date">' . $view->date($balance['date'], [], 'Y-m-d') . '</div>'.
                        '<a href="/orders/edit/' . $balance['order_id'] . '/" class="cell order popup">' . $balance['order_name'] . '</a>'.
                        '<div class="cell dept">' . $view->price($balance['balance']) . '</div>'.
                    '</div>';
            }

            $html .=
                '<div class="row summary">'.
                    '<div class="cb">'.
                        '<input type="checkbox">'.
                    '</div>'.
                    '<div class="cell notice">Выбрано</div>'.
                    '<div class="cell sum">'.
                        '<span class="nbr">0</span>'.
                        '<span class="btn set-paid" data-type="' . $type . '">ok</span>'.
                    '</div>'.
                '</div>'.
                '<div class="clear"></div>'.
            '</div>';
        }

        $html .=
            '</div>';

        return $html;
    }
}
?>