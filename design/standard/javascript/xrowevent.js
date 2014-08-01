var divIDPrefix = 'ezpeventperiod',
    divExcludeIDPrefix = 'ezpeventexcludeperiod',
    counter = {include: 0,
               exclude: 0};;
jQuery(document).ready(function() {
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
                counter['include']++;
                removePeriod($(this), divIDPrefix, 'include', $('.ezpevent_add_period'));
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
                counter['exclude']++;
                removePeriod($(this), divIDPrefix, 'exclude', $('.ezpevent_add_exclude_period'));
            }
        });
    });
    var datePickerName = '.ezpublisheventdate',
        datePickerLocale = $('.ezpeventdate_data_locale').val();
    $.datepicker.setDefaults(datePickerLocale);
    $(datePickerName).each(function(){
        initDate($(this));
    });
});
var initDate = function(element) {
    element.datepicker({
        onClose: function (dateText, inst) {
            if(typeof $(this).data('index') !== 'undefined') {
                if($.trim($(this).val()) == '')
                    $('#ezpeventperiodweekdays_'+$(this).data('index')).hide();
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
        window.console.log('Your date format is not set in xrowevent.js');
        return false;
    }
};
var appendPeriod = function(element, divIDPrefix, findPrefix, removeButtonID) {
    var index = element.data('index');
    counter[findPrefix]++;
    if(index > counter[findPrefix])
        counter[findPrefix] = index;
    var content = $('#'+divIDPrefix+'_'+index).html(),
        new_index = counter[findPrefix];
    content = replaceIndex(content, findPrefix, element, index, new_index);
    // add new node after this
    $('<div id='+divIDPrefix+'_'+new_index+'><hr class="ezpeventhr" />'+content+'</div>').insertAfter('#'+divIDPrefix+'_'+(new_index-1));
    // initilize datepicker
    $('#'+divIDPrefix+'_'+new_index+' .ezpublisheventdate').each(function(){
        initDate($(this));
    });
    // set event for remove selected include period
    $('#'+removeButtonID+'_'+new_index).show();
    $('#'+removeButtonID+'_'+new_index).on( 'click', function(event){
        event.preventDefault();
        removePeriod($(this), divIDPrefix, findPrefix, element);
    });
};
var removePeriod = function(element, divIDPrefix, findPrefix, addButton) {
    var index = element.data('index');
    $('#'+divIDPrefix+'_'+index).remove();
    var allDivsCounter = counter[findPrefix];
    counter[findPrefix]--;
    addButton.attr('data-index', counter[findPrefix]);
    if(typeof $('#'+divIDPrefix+'_'+allDivsCounter) !== 'undefined' && index < allDivsCounter) {
        for ( i=index; i <= allDivsCounter ; i++ ) {
            var content = $('#'+divIDPrefix+'_'+i).html(),
                oldCounter = i+1;
            content = replaceIndex(content, findPrefix, element, oldCounter, i);
            $('#'+divIDPrefix+'_'+i).remove();
            $(content).insertAfter('#'+divIDPrefix+'_'+(i-1));
        }
    }
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
    element.attr('data-index', new_index);
    for (key in findArray) {
        var regex = new RegExp(findArray[key], 'g'),
        content = content.replace(regex, replaceArray[key]);
    }
    return content;
};
function strpos (haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));
    return i === -1 ? false : i;
};