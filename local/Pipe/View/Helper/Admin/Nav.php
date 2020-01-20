<?php
namespace Pipe\View\Helper\Admin;

use Application\Admin\Model\Menu;
use Pipe\Cache\CacheFactory;
use Pipe\Db\Entity\EntityCollectionHierarchy;
use Users\Common\Model\User;
use Zend\View\Helper\AbstractHelper;

class Nav extends AbstractHelper
{
    public function __invoke($class = '')
    {
        $role =  User::getInstance()->getRoleName();

        $cache = CacheFactory::getCache('admin_nav_' . $role, 'html');

        if($cache->has()) {
            return $cache->get();
        }

        $data = $this->generate($class);
        $cache->set($data)
            ->setTags(['admin_nav'])
            ->save();

        return $data;
    }

    public function generate($class = '')
    {
        $view = $this->getView();
        $nav = $this->getNavItems();

        $role =  User::getInstance()->getRoleName();

        $checkRights = function($item) use ($role) {
            $allow = $item->access->deny;
            $deny = $item->access->deny;

            if($deny && in_array($role, explode(',', $deny))) {
                return false;
            }

            if(!$allow || $allow == '*' || in_array($role, explode(',', $allow))) {
                return true;
            }

            return false;
        };

        foreach ($nav as $key => &$row) {
           $nav[$key]['allow'] = $checkRights($row);

            if($row['children']) {
                foreach ($row['children'] as $key2 => &$row2) {
                    $nav[$key]['children'][$key2]['allow'] = $checkRights($row2);
                }
            }
        }

        //$tmp = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

        $module = $view->module;
        $section = $view->section;

        $menuItem = function($item, $sub = '', $isActive = false) use ($module, $section, $view) {
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
                $url = $view->adminUrl($item['module'], $item['section'], $item['action']);
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
            '<ul class="' . $class . '">';

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

    protected function getNavItems($list = null, $depth = 0)
    {
        $nav = [];

        if(!$list) {
            $list = $list ?? EntityCollectionHierarchy::factory(Menu::class, ['sort' => 'sort DESC']);
            $list->setParentId(0);
        }

        foreach ($list as $module) {
            $navItem = [
                'parent'  => $module->parent,
                'module'  => $module['url']['module'],
                'section' => $module['url']['section'],
                'action'  => $module['url']['action'],
                'access'  => $module['access'],
                'label'   => $module['name'],
                'icon'    => $module['icon'] ? $module['icon'] : 'fas fa-users-cog',
                'class'   => $module['options']['class'],
            ];

            if($depth < 2 && $children = $module->getChildren()->load()) {
                $navItem['children'] = $this->getNavItems($children, $depth + 1);
                $this->getNavItems($children, $depth + 1);
            }

            $nav[] = $navItem;
        }

        return $nav;
    }
}