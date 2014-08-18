{if is_set( $item.starttime )}
{def $starttime = hash( 'startdate', $item.starttime|datetime( 'custom', $dateFormat ),
                        'starttimeHour', $item.starttime|datetime( 'custom', '%H' ),
                        'starttimeMinute', $item.starttime|datetime( 'custom', '%i' ) )}
{elseif is_set( $item.startdate )}
{def $starttime = hash( 'startdate', $item.startdate,
                        'starttimeHour', $item.starttime-hour,
                        'starttimeMinute', $item.starttime-minute )}
{/if}
{if is_set( $item.endtime )}
{def $endtime = hash( 'enddate', $item.endtime|datetime( 'custom', $dateFormat ),
                      'endtimeHour', $item.endtime|datetime( 'custom', '%H' ),
                      'endtimeMinute', $item.endtime|datetime( 'custom', '%i' ) )}
{elseif is_set( $item.enddate )}
{def $endtime = hash( 'enddate', $item.enddate,
                      'endtimeHour', $item.endtime-hour,
                      'endtimeMinute', $item.endtime-minute )}
{/if}
    <div id="ezpeventperiod_{$index}" class="ezpeventincludeperiod">
{if $index|gt( 0 )}<hr class="ezpeventhr" />{/if}
        <button class="ezpevent_remove_period" id="ezpevent_remove_period_{$index}" data-index="{$index}"{if $index|eq(0)} style="display: none"{/if}>{'Remove'|i18n( 'design/admin/class/view' )}</button>
        <label for="startdate" class="labelstartdate">
            {'Start date'|i18n( 'extension/ezpublish-event' )}
            <input class="ezpublisheventdate datefrom" id="startdate_{$index}" data-setdatefor="enddate_{$index}" value="{if is_set( $starttime )}{if and( is_set( $starttime.startdate ), $starttime.startdate|ne('') )}{$starttime.startdate}{/if}{/if}" type="text" size="40" maxlength="40" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][startdate]" placeholder="{'Choose date'|i18n( 'extension/ezpublish-event' )}" />
        </label>
        <label for="enddate" class="labelenddate">
            {'End date'|i18n( 'extension/ezpublish-event' )}
            <input class="ezpublisheventdate dateto" id="enddate_{$index}" data-index="{$index}" value="{if is_set( $endtime )}{if and( is_set( $endtime.enddate ), $endtime.enddate|ne('') )}{$endtime.enddate}{/if}{/if}" type="text" size="40" maxlength="40" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][enddate]" placeholder="{'Choose date'|i18n( 'extension/ezpublish-event' )}" />
        </label>
        <br />
        <label for="starttime-hour" class="labelstarttime">
            {'from'|i18n( 'extension/ezpublish-event' )}
            <input type="text" size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][starttime-hour]" value="{if is_set( $starttime )}{if and( is_set( $starttime.starttimeHour ), $starttime.starttimeHour|ne('') )}{$starttime.starttimeHour}{/if}{/if}" /> :
            <input type="text" size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][starttime-minute]" value="{if is_set( $starttime )}{if and( is_set( $starttime.starttimeMinute ), $starttime.starttimeMinute|ne('') )}{$starttime.starttimeMinute}{/if}{/if}" />
        </label>
        <label for="endtime-hour" class="labelendtime">
            {'to'|i18n( 'extension/ezpublish-event' )}
            <input type="text" size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][endtime-hour]" value="{if is_set( $endtime )}{if and( is_set( $endtime.endtimeHour ), $endtime.endtimeHour|ne('') )}{$endtime.endtimeHour}{/if}{/if}" /> : 
            <input type="text" size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][endtime-minute]" value="{if is_set( $endtime )}{if and( is_set( $endtime.endtimeMinute ), $endtime.endtimeMinute|ne('') )}{$endtime.endtimeMinute}{/if}{/if}" />
        </label><span class="ezpeventclock">{"o'clock"|i18n( "extension/ezpublish-event" )}</span>
        <div class="weekdays" id="ezpeventperiodweekdays_{$index}"{if or( and( is_set( $item ), is_set( $item.weekdays )|not() ), and( is_set( $item.weekdays ), $item.weekdays|count|gt( 0 ), $item.weekdays|count|lt( 7 ) ), is_set( $postDataDays[$index] ) )} style="display: block"{/if}>
            <div class="weekdaysheader">{'On this weekdays'|i18n( 'extension/ezpublish-event' )}:</div>
            <label for="Monday" class="labelweekday">
                <input type="checkbox" {if or( is_set( $item )|not(), and( is_set( $item ), or( and( is_set( $item.weekdays ), $item.weekdays|contains( 'Mon' ) ), is_set( $item.weekdays )|not() ) ) )}checked {/if}size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][weekdays][0]" value="Mon">{'Monday'|i18n( 'design/admin/content/translationview' )}
            </label>
            <label for="Tuesday" class="labelweekday">
                <input type="checkbox" {if or( is_set( $item )|not(), and( is_set( $item ), or( and( is_set( $item.weekdays ), $item.weekdays|contains( 'Tue' ) ), is_set( $item.weekdays )|not() ) ) )}checked {/if}size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][weekdays][1]" value="Tue">{'Tuesday'|i18n( 'extension/ezpublish-event' )}
            </label>
            <label for="Wednesday" class="labelweekday">
                <input type="checkbox" {if or( is_set( $item )|not(), and( is_set( $item ), or( and( is_set( $item.weekdays ), $item.weekdays|contains( 'Wed' ) ), is_set( $item.weekdays )|not() ) ) )}checked {/if}size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][weekdays][2]" value="Wed">{'Wednesday'|i18n( 'extension/ezpublish-event' )}
            </label>
            <label for="Thursday" class="labelweekday">
                <input type="checkbox" {if or( is_set( $item )|not(), and( is_set( $item ), or( and( is_set( $item.weekdays ), $item.weekdays|contains( 'Thu' ) ), is_set( $item.weekdays )|not() ) ) )}checked {/if}size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][weekdays][3]" value="Thu">{'Thursday'|i18n( 'extension/ezpublish-event' )}
            </label>
            <label for="Friday" class="labelweekday">
                <input type="checkbox" {if or( is_set( $item )|not(), and( is_set( $item ), or( and( is_set( $item.weekdays ), $item.weekdays|contains( 'Fri' ) ), is_set( $item.weekdays )|not() ) ) )}checked {/if}size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][weekdays][4]" value="Fri">{'Friday'|i18n( 'extension/ezpublish-event' )}
            </label>
            <label for="Saturday" class="labelweekday">
                <input type="checkbox" {if or( is_set( $item )|not(), and( is_set( $item ), or( and( is_set( $item.weekdays ), $item.weekdays|contains( 'Sat' ) ), is_set( $item.weekdays )|not() ) ) )}checked {/if}size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][weekdays][5]" value="Sat">{'Saturday'|i18n( 'extension/ezpublish-event' )}
            </label>
            <label for="Sunday" class="labelweekday lastelement">
                <input type="checkbox" {if or( is_set( $item )|not(), and( is_set( $item ), or( and( is_set( $item.weekdays ), $item.weekdays|contains( 'Sun' ) ), is_set( $item.weekdays )|not() ) ) )}checked {/if}size="3" maxlength="2" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[include][{$index}][weekdays][6]" value="Sun">{'Sunday'|i18n( 'design/admin/content/translationview' )}
            </label>
            <div style="clear: both"></div>
        </div>
    </div>
{if is_set( $starttime )}{undef $starttime}{/if}
{if is_set( $endtime )}{undef $endtime}{/if}