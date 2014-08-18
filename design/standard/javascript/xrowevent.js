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
});
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