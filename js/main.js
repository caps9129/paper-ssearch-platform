$('[data-toggle="tooltip"]').tooltip({
  tooltipClass: "passwordTooltip"
})

function copy(clicked_ID){
  // $('#'+clicked_ID).tooltip('enable')
  console.log(clicked_ID)
  var copyText = document.getElementById(clicked_ID).href;
  document.addEventListener('copy', function(e) {
    e.clipboardData.setData('text/plain', copyText);
    e.preventDefault();
  }, true);

  document.execCommand('copy');  
  console.log('copied text : ', copyText);

}

function recent_submitform() {
  document.forms["recent_search_form"].submit();
 
}

function recommend_choice_submitform() {
  document.forms["recommend_choice_form"].submit();
 
}

function recommend_search_submitform() {
  document.forms["recommend_search_form"].submit();
 
}


function AER_submitform()
{
  document.forms["AER_form"].submit();
}


function select_option()
{
  var x = document.getElementById("input-select-0").value
  if(x == 'ALL'){
    $('#search').val('')
    $('#search').attr('placeholder','Enter Keywords?');
  }
  else if(x == 'Paper Name'){
    console.log(x)
    $('#search').val('')
    $('#search').attr('placeholder','Enter Paper Name?');
  }
  else if(x == 'Author'){
    $('#search').val('')
    $('#search').attr('placeholder','Enter Author?');
  }
  else if(x == 'Year'){
    $('#search').val('')
    $('#search').attr('placeholder','Enter Year?');
  }
  else if(x == 'Journal'){
    $('#search').val('')
    $('#search').attr('placeholder','Enter Journal?');
  }
  else if(x == 'Keyword'){
    $('#search').val('')
    $('#search').attr('placeholder','Enter Keyword?');
  }
}

$( document ).ready(function() {
 
 
  

  console.log(Data);
  $( ".inputFieldClass" ).autocomplete({
    source: Data,
    minLength: 1,
    max:5,
    search: function(event, ui) {
      showall = event.which === 40;
    },

    source: function(request, response) {
      var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex(request.term), "i");
      response($.map(Data, function(item) {
           if (matcher.test(item)) {
               return (item)
           }
      }).slice(0, 10));
      
    }

  });
  
  $('.s003 form .inner-form').css({'margin':'5px'})
  var zindex = 999;
  var search_index = 1;
  var row_index = 0;
  var del_count = 0

  var search_stack = Array()
  var z_stack = Array()
  var row_stack = Array()

  // $('.browser-default custom-select custom-select-lg mb-3').text() == 'A'


  $( document ).on( "click", ".btn-del", function() {


    var reclass = $(this).parent().parent().parent().attr('id')
    var intRegex = /[0-9 -()+]+$/; 

    poploc = z_stack.indexOf(parseInt(reclass.match(intRegex)[0]))


    search_index = search_index - 1;
    
    del_count = del_count + 1

    var update_search_stack = search_stack.slice()
    var update_z_stack = z_stack.slice()
    var update_row_stack = row_stack.slice()

    var old_search_stack = search_stack.slice()
    var old_z_stack = z_stack.slice()
    var update_row_stack = row_stack.slice()

 
    

    // 目標位置後面全部更新

    update_z_stack.forEach(function (item, index) {
      if(index > poploc){
        update_z_stack[index] = update_z_stack[index] + 1
      }
    });

    
    update_search_stack.forEach(function (item, index) {
      if(index > poploc){
        update_search_stack[index] = update_search_stack[index] - 1
      }
    });


    update_search_stack.splice(poploc, 1);
    update_z_stack.splice(poploc, 1);

    old_search_stack.splice(poploc, 1);
    old_z_stack.splice(poploc, 1);



    update_search_stack.forEach(function (item, index) {
   
      $('#input-select-' + update_search_stack[index] + ' option').filter(function() {
        return this.text == $('#input-select-' + old_search_stack[index] + ' :selected').text(); 
      }).prop('selected', true);


      $('#search-' + update_search_stack[index]).val($('#search-' + old_search_stack[index]).val());
      
    });



    $(String('#search-column-' + z_stack[(z_stack.length - 1)])).parent().remove()


    z_stack = update_z_stack;
  
      
    

    search_stack = update_search_stack 
    
    if(z_stack.length === 0){
      zindex = 999
    }
    else{
      zindex = z_stack[(z_stack.length - 1)] - 1
    }
    

   
    if(del_count % 2 == 0 ){

      row_index = row_index - 1
      
      $(String('.showBlockrow-' + row_stack[(row_stack.length - 1)])).remove()
      row_stack.pop() 
     
    }
    
     


  });

  //add input block in showBlock
  $("#btn-add").click(function () {
    
      
    // console.log(choices.getValue(True))
    if(search_index % 2 == 1 || search_index == 1){

      row_index = row_index + 1;
      $("#showBlock").append('<div class = "showBlockrow-' + row_index +'"><div class="container"><div class="row"></div></div></div>')
      row_stack.push(row_index)
    }

    $(".showBlockrow-" + row_index).children().children().append('<div class="col-md"><div id="search-column-' + zindex + '" class="search-column"><div class="inner-form"><div class="input-field first-wrap"><div id="input-select-' + search_index + '" class="input-select"><select class="browser-default custom-select custom-select-lg mb-3" name="select' + search_index + '"><option placeholder="">ALL</option><option>Paper Name</option><option>Author</option><option>Year</option><option>Journal</option><option>Keyword</option></select></div></div><div class="input-field second-wrap"><div class="ui-widget"><input id="search-' + search_index + '" name="value' + search_index + '" class="inputFieldClass"></div></div><div class="input-field third-wrap"><button class="btn-del" type="button"></button></div></div></div></div>');
    $(function ($) {
      $('#search-' + search_index).autocomplete({
        source: Data,
        minLength: 3,
        max:5,
        search: function(event, ui) {
          showall = event.which === 40;
        },
        source: function(request, response) {
          var results = $.ui.autocomplete.filter(Data, request.term)
          response(results.slice(0, 10));
        }
      });
    });
    search_stack.push(search_index)



    $('#search-column-' + zindex ).css({'z-index':zindex, 'position':'relative'})
    $('.s003 form .inner-form').css({'margin':'5px'})
    z_stack.push(zindex)
    zindex = zindex - 1
    search_index  = search_index + 1
    del_count = del_count - 1

    
    console.log(search_stack, z_stack, row_stack)

  });

 

});

