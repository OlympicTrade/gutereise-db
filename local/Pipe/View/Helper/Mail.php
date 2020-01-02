<?php
namespace Pipe\View\Helper;

//use Translator\Model\Translator;
use Zend\View\Helper\AbstractHelper;

class Mail extends AbstractHelper
{
    /**
     * @var Translator
     */
    //protected $translator;

    protected $font = [
        'font-family' => 'Helvetica, Arial, sans-serif !important',
        'color'  => '#000000 !important'
    ];

    public function __invoke($elements)
    {
        //$this->translator = $this->getView()->translator;

        $html = '';

        foreach ($elements as $options) {
            if(is_string($options)) {
                $html .= $options;
                continue;
            }

            $tag = array_shift($options);

            $children = '';

            if(is_array($options['tags'])) {
                $children = $this->__invoke($options['tags']);
                unset($options['tags']);
            } elseif(is_string($options['tags'])) {
                $children = $options['tags'];
                unset($options['tags']);
            }

            $options = $options + ['style' => []];

            switch ($tag) {
                case 'table':
                    $html .= $this->table($options, $children);
                    break;
                case 'td':
                    $html .= $this->td($options, $children);
                    break;
                case 'btn':
                    $html .= $this->btn($options, $children);
                    break;
                case 'row':
                    $html .= $this->row($options, $children);
                    break;
                default:
                    $html .= $this->tag($tag, $options, $children);
                    break;
            }
        }

        return $html;
    }

    protected function row($options, $children)
    {
        $options['style'] += [
            'padding-left'  => '30px',
            'padding-right' => '30px',
        ];

        $options += [
            'bgcolor' => '#ffffff'
        ];

        $children =
        '<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0">'.
            '<tbody>'.
                '<tr>'.
                    $children.
                '</tr>'.
            '</tbody>'.
        '</table>';

        return '<tr>' . $this->tag('td', $options, $children) . '</tr>';
    }

    protected function table($options, $children)
    {
        $options += [
            'width' => '100%',
            'align' => 'center',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'border'      => '0',
        ];

        $options['style'] += [
            'border' => 'none'
        ];

        $children = '<tbody>' . $children . '</tbody>';

        return $this->tag('table', $options, $children);
    }

    protected function btn($options, $children)
    {
        $options['style'] += $this->font + [
            'font-size' => '14px',
            'padding-bottom' => '13px',
            'color' => '#000000',
            'text-decoration' => 'none',
            'padding' => '12px 25px',
            'display' => 'inline-block',
            'border' => 'none',
            'background-color' => '#fcd828',
        ];

        $options += [
            'class' => 'btn'
        ];

        return $this->tag('a', $options, $children);
    }

    protected function td($options, $children)
    {
        $options += [
            'align' => 'center'
        ];

        if($options['background']) {
            $options['style']['background-image'] = 'url(\'' . $options['background'] . '\')';
        }

        if($options['bgcolor']) {
            $options['style']['background-color'] = $options['bgcolor'];
        }


        return $this->tag('td', $options, $children);
    }

    protected function tag($tag, $options, $children)
    {
        if($options['text']) {
            $defaultStyle = $this->font;
        } else {
            $defaultStyle = [];
        }

        switch ($tag) {
            case 'p':
                $defaultStyle += $defaultStyle + [
                    'font-size' => '14px',
                    'padding-bottom' => '10px',
                    'margin' => '0 !important',
                ];

                break;
            case 'h2':
                $defaultStyle += [
                    'font-size' => '18px',
                    'padding-bottom' => '14px',
                    'margin' => '0 !important',
                ];
                break;
            case 'a':
                $defaultStyle += [
                    'font-size' => '14px',
                    'text-decoration' => 'none',
                ];
                break;
        }

        $style = $options['style'] + $defaultStyle;
        unset($options['style']);

        if($options['text']) {
            //$children = $this->translator->translate($options['text']);
            $children = str_replace(['<p>', '<h2>'], ['<p style="font-size: 15px;">', '<h2>'], $options['text']);
        }
        unset($options['text']);

        $attrs = '';
        foreach ($options as $key => $val) {
            $attrs .= $this->attr($key, $val);
        }

        $html =
            '<' . $tag  . $this->style($style) . $attrs . '>' .
                $children.
            (!in_array($tag, ['img', 'br']) ? '</' . $tag  . '>' : '');

        return $html;
    }

    protected function style($styleArr) {
        if(!is_array($styleArr) || !$styleArr) {
            return '';
        }

        $style = '';

        foreach ($styleArr as $key => $val) {
            $style .= $key . ': ' . $val . '; ';
        }

        return ' style="' . $style . '"';
    }

    protected function attr($key, $val, $default = '') {
        if(is_array($val)) {
            if(!isset($val[$key])) {
                $val = '';
            } else {
                $val = $val[$key];
            }
        }

        if($val === '') {
            if(!$default) {
                return '';
            }
            $val = $default;
        }

        return ' ' . $key . '="' . $val . '"';
    }
}