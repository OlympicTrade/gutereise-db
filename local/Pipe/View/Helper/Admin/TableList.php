<?php
namespace Pipe\View\Helper\Admin;

use mysql_xdevapi\Exception;
use Pipe\Db\Entity\EntityCollection;
use Pipe\Db\Entity\EntityHierarchy;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class TableList extends AbstractHelper
{
    /**
     * @param EntityCollection|Paginator  $list
     * @param array $options
     * @return string
     */
    public function __invoke($list, $options)
    {
        $options = $options + [
            'fields'    => [],
            'wrapper'   => true,
        ];

        if(!$list->count()) {
            return
                '<div class="std-box">'
                    .'Записей не найдено'
                .'</div>';
        }

        $view = $this->getView();

        $html =
            '<table class="std-table">'
            .'<thead>'
                .'<tr>';

        foreach($options['fields'] as $field) {
            $style = '';
            if($field['width']) {
                $style = 'width: ' . $field['width'] . 'px;';
            }

            $html .=
                $view->htmlElement('th', ['style' => $style], $field['header']);
        }

        $html .=
                '<th></th>'.
            '</tr>'.
            '</thead>'.
                '<tbody>'.
                    $this->renderList($list, $options).
                '</tbody>'.
            '</table>';

        if($list instanceof Paginator) {
            $html .=
                $view->paginationControl($list, 'Sliding', 'pagination-slide', ['route' => 'application/pagination']);
        }

        if($options['wrapper']) {
            $html =
                '<div class="panel">'.
                    '<div class="table-list" data-module="' . $view->module . '" data-section="' . $view->section . '">'.
                        $html.
                    '</div>'.
                '</div>';
        }

        return $html;
    }

    protected function renderList($list, $options, $depth = 0)
    {
        $html = '';
        $view = $this->getView();

        foreach($list as $item) {
            $html .=
                '<tr class="data-row" data-id="' . $item->id() . '">';

            foreach($options['fields'] as $field => $fieldOpts) {
                if($fieldOpts['preset']) {
                    $fieldOpts = $this->presets($fieldOpts['preset']);
                }

                $class = (isset($fieldOpts['class']) ? ' class="' . $fieldOpts['class'] . '"' : '');
                $html .=
                    '<td' . $class . '>';

                if($field == 'name' && $depth > 0) {
                    $html .=
                        '<span class="branch" style="margin-left: ' . (($depth * 20) - 10) . 'px"></span>';
                }

                $html .=
                        (!isset($fieldOpts['filter']) ? $item->get($field) : call_user_func_array($fieldOpts['filter'], [$item, $view])).
                    '</td>';
            }

            $html .=
                    '<td class="btns">'.
                        '<span class="btn sm i blue copy"><i class="far fa-copy"></i></span> '.
                        '<span class="btn sm i red del"><i class="fa fa-times"></i></span>'.
                    '</td>'.
                '</tr>';

            if($item instanceof EntityHierarchy && ($children = $item->getChildren())->load()) {
                $html .= $this->renderList($item->getChildren(), $options, $depth + 1);
            }
        }

        return $html;
    }

    public function presets($preset)
    {
        switch ($preset) {
            case 'fio':
                $field = [
                    'header' => 'ФИО',
                ];
                break;
            case 'contacts':
                $field = [
                    'header' => 'Контакты',
                    'class'  => 'mb-hide',
                    'filter' => function($model, $view) {
                        return $view->adminContacts($model);
                    }
                ];
                break;
            default:
                throw new \Exception('Unknown field preset');
        }

        return $field;
    }
}