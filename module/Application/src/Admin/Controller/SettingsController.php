<?php
namespace Application\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;
use Pipe\Url\Url;

class SettingsController extends TableController
{
    protected function getViewStructure() {
        return [
            'edit' => [
                'sidebar' => [
                    'items' => [
                        ['preset' => 'save'],
                    ],
                ],
                'form' => [
                    ['id'],
                    [
                        'type'     => 'panel',
                        'name'     => 'Наценка',
                        'children' => 'margin',
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Контакты',
                        'children' => 'contacts',
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Курс',
                        'children' => 'currency',
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Реквизиты',
                        'children' => 'company_details',
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Языки',
                        'children' => 'languages',
                    ],
                ],
            ],
        ];
    }

    public function indexAction()
    {
        return $this->redirect()->toUrl(Url::url(['platform' => $this->platform], ['edit', '1']));
    }
}