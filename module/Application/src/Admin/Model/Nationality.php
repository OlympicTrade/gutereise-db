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

    /*static public $nationalityLangs = [
        1  => 'Русский',
        2  => 'Немецкий',
        3  => 'Английский',
        4  => 'Французский',
        5  => 'Испанский',
        6  => 'Итальянский',
        10 => 'Китайский',
    ];

    static public $languagesDeclension = [
        1  => ['Русский', 'Русском', 'Русским'],
        2  => ['Немецкий', 'Немецком', 'Немецким'],
        3  => ['Английский', 'Английском', 'Английским'],
        4  => ['Французский', 'Французском', 'Французским'],
        5  => ['Испанский', 'Испанском', 'Испанским'],
        6  => ['Итальянский', 'Итальянском', 'Итальянским'],
        10 => ['Китайский', 'Китайском', 'Китайским'],
    ];*/

    static public function langToNationality($langId)
    {
        return $langId != 1 ? Nationality::NATIONALITY_FOREIGN: Nationality::NATIONALITY_RUSSIAN;
    }

    static public function isForeigners($langId)
    {
        return $langId != 1;
    }
}