<?php
namespace Application\Common\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityHierarchy;
use Pipe\StdLib\Singleton;

class Module extends EntityHierarchy
{
    use Singleton;

    protected $module  = '';
    protected $section = '';
    protected $model   = '';

    static public function getFactoryConfig()
    {
        return [
            'table'      => 'site_modules',
            'properties' => [
                'parent'  => [],
                'name'    => [],
                'module'  => [],
                'section' => [],
                'options' => ['type' => Entity::PROPERTY_TYPE_JSON],
                'nav'     => ['type' => Entity::PROPERTY_TYPE_JSON],
                'sort'    => [],
            ],
        ];
    }

    protected function getLoadSelect($forced = false)
    {
        $select = parent::getLoadSelect(true);

        $select->where([
            'module'  => $this->module,
            'section' => $this->section,
        ]);

        return $select;
    }

    public function setModule($module)
    {
        $module = ucfirst($module);

        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function module()
    {
        return $this->module;
    }

    public function setSection($section)
    {
        if($section) $this->section = ucfirst($section);
        return $this;
    }

    /**
     * @return string
     */
    public function section()
    {
        if(!$this->section) {
            $this->section = $this->module();
        }

        return $this->section;
    }

    public function setModel($model)
    {
        if($model) $this->model = ucfirst($model);
        return $this;
    }

    /**
     * @param bool $short
     * @param string $platform
     * @return string
     */
    public function model($short = true, $platform = 'Common')
    {
        if(!$short) {
            $ns = $this->module() . '\\' . ucfirst($platform). '\\Model\\';
        }

        if(!$this->model) {
            $section = $this->section();

            if(class_exists($ns . $section)) {
                $this->model = $section;
            } elseif(substr($section, strlen($section) - 1) == 's') {
                $this->model = substr($section,0, strlen($section) - 1);
            } else {
                $this->model = $section;
            }
        }

        return $ns . $this->model;
    }
}

