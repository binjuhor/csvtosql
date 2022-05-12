<?php
$path = './csvNormalized';
$files = scandir($path);
$files = array_diff(scandir($path), array('.', '..'));
$total = count($files);

$defaultColumns = 'pageTitle;companyName;jobTitle;location;contractType;workType;timeAgoDateTime;description;profile;predictedSalaryRange;predictedSalaryPeriod;name;star1;star4;star5;star2;star3;site';
$heading= explode(';', $defaultColumns);

$seperate = ';';
$f = fopen('./merged/merged_data.csv', 'w');

if ($f === false) {
    die('Error opening the file data.csv');
}
fputcsv($f, $heading, $seperate);
foreach ($files as $key=> $file) {
    system('clear');
    if('.gitignore' == $file || '.DS_Store' == $file) {
        continue;
    }
    echo "Processing file: " . $file . "\n";
    $data = convertCsvToArrayData($path.'/' . $file);
    array_shift($data);

    foreach ($data as $row) {
        fputcsv($f, $row, $seperate);
    }
    echo "Complete: " . round($key / $total * 100, 2) . '% (' . $key . " of " . $total . " files)\n";
}

fclose($f);

function convertCsvToArrayData($csvFile)
{
    if (!file_exists($csvFile)) {
        die('CSV file not found');
    }

    if ('csv' === pathinfo($csvFile, PATHINFO_EXTENSION)) {
        if (($fileOpen = fopen($csvFile, "r")) !== FALSE) {
            while (($data = fgetcsv($fileOpen, 1000, ";")) !== FALSE) {
                $csvData[] = $data;
            }
            fclose($fileOpen);
        }
        return $csvData;
    }
}


function getDataByColumnName($row, $heading, $columnName, $defaultValue = '')
{
    if(is_array($columnName)) {
        $columnIndex = getColumnIndex($columnName, $heading );
    } else {
        $columnIndex = array_search($columnName, $heading );
    }
    
    if(!$columnIndex){
        return $defaultValue;
    }
    
    if($defaultValue === 0) {
        if(isset($row[$columnIndex])) {
            return (int) $row[$columnIndex];
        }
    }

    if(isset($row[$columnIndex])) {
        return $row[$columnIndex];
    }

    return $defaultValue;
}

function getColumnIndex($columnName, $heading) {
    foreach($columnName as $column) {
        $columnIndex = array_search($column, $heading);
        if($columnIndex) {
            return $columnIndex;
        }
    }
    return null;
}

function saveNormalizeCSVfile($sql, $fileName)
{
    $file = fopen('./allinone/'.$fileName . '.csv', 'w');
    fwrite($file, $sql);
    fclose($file);
}
