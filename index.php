<?php
$csvFile = './csv/000000001.json.csv';

$arrayData = csvToArray($csvFile);

$columnName = getColumnName($arrayData);

$createTable = createSQLTable($columnName);


function createSQLTable($columns, $tableName = 'myTable')
{
    $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,";
    foreach ($columns as $column) {
        $sql .= "`{$column}` varchar(255) NOT NULL,";
    }
    return substr($sql, 0, -1) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
}

function getColumnName($data)
{
    $columnList = $data[0];
    $duplicateArray = duplicateColumn($columnList);
    $i = 0;
    
    foreach($columnList as $key => $column) {
        if(in_array($column, $duplicateArray) && $key > 0) {
            $columnList[$key] = $column . '_' . $i;
            $i++;
        }
    }

    return $columnList;
}

function duplicateColumn( $columns ) 
{
    $duplicate = [];
    $array = array_count_values($columns);
    foreach($array as $key => $arr) {
        if($arr > 1) {
            $duplicate[] =  $key;
        }
    }

    return $duplicate;
}

function csvToArray($csvFile)
{
    if (($fileOpen = fopen($csvFile, "r")) !== FALSE) {
        while (($data = fgetcsv($fileOpen, 1000, ";")) !== FALSE) {
            $csvData[] = $data;
        }
        fclose($fileOpen);
    }
    return $csvData;
}

echo "<pre>";
//To display array data
var_dump($createTable);
echo "</pre>";
