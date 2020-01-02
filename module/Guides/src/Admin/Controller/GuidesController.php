<?php
namespace Guides\Admin\Controller;

use Guides\Admin\Model\Guide;
use Guides\Admin\Service\GuidesService;
use Pipe\Mvc\Controller\Admin\TableController;
use Guides\Admin\Model\GuidePrice;
use Zend\View\Model\JsonModel;

/**
 * @method GuidesService getService()
 * @method Guide getModel()
 */
class GuidesController extends TableController
{
    protected function getViewStructure() {
        return [
            'edit' => [
                'form' => [
                    ['id'],
                    [
                        'type'     => 'panel',
                        'name'     => 'Основные параметры',
                        'children' => [[
                            ['width' => 50, 'element' => 'name'],
                            ['width' => 50, 'element' => 'price'],
                        ], 'contacts', 'comment'],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Связь с профилем',
                        'children' => ['user_id'],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Языки',
                        'children' => ['languages'],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Экскурсии',
                        'children' => ['museums'],
                    ],
                ],
            ],
        ];
    }

    public function priceAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            $data = $this->params()->fromPost('gp');

            foreach ($data as $langId => $row) {
                $gPrice = new GuidePrice();
                $gPrice->select()->where(['lang_id' => $langId]);
                $gPrice->load();

                $gPrice->setVariables([
                    'lang_id'   => $langId,
                    'price'     => $row[1],
                    'min_price' => $row[2],
                    'max_price' => $row[3],
                ])->save();
            }

            return new JsonModel(['status' => true]);
        }

        $this->initLayout();

        return [];
    }

    public function getGuidesAction()
    {
        $props = $this->params()->fromPost();

        return new JsonModel(['items' => $this->getService()->getGuides($props)]);
    }
}