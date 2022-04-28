<?php
$tableName = 'myTable';
$path    = './csv';
$files = scandir($path);
$files = array_diff(scandir($path), array('.', '..'));
$total = count($files);
$i = 0;
foreach ($files as $file) {
    system('clear');
    echo "Processing file: " . $file . "\n";
    createSqlFile($path . '/' . $file, str_replace('csv', 'sql', $file), $tableName);
    echo "Complete: " .round($i/$total*100, 2).'% ('.$i . " of " . $total . " files)\n";
    $i++;
}

/**
 * Function to create sql file
 */

function createSQLTable($columns, $tableName = 'myTable')
{
    if (!is_array($columns)) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}`\n(`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,";
    foreach ($columns as $column) {
        $sql .= "`{$column}` varchar(255) NOT NULL,\n";
    }
    return substr($sql, 0, -2) . ")\nENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
}

function insertDataSQL($arrayData, $columnName, $tableName = 'myTable')
{
    if (!is_array($arrayData)) {
        return false;
    }

    array_shift($arrayData);

    $sql = "\nINSERT INTO `{$tableName}`\n(";
    foreach ($columnName as $column) {
        $sql .= "`{$column}`,";
    }
    $sql = substr($sql, 0, -1) . ") VALUES\n";

    foreach ($arrayData as $data) {
        $sql .= "(";
        foreach ($data as $value) {
            $sql .= "'{$value}',";
        }
        $sql = substr($sql, 0, -1) . "),\n";
    }
    $sql = substr($sql, 0, -2) . ";";

    return $sql;
}

function convertSVGtoSQLString($csvFile, $tableName)
{
    $arrayData = csvToArray($csvFile);
    $columnName = getColumnName($arrayData);

    $sqlCreateTable = createSQLTable($columnName, $tableName);
    $sqlInsertData = insertDataSQL($arrayData, $columnName, $tableName);

    return $sqlCreateTable . $sqlInsertData;
}

function createSqlFile($csvFile, $fileName = 'myTable.sql', $tableName = 'myTable')
{
    $sqlString = convertSVGtoSQLString($csvFile, $tableName);
    $file = fopen('./sql/' . $fileName, 'w');
    fwrite($file, $sqlString);
    fclose($file);
}

function getColumnName($data)
{
    if (!is_array($data)) {
        return false;
    }

    $columnList = array_shift($data);
    $duplicateArray = duplicateColumn($columnList);
    $i = 0;

    foreach ($columnList as $key => $column) {
        if (in_array($column, $duplicateArray) && $key > 0) {
            $columnList[$key] = $column . '_' . $i;
            $i++;
        }
    }

    return $columnList;
}

function duplicateColumn($columns)
{
    $duplicate = [];
    $array = array_count_values($columns);
    foreach ($array as $key => $arr) {
        if ($arr > 1) {
            $duplicate[] =  $key;
        }
    }

    return $duplicate;
}

function csvToArray($csvFile)
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
