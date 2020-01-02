<?php
namespace Documents\Admin\Controller;

use Pipe\Mvc\Controller\Admin\TableController;
use Clients\Admin\Model\Client;
use Documents\Admin\Model\Document;
use Orders\Admin\Model\Order;

class DocumentsController extends TableController
{
    public function getTemplateAction()
    {
        $id = $this->params()->fromRoute('id');

        $model = new Document(['id' => $id]);

        $this->getService()->outputFile($model->get('name'), $model->getFile());
    }

    public function getFilledAction()
    {
        $data = $this->params()->fromQuery();

        $document = new Document(['id' => $data['document_id']]);
        $kontragent = new Client(['id' => $data['client_id']]);
        $order = new Order(['id' => $data['order_id']]);

        $doc = $this->getService()->getFilledDocument($document, $kontragent, $order);

        $this->getService()->outputFile($document->get('name'), $doc);
    }
}