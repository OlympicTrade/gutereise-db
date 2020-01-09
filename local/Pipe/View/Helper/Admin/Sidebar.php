<?php
namespace Pipe\View\Helper\Admin;

use Application\Common\Model\Template;
use Pipe\View\Helper\AbstractHelper;

class Sidebar extends AbstractHelper
{
    protected $options = [
        'items'  => [],
        'header' => [],
        'class'  => '',
        'preset' => '',тф
    ];

    public function setOptions($options)
    {
        $this->options = $options + $this->options;
    }

    public function addItems($items)
    {
        $this->options['items'] = array_merge($this->options['items'], $items);
    }

    public function render()
    {
        $view = $this->getView();

        $options = $this->options;

        $presetItems = [];
        switch ($options['preset']) {
            case 'empty':
                $presetItems = [];
                break;
            case 'back':
                $presetItems = [
                    ['class' => '', 'icon' => 'fas fa-angle-double-left', 'label' => 'Назад', 'url' => 'javascript:history.back()'],
                ];
                break;
            case 'list':
                $presetItems = [
                    ['preset' => 'add'],
                    ['preset' => 'search'],
                ];
                break;
            case 'edit':
                $presetItems = [
                    ['preset' => 'save'],
                    ['preset' => 'del'],
                    ['preset' => 'back'],
                ];
                break;
            default:
                break;
        }

        $items = array_merge($presetItems, $options['items']);

        foreach ($items as $key => $item) {
            if(!$item['preset']) continue;

            switch ($item['preset']) {
                case 'add':
                    $items[$key] = ['class' => 'item-add yellow', 'icon' => 'far fa-plus', 'label' => 'Добавить', 'url' => $this->getUrl('edit')];
                    break;
                case 'save':
                    $items[$key] = ['class' => 'item-save', 'icon' => 'far fa-cloud-upload', 'label' => 'Сохранить'];
                    break;
                case 'del':
                    $items[$key] = ['class' => 'item-del', 'icon' => 'far fa-trash-alt', 'label' => 'Удалить'];
                    break;
                case 'back':
                    $items[$key] = ['class' => '', 'icon' => 'fas fa-angle-double-left', 'label' => 'К списку', 'url' => $this->getUrl()];
                    break;
                case 'search':
                    $items[$key] = ['position' => 'before', 'html' =>
                        '<div class="widget search">'.
                            '<i class="fal fa-search"></i>'.
                            '<i class="fal fa-plus add"></i>'.
                            '<input type="text" placeholder="Поиск..." name="query">'.
                        '</div>'];

                    $templatePage = new Template();
                    $tempOpts = $templatePage->setSelector($this->getModule() . '/' . $this->getSection() . '/list');

                    $srLinksHtml = '<div class="widget search-help">';
                    if($queries = $tempOpts->get('options')->search->queries) {
                        foreach ($queries as $row) {
                            $srLinksHtml .= '<div class="row"><span>' . $row . '</span><i class="del"></i></div>';
                        }
                    }
                    $srLinksHtml .= '</div>';

                    $items[] = [];
                    $items[] = ['position' => 'after', 'html' => $srLinksHtml];
                    break;
                default:
                    throw new \Exception('Unknown sidebar btn preset');
            }
        }

        $html = '';

        foreach($items as $key => $item) {
            if($item['html'] && $item['position'] == 'before') {
                $html .= $item['html'];
                unset($items[$key]);
            }
        }

        $html .= '<ul data-class="' . $options['class'] . '" data-module="' . $view->module . '" data-section="' . $view->section . '">';

        foreach($items as $item) {
            if(!$item || $item['type'] == 'space') {
                $html .= '<li class="space"></li>';
                continue;
            }

            if(isset($item['html'])) {
                continue;
            }

            $attrs = '';
            if(!empty($item['attrs'])) {
                foreach ($item['attrs'] as $key => $val) {
                    $attrs .= ' ' . $key . '="' . $val . '"';
                }
            }

            $html .=
                '<li>';

            if(!empty($item['url'])) {
                $html .=
                    '<a href="' . $item['url'] . '" class="' . $item['class'] . '"' . $attrs . ' title="' . $item['label'] . '">'.
                        '<i class="' . $item['icon'] . '"></i>'.
                        '<span>' . $item['label'] . '</span>'.
                    '</a>';
            } else {
                $html .=
                    '<span class="' . $item['class'] . '"' . $attrs . ' title="' . $item['label'] . '">'.
                        '<i class="' . $item['icon'] . '"></i>'.
                        '<span>' . $item['label'] . '</span>'.
                    '</span>';
            }

            $html .=
                '</li>';
        }

        foreach($items as $key => $item) {
            if($item['html'] && $item['position'] == 'after') {
                $html .= $item['html'];
            }
        }

        $html .=
            '</div>';

        return $html;
    }

    public function getUrl($action = '', $id = '')
    {
        $view = $this->getView();

        $module = $view->module;
        $section = $view->section;

        return $view->adminUrl($module, $section, $action, $id);
    }
}