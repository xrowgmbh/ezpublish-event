var divIDPrefix = 'ezpeventperiod',
    divExcludeIDPrefix = 'ezpeventexcludeperiod',
    ezpeCounter = {include: 1,
                   exclude: 1};
jQuery(document).ready(function() {
    $('.ezpevent').each(function(){
        if(typeof $(this).data('attrid') !== 'undefined') {
            var attrid = $(this).data('attrid');
            if(typeof $('#counterInclude'+attrid) !== 'undefined' && parseInt($('#counterInclude'+attrid).data('counter')) > 1)
                ezpeCounter['include'] = $('#counterInclude'+attrid).data('counter');
            if(typeof $('#counterExclude'+attrid) !== 'undefined' && parseInt($('#counterExclude'+attrid).data('counter')) > 1)
                ezpeCounter['exclude'] = $('#counterExclude'+attrid).data('counter');
        }
        if(typeof $(this).data('locale') !== 'undefined') {
            var datePickerLocale = $(this).data('locale'),
                datePickerName = '.ezpublisheventdate';
            $.datepicker.setDefaults(datePickerLocale);
            $(datePickerName).each(function(){
                initDate($(this));
            });
        }
    });
    // add new include period
    $('.ezpevent_add_period').each(function(){
        $(this).on( 'click', function(event){
            event.preventDefault();
            appendPeriod($(this), divIDPrefix, 'include', 'ezpevent_remove_period');
        });
    });
    $('.ezpevent_remove_period').each(function(){
        $(this).on( 'click', function(event){
            event.preventDefault();
            if(typeof $(this).data('index') !== 'undefined') {
                removePeriod($(this), divIDPrefix, 'include', $('.ezpevent_add_period'), 'ezpevent_remove_period');
            }
        });
    });
    // add new exclude period
    $('.ezpevent_add_exclude_period').each(function(){
        $(this).on( 'click', function(event){
            event.preventDefault();
            appendPeriod($(this), divExcludeIDPrefix, 'exclude', 'ezpevent_remove_exclude_period');
        });
    });
    $('.ezpevent_remove_exclude_period').each(function(){
        $(this).on( 'click', function(event){
            event.preventDefault();
            if(typeof $(this).data('index') !== 'undefined') {
                removePeriod($(this), divExcludeIDPrefix, 'exclude', $('.ezpevent_add_exclude_period'), 'ezpevent_remove_exclude_period');
            }
        });
    });
    
    /* EVENT DATEPICKER LOGIC */
    if ($(".event_datefield_from").length > 0)
    {
        var myDate = new Date();
        var formated_days = myDate.getDate();
        var formated_months = myDate.getMonth()+1;
        if(formated_days<10) formated_days = "0" + formated_days;
        if(formated_months<10) formated_months= "0" + formated_months;
        if($(".event_datefield_to").val() == $(".event_datefield_from").val() && $(".event_datefield_to").val() == "" &&  $(".event_datefield_from").val() == "")
        {
            $(".event_datefield_from, .event_datefield_to").val( formated_days + '.' +  formated_months + '.' + myDate.getFullYear() );
            var nextDayDate = new Date();
            nextDayDate.setDate(myDate.getDate());
            nextDate = nextDayDate.getDate();
            nextMonth = nextDayDate.getMonth()+1;
            if(nextDate<10) nextDate = "0" + nextDate;
            if(nextMonth<10) nextMonth= "0" + nextMonth;
            $(".event_datefield_to").val( nextDate + '.' +  nextMonth + '.' + myDate.getFullYear() );
        }
        
        if ( $(".dp_language").val() != "GB" )
        {
            $.getScript("/extension/hannover/design/hannover/javascript/datepicker_locals/jquery.ui.datepicker-" + $(".dp_language").val() + ".js", function(){
            });
        }
        
        $( ".event_datefield_from" ).datepicker({
            showOn: "button",
            buttonImage: "/extension/hannover/design/hannover/images/calendar.png",
            buttonImageOnly: true,
            buttonText: $(".dp_button_value").val(),
            dateFormat: 'dd.mm.yy',
            minDate: new Date(),
            onSelect: function( selectedDate ) {
                var ctoday_split_array = selectedDate.split('.');
                var myCToday=new Date(ctoday_split_array[2],ctoday_split_array[1]-1,ctoday_split_array[0]);
                myCToday.setDate(myCToday.getDate());
                var next_today_year = myCToday.getFullYear();
                var next_today_month = myCToday.getMonth()+1;
                var next_today_day = myCToday.getDate();
                next_today_day = parseInt(next_today_day)<10 ?"0"+next_today_day:next_today_day;
                next_today_month = parseInt(next_today_month)<10 ?"0"+next_today_month:next_today_month;
                var this_my_day = next_today_day+'.'+next_today_month+'.'+next_today_year;
                $( ".event_datefield_to" ).datepicker( "option", "minDate", this_my_day );
                
                var myday=$(this).val();
                if(myday == $(".event_datefield_to").val())
                {
                    var split_array = myday.split('.');
                    var myDate=new Date(split_array[2],split_array[1]-1,split_array[0]);
                    myDate.setDate(myDate.getDate());
                    var next_day_year = myDate.getFullYear();
                    var next_day_month = myDate.getMonth()+1;
                    var next_day_day = myDate.getDate();
                    next_day_day = parseInt(next_day_day)<10 ?"0"+next_day_day:next_day_day;
                    next_day_month = parseInt(next_day_month)<10 ?"0"+next_day_month:next_day_month;
                    $(".event_datefield_to").val( next_day_day + '.' +  next_day_month + '.' + next_day_year );
                }
            }
        });
        
        var today=$(".event_datefield_to").val();
        var today_split_array = today.split('.');
        var myToday=new Date(today_split_array[2],today_split_array[1]-1,today_split_array[0]);
        
        $( ".event_datefield_to" ).datepicker({
            showOn: "button",
            buttonImage: "/extension/hannover/design/hannover/images/calendar.png",
            buttonImageOnly: true,
            buttonText: $(".dp_button_value").val(),
            dateFormat: 'dd.mm.yy',
            minDate: myToday,
            onSelect: function( selectedDate ) {
                $( ".event_datefield_from" ).datepicker();
            }
        });
    }
    
    //for event search
    $.date = function(dateObject) {
        var d = new Date(dateObject * 1000);
        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        if (day < 10) {
            day = "0" + day;
        }
        if (month < 10) {
            month = "0" + month;
        }
        var date = day + "." + month + "." + year;
        return date;
    };
    $.datejava = function(dateObject) {
        var d = new Date(dateObject);
        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        if (day < 10) {
            day = "0" + day;
        }
        if (month < 10) {
            month = "0" + month;
        }
        var date = day + "." + month + "." + year;
        return date;
    };

    if($("#contenteventsearch").length > 0)
    {
        var sorttype ='';
        var searchtext_temp='';
        var subtreearray='';
        var fromDate='';
        var toDate='';
        var event_city='';
        var free_event='';
        var long_event='';
        var facetlist_temp=$('.content-searchevent .search_form').serializeArray();

        $.each(facetlist_temp,function(i,fd)
        {
            if(fd.name=='sort_type'){
                sorttype=fd.value;
            }else if(fd.name=='SearchText'){
                searchtext_temp=fd.value;
            }else if(fd.name=='SubTreeArray'){
                subtreearray=fd.value;
            }else if(fd.name=='fromDate'){
                fromDate=fd.value;
            }else if(fd.name=='toDate'){
                toDate=fd.value;
            }else if(fd.name=='event_city'){
                event_city=fd.value;
            }else if(fd.name=='free_event'){
                free_event=fd.value;
            }else if(fd.name=='long_event'){
                long_event=fd.value;
            }
         });
        var param = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                     'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,'sort_type':sorttype};
        var url = '/event/searchevent';
        $("#search_results").children().remove();
        $("#search_results").addClass("is_loading");
        var aj=$.ajax({
            url:url,
            data:(param),
            type:'POST',
            dataType:'json',
            success:function(data){
                var today1 = $.datejava(new Date().getTime());
                var tomorrow1 = $.datejava(new Date().getTime() + 3600*24*1000);
                $("#search_results").removeClass("is_loading");
                for (var i in data)
                {
                    if(i == 'result_nummer')
                    {
                        $('.search_result_number_line .result_numer').html(data[i]);
                        var optInit = getOptionsFromForm();
                        $("#Pagination").pagination(data[i], optInit);
                    }
                }
                
                function pageselectCallback(page_index, jq){
                    $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                    $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                    var items_per_page = $('#items_per_page').val();
                    var max_elem = Math.min((page_index+1) * items_per_page, data.facet_list.length);
                    var newcontent = '';
                    var params = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                                  'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,
                                  'sort_type':sorttype,'offset':page_index};
                    if(page_index >= 1)
                    {   
                        $("#Pagination .prev img").remove();
                        $("#Pagination .next img").remove();
                        $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                        $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                        $("#search_results").addClass("is_loading");
                        $("#search_results").children().remove();
                        var ajx=$.ajax({
                            url:url,
                            data:(params),
                            type:'POST',
                            dataType:'json',
                            success:function(datas){ 
                                var max_elems = Math.min((page_index+1) * items_per_page, datas.facet_list.length);
                                for(var i=0;i<max_elems;i++)
                                {
                                    var today2=$.date(datas.facet_list[i][0]);
                                    var mark= datas.facet_list[i][2];
                                    if(today1 == today2 && mark == 0)
                                    {
                                        newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                    }else if(tomorrow1 == today2 && mark == 0){
                                        newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                    }else if(mark == 0){
                                        newcontent +='<h1 style="margin-bottom:0;">'+$.date(datas.facet_list[i][0])+'</h1>';
                                    }
                                    newcontent += datas.facet_list[i][1];
                                }
                                $("#search_results").removeClass("is_loading");
                                $("#search_results").children().remove();
                                $('#search_results').append(newcontent);
                            },
                            error:function(){
                                alert("error page!");
                            } });
                    }else{
                        for(var i=page_index*items_per_page;i<max_elem;i++)
                        {
                            var today2=$.date(data.facet_list[i][0]);
                            var mark= data.facet_list[i][2];
                            if(today1 == today2 && mark == 0)
                            {
                                newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                            }else if(tomorrow1 == today2 && mark == 0){
                                newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                            }else if(mark == 0){
                                newcontent +='<h1 style="margin-bottom:0;">'+$.date(data.facet_list[i][0])+'</h1>';
                            }
                            newcontent += data.facet_list[i][1];
                        }
                        $("#search_results").removeClass("is_loading");
                        $("#search_results").children().remove();
                        $('#search_results').append(newcontent);
                    }
                    return false;
                }
                function getOptionsFromForm(){
                    var opt = {callback: pageselectCallback};
                    $("input:text").each(function(){
                        opt[this.name] = this.className.match(/numeric/) ? parseInt(this.value) : this.value;
                    });
                    var htmlspecialchars ={ "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;"}
                    $.each(htmlspecialchars, function(k,v){
                        opt.prev_text = opt.prev_text.replace(k,v);
                        opt.next_text = opt.next_text.replace(k,v);
                    })
                    return opt;
                }
            },
            error:function(){
                alert("error!");
            }
        });
    }
    
    $("#contenteventsearch .searchtext").live("keydown",function(e){
        if(e.keyCode==13)
        { 
            var sorttype ='';
            var searchtext_temp='';
            var subtreearray='';
            var fromDate='';
            var toDate='';
            var event_city='';
            var free_event='';
            var long_event='';
            var facetlist_temp=$('.content-searchevent .search_form').serializeArray();

            $.each(facetlist_temp,function(i,fd)
            {
                if(fd.name=='sort_type'){
                    sorttype=fd.value;
                }else if(fd.name=='SearchText'){
                    searchtext_temp=fd.value;
                }else if(fd.name=='SubTreeArray'){
                    subtreearray=fd.value;
                }else if(fd.name=='fromDate'){
                    fromDate=fd.value;
                }else if(fd.name=='toDate'){
                    toDate=fd.value;
                }else if(fd.name=='event_city'){
                    event_city=fd.value;
                }else if(fd.name=='free_event'){
                    free_event=fd.value;
                }else if(fd.name=='long_event'){
                    long_event=fd.value;
                }
             });
            var param = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                         'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,'sort_type':sorttype};
            var url = '/event/searchevent';
            $("#search_results").children().remove();
            $("#search_results").addClass("is_loading");
            var aj=$.ajax({
                url:url,
                data:(param),
                type:'POST',
                dataType:'json',
                success:function(data){
                    var today1 = $.datejava(new Date().getTime());
                    var tomorrow1 = $.datejava(new Date().getTime() + 3600*24*1000);
                    $("#search_results").removeClass("is_loading");
                    for (var i in data)
                    {
                        if(i == 'result_nummer')
                        {
                            $('.search_result_number_line .result_numer').html(data[i]);
                            var optInit = getOptionsFromForm();
                            $("#Pagination").pagination(data[i], optInit);
                        }
                    }
                    
                    function pageselectCallback(page_index, jq){
                        $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                        $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                        var items_per_page = $('#items_per_page').val();
                        var max_elem = Math.min((page_index+1) * items_per_page, data.facet_list.length);
                        var newcontent = '';
                        var params = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                                      'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,
                                      'sort_type':sorttype,'offset':page_index};
                        if(page_index >= 1)
                        {   
                            $("#Pagination .prev img").remove();
                            $("#Pagination .next img").remove();
                            $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                            $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                            $("#search_results").addClass("is_loading");
                            $("#search_results").children().remove();
                            var ajx=$.ajax({
                                url:url,
                                data:(params),
                                type:'POST',
                                dataType:'json',
                                success:function(datas){ 
                                    var max_elems = Math.min((page_index+1) * items_per_page, datas.facet_list.length);
                                    for(var i=0;i<max_elems;i++)
                                    {
                                        var today2=$.date(datas.facet_list[i][0]);
                                        var mark= datas.facet_list[i][2];
                                        if(today1 == today2 && mark == 0)
                                        {
                                            newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                        }else if(tomorrow1 == today2 && mark == 0){
                                            newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                        }else if(mark == 0){
                                            newcontent +='<h1 style="margin-bottom:0;">'+$.date(datas.facet_list[i][0])+'</h1>';
                                        }
                                        newcontent += datas.facet_list[i][1];
                                    }
                                    $("#search_results").removeClass("is_loading");
                                    $("#search_results").children().remove();
                                    $('#search_results').append(newcontent);
                                },
                                error:function(){
                                    alert("error page!");
                                } });
                        }else{
                            for(var i=page_index*items_per_page;i<max_elem;i++)
                            {
                                var today2=$.date(data.facet_list[i][0]);
                                var mark= data.facet_list[i][2];
                                if(today1 == today2 && mark == 0)
                                {
                                    newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                                }else if(tomorrow1 == today2 && mark == 0){
                                    newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                                }else if(mark == 0){
                                    newcontent +='<h1 style="margin-bottom:0;">'+$.date(data.facet_list[i][0])+'</h1>';
                                }
                                newcontent += data.facet_list[i][1];
                            }
                            $("#search_results").removeClass("is_loading");
                            $("#search_results").children().remove();
                            $('#search_results').append(newcontent);
                        }
                        return false;
                    }
                    function getOptionsFromForm(){
                        var opt = {callback: pageselectCallback};
                        $("input:text").each(function(){
                            opt[this.name] = this.className.match(/numeric/) ? parseInt(this.value) : this.value;
                        });
                        var htmlspecialchars ={ "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;"}
                        $.each(htmlspecialchars, function(k,v){
                            opt.prev_text = opt.prev_text.replace(k,v);
                            opt.next_text = opt.next_text.replace(k,v);
                        })
                        return opt;
                    }
                },
                error:function(){
                    alert("error!");
                }
            });
        }
    });
    $("#contenteventsearch input, #contenteventsearch select").change(function() {
        var sorttype ='';
        var searchtext_temp='';
        var subtreearray='';
        var fromDate='';
        var toDate='';
        var event_city='';
        var free_event='';
        var long_event='';
        var facetlist_temp=$('.content-searchevent .search_form').serializeArray();

        $.each(facetlist_temp,function(i,fd)
        {
            if(fd.name=='sort_type'){
                sorttype=fd.value;
            }else if(fd.name=='SearchText'){
                searchtext_temp=fd.value;
            }else if(fd.name=='SubTreeArray'){
                subtreearray=fd.value;
            }else if(fd.name=='fromDate'){
                fromDate=fd.value;
            }else if(fd.name=='toDate'){
                toDate=fd.value;
            }else if(fd.name=='event_city'){
                event_city=fd.value;
            }else if(fd.name=='free_event'){
                free_event=fd.value;
            }else if(fd.name=='long_event'){
                long_event=fd.value;
            }
         });
        var param = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                     'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,'sort_type':sorttype};
        var url = '/event/searchevent';
        $("#search_results").children().remove();
        $("#search_results").addClass("is_loading");
        var aj=$.ajax({
            url:url,
            data:(param),
            type:'POST',
            dataType:'json',
            success:function(data){
                var today1 = $.datejava(new Date().getTime());
                var tomorrow1 = $.datejava(new Date().getTime() + 3600*24*1000);
                $("#search_results").removeClass("is_loading");
                for (var i in data)
                {
                    if(i == 'result_nummer')
                    {
                        $('.search_result_number_line .result_numer').html(data[i]);
                        var optInit = getOptionsFromForm();
                        $("#Pagination").pagination(data[i], optInit);
                    }
                }
                
                function pageselectCallback(page_index, jq){
                    $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                    $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                    var items_per_page = $('#items_per_page').val();
                    var max_elem = Math.min((page_index+1) * items_per_page, data.facet_list.length);
                    var newcontent = '';
                    var params = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                                  'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,
                                  'sort_type':sorttype,'offset':page_index};
                    if(page_index >= 1)
                    {   
                        $("#Pagination .prev img").remove();
                        $("#Pagination .next img").remove();
                        $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                        $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                        $("#search_results").addClass("is_loading");
                        $("#search_results").children().remove();
                        var ajx=$.ajax({
                            url:url,
                            data:(params),
                            type:'POST',
                            dataType:'json',
                            success:function(datas){ 
                                var max_elems = Math.min((page_index+1) * items_per_page, datas.facet_list.length);
                                for(var i=0;i<max_elems;i++)
                                {
                                    var today2=$.date(datas.facet_list[i][0]);
                                    var mark= datas.facet_list[i][2];
                                    if(today1 == today2 && mark == 0)
                                    {
                                        newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                    }else if(tomorrow1 == today2 && mark == 0){
                                        newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                    }else if(mark == 0){
                                        newcontent +='<h1 style="margin-bottom:0;">'+$.date(datas.facet_list[i][0])+'</h1>';
                                    }
                                    newcontent += datas.facet_list[i][1];
                                }
                                $("#search_results").removeClass("is_loading");
                                $("#search_results").children().remove();
                                $('#search_results').append(newcontent);
                            },
                            error:function(){
                                alert("error page!");
                            } });
                    }else{
                        for(var i=page_index*items_per_page;i<max_elem;i++)
                        {
                            var today2=$.date(data.facet_list[i][0]);
                            var mark= data.facet_list[i][2];
                            if(today1 == today2 && mark == 0)
                            {
                                newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                            }else if(tomorrow1 == today2 && mark == 0){
                                newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                            }else if(mark == 0){
                                newcontent +='<h1 style="margin-bottom:0;">'+$.date(data.facet_list[i][0])+'</h1>';
                            }
                            newcontent += data.facet_list[i][1];
                        }
                        $("#search_results").removeClass("is_loading");
                        $("#search_results").children().remove();
                        $('#search_results').append(newcontent);
                    }
                    return false;
                }
                function getOptionsFromForm(){
                    var opt = {callback: pageselectCallback};
                    $("input:text").each(function(){
                        opt[this.name] = this.className.match(/numeric/) ? parseInt(this.value) : this.value;
                    });
                    var htmlspecialchars ={ "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;"}
                    $.each(htmlspecialchars, function(k,v){
                        opt.prev_text = opt.prev_text.replace(k,v);
                        opt.next_text = opt.next_text.replace(k,v);
                    })
                    return opt;
                }
            },
            error:function(){
                alert("error!");
            }
        });
    });
    $("#contenteventsearch .submitbutton").click(function(){
        var sorttype ='';
        var searchtext_temp='';
        var subtreearray='';
        var fromDate='';
        var toDate='';
        var event_city='';
        var free_event='';
        var long_event='';
        var facetlist_temp=$('.content-searchevent .search_form').serializeArray();

        $.each(facetlist_temp,function(i,fd)
        {
            if(fd.name=='sort_type'){
                sorttype=fd.value;
            }else if(fd.name=='SearchText'){
                searchtext_temp=fd.value;
            }else if(fd.name=='SubTreeArray'){
                subtreearray=fd.value;
            }else if(fd.name=='fromDate'){
                fromDate=fd.value;
            }else if(fd.name=='toDate'){
                toDate=fd.value;
            }else if(fd.name=='event_city'){
                event_city=fd.value;
            }else if(fd.name=='free_event'){
                free_event=fd.value;
            }else if(fd.name=='long_event'){
                long_event=fd.value;
            }
         });
        var param = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                     'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,'sort_type':sorttype};
        var url = '/event/searchevent';
        $("#search_results").children().remove();
        $("#search_results").addClass("is_loading");
        var aj=$.ajax({
            url:url,
            data:(param),
            type:'POST',
            dataType:'json',
            success:function(data){
                var today1 = $.datejava(new Date().getTime());
                var tomorrow1 = $.datejava(new Date().getTime() + 3600*24*1000);
                $("#search_results").removeClass("is_loading");
                for (var i in data)
                {
                    if(i == 'result_nummer')
                    {
                        $('.search_result_number_line .result_numer').html(data[i]);
                        var optInit = getOptionsFromForm();
                        $("#Pagination").pagination(data[i], optInit);
                    }
                }
                
                function pageselectCallback(page_index, jq){
                    $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                    $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                    var items_per_page = $('#items_per_page').val();
                    var max_elem = Math.min((page_index+1) * items_per_page, data.facet_list.length);
                    var newcontent = '';
                    var params = {'event_city':event_city,'fromDate':fromDate,'toDate':toDate,'SubTreeArray':subtreearray,
                                  'SearchText':searchtext_temp,'long_event':long_event,'free_event':free_event,
                                  'sort_type':sorttype,'offset':page_index};
                    if(page_index >= 1)
                    {   
                        $("#Pagination .prev img").remove();
                        $("#Pagination .next img").remove();
                        $("#Pagination .prev").prepend('<img src="/extension/hannover/design/hannover/images/reverse_arrow.png">&nbsp;');
                        $("#Pagination .next").append('&nbsp;<img src="/extension/hannover/design/hannover/images/li_icon.png">');
                        $("#search_results").addClass("is_loading");
                        $("#search_results").children().remove();
                        var ajx=$.ajax({
                            url:url,
                            data:(params),
                            type:'POST',
                            dataType:'json',
                            success:function(datas){ 
                                var max_elems = Math.min((page_index+1) * items_per_page, datas.facet_list.length);
                                for(var i=0;i<max_elems;i++)
                                {
                                    var today2=$.date(datas.facet_list[i][0]);
                                    var mark= datas.facet_list[i][2];
                                    if(today1 == today2 && mark == 0)
                                    {
                                        newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                    }else if(tomorrow1 == today2 && mark == 0){
                                        newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(datas.facet_list[i][0])+')</h1>';
                                    }else if(mark == 0){
                                        newcontent +='<h1 style="margin-bottom:0;">'+$.date(datas.facet_list[i][0])+'</h1>';
                                    }
                                    newcontent += datas.facet_list[i][1];
                                }
                                $("#search_results").removeClass("is_loading");
                                $("#search_results").children().remove();
                                $('#search_results').append(newcontent);
                            },
                            error:function(){
                                alert("error page!");
                            } });
                    }else{
                        for(var i=page_index*items_per_page;i<max_elem;i++)
                        {
                            var today2=$.date(data.facet_list[i][0]);
                            var mark= data.facet_list[i][2];
                            if(today1 == today2 && mark == 0)
                            {
                                newcontent +='<h1 style="margin-bottom:0;">'+$("#today_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                            }else if(tomorrow1 == today2 && mark == 0){
                                newcontent +='<h1 style="margin-bottom:0;">'+$("#tomorrow_text").val()+' ('+$.date(data.facet_list[i][0])+')</h1>';
                            }else if(mark == 0){
                                newcontent +='<h1 style="margin-bottom:0;">'+$.date(data.facet_list[i][0])+'</h1>';
                            }
                            newcontent += data.facet_list[i][1];
                        }
                        $("#search_results").removeClass("is_loading");
                        $("#search_results").children().remove();
                        $('#search_results').append(newcontent);
                    }
                    return false;
                }
                function getOptionsFromForm(){
                    var opt = {callback: pageselectCallback};
                    $("input:text").each(function(){
                        opt[this.name] = this.className.match(/numeric/) ? parseInt(this.value) : this.value;
                    });
                    var htmlspecialchars ={ "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;"}
                    $.each(htmlspecialchars, function(k,v){
                        opt.prev_text = opt.prev_text.replace(k,v);
                        opt.next_text = opt.next_text.replace(k,v);
                    })
                    return opt;
                }
            },
            error:function(){
                alert("error!");
            }
        });
    });
});
/*events search end*/
var initDate = function(element) {
    element.datepicker({
        beforeShow: function() {
            if(typeof $(this).data('mindate') !== 'undefined') {
                var dateFrom = $('#'+$(this).data('mindate'));
                if(dateFrom.val() != '') {
                    $(this).datepicker('option', 'minDate', dateFrom.val());
                }
            }
        },
        onClose: function (dateText, inst) {
            if(typeof $(this).data('setdatefor') !== 'undefined') {
                var setDateForID = $(this).data('setdatefor');
                if(dateText != '' && $('#'+setDateForID).val() == '') {
                    $('#'+setDateForID).val(dateText);
                }
            }
            if(typeof $(this).data('index') !== 'undefined') {
                if($.trim($(this).val()) == '')
                    $('#ezpeventperiodweekdays_'+$(this).data('index')).hide();
                if($('#startdate_'+$(this).data('index')).length) {
                    var startDateVal = $('#startdate_'+$(this).data('index')).val(),
                        startDate = parseDate(startDateVal),
                        endDate = parseDate(dateText);
                    var days = (endDate-startDate)/86400000;
                    if(days >= 3) {
                        $('#ezpeventperiodweekdays_'+$(this).data('index')).show();
                    }
                    if(days < 3) {
                        $('#ezpeventperiodweekdays_'+$(this).data('index')).hide();
                    }
                }
            }
        },
        minDate: 0,
        changeMonth: true,
        changeYear: true,
        showWeek: true
    });
};
var parseDate = function(date) {
    if(strpos(date, '.') || strpos(date, '/') || strpos(date, '-')) {
        if(strpos(date, '.')) {
            var dateParts = date.split("."),
                date = new Date(dateParts[2], (dateParts[1] - 1), dateParts[0]);
        }
        else {
            var date = new Date(Date.parse(date));
        }
        return date;
    }
    else {
        //window.console.log('Your date format is not set in xrowevent.js');
        return false;
    }
};
var appendPeriod = function(element, div, findPrefix, removeButtonID) {
    var index = element.data('index'),
        new_index = ezpeCounter[findPrefix];
    if(ezpeCounter[findPrefix] == 1)
        index = 0;
    //window.console.log('appendPeriod index '+index+' new_index '+new_index);
    if(typeof $('#'+div+'_'+index) !== 'undefined' && $('#'+div+'_'+index).length) {
        var content = $('#'+div+'_'+index).html(),
            new_index = ezpeCounter[findPrefix];
        content = replaceIndex(content, findPrefix, element, index, new_index);
        // add new node after this
        var newHTML = '<div id='+div+'_'+new_index+'>'+content+'</div>';
        if(index == 0)
            newHTML = '<div id='+div+'_'+new_index+'><hr class="ezpeventhr" />'+content+'</div>';
        $(newHTML).insertAfter('#'+div+'_'+(new_index-1));
        initializeDefault(new_index, findPrefix, element, div, removeButtonID);
        ezpeCounter[findPrefix]++;
    }
};
var removePeriod = function(element, div, findPrefix, addButton, removeButtonID) {
    var index = element.data('index'),
        next_index = index+1,
        allDivsCounter = ezpeCounter[findPrefix];
    $('#'+div+'_'+index).remove();
    ezpeCounter[findPrefix]--;
    //window.console.log('1. initializeDefault removePeriod index '+index+' next index '+next_index+' counter '+ezpeCounter[findPrefix]);
    addButton.attr('data-index', ezpeCounter[findPrefix]);
    if(typeof $('#'+div+'_'+next_index) !== 'undefined' && $('#'+div+'_'+next_index).length) {
        for(i = next_index; i <= allDivsCounter ; i++) {
            if(typeof $('#'+div+'_'+i) !== 'undefined' && $('#'+div+'_'+i).length) {
                var content = $('#'+div+'_'+i).html();
                content = replaceIndex(content, findPrefix, element, i, (i-1));
                if(content) {
                    $('#'+div+'_'+i).remove();
                    $('<div id='+div+'_'+(i-1)+'>'+content+'</div>').insertAfter('#'+div+'_'+(i-2));
                    initializeDefault((i-1), findPrefix, addButton, div, removeButtonID);
                    //window.console.log('initializeDefault removePeriod Index '+(i-1));
                    var lastCounter = i-1;
                }
            }
        }
        if(typeof lastCounter !== 'undefined') {
            addButton.attr('data-index', lastCounter);
            ezpeCounter[findPrefix] = lastCounter+1;
        }
    }
    else if(ezpeCounter[findPrefix] == 1) {
        addButton.attr('data-index', 0);
    }
};
var initializeDefault = function(index, findPrefix, element, div, removeButtonID) {
    if(findPrefix == 'include')
        activeAllCheckboxes(index);
    // initilize datepicker
    $('#'+div+'_'+index+' .ezpublisheventdate').each(function(){
        initDate($(this));
    });
    // set event for remove selected include period
    $('#'+removeButtonID+'_'+index).show();
    //window.console.log('removeButtonID: #'+removeButtonID+'_'+index);
    $('#'+removeButtonID+'_'+index).on( 'click', function(event){
        event.preventDefault();
        removePeriod($(this), div, findPrefix, element, removeButtonID);
    });
};
var replaceIndex = function(content, findPrefix, element, index, new_index) {
    var findArray = {0: findPrefix+'\\]\\['+index,
            1: '_'+index,
            2: 'data-index="'+index+'"',
            3: ' hasDatepicker',
            4: 'value=".*?"'},
        replaceArray = {0: findPrefix+'\]\['+new_index,
            1: '_'+new_index,
            2: 'data-index="'+new_index+'"',
            3: '',
            4: 'value=""'};
    if(typeof content !== 'undefined') {
        for (key in findArray) {
            var regex = new RegExp(findArray[key], 'g'),
            content = content.replace(regex, replaceArray[key]);
        }
        element.attr('data-index', new_index);
        return content;
    }
    return '';
};
var activeAllCheckboxes = function(index) {
    $('#ezpeventperiodweekdays_'+index).hide();
    $('#ezpeventperiodweekdays_'+index+' input:checkbox').each(function(){
        if($(this).is(':checked') === false) {
            $(this).attr('checked', 'checked');
        }
    });
};
function strpos (haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));
    return i === -1 ? false : i;
};