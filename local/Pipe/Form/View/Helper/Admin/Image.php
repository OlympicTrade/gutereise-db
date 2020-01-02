<?php
namespace Pipe\Form\View\Helper\Admin;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class Image extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $element->setAttribute('class', 'fm');

        $model = $element->getOption('model');

        $html =
            '<div class="image-form">'
                . '<div class="left-col">'
                    .'<a class="pic-box popup-image" href="' . $model->getImage('hr') . '"><img src="' . $model->getImage('a') . '" alt="' . $model->getDesc() . '"></a>'
                . '</div>'
                . '<div class="info">'
                    . '<div class="row file">'
                        . '<input type="text" name="' . $element->getName() . '[filePath]" placeholder="Файл">'
                        . ' <input type="button" class="btn btn-green" onclick="showFileManager(this)" value="Обзор">'
                    . '</div>'
                    . '<div class="row">'
                        . '<input type="text" name="' . $element->getName() . '[desc]" value="' . $model->getDesc() . '" placeholder="Описание (alt)">'
                        . '<div class="tooltip">'
                            . '<div class="tooltip-icon"><i class="fa fa-question-circle"></i></div>'
                            . '<div class="tooltip-desc">'
                                . 'Что на изображении?'
                            . '</div>'
                        . '</div>'
                    . '</div>'
                    . '<div class="row">'
                        . '<input type="text" name="' . $element->getName() . '[fileName]" value="' . $model->getFileName() . '" placeholder="Имя файла">'
                        . '<div class="tooltip">'
                            . '<div class="tooltip-icon"><i class="fa fa-question-circle"></i></div>'
                            . '<div class="tooltip-desc">'
                                . 'Разрешены латинские символы и цифры'
                            . '</div>'
                        . '</div>'
                    . '</div>'
                    . ($model->hasImage() ? '<div class="row-del"><input type="checkbox" name="' . $element->getName() . '[del]"> удалить фото</div>' : '')
                . '</div>'
            . '</div>';

        return $html;
    }

    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }
}