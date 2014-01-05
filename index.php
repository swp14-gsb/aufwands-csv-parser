<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 05.01.14
 * Time: 22:22
 */

$valid = true;

$names = Array(
    'Lukas' => 'LE',
    'Siggi' => 'SZ',
    'Christoph' => 'CS',
    'Elvira' => 'EA',
    'Felix' => 'FH',
    'Nico' => 'NG',
    'Nick' => 'NB',
);

asort($names);

function parseGermanDate($date, $row)
{
    global $valid;
    if (preg_match('/^\d{2}\.\d{2}\.201[34]$/', $date) === 0) {
        echo 'Datum in Zeile ' . $row . ' nicht valide<br>';
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
    global $names;
    $name = str_replace('&', ',', preg_replace('/\s+/', '', $name));
    if (strpos($name, 'Alle') === 0) {
        $pos = strpos($name, '\\');
        if ($pos > 0) {
            $without = explode(',', replaceNames(substr($name, $pos + 1)));
            $name = implode(',', array_diff($names, $without));
        }
    } else {
        $name = replaceNames($name);
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
        echo 'Zeile ' . $row . ':<pre>';
        var_dump($data);
        echo '</pre><br>';
    }
    return $valid;
}

$von = mktime(0, 0, 0, 5, 12, 2013);
$bis = mktime(0, 0, 0, 1, 5, 2014);
$createdBy = 'LE';
$createdAt = date('Y-m-d');
$gruppe = 'swp14-gsb';
$row = 0;

$xml = new SimpleXMLElement("<Analyse></Analyse>");

$xml->addAttribute('von', date('Y-m-d', $von));
$xml->addAttribute('bis', date('Y-m-d', $bis));
$xml->addAttribute('createdBy', $createdBy);
$xml->addAttribute('createdAt', $createdAt);
$xml->addAttribute('gruppe', $gruppe);

if (($handle = fopen("beispiel.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($row > 1) {
            $date = parseGermanDate($data[1], $row);
            if ($date >= $von && $date <= $bis) {
                $data[0] = parseName($data[0]);
                $data[2] = parseTime($data[2]);
                $valid = $valid && checkData($data, $row);
                $done = $xml->addChild('done', $data[5]);
                $done->addAttribute('who', $data[0]);
                $done->addAttribute('A', $data[3]);
                $done->addAttribute('S', $data[4]);
                $done->addAttribute('Zeit', $data[2]);
            }
        }
        $row++;
    }
    fclose($handle);
}

if ($valid) {
    Header('Content-type: text/xml');
    echo $xml->asXML();
}
