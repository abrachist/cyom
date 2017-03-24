    
    $(function() {
        $('.selectize').selectize({
            preload: false,
            sortField: [[]],
            initData: true
        });
    });
    

    // jquery function to initial datatable  
    function initDataTable(id_elemen){
        return $('#'+id_elemen).DataTable({
                  bSort: false,
                  bPaginate: false,
                  bInfo: false,
                  bFilter: false,
                  bScrollCollapse: false,
                  scrollX: false
              });
    }
    
    // jquery function to initial selectize selectbox control
    function initSelectize(id_elemen){
        $('#'+id_elemen).selectize({
            preload: false,
            sortField: [[]],
            initData: true
        });
    }

    // jquery function to load selectbox control option item use ajax
    function loadSelectizeList(id_elemen, ajaxUrl) {
        $.ajax({
            url: ajaxUrl,
            success: function(data) {
                        var item = $('#'+id_elemen)[0].selectize;
                        item.load(function(callback) {
                            callback(eval(JSON.stringify(data.lists)));
                        });
            }
        });
    }

    // jquery function to load all item detail selectbox control option item use ajax
    function loadAllSelectizeList(id_elemen, counter, ajaxUrl) {
        $.ajax({
            url: ajaxUrl,
            success: function(data) {
                for(var i = 0; i < counter; i++){
                    if($('#'+id_elemen+i).length != 0){
                        var item = $('#'+id_elemen+i)[0].selectize;
                        item.load(function(callback) {
                            callback(eval(JSON.stringify(data.lists)));
                        });
                    }
                }
            }
        });
    }

    // jquery function to load all item detail selectbox control option item use ajax on transaction edit
    function addAllSelectizeList(id_elemen, counter, ajaxUrl) {
        $.ajax({
            url: ajaxUrl,
            success: function(data) {
                for(var i = 0; i < counter; i++){
                    if($('#'+id_elemen+i).length != 0){
                        var item = $('#'+id_elemen+i)[0].selectize;
                        for(var x = 0; x < count(data.lists); x++){
                            item.addOption({value:data.lists[x].value, text:data.lists[x].text});
                        }
                    }
                }
            }
        });
    }

    // jquery function to multiply elemen controller item detail
    function multiplyChild2operand(opr1,opr2,counter,resultOne,resultAll) {

        var rows_length = counter+1;

        var subtotal = 0;

        for (var i = 0; i < rows_length; i++) {
            if($('#'+opr1+i).length > 0) {
                    var total_per_row = $('#'+opr1+i).val() * $('#'+opr2+i).val();
                    subtotal += total_per_row;
                    $('#'+resultOne+i).val(total_per_row);
            }
        }

        $('#'+resultAll).val(subtotal);    
    }



    
