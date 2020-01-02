<?php
namespace Orders\Admin\Form;

use Pipe\Form\Filter\FArray;
use Pipe\Form\Form\Admin\Form;
use Clients\Admin\Model\Client;
use Documents\Admin\Model\Document;
use Zend\InputFilter\Factory as InputFactory;

use Zend\Form\Element as ZElement;
use Pipe\Form\Element as PElement;

class OrderDocumentsForm extends Form
{
    public function init()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $order = $this->getModel();

        $this->add([
            'name'  => 'order_id',
            'type'  => ZElement\Hidden::class,
        ]);

        $this->add([
            'name'  => 'document_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label'   => 'Шаблон',
                'options' => Document::getEntityCollection(),
            ]
        ]);

        $clients = Client::getEntityCollection();
        $clients->select()
            ->join(['oc' => 'orders_clients'], 't.id = oc.client_id', [])
            ->where(['oc.depend' => $order->id()]);

        $this->add([
            'name'  => 'client_id',
            'type'  => PElement\ESelect::class,
            'options' => [
                'label'   => 'Контрагент',
                'options' => $clients,
            ]
        ]);

        return $this;
    }

    public function setFilters()
    {
        $inputFilter = $this->getInputFilter();
        $factory     = new InputFactory();

        /*$inputFilter->add($factory->createInput([
            'name'     => 'proposal',
            'filters'  => [new Pipe()],
        ]));*/

        return $this;
    }
}