<?php

/* search condition difined
1.Search condition : ALL(keyword)  
  Query : Resource or Paper Name or Author or Date (keyword) 
2.Search condition: Author (keyword) + Paper Name(keyword1)
  Query : (1) Author(keyword) and Paper Name(keyword1) 
          (2) Author (keyword) or Paper Name(keyword1)
3.Search condition : ALL (keyword) + Author (keyword1)+ Paper Name(keyword2) 
  Query : (1) Resource or Paper Name or Author or Date (keyword) and  Author(keyword1) and  Paper Name(keyword2) 
          (2) Resource or Paper Name or Author or Date (keyword) and  Author(keyword1) or  Paper Name(keyword2)
6.Search condition : ALL(keyword) contains "AND"、"OR" operator in keyword
  Query : Resource or Paper Name or Author or Date (keyword) 
  combine Query result by operator
 */

 

  $data = json_decode($json);
  $results_per_page = 10;
  
  $label = array();
  $input = array();
  $result = array();

  $data = $_POST;
  $ifall = 0;
  $condition = 0;
  $muti_op = 0;
  $keep_label = "";
  $keep_value = "";
  require_once('./database/connectDB.php');
  $string = file_get_contents('./database/wordlist.json');

  $DataBase = new DBClass();
  // normal select = 0, AER select = 1, recommend choice = -2, recommend_search = -1, recent_search = 2
  $select_type = 0;




   // for normal search
  foreach($data as $key => $value){

   
    if (strpos($key, 'select') !== false){
      array_push($label, $value);
    }
    else{
      array_push($input, $value);          
    }
    
    if((strpos($value, 'AND') !== FALSE) or (strpos($value, 'OR')!== FALSE)){
      $muti_op = 1;
    
    }
  }

  // AER
  foreach($data as $key => $value){
    if (strpos($value, 'AER百大') !== false){
      array_push($label, $value);
      $select_type = 1;
    }
  }

  // for recommend
  foreach($data as $key => $value){
    if (strpos($key, 'recommend_choice') !== false){
      header('Location: '.$data['recommend_choice']);
      $select_type = -2;
    }
    elseif(strpos($key, 'recommend_search') !== false){
     
      $select_type = -1;
      $result = $DataBase->choice_select($progress);
      $choice_index = $data['recommend_search'];
      $choice_data = $result[$choice_index];  
               
    
    }
 
  }

  // for recent 
  foreach($data as $key => $value){
    if (strpos($value, 'recent_search') !== false){
      $select_type = 2;
    }
  }
 


  if(count($data) == 2){
    
    $keep_label = $label[0];
    $keep_value = $input[0];
  }
  

 


  foreach ($data as $value){
    
    if($ifall == 1){
      $keep_word = $value;
      break;
    }

    if ($value == 'ALL'){
      $ifall = 1;
    }
    
  }

  if(count($data) / 2 == 1 && $ifall == 1){
    if($muti_op == 0){
      $condition = 1;
    }
    else{
      $condition = 6;
    }
  }
  else if($ifall == 1){
    $condition = 3;
  }
    
  
  else if(count($data) / 2 == 1){
    $condition = 4;
  }
  else{
    $condition = 2;
  }
  


  $wordsToHighlight = array();
  
  if($condition == 6) {
    $split = explode(" ", $data['value0']);
    
    foreach ($split as $key => $value) {
 
      if(($value != 'OR') and ($value != 'AND')){
        if($key == 0){
          $st = $value;
          
        }
        else{
          $st = $st . ' ' . $value;
        }
 
      }
    } 
    array_push($wordsToHighlight, $st);
  }
  else {
    $wordsToHighlight = $input;
  }


  session_start();


  // // 四種狀態、如果回傳0

  $current_page = ($_GET['page'] == '') ? 1:$_GET['page'];
  $offset = ($current_page-1) * $results_per_page;

  $ordered_result = array();
  $back_result = array();


  if($current_page == 0 && $select_type != -2){

    

    $str = file_get_contents('./database/wordlist.json');
    $wordlist = json_decode($str, true);
    $shortest = -1;
    
    // print_r($wordlist);
    if($select_type == 1){
      $condition = 5;
      $result = $DataBase->select($data, $condition);
    }
    elseif($select_type == 2){
      $result = $DataBase->recent_choice_select();
      $DataBase->total_results = count($result);
    }
    elseif($select_type == -1){

      $result = array();
      $result[0] = $choice_data; 
     
      $DataBase->total_results = 1;
      $closest = "";
      $keep_label = "ALL";
      $keep_value = $result[0]['PaperName'];
    }
    else{

      $result = $DataBase->select($data, $condition);
      // print_r($result);
   

    }
  
    if($DataBase->total_results == 0 && $select_type != 2){
      foreach($wordlist as $word){
        $lev = levenshtein($keep_value, $word);
        if ($lev <= $shortest || $shortest < 0) {
          // set the closest match, and shortest distance
          $closest  = $word;
          $shortest = $lev;
        }
      }

    }

    $_SESSION['closest'] = $closest;
    $_SESSION['keep_label'] = $keep_label;
    $_SESSION['keep_value'] = $keep_value;
    $_SESSION['total_results'] = $DataBase->total_results;
    $_SESSION['query_result'] = $result;
    $_SESSION['total_pages'] = ceil($DataBase->total_results / $results_per_page); 
    $_SESSION['wordsToHighlight'] = $wordsToHighlight;
    $_SESSION['condition'] = $condition;
    $result = array_slice($result, $offset, $results_per_page);
    header('Location: '.'./show.php?page=1');
   
    // print_r($result);
  }
  else{
    $result = $_SESSION['query_result'];
    $result = array_slice($result, $offset, $results_per_page);
    // print_r($result);
  }


  function highlight($text, $words) {
    preg_match_all('~\w+~', $words, $m);
    if(!$m)
        return $text;
    $re = '~\\b(' . implode('|', $m[0]) . ')\\b~i';
    return preg_replace($re, '<span style="color:#dd4b39;"><b>$0</b></span>', $text);
  }


?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="colorlib.com">
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" />
    <link href="css/main.css" rel="stylesheet" />
    <link href="css/bootstrap.css" rel="stylesheet" />
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <!--bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.standalone.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.standalone.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css">
    <!--autocomplete -->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script type="text/javascript" src="./js/wordlist.js"></script>
    
  </head>
  <body>
    <div class="row">
      <nav class="navbar navbar-light" style="background-color: #f5f5f5; border-bottom: 1px solid #e5e5e5">
        <div class="s003" style="width: 2000px;justify-content: left;">
        <div class="col-1">
          <div id="slogo">
            <a href="./index.php" style="color:black">學術搜尋</a>
          </div>
        </div>
        <div class="col-10">
          <form action="./show.php?page=0" method="post">
            <div id='showBlock'>
              <div class='search-column-1000' style="z-index: 1000; position: relative;">
                <div class="inner-form">
                  <div class="input-field first-wrap">
                    <div class="input-select">
                      <select class="browser-default custom-select custom-select-lg mb-3" id="input-select-0" name="select0" onchange="select_option()">
                        <!-- <option placeholder="">ALL</option> -->
                        <option value='ALL' <?php if($_SESSION['keep_label'] == 'ALL') echo 'selected';?>>ALL</option>
                        <option value='Paper Name' <?php if($_SESSION['keep_label'] == 'Paper Name') echo 'selected';?>>Paper Name</option>
                        <option value='Author' <?php if($_SESSION['keep_label'] == 'Author') echo 'selected';?>>Author</option>
                        <option value='Year' <?php if($_SESSION['keep_label'] == 'Year') echo 'selected';?>>Year</option>
                        <option value='Journal' <?php if($_SESSION['keep_label'] == 'Journal') echo 'selected';?>>Journal</option>
                        <option value='Keyword' <?php if($_SESSION['keep_label'] == 'Keyword') echo 'selected';?>>Keyword</option>
                      </select>      
                    </div>
                  </div>
                  <div class="input-field second-wrap">
                    <div class="ui-widget">
                      <input id="search" type="text" placeholder="Enter Keywords?", value = "<?php echo $_SESSION['keep_value'];?>", name="value0", class="inputFieldClass"/>
                    </div>  
                  </div>
                  <div class="input-field third-wrap">
                    <button class="btn-search" type="submit">
                        <svg class="svg-inline--fa fa-search fa-w-16" aria-hidden="true" data-prefix="fas" data-icon="search" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path>
                        </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </nav>
    </div>
    <div class="row result">
      <div class="col-1"></div>
      <div class="col-4" style="left: 35px;">
        <a class="count">約有 <?php echo $_SESSION['total_results'];?> 項結果，這是第 <?php echo $current_page;?> 頁</a>
      </div>
  </div>


<div class="row">
  <div class="col-1"></div>
  <div class="col-6" style="left: 22px;">
    <table class="table table-borderless">
      <tbody>
        <?php 
          $count = 0;
          if($_SESSION['total_results'] == 0){
            echo "<a class='suggestion'>您是不是要查 : ".$_SESSION['closest']."</a>";
          }
          foreach($result as $key=>$value){
            
            preg_match_all('/(\/\/*)(\w+)(\/\/*)(\w+)(\/\/*)(\w+)(\/\/*)([\s\S]*)/', $value['原文link'], $output_array);
            $ID = str_pad($value['ID'], 6,'0', STR_PAD_LEFT);
            if($value['原文link']){
              // @$output_array[8][0] = str_replace("'", "\'", @$output_array[8][0]);
              $enlink = "href=\"./DB/".@$output_array[8][0]."\"";
              
            }
            else{
              $enlink = "";
            }
            preg_match_all('/(\/\/*)(\w+)(\/\/*)(\w+)(\/\/*)(\w+)(\/\/*)(.*)/', $value['中文link'], $output_array);
            if($value['中文link']){
              $cnlink = "href=\"./DB/".$ID."/".@$output_array[8][0]."\"";
            }
            else{
              $cnlink = "";
            }
            
            $fdlink = "href='file://140.109.160.188/web/DB/".$ID."'";

            if($value['Citation']){
              $citation = $value['Citation'];
            }
            else{
              $citation = 0;
            }
            if($_SESSION['wordsToHighlight'][0] == FALSE) {
   
              $PaperName = $value['PaperName'];
              $Author = $value['Authors_FullName'];
              $Journal = $value['JournalName'];
              $Year = $value['Year'];
      
            }

            else {
              // print("in");
              // print_r($value['PaperName']);
              if($_SESSION['condition'] == 6){
                $PaperName = highlight($value['PaperName'], $_SESSION['wordsToHighlight'][0]);
                $Author = highlight($value['Authors_FullName'], $_SESSION['wordsToHighlight'][0]);
                $Journal = highlight($value['JournalName'], $_SESSION['wordsToHighlight'][0]);
                $Year = highlight($value['Year'], $_SESSION['wordsToHighlight'][0]);
              }
              else{
                $PaperName = preg_replace('/'.implode('|', $_SESSION['wordsToHighlight']).'/i', '<span style="color:#dd4b39;"><b>$0</b></span>', $value['PaperName']);
                $Author = preg_replace('/'.implode('|', $_SESSION['wordsToHighlight']).'/i', '<span style="color:#dd4b39;"><b>$0</b></span>', $value['Authors_FullName']);
                $Journal = preg_replace('/'.implode('|', $_SESSION['wordsToHighlight']).'/i', '<span style="color:#dd4b39;"><b>$0</b></span>', $value['JournalName']);
                $Year = preg_replace('/'.implode('|', $_SESSION['wordsToHighlight']).'/i', '<span style="color:#dd4b39;"><b>$0</b></span>', $value['Year']);
              }
        

            }
            // if($PaperName){
            echo"
              <tr>
                <td>
                  <a class='title' ".$enlink. ">".$PaperName."</a>
                  <p class='author'>".$Author."</p>
                  <p><a class='journal'>".$Journal."</a><a class='year'>".$Year."</a><a class='citation'>"."被引用".$citation."次"."</a><a class='cnlink'".$cnlink.">中文版</a><a id='fld_$count' onClick='copy(this.id)' class='floderlink' data-toggle='tooltip' title='點擊此處複製資料夾鏈結，再貼至網址列按下Enter鍵即可進入本資料夾'".$fdlink.">資料夾鏈結</a></p>
                </td>
              </tr>";
              $count++;
            }
            
          // }
        ?>
      </tbody>
    </table>
  </div> 
</div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.js"></script>
    <script src="js/main.js"></script>
    <footer class="page-footer text-md-left">
      <div class="row">
      <div class="col-3"></div>
      <div class="col-6" style="left: 22px;">
      
        <?php
          if($current_page != 1){
            $i = $current_page - 1;
            echo '
              <a href="show.php?page='.$i.'">
                <button type="button" class="btn btn-outline-dark"><i class="arrow left"></i></button>
              </a>';
          }
      
          if($_SESSION['total_pages'] > 1){
            for ($i=1; $i <= $_SESSION['total_pages']; $i++) {
              if ($i !=  $current_page){
                echo '<a style="padding:5px" href="show.php?page='.$i.'">'.$i.'</a>';
              }
              else {
                echo '<a style="padding:5px">'.$i.'</a>';
              }
            }
          }

          if($current_page != $_SESSION['total_pages'] and $current_page >= 1){
            $i = $current_page + 1;
            echo '
              <a href="show.php?page='.$i.'">
                <button type="button" class="btn btn-outline-dark"><i class="arrow right"></i></button>
              </a>';
          }
          // echo "current page is.$current_page"
        ?>
      </div>
      </div>
    </footer>
  </body>
</html> 