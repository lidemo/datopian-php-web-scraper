<?php
# goutte_xpath.php

require 'vendor/autoload.php';

$client = new \Goutte\Client();
$filename = 'European Union Road Safety Facts and Figures.csv';

$crawler = $client->request('GET', 'https://en.wikipedia.org/wiki/Road_safety_in_Europe');

$keys = [];
$data = [];

/** Get the table from the DOM */
$table = $crawler->filter('.wikitable')->eq(0);

/** Scrape the table column names*/
$table->filter('th')->each(function ($node, $i) use (&$keys) {
    $keys[$i] = preg_replace('/\[[^)]+\]/', '', $node->text());
});

array_splice($keys, 1, 0, 'Year'); // add the Year column

//** Scrape the table values */
$key_num = 0;
$array_num = 0;
$table->eq(0)->filter('td')->each(function ($node, $i) use (&$data, $keys, &$key_num, &$array_num) {
    if ($key_num == 1) {
        $data[$array_num][$keys[$key_num]] = '2018';
        $key_num++;
    }

    if ($i % 11 == 0) {
        $key_num = 0;
        $array_num++;
    }

    $data[$array_num][$keys[$key_num]] = $node->text();
    $key_num++;
});

/** Sort the array by the Road deaths per Million Inhabitants value */
usort($data, function($a, $b) {
    return $a['Road deaths per Million Inhabitants in 2018'] <=> $b['Road deaths per Million Inhabitants in 2018'];
});

/** Save CSV */
$csv = implode(',', array_keys($data[0])) . "\n";
foreach ($data as $row) {
    $count = 0;
    foreach ($row as $value) {
        $count++;
        $csv .= '"'.$value.'"';
        if ($count < count($row)) $csv .= ',';
    }
    $csv .= "\n";
}

echo $csv;

file_put_contents($filename, $csv);