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
    $dataRow[0] = explode(';', $defaultRow);
   
    foreach ($data as $key => $row) {
        $dataRow[$key+1]= [
            getDataByColumnName($row, $data, 'pageTitle'),
            getDataByColumnName($row, $data, 'company'), //companyName
            getDataByColumnName($row, $data, ['title', 'jobTitle']), //jobTitle
            getDataByColumnName($row, $data, 'location'),
            getDataByColumnName($row, $data, 'contractType', ''),
            getDataByColumnName($row, $data, ['workType','workFromHome']), //workType
            getDataByColumnName($row, $data, 'timeAgoDateTime'),
            getDataByColumnName($row, $data, 'description', ''),
            getDataByColumnName($row, $data, 'profile', ''),
            getDataByColumnName($row, $data, 'predictedSalaryRange', ''),
            getDataByColumnName($row, $data, 'predictedSalaryPeriod', ''),
            getDataByColumnName($row, $data, 'name', ''),
            getDataByColumnName($row, $data, 'star1', 0),
            getDataByColumnName($row, $data, 'star2', 0),
            getDataByColumnName($row, $data, 'star3', 0),
            getDataByColumnName($row, $data, 'star4', 0),
            getDataByColumnName($row, $data, 'star5', 0),
            getDataByColumnName($row, $data, ['site', 'url']),
        ];
        return $dataRow;
    }
}

function getDataByColumnName($row, $data, $columnName, $defaultValue = '')
{
    if(is_array($columnName)) {
        $columnIndex = getColumnIndex($columnName, $data);
    } else {
        $columnIndex = array_search($columnName, $data[0]);
    }
    
    if(!$columnIndex){
        return $defaultValue;
    }
    
    if($defaultValue === 0) {
        if($row[$columnIndex]) {
            return (int) $row[$columnIndex];
        }
    }

    if($row[$columnIndex]) {
        return $row[$columnIndex];
    }

    return $defaultValue;
}

function getColumnIndex($columnName, $data) {
    foreach($columnName as $column) {
        $columnIndex = array_search($column, $data[0]);
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
