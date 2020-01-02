<?php
namespace Pipe\Form\Filter;


use Zend\Filter\AbstractFilter;

class FArray extends AbstractFilter
{
    public function filter($data)
    {
        return $data;
    }
}