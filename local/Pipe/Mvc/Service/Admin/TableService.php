<?php
namespace Pipe\Mvc\Service\Admin;

use Application\Common\Model\Module;
use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;
use Pipe\Mvc\Service\AbstractService;
use Pipe\String\Translit;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceManager;

class TableService extends AbstractService
{
    public function saveModel($data)
    {
        $model = $this->getModel()
            ->id($data['id'])
            ->load()
            ->unserializeArray($data);

        $model = $this->saveModelBefore($model, $data);

        $model->save();

        $this->saveModelAfter($model, $data);

        return $model;
    }

    protected function saveModelBefore($model, $data)
    {
        return $model;
    }

    protected function saveModelAfter($model, $data)
    {

    }

    /**
     * @param array $filters
     * @return EntityCollection
     */
    public function getCollection($filters = [])
    {
        $collection = $this->getModel()->getCollection();

        $collection->select()->order($filters['sort'] ?? 't.name');

        $collection = $this->setFilters($collection, $filters);

        return $collection;
    }

    /**
     * @param $page
     * @param array $filters
     * @return Paginator
     */
    public function getPaginator($page, $filters = [])
    {
        return $this->getCollection($filters)->getPaginator($page, 60);
    }

    /**
     * @param EntityCollection $collection
     * @param $filters
     * @return EntityCollection
     */
    protected function setFilters(EntityCollection $collection, $filters)
    {
        if($filters['query']) {
            $queries = Translit::searchVariants($filters['query']);

            $where = new Where();
            foreach ($queries as $query) {
                $where->like('name', '%' . $query . '%')->or;
            }

            $collection->select()->where->addPredicate($where);
        }

        return $collection;
    }

    /**
     * @return Module
     */
    protected function module()
    {
        return Module::getInstance();
        //return $this->getServiceManager()->get('Module');
    }

    /**
     * @return Entity
     */
    public function getModel()
    {
        $model = $this->module()->model(false, 'Admin');

        return new $model;
    }

    /** @var ServiceManager */
    protected $serviceManager = null;
    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @param string $path
     * @param string $class
     * @return string
     */
    protected function getNS($path = '', $class = '')
    {
        $ns = $this->module()->module() . '\\Admin\\';

        $ns .= $path ?: $path . '\\';
        $ns .= $class ?: $class;

        return $ns;
    }
}