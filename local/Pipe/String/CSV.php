<?php
namespace Pipe\String;

class CSV
{
    static public function arrayToCSV(array $table, $cellDelimiter = ',', $rowDelimiter = "\n", $enclosure = '"', $encloseAll = false) {
        $delimiter_esc = preg_quote($cellDelimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $rows = array();
        foreach($table as $row) {
            $cells = array();
            foreach ($row as $cell) {
                if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $cell)) {
                    $cells[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $cell) . $enclosure;
                }
                else {
                    $cells[] = $cell;
                }
            }
            $rows[] = implode($cellDelimiter, $cells);
        }

        return implode($rowDelimiter, $rows);
    }
}