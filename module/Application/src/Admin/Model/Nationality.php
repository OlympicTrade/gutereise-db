<?php
namespace Application\Admin\Model;

class Nationality
{
    const NATIONALITY_ALL       = 3;
    const NATIONALITY_RUSSIAN   = 1;
    const NATIONALITY_FOREIGN   = 2;

    static public $nationalityType = [
        self::NATIONALITY_ALL       => 'Все',
        self::NATIONALITY_RUSSIAN   => 'Русские',
        self::NATIONALITY_FOREIGN   => 'Иностранцы',
    ];

    static public function langToNationality($langId)
    {
        return [
            $langId != 1 ? Nationality::NATIONALITY_FOREIGN: Nationality::NATIONALITY_RUSSIAN,
            Nationality::NATIONALITY_ALL
        ];
    }

    static public function isForeigners($langId)
    {
        return $langId != 1;
    }
}