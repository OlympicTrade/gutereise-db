<?php
namespace Pipe\Mvc\Controller\Admin;

use Application\Common\Model\Template;
use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollectionHierarchy;
use Pipe\Form\Form;
use Pipe\Mvc\Service\Admin\TableService;
use Zend\View\Model\JsonModel;

class TableController extends AbstractController
{
    protected $platform = ADMIN_PREFIX;

    protected function getViewStructure() {
        return [
            //Example
            /*'list' => [
                'sidebar' => [
                    'preset' => 'list',
                ],
                'table' => [
                    'sort'   => 'name',
                    'fields' => [
                        'name' => [
                            'header' => 'Название',
                        ],
                        'contacts' => [
                            'header' => 'Контакты',
                            'class' => 'mb-hide',
                            'filter' => function() {
                                return 'asdasd';
                            }
                        ],
                    ],
                ],
            ],
            'edit' => [
                'sidebar' => [
                    'preset' => 'edit'
                ],
                'form' => [
                    ['id'],
                    [
                        'type'     => 'panel',
                        'name'     => 'Основные параметры',
                        'children' => 'name',
                    ],
                ],
            ],*/
        ];
    }

    public function copyAction()
    {
        $id = (int) $this->params()->fromRoute('id');

        $model = $this->getModel();
        $model->id($id)->load();

        $model = clone $model;
        $model->set('name', $model->name . ' (Копия)');
        $model->save();

        return $this->redirect()->toUrl($this->getEditUrl($model));
    }

    /**
     * @return TableService
     */
    protected function getService()
    {
        $serviceClassName = $this->getNS('Service', $this->module()->section() . 'Service');

        if(class_exists($serviceClassName)) {
            $service = $this->getServiceManager()->get($serviceClassName);
        } else {
            $service = $this->getServiceManager()->get(TableService::class);
        }

        return $service;
    }

    public function autocompleteAction()
    {
        $query = $this->params()->fromQuery('query');

        $collection = $this->getModel()->getCollection();
        $collection->select()
            ->limit(10)
            ->where->like('name', '%' . $query . '%');

        $resp = [];
        foreach ($collection as $item) {
            $name = str_replace(['"'], [''], $item->get('name'));

            $resp[] = [
                'id'    => $item->id(),
                'label' => $name,
                'value' => $name,
            ];
        }

        return new JsonModel($resp);
    }

    public function getListDataAction()
    {
        $collection = $this->getModel()->getCollection();
        $collection->select()->order('name');

        $resp = [];
        foreach($collection as $item) {
            $resp[] = [
                'id'    => $item->id(),
                'label' => $item->get('name'),
            ];
        }

        return new JsonModel($resp);
    }

    public function indexAction()
    {
        return $this->listAction();
    }

    public function listAction()
    {
        $view = $this->initLayout();
        $page = (int) $this->params()->fromQuery('page', 1);
        $module = $this->module();

        $structure = [
            'table' => $this->getViewStructure()['list']['form'] ?? [
                    'fields' => [
                        'name' => [
                            'header' => 'Название',
                        ],
                    ],
                ],
            'sidebar' => $this->getViewStructure()['list']['sidebar'] ?? [
                    'preset' => 'list',
                ],
        ];

        $data = $this->params()->fromPost();
        $data['sort'] = $data['sort'] ?? ($this->getViewStructure()['list']['table']['sort']);

        if($this->request->isPost()) {
            $collection = $this->getService()->getCollection($data);

            if($collection instanceof EntityCollectionHierarchy) {
                $collection->setParentId(null);
            }

            return new JsonModel(['html' => $this->getViewHelper('adminTableList')($collection, [
                'fields'    => $structure['table']['fields'],
                'wrapper'   => false,
            ])]);
        }

        $paginator = $this->getService()->getPaginator($page, $data);

        $view->setVariables([
            'structure' => $structure,
            'items'     => $paginator,
            'options'   => [
                'fields'    => $structure['form'],
                'wrapper'   => true,
            ],
         ]);

        $this->getViewHelper('adminSidebar')->setOptions([
                'class'  => 'table-edit-' . strtolower($module->model()),
                'header' => $module->get('name'),
            ] + $structure['sidebar']);

        $view->setTemplate($this->getTemplate('list'));

        return $view;
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id');

        $module = $this->module();
        $model = $this->getModel();
        $model->id($id);

        if(!$model->load()) {
            $newModel = $this->getModel()->save();
            return $this->redirect()->toUrl($this->getEditUrl($newModel));
        }

        $editForm = $this->getEditForm()
            ->setOptions(['model' => $model]);
        $editForm->init();

        $editForm->get('id')->setValue($id);

        if($this->getRequest()->isPost()) {
            $editForm->setData(array_merge_recursive(
                $this->params()->fromPost(),
                $this->params()->fromFiles()
            ));

            $editForm->setFilters();

            if($editForm->isValid()) {
                $model = $this->getService()->saveModel($editForm->getData());

                return new JsonModel([
                    'status' => 1,
                    'id'     => $model->id(),
                    //'data'   => $model->serializeArray()
                ]);
            } else {
                return new JsonModel([
                    'status' => 0,
                    'id'     => $model->id(),
                    'errors' => $editForm->getMessages()
                ]);
            }
        } else {
            //$editForm->setData($model->serializeArray(3));
            $editForm->setDataFromModel();
        }

        $view = $this->initLayout();

        if(!$header = $model->get('name')) {
            $header = 'Новая запись';
        }

        if(!$this->isAjax()) {
            $this->layout()->getVariable('meta')->title = $header;
            $this->layout()->setVariable('header', $header);
        }

        $breadcrumbs = $this->layout()->getVariable('breadcrumbs');
        $breadcrumbs[] = ['url' => '/', 'name' => $header];
        $this->layout()->setVariable('breadcrumbs', $breadcrumbs);

        $structure = [
            'form' => $this->getViewStructure()['edit']['form'] ?? [
                ['id'],
                [
                    'type'     => 'panel',
                    'name'     => 'Основные параметры',
                    'children' => 'name',
                ],
            ],
            'sidebar' => $this->getViewStructure()['edit']['sidebar'] ?? [
                'preset' => 'edit',
            ],
        ];

        $view->setVariables([
            'structure' => $structure,
            'form'      => $editForm,
            strtolower($module->model()) => $model,
        ]);

        $this->getViewHelper('adminSidebar')->setOptions([
            'class'  => 'table-edit-' . strtolower($module->model()),
        ] + $structure['sidebar']);

        $view->setTemplate($this->getTemplate('edit'));

        return $view;
    }

    public function searchQueriesAction()
    {
        $data = $this->params()->fromPost();

        $templatePage = new Template();
        $tempOpts = $templatePage->setSelector($data['module'] . '/' . $data['section'] . '/list');

        $options = $tempOpts->get('options');

        if(!$options->search) {
            $options->search = (object)['queries' => []];
        }
        $queries  = (array) $options->search->queries;
        $cQuery   = $data['query'];
        $action   = $data['action'];

        foreach ($queries as $key => $query) {
            if($query == $cQuery) {
                if($action == 'del') {
                    unset($queries[$key]);
                    break;
                } else {
                    return new JsonModel(['errors' => 'Такая запись уже существует']);
                }
            }
        }
        if($action == 'add') {
            $queries[] = $cQuery;
        }
        $options->search->queries = $queries;
        $tempOpts->save();

        return new JsonModel(['error' => 0]);
    }

    public function deleteAction()
    {
        $status = false;
        $id = (int) $this->params()->fromRoute('id');

        $model = $this->getModel();

        if($id && $model->id($id)->load()) {
            $model->remove();
            $status = true;
        }

        return new JsonModel([
            'status' => (int) $status
        ]);
    }

    public function addHitAction()
    {
        $id = $this->params()->fromPost('id');

        $model = $this->getModel();

        if(!$model->hasProperty('hits')) {
            return new JsonModel(['status' => 'no property "hits"']);
        }

        $model->select()->columns(['id', 'hits']);
        $model->id($id)->load();
        $model->set('hits', $model->hits + 1);
        $model->save();

        return new JsonModel(['status' => 'success']);
    }

    /**
     * @return Form
     */
    protected function getEditForm()
    {
        $editFormClass = $this->getNS('Form', $this->module()->section() . 'EditForm');
        return new $editFormClass;
    }

    /**
     * @return Entity
     */
    protected function getModel()
    {
        $model = $this->module()->model(false, 'Admin');
        return new $model;
    }

    /**
     * @param string $path
     * @param string $class
     * @return string
     */
    protected function getNS($path = '', $class = '')
    {
        $ns = $this->module()->module() . '\\Admin\\';

        $ns .= $path  ? $path . '\\' : '';
        $ns .= $class ? $class : '';

        return $ns;
    }

    public function getEditUrl($model)
    {
        $module = $this->module();
        $url = ADMIN_PREFIX . '/' . $module->module();

        if($module->section() != $module->module()) $url .= '-' . $module->section();

        $url .= '/edit/' . $model->id() . '/';

        return strtolower($url);
    }

    protected function getTemplate($file) {
        $module = $this->module();

        $template = strtolower($module->module() . '/admin/' . $module->section() . '/' . $file);

        if(!file_exists(MODULE_DIR . '/' . $module->module() . '/view/' . $template . '.phtml')) {
            $template = 'application/admin/default/' . $file;
        }

        return $template;
    }
}