<?php
namespace Documents\Admin\Model;

use Pipe\Db\Entity\Entity;

class Document extends Entity
{
    static public function getFactoryConfig()
    {
        return [
            'table'      => 'documents',
            'properties' => [
                'name'    => [],
                'file'    => [],
            ],
        ];
    }

    public function getFile($checkExistence = true)
    {
        $file = DATA_DIR . '/uploads/documents/doc-' . $this->id() . '.docx';

        return file_exists($file) || $checkExistence ? $file : false;
    }

    public function getPublicFile()
    {
        return $this->getFile() ? '/documents/get-template/' . $this->id() . '/' : false;
    }
}