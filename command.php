<?php
$path = './csv';
$files = scandir($path);
$files = array_diff(scandir($path), array('.', '..'));
$total = count($files);

$i = 0;
foreach ($files as $file) {
    system('clear');
    if('.gitignore' == $file) {
        continue;
    }
    echo "Processing file: " . $file . "\n";
    $tableName = 'tbl_' . str_replace('.json.csv', '', $file);
    $data = convertCsvToArrayData($path.'/' . $file);
    $sql =  createDefaultTableSQL($tableName)."\n".sqlInsertDataToTable($tableName, $data);
    createSQLTableFile($sql, $tableName);
    echo "Complete: " . round($i / $total * 100, 2) . '% (' . $i . " of " . $total . " files)\n";
    $i++;
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


function getDataByColumnName($row, $data, $columnName, $defaultValue = '')
{
    $columnIndex = array_search($columnName, $data[0]);
    if(!$columnIndex){
        return $defaultValue;
    }

    if($defaultValue === 0) {
        if($row[array_search($columnName, $data[0])]) {
            return (int) $row[array_search($columnName, $data[0])];
        }
    }

    if($row[array_search($columnName, $data[0])]) {
        return $row[$columnIndex];
    }

    return $defaultValue;
}

function sqlInsertDataToTable($tableName, $data)
{
    $rawData = $data;

    $sql = "INSERT INTO `{$tableName}` (`SearchNumber`, `Source`, `SearchJob`, `SearchPlace`, `Ranking`, `Distance`, `Experience`, `JobTitle`, `JobTasks`, `JobQualifications`) \nVALUES ";
    array_shift($data);
    foreach ($data as $row) {
        $sql .= "('".getDataByColumnName($row, $rawData, 'searchNumber', 0 )."', '".getDataByColumnName($row, $rawData, 'Source' )."', '".getDataByColumnName($row, $rawData, 'SearchJob' )."', '".getDataByColumnName($row, $rawData, 'SearchPlace' )."', '".getDataByColumnName($row, $rawData, 'Ranking', 0 )."', '".getDataByColumnName($row, $rawData, 'Distance', 0)."', '".getDataByColumnName($row, $rawData, 'Experience','unknown' )."', '".getDataByColumnName($row, $rawData, 'JobTitle' )."', '".getDataByColumnName($row, $rawData, 'JobTasks' )."', '".getDataByColumnName($row, $rawData, 'JobQualifications' )."'),";
    }
    $sql = rtrim($sql, ',');
    $sql .= ";";
    return $sql;
}

function createSQLTableFile($sql, $tableName)
{
    $file = fopen('./sql/'.$tableName . '.sql', 'w');
    fwrite($file, $sql);
    fclose($file);
}

function createDefaultTableSQL($tableName)
{
    return "CREATE TABLE IF NOT EXISTS `{$tableName}` (
    `id` INT(11) NOT NULL AUTO_INCREMENT , 
    `SearchNumber`INT NOT NULL, 
    `Source` VARCHAR(255) NOT NULL , 
    `SearchJob` VARCHAR(255) NOT NULL , 
    `SearchPlace` VARCHAR(255) NOT NULL , 
    `Ranking` INT NOT NULL , 
    `Distance` INT NOT NULL , 
    `Experience` VARCHAR(255) NOT NULL DEFAULT 'unknown', 
    `JobTitle` VARCHAR(255) NOT NULL , 
    `JobTasks` TEXT NOT NULL , 
    `JobQualifications` TEXT NOT NULL , 
    PRIMARY KEY (`id`)
    ) ENGINE = InnoDB;";
}


