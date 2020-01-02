<?php

namespace Translator\Service;

use Pipe\Db\Entity\ConfigCollector;
use Pipe\Mvc\Service\AbstractService;
use Translator\Admin\Model\Translator;
use Zend\Db\Sql\Where;

class TranslatorService extends TableService
{
    /*public function getPaginator($page, $filters)
    {
        $translator = ConfigCollector::collection(new Translator());

        $translator->select()->order('t.name');

        if(!empty($filters)) {
            if($filters['search']) {
                $translator->select()->where(function (Where $where) use($filters) {
                    $where->like('name', '%' . $filters['search'] . '%');
                });
            }
        }

        return $translator->getPaginator($page, 30);
    }*/
}