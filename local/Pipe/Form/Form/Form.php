<?php
namespace Pipe\Form\Form;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;
use Pipe\Form\Element\EntityAware;
use Pipe\Form\Filter\FArray;
use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;
use Zend\Form\Form as ZendForm;
use Zend\InputFilter\Factory as InputFactory;

class Form extends ZendForm {
    /** @var String */
    protected $prefix;

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $this->setUseInputFilterDefaults(false);
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    protected $options = [
        'prefix' => ''
    ];

    /**
     * @param array $options
     * @return $this|ZendForm
     */
    public function setOptions($options = []) {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /** @return Entity */
    public function getModel()
    {
        return $this->options['model'];
    }

    /**
     * @return $this
     */
    public function init()
    {
        return $this;
    }

    public function getElName($field)
    {
        if(!($prefix = $this->getPrefix())) {
            return $field;
        }

        if(strpos($field, '[') === false) {
            return $prefix . '[' . $field . ']';
        }

        return $prefix . $field;
    }

    public function get($elName) {
        return parent::get($this->getElName($elName));
    }

    public function add($elData, array $flags = [])
    {
        if($elData instanceof ZElement) {
            $element = $elData;
        } else {
            $elData['type'] = $elData['type'] ?? ZElement\Text::class;
            $element = $this->getFormFactory()->create($elData);
        }

        $element->setName($this->getElName($element->getName()));

        parent::add($element, $flags);

        return $this;
    }


    public function setFilters()
    {
        $factory = new InputFactory();
        $filter = $this->getInputFilter();

        /** @var ZElement $element */
        foreach ($this->getElements() as $element) {
            $elName = $element->getName();

            if($filter->has($elName)) {
                continue;
            }

            $require = $element->getOption('require') ?? false;

            switch ($elName) {
                case 'id':
                    $filter->add($factory->createInput([
                        'name'     => 'id',
                        'required' => true,
                    ]));
                    continue;
                default:
            }

            if(strpos($elName, '[') > 1) {
                $filter->add($factory->createInput([
                    'name'     => $elName,
                    'required' => $require,
                ]));

                $filter->add($factory->createInput([
                    'name'     => substr($elName, 0, strpos($elName, '[')),
                    'required' => false,
                    'filters'  => [new FArray()],
                ]));
                continue;
            }

            if(in_array(get_class($element), [ZElement\Text::class, ZElement\Textarea::class])) {
                $filter->add($factory->createInput([
                    'name'     => $elName,
                    'required' => $require,
                    'filters'  => [
                        ['name' => 'StripTags'],
                        ['name' => 'StringTrim'],
                    ],
                ]));
                continue;
            }

            if(in_array(get_class($element), [
                PElement\EArray::class,
                PElement\ECollection::class,
                PElement\ECheckbox::class,
                PElement\Admin\CompanyDetails::class,
            ])) {
                $filter->add($factory->createInput([
                    'name'     => $elName,
                    'required' => $require,
                    'filters'  => [new FArray()],
                ]));
                continue;
            }

            $filter->add($factory->createInput([
                'name'     => $elName,
                'required' => $require,
            ]));
        }

        return $this;
    }

    public function setDataFromModel()
    {
        $model = $this->getModel();

        foreach ($this as $element) {
            $elName = $element->getName();

            if($this->prefix) {
                $elName = substr($elName, strlen($this->prefix));
            }

            $value = $model->getByTrace($elName);

            if($element instanceof EntityAware) {
                $element->setValue($value);
            } else {
                if($value instanceof Entity || $value instanceof EntityCollection) {
                    $element->setValue($value->serrializeArray(1));
                } else {
                    $element->setValue($value);
                }
            }
        }
    }
}