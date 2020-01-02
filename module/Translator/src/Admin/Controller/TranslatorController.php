<?php
namespace Translator\Admin\Controller;

use Pipe\Mvc\Controller\Admin\AbstractController;
use Translator\Admin\Model\Translator;
use Translator\Admin\Model\Translate;
use Zend\View\Model\JsonModel;

class TranslatorController extends AbstractController
{
    public function indexAction()
    {
        $this->initLayout();

        $translatorList = Translate::getEntityCollection();
        $translatorList->select()
            ->order('id DESC');

        return [
            'items' => $translatorList
        ];
    }

    public function syncAction()
    {
        $save = function ($lang) {
            $translatorList = Translate::getEntityCollection();
            $translatorList->select()
                ->order('id DESC')
                ->where
                ->notEqualTo('code', '')
                ->notEqualTo('ru', '')
                ->notEqualTo($lang, '');

            $arr = [];

            foreach ($translatorList as $row) {
                $row->set('code', Translator::getCode($row->get('ru')))->save();

                $str = $row->get($lang);
                if(strpos($str, '<p>') === false) {
                    $str = str_replace(["\n", "\r"], ['<br>', ''], $str);
                }

                $arr[$row->get('code')] = $str;
            }

            file_put_contents(MODULE_DIR . '/Translator/translates/' . $lang . '.php', '<?php return ' . var_export($arr, true) . ';');
        };

        $save('en');
        $save('de');

        return new JsonModel([]);
    }

    public function updateAction()
    {
        $data = $this->params()->fromPost();
        $id = $data['id'];

        $translator = new Translate(['id' => $id]);
        $translator->load();
        $translator->set($data['lang'], $data['text']);

        $translator->save();

        return new JsonModel([
            'id' => $translator->id()
        ]);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        $translator = new Translate(['id' => $id]);
        $translator->remove();
        return new JsonModel([]);
    }

    /**
     * @return \Translator\Service\SystemService
     */
    protected function getSystemService()
    {
        return $this->getServiceManager()->get('Translator\Service\SystemService');
    }
}