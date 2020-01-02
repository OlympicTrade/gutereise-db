<?php
namespace Guides\Admin\Form;

use Application\Admin\Model\Settings;
use Pipe\Form\Form\Admin\Form;
use Museums\Admin\Model\Museum;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class GuidesEditForm extends Form
{
    public function init()
    {
        $this->addCommonElements(['id', 'fio', 'contacts', 'comment']);

        $this->add(array(
            'name' => 'price',
            'type'  => ZElement\Text::class,
            'options' => array(
                'label' => 'Тариф (руб.в час)',
            ),
        ));

        $this->add([
            'name' => 'user_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label' => 'Связь с аккаунтом',
                'model' => $this->getModel()->plugin('user'),
                'empty' => 'Не выбран',
                'sort'  => 'name',
            ]
        ]);

        $this->add([
            'name'  => 'languages',
            'type'  => PElement\ECollection::class,
            'options' => [
                'fields'    => $fields = [
                    'lang_id' => [
                        'name' => 'Язык',
                        'options' => Settings::getInstance()->plugin('languages'),
                    ],
                ],
                'plugin'  => $this->getModel()->plugin('languages'),
            ],
        ]);

        $this->add([
            'name'  => 'museums',
            'type'  => 'Pipe\Form\Element\ECheckbox',
            'options' => [
                'label'      => 'Экскурсии по музеям',
                'model'      => $this->getModel()->plugin('museums'),
                'collection' => Museum::getEntityCollection(),
            ],
        ]);
    }
}