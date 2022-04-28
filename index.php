<?php
$csvFile = './csv/000000001.json.csv';

$arrayData = csvToArray($csvFile);

$columnName = getColumnName($arrayData);

$createTable = createSQLTable($columnName);


function createSQLTable( $columns, $tableName = 'myTable' ) {
    $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,";
    foreach($columns as $column) {
        $sql .= "`{$column}` varchar(255) NOT NULL,";
    }
    return substr($sql, 0, -1) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
}

function renameDuplicateColumn( $columns ) {

    foreach($columns as $key => $value) {
        if(array_count_values($columns) > 1) {
            $columns[$key] = $value . '_';
        }
    }

    return $columns;
}

function getColumnName( $data ){
    return $data[0];
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
