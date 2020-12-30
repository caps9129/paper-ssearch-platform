<?php



ini_set('memory_limit', '-1');
ini_set('max_execution_time','0');


// print('Current PHP version: ' . phpversion());

// define("DB_HOST", "127.0.0.1");
// define("DB_USER", "root");
// define("DB_PASS", "");
// define("DB_NAME", "test");

define("DB_HOST", "140.109.160.188:3307");
define("DB_USER", "caps9129");
define("DB_PASS", "Cibs@27854160");
define("DB_NAME", "paper");

https://vc31.cbe.tw:20443/
 
// $DataBase = new DBClass();

class DBClass {

    var $conn, $query, $result, $sql, 
        $select_result = array(),
        $select_ngram_result = array(),
        $select_modal_result = array(),
        $temp_select_modal_result = array(),
        $label = array(),
        $input = array(),
        $json_select_result,
        $total_results = 0,
        $table_name = 'paper_research';
  
    
    public function __construct() {
        $this->connect();
        $this->table_exists($this->table_name);
    }

    public function disconnect() {
        mysqli_close($this->conn);
    }

    public function reconnect() {
        $this->disconnect();
        $this->connect();
    }

    public function connect() {
        $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if(!$this->conn){
            die("dbConnect fail". mysqli_connect_error()."\n");
            exit;
        }
        else{
            if ( TRUE !== $this->conn->set_charset( 'utf8mb4' ) )
                throw new \Exception( $this->conn->error, $this->conn->errno );
        
            if ( TRUE !== $this->conn->query( 'SET collation_connection = @@collation_database;' ) )
            throw new \Exception( $this->conn->error, $this->conn->errno );
            $this->conn->character_set_name();
            // print("connect successful");
    
            // echo 'character_set_name: ', $this->conn->character_set_name(), '<br />', PHP_EOL;
            
            // foreach( $this->conn->query( "SHOW VARIABLES LIKE '%_connection';" )->fetch_all() as $setting )
            // echo $setting[0], ': ', $setting[1], '<br />', PHP_EOL;

            
        }
    }

    public function table_exists($table_name){
        
        $this->sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table_name'";

        if($this->conn->query($this->sql)->num_rows == 0){

            $this->create_table();
        }
        // else{
        //     print("exist!");
        // }
       
    }

    public function create_table(){

        $this->sql = "CREATE TABLE `paper_research` (
            `ID` int(11) NOT NULL PRIMARY KEY,
            `文章來源` varchar(200) NOT NULL,
            `加入日期` varchar(20) NOT NULL,
            `急迫程度` varchar(20) NOT NULL,
            `老師` varchar(20) NOT NULL,
            `NOTE` TEXT NOT NULL,
            `Citation` int(11) NOT NULL,
            `Citation_update_date` DATETIME NULL,
            `Authors_LastName` varchar(200) NOT NULL,
            `Authors_FullName` varchar(200) NOT NULL,
            `Year` int(11) NOT NULL,
            `PaperName` varchar(200) NOT NULL,
            `JournalName` varchar(200) NOT NULL,
            `JournalAbbreviations` varchar(200) NOT NULL,
            `VolNo` varchar(200) NOT NULL,
            `Page` varchar(200) NOT NULL,
            `Symposium` varchar(200) NOT NULL,
            `JELCode` varchar(200) NOT NULL,
            `keyword` varchar(200) NOT NULL,
            `PDF檔名` TEXT NOT NULL,
            `初稿日期` DATETIME NULL,
            `更新日期` DATETIME NULL,
            `全文` int(11)  DEFAULT 0,
            `文章進度` int(11) NOT NULL,
            `資料夾link` TEXT NOT NULL,
            `原文link` TEXT NOT NULL,
            `中文link` TEXT NOT NULL,
            `checked` int(11) DEFAULT 0,
            `log` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        // print($this->sql);

        $this->conn->query($this->sql);

        // print_r("create table successful");
    }

    public function sort($result){

        function udiffCompare($a, $b)
        {
            return $a['ID'] - $b['ID'];
        }
          
        $ordered_result = array();
        $journal_importance = array('American Economic Review', 'Journal of Political Economy', 'Quarterly Journal of Economics', 'Review of Economic Studies', 'Journal of Economic Perspectives', 'Journal of Economic Literature', 'RAND Journal of Economics');
        $year_importance = array();
        for ( $i=2021 ; $i>=1800 ; $i-- ) {
          array_push($year_importance, (string)$i);
        }
        // print_r($year_importance);
        foreach($year_importance as $year){

            $t_year = array();
            $t_jour = array();
            $t_ordered = array();
            $back_result = array();
            $count = 0;
            // print($journal);
            foreach($result as $key=>$value){

                if($value['Year'] == $year){
                    array_push($t_year, $value);
                }
                    
            }
            foreach($t_year as $t_year_v){
                $count = $count + 1;
                foreach($journal_importance as $journal){
                    if($t_year_v['JournalName'] == $journal){
                        array_push($t_jour, $t_year_v);
                    }
                }
                if($count == count($t_year)){          
                    $back_result = array_udiff($t_year, $t_jour, 'udiffCompare');
                    $t_ordered = array_merge($t_jour, $back_result);
                    $ordered_result = array_merge($ordered_result, $t_ordered);
                }

            }
            
        }

        return $ordered_result;
    }

    // recent search
    public function recent_choice_select(){
        
        $this->select_result = array();
        $query_result = array();
        $log_time_result = array();

        $this->query = "SELECT * FROM `paper_research`";

        $query_result = $this->execute();

        
        // covert datetime to date
        foreach($query_result as &$row){
            // print_r($row['更新日期']);
            $date_time_obj = datetime::createFromFormat('Y-m-d H:i:s', $row['log']);
            // print_r($date_time_obj->format('Y-m-d'));
            $row['log'] = $date_time_obj->format('Y-m-d');
            array_push($log_time_result, $row);
      
        }

        // group date and rank

        // unique datetime
        $unique_date = array();
        foreach($log_time_result as $row){
            if(in_array($row['log'], $unique_date) == FALSE){
                array_push($unique_date, $row['log']);
            }
        }
        
        sort($unique_date);
        $recent_date = end($unique_date);
        
        // recent datetime array
        $choice_data = array();
        foreach($log_time_result as $row){
            if($row['log'] == $recent_date){
                array_push($choice_data, $row);
            }
            
        }
        // print_r($choice_data);


        return $choice_data;


    }

    // 每日精選 傳入文章進度
    public function choice_select($paper_progress){

        $this->select_result = array();
        $query_result = array();
    

        $this->query = "SELECT * FROM `paper_research` WHERE `文章進度` = $paper_progress";
        $this->query = "SELECT * FROM `paper_research`";
      
        $query_result = $this->execute();

        return $query_result;


    }

    public function select($arr_query_result, $condition){

        $this->select_result = array();
        $query_result = array();
    
       
        if($condition == 1){
            // SELECT * FROM `paper_research` WHERE `文章來源` + `加入日期`  LIKE ('%21%')
            foreach($arr_query_result as $key => $value){
                $this->query = "SELECT * FROM `paper_research` WHERE  `Authors_FullName` LIKE '%$value%' or `Year` LIKE '%$value%' or `PaperName` LIKE '%$value%' or `JournalName` LIKE '%$value%' or `JournalAbbreviations` LIKE '%$value%' or `keyword` LIKE '%$value%' ORDER BY `Year` DESC";  
            }

            // $this->query = "SELECT * FROM `paper_research` ORDER BY `Year` DESC";

            // $this->query = $this->query." ORDER BY `Year` DESC";
            // print($this->query);
            $query_result = $this->execute();
            $query_result = array_unique($query_result, SORT_REGULAR);
            $this->total_results = count($query_result);
            $start = microtime(true);
            $query_result = $this->sort($query_result);
            $time_elapsed_secs = microtime(true) - $start;
            // print($time_elapsed_secs);
            // print_r($query_result);
            
        }
        else if($condition == 2){
            $this->generate_query_stack($arr_query_result);
            $this->generate_sql('and');
            $this->query = $this->query." ORDER BY `Year` DESC";

            $r_1 = $this->execute();

            $query_result = $r_1;

            $this->total_results = count($query_result);
            $query_result = $this->sort($query_result);
           
        }
        else if($condition == 3){
            $this->generate_query_stack($arr_query_result);
            foreach($this->label as $key=>$value){
                if($value == 'ALL'){
                    $v = $this->input[$key];
                    $this->query = "SELECT * FROM `paper_research` WHERE  `Authors_FullName` LIKE '%$v%' or `Year` LIKE '%$v%' or `PaperName` LIKE '%$v%' or `JournalName` LIKE '%$v%' or `JournalAbbreviations` LIKE '%$v%' or `keyword` LIKE '%$v%' ORDER BY `Year` DESC";

                }
                array_splice($this->label, $key, 1);
                array_splice($this->input, $key, 1);
            }
            $r_1 = $this->execute();
            $this->generate_sql('and');
            $this->query = $this->query." ORDER BY `Year` DESC";
            $r_2 = $this->execute();
            // $this->generate_sql('or');
            // $this->query = $this->query." ORDER BY `Year` DESC";
            // $r_3 = $this->execute();
            $query_result = array_merge($r_1, $r_2);
            // $query_result = array_merge($query_result, $r_3);
            $query_result = array_unique($query_result, SORT_REGULAR);
            $this->total_results = count($query_result);
            $query_result = $this->sort($query_result);
   
        }
        else if($condition == 4){
            $this->generate_query_stack($arr_query_result);
            $this->generate_sql('and');
            $this->query = $this->query." ORDER BY `Year` DESC";
            $r_1 = $this->execute();
            $query_result = array_unique($r_1, SORT_REGULAR);
            $this->total_results = count($query_result);
            $query_result = $this->sort($query_result);
            // print($this->query);
        }
        else if($condition == 5){

            foreach ($arr_query_result as $key => $value) {
                // print($key);
                // print($value);
                $this->query = "SELECT * FROM `paper_research` WHERE `NOTE` LIKE '%$value%' ORDER BY `Year` DESC";
            }
            $r_1 = $this->execute();
            $query_result = $r_1;
            $this->total_results = count($query_result);
            $query_result = $this->sort($query_result);

        }
        else if($condition == 6){
            $input = explode(" ", $arr_query_result['value0']);

         

            foreach ($input as $key => $value) {
           
                if($key == 0){
                    $this->query = "SELECT * FROM `paper_research` WHERE  `Authors_FullName` LIKE '%$value%' or `Year` LIKE '%$value%' or `PaperName` LIKE '%$value%' or `JournalName` LIKE '%$value%' or `JournalAbbreviations` LIKE '%$value%' or `keyword` LIKE '%$value%'"; 
                }
                elseif($value == 'AND'){
                    $this->query = $this->query . ' INTERSECT ';
                    
                }
                elseif($value == 'OR'){
                    $this->query = $this->query . ' UNION ';
                }
                else{
                    $base= "SELECT * FROM `paper_research` WHERE  `Authors_FullName` LIKE '%$value%' or `Year` LIKE '%$value%' or `PaperName` LIKE '%$value%' or `JournalName` LIKE '%$value%' or `JournalAbbreviations` LIKE '%$value%' or `keyword` LIKE '%$value%'"; 
                    $this->query = $this->query . $base;
                }
                
            }
            $this->query = $this->query . ' ORDER BY `Year` DESC';
            $query_result = $this->execute();
            $query_result = array_unique($query_result, SORT_REGULAR);
            $this->total_results = count($query_result);
            $query_result = $this->sort($query_result);
         

           
        
        }
        else{
            if(!mysqli_ping($this->conn)){
                $this->reconnect();
                $this->select($arr_query_result, $condition);
            }
            // create query array
  
            // create query string 

        }   
        
        return $query_result;


    }

    //insert record
    public function insert($data) {

        if(!mysqli_ping($this->conn)){
            $this->reconnect();
            $this->insert($data);
        }
        
        foreach ($data as $index => $value){
            if(is_string($data[$index])){
                $data[$index] = str_replace("'", "''", $data[$index]);
            }
            
        }

        if($data[6] == ""){
            $data[6] = 0;
        }

        // datetime
        if($data[20] == ''){

            if($data[7] == ""){
                $this->sql = "INSERT INTO `$this->table_name` (ID, 文章來源, 加入日期, 急迫程度, 老師, NOTE, Citation, Authors_LastName, Authors_FullName, Year, PaperName, JournalName, JournalAbbreviations, VolNo, Page, Symposium, JELCode, keyword, PDF檔名, 全文, 文章進度, 資料夾link, 原文link, 中文link, checked)
                VALUES (N'$data[0]', N'$data[1]', N'$data[2]', N'$data[3]', N'$data[4]', N'$data[5]', N'$data[6]', N'$data[8]', N'$data[9]', N'$data[10]', N'$data[11]', N'$data[12]', N'$data[13]', N'$data[14]', N'$data[15]', N'$data[16]', N'$data[17]', N'$data[18]', N'$data[19]', N'$data[22]', N'$data[23]', N'$data[24]', N'$data[25]', N'$data[26]', N'$data[28]')";
            
                $this->query = "UPDATE `$this->table_name` SET 文章來源 = N'$data[1]', 加入日期 = N'$data[2]', 急迫程度 = N'$data[3]', 老師 = N'$data[4]', NOTE = N'$data[5]', Citation = N'$data[6]', Authors_LastName = N'$data[8]', 
                Authors_FullName = N'$data[9]', Year = N'$data[10]', PaperName = N'$data[11]',  JournalName = N'$data[12]', JournalAbbreviations = N'$data[13]', VolNo = N'$data[14]', Page = N'$data[15]', Symposium = N'$data[16]', JELCode = N'$data[17]', keyword = N'$data[18]', PDF檔名 = N'$data[19]', 
                全文 = N'$data[22]', 文章進度 = N'$data[23]', 資料夾link= N'$data[24]', 原文link = N'$data[25]', 中文link = N'$data[26]', checked = N'$data[28]' where ID = N'$data[0]'";
            }
            else{
                $this->sql = "INSERT INTO `$this->table_name` (ID, 文章來源, 加入日期, 急迫程度, 老師, NOTE, Citation, Citation_update_date, Authors_LastName, Authors_FullName, Year, PaperName, JournalName, JournalAbbreviations, VolNo, Page, Symposium, JELCode, keyword, PDF檔名, 全文, 文章進度, 資料夾link, 原文link, 中文link, checked)
                VALUES (N'$data[0]', N'$data[1]', N'$data[2]', N'$data[3]', N'$data[4]', N'$data[5]', N'$data[6]', N'$data[7]', N'$data[8]', N'$data[9]', N'$data[10]', N'$data[11]', N'$data[12]', N'$data[13]', N'$data[14]', N'$data[15]', N'$data[16]', N'$data[17]', N'$data[18]', N'$data[19]', N'$data[22]', N'$data[23]', N'$data[24]', N'$data[25]', N'$data[26]', N'$data[28]')";
            
                $this->query = "UPDATE `$this->table_name` SET 文章來源 = N'$data[1]', 加入日期 = N'$data[2]', 急迫程度 = N'$data[3]', 老師 = N'$data[4]', NOTE = N'$data[5]', Citation = N'$data[6]', Citation_update_date = N'$data[7]', Authors_LastName = N'$data[8]', 
                Authors_FullName = N'$data[9]', Year = N'$data[10]', PaperName = N'$data[11]',  JournalName = N'$data[12]', JournalAbbreviations = N'$data[13]', VolNo = N'$data[14]', Page = N'$data[15]', Symposium = N'$data[16]', JELCode = N'$data[17]', keyword = N'$data[18]', PDF檔名 = N'$data[19]', 
                全文 = N'$data[22]', 文章進度 = N'$data[23]', 資料夾link= N'$data[24]', 原文link = N'$data[25]', 中文link = N'$data[26]', checked = N'$data[28]' where ID = N'$data[0]'";
            }
            
            // $this->query = "UPDATE `$this->table_name` SET 文章來源 = N'$data[1]', 加入日期 = N'$data[2]', 急迫程度 = N'$data[3]', 老師 = N'$data[4]',  NOTE = N'$data[5]', Authors_LastName = N'$data[6]', 
            // Authors_FullName = N'$data[7]', Year = N'$data[8]', PaperName = N'$data[9]',  JournalName = N'$data[10]', JournalAbbreviations = N'$data[11]', VolNo = N'$data[12]', Page = N'$data[13]', Symposium = N'$data[14]', JELCode = N'$data[15]', keyword = N'$data[16]', PDF檔名 = N'$data[17]', 
            // 全文 = N'$data[20]', 文章進度 = N'$data[21]', 資料夾link= N'$data[22]', 原文link = N'$data[23]', 中文link = N'$data[24]', checked = N'$data[26]' where ID = N'$data[0]'";
        }
        else{
            if($data[7] == ""){
                $this->sql = "INSERT INTO `$this->table_name` (ID, 文章來源, 加入日期, 急迫程度, 老師, NOTE, Citation, Authors_LastName, Authors_FullName, Year, PaperName, JournalName, JournalAbbreviations, VolNo, Page, Symposium, JELCode, keyword, PDF檔名, 初稿日期, 更新日期, 全文, 文章進度, 資料夾link, 原文link, 中文link, checked)
                VALUES (N'$data[0]', N'$data[1]', N'$data[2]', N'$data[3]', N'$data[4]', N'$data[5]', N'$data[6]', N'$data[8]',  N'$data[9]', N'$data[10]', N'$data[11]', N'$data[12]', N'$data[13]', N'$data[14]', N'$data[15]', N'$data[16]', N'$data[17]', N'$data[18]', N'$data[19]', N'$data[20]', N'$data[21]', N'$data[22]', N'$data[23]', N'$data[24]', N'$data[25]', N'$data[26]', N'$data[28]')";
            
                $this->query = "UPDATE `$this->table_name` SET 文章來源 = N'$data[1]', 加入日期 = N'$data[2]', 急迫程度 = N'$data[3]', 老師 = N'$data[4]',  NOTE = N'$data[5]', Citation = N'$data[6]', Authors_LastName = N'$data[8]', 
                Authors_FullName = N'$data[9]', Year = N'$data[10]', PaperName = N'$data[11]',  JournalName = N'$data[12]', JournalAbbreviations = N'$data[13]', VolNo = N'$data[14]', Page = N'$data[15]', Symposium = N'$data[16]', JELCode = N'$data[17]', keyword = N'$data[18]', PDF檔名 = N'$data[19]', 初稿日期 = N'$data[20]', 
                更新日期 = N'$data[21]', 全文 = N'$data[22]', 文章進度 = N'$data[23]', 資料夾link= N'$data[24]', 原文link = N'$data[25]', 中文link = N'$data[26]', checked = N'$data[28]' where ID = N'$data[0]'"; 
            }
            else{
                $this->sql = "INSERT INTO `$this->table_name` (ID, 文章來源, 加入日期, 急迫程度, 老師, NOTE, Citation, Citation_update_date, Authors_LastName, Authors_FullName, Year, PaperName, JournalName, JournalAbbreviations, VolNo, Page, Symposium, JELCode, keyword, PDF檔名, 初稿日期, 更新日期, 全文, 文章進度, 資料夾link, 原文link, 中文link, checked)
                VALUES (N'$data[0]', N'$data[1]', N'$data[2]', N'$data[3]', N'$data[4]', N'$data[5]', N'$data[6]', N'$data[7]', N'$data[8]',  N'$data[9]', N'$data[10]', N'$data[11]', N'$data[12]', N'$data[13]', N'$data[14]', N'$data[15]', N'$data[16]', N'$data[17]', N'$data[18]', N'$data[19]', N'$data[20]', N'$data[21]', N'$data[22]', N'$data[23]', N'$data[24]', N'$data[25]', N'$data[26]', N'$data[28]')";
                
                $this->query = "UPDATE `$this->table_name` SET 文章來源 = N'$data[1]', 加入日期 = N'$data[2]', 急迫程度 = N'$data[3]', 老師 = N'$data[4]',  NOTE = N'$data[5]', Citation = N'$data[6]', Citation_update_date = N'$data[7]', Authors_LastName = N'$data[8]', 
                Authors_FullName = N'$data[9]', Year = N'$data[10]', PaperName = N'$data[11]',  JournalName = N'$data[12]', JournalAbbreviations = N'$data[13]', VolNo = N'$data[14]', Page = N'$data[15]', Symposium = N'$data[16]', JELCode = N'$data[17]', keyword = N'$data[18]', PDF檔名 = N'$data[19]', 初稿日期 = N'$data[20]', 
                更新日期 = N'$data[21]', 全文 = N'$data[22]', 文章進度 = N'$data[23]', 資料夾link= N'$data[24]', 原文link = N'$data[25]', 中文link = N'$data[26]', checked = N'$data[28]' where ID = N'$data[0]'";

            }
            
        }
    

        // print($this->query);
        // exit();

        if(!mysqli_query($this->conn, $this->sql)){
            
            
            if(strpos(mysqli_error($this->conn),"key 'PRIMARY'")!==false){
                

                if(mysqli_query($this->conn, $this->query)){
                    echo "Update in ".$this->table_name.": ".$data[0]." complete<br>\n";
                }
                else{
                    
                    $this->insert($data);
                }
            }
            else{
                echo "SQL Error: " . mysqli_error($this->conn)."\n";
            }
        }
        else{

            print("Insert in ".$this->table_name.": ".$data[0]." complete<br>\n");
        }

        
    }

    public function execute() {
        $select_result = array();
        $this->result = $this->conn->query($this->query);
        // print_r($this->result."\n");
        if(@$this->result->num_rows <= 0){
            
            if(!mysqli_ping($this->conn)){
                $this->reconnect();
                $this->execute();
            }
            else if(@$this->result->num_rows == 0){
                // echo "0 results\n";
                // exit;
                return $select_result;
            }
            else{
                echo "SQL Error: " . mysqli_error($this->conn)."\n";
                exit;
            }
        }

        while($row = $this->result->fetch_assoc()){
            
  
            array_push($select_result, $row);
            
        }

        return $select_result;

    }

    public function generate_query_stack($arr_query_result){

        $map_value_count = 0;
        $map_count = 1;
        $map_array = array('Journal' => array('JournalName', 'JournalAbbreviations'),
                            'Author' => array('Authors_FullName'),
                            'Paper Name' => array('PaperName'));

        foreach($arr_query_result as $key => $value){

            $if_map = 0;
            
            if($map_count % 2 == 0){
                $map_count = 0;
            }
            if (strpos($key, 'select') !== false){
                foreach($map_array as $map_key => $map_values){
                    if($value == $map_key){
                        $if_map = 1;
                        foreach($map_values as $map_value){
                            
                            $map_value_count = $map_value_count + 1;
                            array_push($this->label, $map_value);
                        }
                    }
                }
                if($if_map == 0){
                    $map_value_count = $map_value_count + 1;
                    array_push($this->label, $value);
                }
            }
            else{
                // print("value:".$value."map_value_count:".$map_value_count);
                for($i=0 ; $i < $map_value_count ; $i++){
                    array_push($this->input, $value);
                }
                $map_value_count = 0;
            }
            $map_count = $map_count + 1;
        }
    }

    public function generate_sql($type) {

        $arr_count = 0;
        $legal_count = 0;


        // print_r($this->label);
        // print_r($this->input);
        $this->query = "SELECT * FROM `paper_research` WHERE ";
        foreach($this->label as $key => $value){
            $t_label = $this->label[$key];
            $t_input = $this->input[$key];
            $arr_count = $arr_count + 1;
            if($arr_count <= count($this->label)){
            //    print($t_label);
            
                
                if($t_input){
                    if($legal_count == 0){
                        if($t_label == 'JournalName'){
                            $condition = "(`$t_label` LIKE '%$t_input%'";
                            $legal_count = $legal_count + 1;
                        }
                        else{
                            $condition = "`$t_label` LIKE '%$t_input%'";
                            $legal_count = $legal_count + 1;
                        }
                    }
                    else{
                        // print($t_label);
                        if($t_label == 'JournalAbbreviations'){
                            $condition = " or `$t_label` LIKE '%$t_input%' )";
                        }
                        else{
                            if($t_label == 'JournalName'){
                                $condition = " $type (`$t_label` LIKE '%$t_input%'";
                            }
                            else{
                                $condition = " $type `$t_label` LIKE '%$t_input%'";
                            }
                        }
                        $legal_count = $legal_count + 1;
                    }
                }
                else{
                    $condition = "";
                    $legal_count = $legal_count + 1;
                }

                
            }
            $this->query = $this->query.$condition;
        }

    }

}



?>