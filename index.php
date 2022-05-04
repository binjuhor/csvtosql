<?php
$path = './csv';
$files = scandir($path);
$files = array_diff(scandir($path), array('.', '..'));
$total = count($files) - 1; // -1 because of .gitignore file

/**
 * SearchNumber: number | retryCount
 * Source: text|  url, 
 * SearchJob: text | 
 * SearchPlace: varchar | location
 * Ranking: number |  | (yes/no/unknown)
 * Distance: number | 
 * Experience: varchar | 
 * JobTitle: varchar | title ,
 * JobTasks: varchar | 
 * JobQualifications: varchar | 
 */

$i = 0;
foreach ($files as $file) {
    system('clear');
    $tableName = 'tbl_' . str_replace('.json.csv', '', $file);
    echo "Processing file: " . $file . "\n";

    convertCsvToArrayData($path . '/' . $file,);
    die();

    // createSqlFile($path . '/' . $file,  $tableName . '.sql', $tableName);
    echo "Complete: " . round($i / $total * 100, 2) . '% (' . $i . " of " . $total . " files)\n";
    $i++;
}

function createDefaultTableSQL($tableName)
{
    return "CREATE TABLE IF NOT EXISTS `{$tableName}` (
    `id` INT(11) NOT NULL AUTO_INCREMENT , 
    `Source` VARCHAR(255) NOT NULL , 
    `SearchJob` VARCHAR(255) NOT NULL , 
    `SearchPlace` VARCHAR(255) NOT NULL , 
    `Ranking` INT NOT NULL , 
    `Distance` INT NOT NULL , 
    `Experience` VARCHAR(255) NOT NULL , 
    `JobTitle` VARCHAR(255) NOT NULL , 
    `JobTasks` VARCHAR(255) NOT NULL , 
    `JobQualifications` VARCHAR(255) NOT NULL , 
    PRIMARY KEY (`id`)
    ) ENGINE = InnoDB;";
}

function sqlFillDataToDefaultTable($tableName, $data)
{
    $sql = "INSERT INTO `{$tableName}` (`Source`, `SearchJob`, `SearchPlace`, `Ranking`, `Distance`, `Experience`, `JobTitle`, `JobTasks`, `JobQualifications`) VALUES ";
    foreach ($data as $row) {
        $sql .= "('{$row['Source']}', '{$row['SearchJob']}', '{$row['SearchPlace']}', '{$row['Ranking']}', '{$row['Distance']}', '{$row['Experience']}', '{$row['JobTitle']}', '{$row['JobTasks']}', '{$row['JobQualifications']}'),";
    }
    $sql = rtrim($sql, ',');
    $sql .= ";";
    return $sql;
}

function createTableSql($columns, $tableName = 'myTable')
{
    if (!is_array($columns)) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}`\n(`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,";
    foreach ($columns as $column) {
        $sql .= "`{$column}` varchar(255) NOT NULL,\n";
    }
    return substr(
        $sql,
        0,
        -2
    ) . ")\nENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
}

function createInsertDataSql($arrayData, $columnName, $tableName = 'myTable')
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

function convertCsvToSqlString($csvFile, $tableName)
{
    $arrayData = convertCsvToArrayData($csvFile);
    $columnName = getColumnName($arrayData);

    $sqlCreateTable = createTableSql($columnName, $tableName);
    $sqlInsertData = createInsertDataSql($arrayData, $columnName, $tableName);

    return $sqlCreateTable . $sqlInsertData;
}

function createSqlFile($csvFile, $fileName = 'myTable.sql', $tableName = 'myTable')
{
    $sqlString = convertCsvToSqlString($csvFile, $tableName);
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
    $duplicateArray = getDuplicateColumns($columnList);
    $i = 0;

    foreach ($columnList as $key => $column) {
        if (in_array($column, $duplicateArray) && $key > 0) {
            $columnList[$key] = $column . '_' . $i;
            $i++;
        }
    }

    return $columnList;
}

function getDuplicateColumns($columns)
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
