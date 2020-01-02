<?php

namespace Pipe\Mail;

//use Pipe\View\Helper\Translator as TrHelper;
//use Translator\Model\Translator;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;

class Mail
{
    protected $mail;

    /**
     * @var array
     */
    protected $variables = array();

    /** @var Translator */
    protected $translator;

    /** @var string */
    protected $header = '';

    /** @var \Zend\View\Renderer\PhpRenderer */
    protected $view;

    /** @var \Zend\View\Model\ViewModel */
    protected $viewModel;

    /**
     * @var \Zend\Mail\Transport\Smtp
     */
    protected $transport;

    /** @var array */
    protected $attachments = [];

    /** @var array|Message */
    protected $message = [];

    /** @var array */
    protected $options = [];
    
    public function __construct ()
    {
        $this->view = new PhpRenderer();

        $this->view->getHelperPluginManager()
            ->setFactory('tags', function () {
                return new \Pipe\View\Helper\Mail();
            })/*->setFactory('tr', function () {
                return new TrHelper();
            })*/;

        $this->message = new Message();
        $this->message->setEncoding('utf-8');
        $this->message->addFrom($this->options['sender']['email'], $this->options['sender']['name']);

        $this->transport = new Smtp();
        $this->transport->setOptions(new SmtpOptions($this->options['connection']));
    }
    
    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function addTo($email)
    {
        $this->message->addTo($email);

        return $this;
    }

    public function setTemplate($path)
    {
        $resolver = new TemplateMapResolver();

        $resolver->setMap(array(
            'mailLayout'    => MODULE_DIR . '/Application/view/mail/layout.phtml',
            'mailTemplate'  => $path
        ));

        $this->view->setResolver($resolver);

        return $this;
    }

    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }
/*
    public function setLangCode($langCode)
    {
        $this->translator = new Translator($langCode);

        return $this;
    }*/

    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;

        return $this;
    }

    public function setVariables($variables)
    {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }

    public function setAttachment($file)
    {
        $attachment = new MimePart(fopen($file, 'r'));
        $attachment->type = mime_content_type($file);
        $attachment->encoding    = Mime::ENCODING_BASE64;
        $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;

        $this->attachments[] = $attachment;

        return $this;
    }

    public function send()
    {
        /*if(!$this->translator) {
            $this->translator = new Translator();
            $this->translator->setLangCode();
        }*/

        $this->message->setSubject($this->header);
        //$this->message->setSubject($this->translator->translate($this->header));

        //Render message
        $viewModel = new ViewModel();
        $viewModel->setTemplate('mailTemplate');
        $viewModel->setVariables($this->variables);

        $domain = \Application\Model\Settings::getInstance()->get('domain');

        $viewModel->setVariables([
            'domain'     => $domain,
            'translator' => $this->translator,
        ]);

        //die($this->view->render($viewModel));

        //Render template
        $viewLayout = new ViewModel();
        $viewLayout->setTemplate('mailLayout')
            ->setVariables($this->variables)
            ->setVariables([
                'domain'     => $domain,
                //'translator' => $this->translator,
                'content'    => $this->view->render($viewModel),
            ]);

        //Send mail
		$html = $this->view->render($viewLayout);

        $html = new MimePart($html);
        $html->type = "text/html";
        $html->setCharset('utf-8');

        array_unshift($this->attachments, $html);

        $body = new MimeMessage();
        $body->setParts($this->attachments);

        $this->message->setBody($body);
        
        //if(MODE != 'dev') {
            $this->transport->send($this->message);
        //}
    }
}