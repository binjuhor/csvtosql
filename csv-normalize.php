<?php
$path = './csv';
$files = scandir($path);
$files = array_diff(scandir($path), array('.', '..'));
$total = count($files);

foreach ($files as $key=> $file) {
    system('clear');
    if('.gitignore' == $file) {
        continue;
    }
    echo "Processing file: " . $file . "\n";
    // $tableName = 'tbl_' . str_replace('.json.csv', '', $file);
    $data = convertCsvToArrayData($path.'/' . $file);
    $csvData = normalizeDataTable($data);

    $filename = str_replace('.json', '', $file);

    $f = fopen('./csvNormalized/'.$filename, 'w');
    if ($f === false) {
        die('Error opening the file ' . $filename);
    }
    foreach ($csvData as $row) {
        fputcsv($f, $row);
    }
    fclose($f);
    // $sql =  createDefaultTableSQL($tableName)."\n".sqlInsertDataToTable($tableName, $data);
    // createSQLTableFile($sql, $tableName);
    echo "Complete: " . round($key / $total * 100, 2) . '% (' . $key . " of " . $total . " files)\n";
}

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

function normalizeDataTable( $data )
{
    $defaultRow = 'pageTitle;companyName;jobTitle;location;contractType;workType;timeAgoDateTime;description;profile;predictedSalaryRange;predictedSalaryPeriod;name;star1;star4;star5;star2;star3;site';
    $dataRow[] = explode(';', $defaultRow);
    
    $heading = array_shift($data);
   
    foreach ($data as $key => $row) {
        $dataRow[]= [
            getDataByColumnName($row, $heading, 'pageTitle'),
            getDataByColumnName($row, $heading, 'company'), //companyName
            getDataByColumnName($row, $heading, ['title', 'jobTitle']), //jobTitle
            getDataByColumnName($row, $heading, 'location'),
            getDataByColumnName($row, $heading, 'contractType', ''),
            getDataByColumnName($row, $heading, ['workType','workFromHome']), //workType
            getDataByColumnName($row, $heading, 'timeAgoDateTime'),
            getDataByColumnName($row, $heading, 'description', ''),
            getDataByColumnName($row, $heading, 'profile', ''),
            getDataByColumnName($row, $heading, 'predictedSalaryRange', ''),
            getDataByColumnName($row, $heading, 'predictedSalaryPeriod', ''),
            getDataByColumnName($row, $heading, 'name', ''),
            getDataByColumnName($row, $heading, 'star1', 0),
            getDataByColumnName($row, $heading, 'star2', 0),
            getDataByColumnName($row, $heading, 'star3', 0),
            getDataByColumnName($row, $heading, 'star4', 0),
            getDataByColumnName($row, $heading, 'star5', 0),
            getDataByColumnName($row, $heading, ['site', 'url']),
        ];
    }
    return $dataRow;
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
    $file = fopen('./csvNormalize/'.$fileName . '.csv', 'w');
    fwrite($file, $sql);
    fclose($file);
}
