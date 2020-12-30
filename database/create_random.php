<?php
    require_once('connectDB.php');
    $DataBase = new DBClass();
    global $choice_index;
    // $choice_data = 3;
    $progress = 3;
    $result = $DataBase->choice_select($progress);
    $choice_index = mt_rand(1, count($result)); 
    $choice_data = $result[$choice_index];

?>