<?php
namespace Translator\Admin\Model;

use Pipe\Db\Entity\Entity;
use Pipe\Db\Entity\EntityCollection;
use Pipe\StdLib\Singleton;
use Pipe\String\Numbers;

class Translator
{
    use Singleton;

    protected $langCode = 'ru';

    protected $translator = null;

    static public $codes = [
        'ru'  => 1,
        'de'  => 2,
        'en'  => 3,
        'fr'  => 4,
        'sp'  => 5,
        'it'  => 6,
        'ch'  => 10,
    ];

    static public $codesTranscript = [
        'ru'  => 'Русский',
        'de'  => 'Немецкий',
        'en'  => 'Английский',
        'fr'  => 'Французкий',
        'sp'  => 'Испанский',
        'it'  => 'Итальянский',
        'ch'  => 'Китайский',
    ];

    protected $files = [
        'ru'  => 'ru',
        'de'  => 'de',
        'en'  => 'en',
        'fr'  => 'en',
        'sp'  => 'en',
        'it'  => 'en',
        'ch'  => 'en',
    ];

    protected $translatesPath  = MODULE_DIR . '/Translator/translates/';
    protected $translatesTable = [];
    protected $declensionTable = [];

    public function __construct($langCode = null)
    {
        if($langCode) {
            $this->setLangCode($langCode);
            return;
        }
    }

    protected function updateTranslateTable()
    {
        $this->translatesTable = include($this->translatesPath . $this->files[$this->getLangCode()] . '.php');
    }

    public function setLangCode($langCode = 'default')
    {
        if($langCode == 'default') {
            $langCode = 'ru';
        }

        $this->langCode = $langCode;

        $this->updateTranslateTable();
    }

    public function getLangCode()
    {
        return $this->langCode;
    }

    public function getLangId()
    {
        return self::$codes[$this->langCode];
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    static public function removeModelFromTranslate($url) {
        $translates = Translate::getEntityCollection();
        $translates->select()
            ->where(['url' => $url]);

        $translates->remove();
    }

    static public function setModelEvents(Entity $model, $options = [])
    {
        $model->getEventManager()->attach([Entity::EVENT_PRE_DELETE], function ($event) {
            $model = $event->getTarget();

            Translator::removeModelFromTranslate($model->getUrl());
        });

        $model->getEventManager()->attach(
            [Entity::EVENT_POST_INSERT, Entity::EVENT_POST_UPDATE],
            function ($event) use ($options) {
                /** @var Entity $model */
                $model = $event->getTarget();

                $url = $model->getUrl();

                $strs = [];

                $objToTranslate = self::preparePlugins($model, $options);

                foreach ($objToTranslate as $data) {
                    $plugin = $data['model'];

                    if($data['include']) {
                        foreach($data['include'] as $key) {
                            $str = $plugin->get($key);
                            if(is_string($str) && strlen($str) != mb_strlen($str, 'utf-8')) {
                                $strs[] = $str;
                            }
                        }

                        continue;
                    }

                    foreach ($plugin as $key => $str) {
                        if(in_array($key, $data['exclude'])) {
                            continue;
                        }

                        if(is_string($str) && strlen($str) != mb_strlen($str, 'utf-8')) {
                            $strs[] = $str;
                        }
                    }
                };

                Translator::addToTranslate($strs, $url);

                return true;
            }
        );
    }

    static protected function preparePlugins($model, $opts) {
        $opts = $opts + [
                'include' => [],
                'exclude' => [],
                'plugins' => [],
            ];

        if($opts['exclude'] !== 'all') {
            $result[] = [
                'model'   => $model,
                'include' => (array) $opts['include'],
                'exclude' => (array) $opts['exclude'],
            ];
        } else {
            $result = [];
        }

        foreach ($opts['plugins'] as $pluginName => $pluginOpts) {
            $plugin = $model->plugin($pluginName);

            if($plugin instanceof EntityCollection) {
                foreach($plugin as $row) {
                    $result = array_merge($result, self::preparePlugins($row, $pluginOpts));
                }
            } else {
                $result = array_merge($result, self::preparePlugins($plugin, $pluginOpts));
            }
        }

        return $result;
    }

    static public function addToTranslate($strs, $parentUrl = '') {
        if(!$strs) return;
        $strs = array_unique((array) $strs);

        $oldTranslates = Translate::getEntityCollection();
        $oldTranslates->select()
            ->where(['url' => $parentUrl]);

        foreach ($oldTranslates as $translate) {
            $exists = false;
            foreach ($strs as $key => $str) {
                if(self::getCode($str) == $translate->get('code')) {
                    $exists = true;
                    unset($strs[$key]);
                }
            }
            if(!$exists) {
                $translate->remove();
            }
        }

        foreach ($strs as $str) {
            $translator = new Translate();
            $translator->setVariables([
                'url'    => $parentUrl,
                'ru'     => $str,
            ])->save();
        }
    }

    static public function getCode($str)
    {
        if(!$str) return '';

        $str = str_replace('<br>', '', $str);
        $codeStr = preg_replace("/[^\w]+/u", '', mb_strtolower($str));
        return hash('sha1', $codeStr);
    }

    public function tr($str)
    {
        $code = self::getCode($str);

        if(!array_key_exists($code, $this->translatesTable) || !($translate = $this->translatesTable[$code])) {
            return $str;
        }

        if($translate == $code) {
            return $str;
        }

        return $translate;
    }

    public function trt($str)
    {
        $code = self::getCode($str);
        //d($code . ' -> ' . $str);

        if(!array_key_exists($code, $this->translatesTable) || !($translate = $this->translatesTable[$code])) {
            return $str;
        }

        if($translate == $code) {
            return $str;
        }

        return $translate;
    }

    public function declension($number, $word, $showNbr = true)
    {
        if(!$this->declensionTable) {
            $this->declensionTable = include($this->translatesPath . 'declension.php');
        }

        return Numbers::declension($number, $this->declensionTable[$word], $this->getLangCode(), $showNbr);
    }
}