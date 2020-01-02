<?php
namespace Pipe\Mvc\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;

class AbstractService
{
    /**
     * @var DbAdapter
     */
    static protected $adapter;

    /**
     * @var Sql
     */
    static protected $sql;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @return Sql
     */
    protected function getSql()
    {
        if(!self::$sql) {
            self::$sql = new Sql(self::getAdapter());
        }

        return self::$sql;
    }

    /**
     * @return DbAdapter
     */
    protected function getAdapter()
    {
        if(!self::$adapter) {
            self::$adapter = StaticDbAdapter::getStaticAdapter();
        }

        return self::$adapter;
    }

    /**
     * @param $select
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function execute($select)
    {
        return self::$sql->prepareStatementForSqlObject($select)->execute();
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}