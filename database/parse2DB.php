<?php
    setlocale(LC_ALL, 'en_US.UTF-8');
    require_once('/volume1/web/database/connectDB.php');
    $DataBase = new DBClass();
    $row = 1;
    $buffer_size = 1000;

    if (($handle = fopen("/volume1/Database/ing/journal_list.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, $buffer_size, ",")) !== FALSE) {
            if($row > 1){
                $DataBase->insert($data); 
            }

            $row++;

        }
        fclose($handle);
    }


?>