<?php

$divisions_url = 'https://bdapis.com/api/v1.1/divisions';
$divisions_json = @file_get_contents($divisions_url);

if (!$divisions_json) {
    die("Failed to fetch divisions\n");
}

$divisions = json_decode($divisions_json, true)['data'];
$upazilasData = [];
$districtsData = []; 

foreach ($divisions as $div) {
    $div_name = $div['divisioneng'];
    $div_data_json = @file_get_contents('https://bdapis.com/api/v1.1/division/' . $div_name);
    if (!$div_data_json) continue;

    $div_data = json_decode($div_data_json, true)['data'];

    foreach ($div_data as $dist) {
        $dist_name = $dist['district'];
        $dist_bn_name = $dist['districtbn'];
        
        // Also save district translation mapping
        $districtsData[$dist_name] = $dist_bn_name;
        
        $upazilas = $dist['upazilla'] ?? [];
        foreach ($upazilas as $upa) {
            $upazilasData[] = [
                'district' => $dist_name,
                'name' => $upa,
                'bn_name' => null 
            ];
        }
    }
}

if (!is_dir(__DIR__ . '/database/data')) {
    mkdir(__DIR__ . '/database/data', 0755, true);
}

file_put_contents(__DIR__ . '/database/data/upazilas.json', json_encode($upazilasData, JSON_PRETTY_PRINT));
file_put_contents(__DIR__ . '/database/data/districts_bn.json', json_encode($districtsData, JSON_PRETTY_PRINT));
echo "Saved " . count($upazilasData) . " upazilas to database/data/upazilas.json\n";

