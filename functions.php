<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 21.01.14
 * Time: 00:07
 */

function parseGermanDate($date, $row = 0)
{
    global $valid;
    if (preg_match('/^\d{2}\.\d{2}\.20\d{2}$/', $date) === 0) {
        echo 'Datum in ' . $row . ' nicht valide.<br>';
        $valid = false;
    }
    $date = explode('.', $date);
    return mktime(0, 0, 0, $date[1], $date[0], $date[2]);
}

function replaceNames($name)
{
    global $names;
    foreach ($names as $long => $short) {
        $name = str_replace($long, $short, $name);
    }
    return $name;
}

function parseName($name)
{
    global $names, $gsb;
    $name = str_replace('&', ',', preg_replace('/\s+/', '', $name));
    if ($gsb) {
        if (strpos($name, 'Alle') === 0) {
            $pos = strpos($name, '\\');
            if ($pos > 0) {
                $without = explode(',', replaceNames(substr($name, $pos + 1)));
                $name = implode(',', array_diff($names, $without));
            }
        } else {
            $name = replaceNames($name);
        }
    }
    return $name;
}

function parseTime($time)
{
    return str_replace(',', '.', $time);
}

function checkData($data, $row)
{
    $valid = true;
    if (preg_match('/^\w+(,\s*\w+)*$/', $data[0]) === 0) {
        echo 'Name in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if ($data[3] > 5 || $data[3] < 1) {
        echo 'Angemessenheit (' . $data[2] . ') in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if ($data[4] > 5 || $data[4] < 1) {
        echo 'Schwierigkeit (' . $data[2] . ') in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if (!is_numeric($data[2])) {
        echo 'Zeit (' . $data[2] . ') in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if (!$valid) {
        echo 'Zeile ' . $row . ':';
        var_dump($data);
        echo '<br>';
    }
    return $valid;
}