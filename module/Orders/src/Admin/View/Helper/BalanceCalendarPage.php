<?php
namespace Orders\Admin\View\Helper;

use Zend\View\Helper\AbstractHelper;

class BalanceCalendarPage extends AbstractHelper
{
    public function __invoke($calendar, $balance)
    {
        $html =
            '<div class="cols">';

        foreach ($balance as $name => $data) {
            $html .=
                '<div class="col-33">'.
                    $this->balanceBlock($name, $data).
                '</div>';
        }

        $html .=
            '</div>';

        return $html;
    }

    protected function balanceBlock($name, $data)
    {
        $view = $this->getView();

        $formRow = function ($label, $val) use($view) {
            return
                '<div class="row">'.
                    '<div class="label">' . $label . ': </div>'.
                    $view->price((int) $val).
                '</div>';
        };

        $html =
            '<div class="balance">'.
                '<div class="title">' . $name . '</div>'.
                $formRow('Доход', $data['income']).
                $formRow('Расход', $data['outgo']).
                $formRow('Прибыль', $data['profit']).
                $formRow('Туристов', $data['tourists']).
            '</div>';

        return $html;
    }
}