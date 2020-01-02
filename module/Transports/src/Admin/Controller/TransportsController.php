<?php
namespace Transports\Admin\Controller;

use Pipe\Db\Entity\EntityCollection;
use Pipe\Mvc\Controller\Admin\TableController;
use Transports\Admin\Model\Transport;
use Zend\View\Model\JsonModel;

class TransportsController extends TableController
{
    protected function getViewStructure() {
        return [
            'edit' => [
                'form' => [
                    ['id'],
                    [
                        'type'     => 'panel',
                        'name'     => 'Основные параметры',
                        'children' => [
                            [
                                ['width' => 33, 'element' => 'name'],
                                ['width' => 33, 'element' => 'genitive1'],
                                ['width' => 33, 'element' => 'genitive2'],
                            ],
                            [
                                ['width' => 33, 'element' => 'type'],
                                ['width' => 33, 'element' => 'capacity'],
                                ['width' => 33, 'element' => 'min_price'],
                            ],
                            'comment',
                        ],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Базовый тариф',
                        'children' => ['price'],
                    ],
                    [
                        'type'     => 'panel',
                        'name'     => 'Водители',
                        'children' => ['drivers'],
                    ],
                ],
            ],
        ];
    }

    public function getTransportByTypeAction()
    {
        $id = $this->params()->fromPost('id');

        $transports = EntityCollection::factory(Transport::class);
        $transports->select()->where(['type' => $id]);

        $ids = [];
        foreach ($transports as $transport) {
            $ids[] = (string) $transport->id();
        }

        return new JsonModel(['ids' => $ids]);
    }

    public function getInfoAction()
    {
        $id = $this->params()->fromPost('id');

        $transport = new Transport();
        if($id != 0) {
            $transport->id($id)->load();
            $name = str_replace(['"'], [''], $transport->get('name'));
            $desc = ' (' . $transport->get('capacity') . ' чел.)';
        } else {
            $transport->setVariables([
                'type'  => Transport::TYPE_AUTO,
            ]);
            $name = 'Автоподбор транспорта';
            $desc = '';
        }

        return new JsonModel([
            'id'   => $transport->id(),
            'desc' => $desc,
            'name' => $name,
            'type' => $transport->get('type'),
        ]);
    }

    public function autocompleteAction()
    {
        $query = $this->params()->fromQuery('query');

        $collection = $this->getModel()->getCollection();
        $collection->select()
            ->limit(7);

        $where = new Where();
        foreach(Translit::searchVariants($query) as $queryVar) {
            $where->or->like('name', '%' . $queryVar . '%');
        }
        $collection->select()->where($where);

        $resp = [];

        $resp[] = [
            'id'    => 0,
            'label' => 'Автоподбор транспорта',
            'value' => 'Автоподбор транспорта',
            'type'  => Transport::TYPE_AUTO,
        ];

        foreach ($collection as $item) {
            $name = str_replace(['"'], [''], $item->get('name'));

            $resp[] = [
                'id'    => $item->id(),
                'label' => $name,
                'value' => $name,
                'type'  => $item->get('type'),
            ];
        }

        return new JsonModel($resp);
    }

    /**
     * @return \Transports\Admin\Service\TransportsService
     */
    protected function getTransportService()
    {
        return $this->getServiceManager()->get('Transports\Admin\Service\TransportsService');
    }
}