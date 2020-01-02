<?php
namespace Application\Admin\View\Helper;

use Users\Admin\Model\User;
use Zend\View\Helper\AbstractHelper;

class Menu extends AbstractHelper
{
    public function __invoke($class = '')
    {
        $nav = [
            ['url' => '/', 'class' => 'calc-open', 'module' => 'orders', 'section' => 'calc', 'label' => 'Калькулятор', 'icon' => 'fa fa-calculator'],
            ['module' => 'orders', 'label' => 'Заказы', 'icon' => 'fal fa-calendar-alt', 'children' => [
                ['url' => '/balance/', 'module' => 'orders', 'section' => 'balance', 'label' => 'Баланс'],
            ]],
            ['module' => 'clients', 'label' => 'Клиенты', 'icon' => 'fa fa-users'],
            ['module' => 'guides', 'section' => 'calendar', 'label' => 'Календарь', 'icon' => 'far fa-calendar-alt'],
            ['module' => 'transports', 'label' => 'Транспорт', 'icon' => 'fa fa-car', 'children' => [
                ['module' => 'drivers', 'label' => 'Водители'],
                ['module' => 'transports', 'section' => 'transfers', 'label' => 'Трансферы'],
            ]],
            ['module' => 'excursions', 'label' => 'Экскурсии', 'icon' => 'fa fa-university', 'children' => [
                ['module' => 'museums', 'label' => 'Музеи'],
                ['module' => 'guides', 'label' => 'Гиды'],
            ]],
            ['module' => 'hotels', 'label' => 'Гостиницы', 'icon' => 'fa fa-hotel'],
            ['url' => '/settings/','module' => 'application', 'section' => 'settings', 'label' => 'Настройки', 'icon' => 'fa fa-cog', 'children' => [
                ['module' => 'translator', 'label' => 'Перевод'],
                ['url' => '/guides/price/', 'module' => 'guides', 'section' => 'price', 'label' => 'Цены гидов'],
                ['module' => 'users', 'label' => 'Пользователи'],
                ['module' => 'documents', 'label' => 'Документы'],
                ['module' => 'managers', 'label' => 'Менеджеры'],
            ]],
        ];

        //$allowedMods = User::getInstance()->getAllowedModules();
        $user = User::getInstance();

        $checkRules = function($val) use ($user) {
            $module = $val['module'] . '/' . (isset($val['section']) ? $val['section'] : $val['module']);
            //return in_array($module, $allowedMods);
            return $user->checkRights($module, false);
        };

        foreach ($nav as $key => &$row) {
           $nav[$key]['allow'] = $checkRules($row);

            if($row['children']) {
                foreach ($row['children'] as $key2 => &$row2) {
                    $nav[$key]['children'][$key2]['allow'] = $checkRules($row2);
                }
            }
        }

        $tmp = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $url = '/' . $tmp[0] . '/';

        $view = $this->getView();
        $module = $view->module;
        $section = $view->section;

        $menuItem = function($item, $sub = '', $isActive = false) use ($url, $module, $section) {
            $result = [
                'html'    => '',
                'active'  => false,
            ];

            if(!$item['allow'] && !$sub) return $result;

            $isActive = $isActive || ($module == $item['module'] && $section == ($item['section'] ?: $item['module']));

            $html =
                '<li' . ($isActive ? ' class="open"' : '') . '>';

            $class = $item['class'];

            if($item['allow']) {
                if(!$url = $item['url']) {
                    $url = '/' . $item['module'] . '/' . ($item['section'] ? $item['section'] . '/' : '');
                }
                $html .=
                    '<a class="' . $class . '" href="' . $url . '">';
            } else {
                $html .=
                    '<span class="' . $class . '" >';
            }

            $html .=
                ($item['icon'] ? '<i class="' . $item['icon'] . '"></i>' : '').
                '<span class="name">' . $item['label'] . '</span>';

            if($sub) {
                $html .=
                    '<span class="arr"></span>';
            }

            if($item['allow']) {
                $html .=
                    '</a>';
            } else {
                $html .=
                    '</span>';
            }

            $html .=
                    $sub.
                '</li>';

            return [
                'html'  => $html,
                'active'  => $isActive,
            ];
        };

        $html =
            '<ul class="' . $class . '">'/*.
                '<li>'.
                    '<a href="/">'.
                        '<i class="fa fa-home"></i>'.
                    '</a>'.
                '</li>'*/;

        foreach($nav as $item) {
            $subHtml = '';

            $isActive = false;
            if($item['children']) {
                foreach($item['children'] as $child) {
                    $subItems = $menuItem($child);
                    $isActive = $subItems['active'] || $isActive;
                    $subHtml .= $subItems['html'];
                }

                if($subHtml) {
                    $subHtml = '<ul>' . $subHtml . '</ul>';
                }
            }

            $html .= $menuItem($item, $subHtml)['html'];
        }

        $html .=
            '</ul>';

        return $html;
    }
}