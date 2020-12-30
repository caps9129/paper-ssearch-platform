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
    
    <?php
      include('./database/create_random.php');
      $output_array = array();
      preg_match_all('/(\/\/*)(\w+)(\/\/*)(\w+)(\/\/*)(\w+)(\/\/*)([\s\S]*)/', $choice_data['原文link'], $output_array);
      $ID = str_pad($choice_data['ID'], 6,'0', STR_PAD_LEFT);
      $enlink = "./DB/".@$output_array[8][0];
    ?>
  

  </head>
  <body>

    <div class="container" style="padding-top:5%" >
      <div id="logo">
        學術搜尋
      </div>
    </div>
    <div class="s003">
      <form target="_blank" action="../show.php?page=0" method="post">
        <div id='showBlock'>
          <div class='search-column-1000' style="z-index: 1000; position: relative;">
            <div class="inner-form">
              <div class="input-field first-wrap">
                <div class="input-select" >
                  <select class="browser-default custom-select custom-select-lg mb-3" id="input-select-0" name="select0" onchange="select_option()">
                    <option value="ALL" placeholder="">ALL</option>

                    <option value="Paper Name">Paper Name</option>
                    <option value="Author">Author</option>
                    <option value="Year">Year</option>
                    <option value="Journal">Journal</option>
                    <option value="Keyword">Keyword</option>
                  </select>      
                </div>
              </div>
              <div class="input-field second-wrap">
                <div class="ui-widget">
                  <input id="search" type="text" placeholder="Enter Keywords?" name="value0" class="inputFieldClass">
                </div>  
              </div>
              <div class="input-field third-wrap">
                <button class="btn-add" type="button" id="btn-add"></button>
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
    <div class="wrapper">
      <div class="item1">
        <form id="AER_form" target="_blank" action="../show.php?page=0" method="post">
          <input type="hidden" name="type" value="AER百大">
          <a id="AER100" href="javascript: AER_submitform()">
            <span class="w3-hide-small">AER百年百大文章</span>
          </a>  
        </form>
      </div>
      <div class='item2'>
        <div class='recommend_column'>
            <div class='recommend_cell'>
              <form id="recommend_choice_form" target="_blank" action="../show.php?page=0" method="post">
                <input type="hidden" name="recommend_choice" value="<?php echo $enlink; ?>">

                <a class='recommend' href="javascript: recommend_choice_submitform()">
                  <span class="w3-hide-small">隨機推薦</span>
                </a>                             
              </form>
            </div>

            <div class='recommend_cell'>
              <form id="recommend_search_form" target="_blank" action="../show.php?page=0" method="post">
                <input type="hidden" name="recommend_search" value=<?php echo $choice_index; ?>>
          
                <a class='recommend' href="javascript: recommend_search_submitform()">
                  <span class="glyphicon glyphicon-search"></span>
                </a>                         
              </form>
            </div>

            <div class='recommend_cell'>
              <form id="recent_search_form" target="_blank" action="../show.php?page=0" method="post">
                <input type="hidden" name="type" value="recent_search">
                <a id="AER100" href="javascript: recent_submitform()">
                  <span class="w3-hide-small">近期更新</span>
                </a>  
              </form>
            </div>

          </div>
        </div> 
      </div>
    </div>

  


    <!-- <script src="js/extention/choices.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.js"></script>
    <script src="js/main.js"></script>
    

    <script>

    </script>
  </body><!-- This templates was made by Colorlib (https://colorlib.com) -->
</html>

