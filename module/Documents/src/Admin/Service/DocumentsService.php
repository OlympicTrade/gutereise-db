<?php
namespace Documents\Service;

use Application\Common\Model\Settings;
use Pipe\Mvc\Service\Admin\TableService;
use Pipe\String\Date as DateStr;
use \Pipe\DateTime\Date;
use Pipe\String\Names;
use Pipe\String\Price;
use Orders\Admin\Model\Order;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class DocumentsService extends TableService
{
    public function getFilledDocument($document, $client = null, $order = null)
    {
        $templateProcessor = new TemplateProcessor($document->getFile());

        $dt = Date::getDT('NOW');
        $vals = [
            'date_str'     => $dt->format('«d»')  . ' ' . DateStr::$months2[$dt->month(false)] . ' ' . $dt->year() . ' г.',
            'contract_nbr' => $dt->format('ymdh'),
        ];

        $company = Settings::getInstance();
        $vals += $this->companyData($company, 'comp_');

        if($client) {
            $vals += $this->companyData($client, 'kontr_');
        }

        if($order) {
            $vals += $this->orderData($order);
        }

        //$errors = '';
        foreach ($vals as $key => $val) {
            if(!$val) unset($vals[$key]);
            //$errors .= 'Нет данных для параметра ' . $key . '<br>';
        }
        //if($errors) die($errors);


        foreach ($vals as $key => $val) {
            if(is_string($val)) {
                $templateProcessor->setValue($key, $val);
            } else {
                $templateProcessor->setComplexBlock($key, $val);
            }
        }

        return $templateProcessor;
    }

    protected function orderData(Order $order)
    {
        $br = '<w:br/>';
        $vals = [];

        $pData = $this->getOrdersService()->getOrderProposalData($order, ['br' => $br]);

        $programm = new TextRun();
        $i = 0;
        $daysCount = count($pData['days']);
        foreach ($pData['days'] as $day) {
            $i++;

            if($day['header'])  {
                $programm->addText($day['header'], ['bold' => true]);
                $programm->addText($br);
            }

            $programm->addText($day['timetable'] . $br);

            if($i > 1 && $i != $daysCount) {
                $programm->addText($br);
            }
        }

        $vals['order_program']   = $programm;
        $vals['order_group']     = $pData['group'];
        $vals['order_price']     = Price::nbrToStr($order->get('income'));
        $vals['order_price_txt'] = Price::priceToWords($order->get('income'));
        $vals['order_summary']   = $pData['summary'];

        return $vals;

        $table = new Table(['borderSize' => 1, 'borderColor' => 'black', 'width' => 10000, 'unit' => TblWidth::TWIP]);
        $table->addRow();
        $table->addCell(150)->addText('Cell A1');
        $table->addCell(150)->addText('Cell A2');
        $table->addCell(150)->addText('Cell A3');
        $table->addRow();
        $table->addCell(150)->addText('Cell B1');
        $table->addCell(150)->addText('Cell B2');
        $table->addCell(150)->addText('Cell B3');


        $title = new TextRun();
        //$title->setParagraphStyle(['align' => 'center']);
        $title->addText('This title has been set ', ['bold' => true,]);
        $title->addText('<w:br/>');
        $title->addText('Воровской узел — крайне ненадёжный' . $br . 'верёвочный узел, напоминает прямой узел и более того' . $br . ',
         неотличим от него, в случае если не видны ходовые концы' . $br . ' верёвки. На этом единственном полезном свойстве',
            array('bold' => true, 'italic' => true, 'color' => 'red', 'underline' => 'single'));

        $vals['test'] = $title;

        return $vals;
    }

    protected function companyData($model, $prefix)
    {

        $cd = $model->get('company_details');

        $br = '<w:br/>';

        $orgForms = [
            ''    => '',
            'ИП'  => 'Индивидуальный предприниматель',
            'ООО' => 'Общество с ограниченной ответственностью',
            'ОАО' => 'Публичное акционерное общество',
            'ЗАО' => 'Непубличное акционерное общество',
        ];

        $vals[$prefix . 'org_form'] = $cd->org_form;
        $vals[$prefix . 'org_form_full'] = $orgForms[$cd->org_form];

        $vals[$prefix . 'name'] = $cd->company_name;

        $vals[$prefix . 'name_full'] = $cd->org_form . ' ' .
            (array_key_exists($cd->org_form, ['', 'ИП']) ? $cd->company_name : '«' . $cd->company_name . '»');

        $vals[$prefix . 'dir_name'] = $cd->director_name . ' ' . $cd->director_surname . ' ' . $cd->director_patronymic;

        $decl = new Names();
        $vals[$prefix . 'dir_name_decl'] =
            $decl->qSecondName($cd->director_surname, 1) . ' ' .
            $decl->qFirstName($cd->director_name, 1) . ' ' .
            $decl->qFatherName($cd->director_patronymic, 1);

        $vals[$prefix . 'dir_name_short'] = $cd->director_surname . ' ' . mb_substr($cd->director_name, 0, 1) . '. ' .
            mb_substr($cd->director_patronymic, 0, 1) . '.';

        if(array_key_exists($cd->org_form, ['', 'ИП'])) {
            $vals[$prefix . 'dir_name_full'] = 'Генеральный директор ' . $br . $vals[$prefix . 'dir_name_short'];
        } else {
            $vals[$prefix . 'dir_name_full'] = $vals[$prefix . 'dir_name_short'];
        }

        //Реквизиты
        $companyDetails = '';

        switch ($cd->org_form) {
            case 'ИП':
                $companyDetails .=
                    'ИНН: ' . $cd->inn . $br .
                    'ОГРНИП: ' . $cd->ogrn . $br;
            case '':
                $companyDetails .=
                    'Пасспорт: ' . $cd->passport . $br .
                    'Паспорт выдан: ' . $cd->passport_issued . $br .
                    'Дата выдачи: ' . $cd->passport_date . $br .
                    'Дата рождения: ' . $cd->birthday . $br;
                break;
            default:
                $companyDetails .=
                    'ИНН: ' . $cd->inn . $br .
                    'КПП: ' . $cd->kpp . $br .
                    'ОГРН: ' . $cd->ogrn . $br;
                break;
        }

        if($cd->org_form) {
            $companyDetails .=
                'Банк: ' . $cd->bank . $br .
                'БИК: ' . $cd->bik . $br .
                'Р/С: ' . $cd->rs . $br .
                'К/С: ' . $cd->ks . $br;
        }

        if($cd->reg_address == $cd->fact_address) {
            $companyDetails .=
                'Юридический и фактический адрес: ' . $cd->fact_address . $br;
        } else {
            $companyDetails .=
                'Юридический адрес: ' . $cd->reg_address . $br .
                'Фактический адрес: ' . $cd->fact_address . $br;
        }

        $companyDetails .=
            'Тел: ' . implode(', ', $model->getPhones()) . $br .
            'E-mail: ' . implode(', ', $model->getEmails()) . $br ;

        $vals[$prefix . 'details'] = $companyDetails;

        //dd($vals);
        return $vals;
    }

    protected function saveModelAfter($model, $data)
    {
        if($data['file']) {
            $source = $data['file']['tmp_name'];
            rename($source, $model->getFile(false));
            unlink($source);
        }
    }

    public function outputFile($filename, $file)
    {
        if(is_string($file)) {
            $file = IOFactory::load($file);
        }

        header( 'Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document');
        header( 'Content-Disposition: attachment; filename=' . $filename . '.docx');

        if($file instanceof PhpWord) {
            $file->save('php://output');
        } else {
            $file->saveAs('php://output');
        }

        die();
    }

    /**
     * @return \Orders\Admin\Service\OrdersService
     */
    protected function getOrdersService()
    {
        return $this->getServiceManager()->get('Orders\Admin\Service\OrdersService');
    }
}