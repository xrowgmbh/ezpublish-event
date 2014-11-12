{if is_set( $item.starttime )}
{def $startdate = $item.starttime|datetime( 'custom', $dateFormat )}
{elseif is_set( $item.startdate )}
{def $startdate = $item.startdate}
{/if}
{if is_set( $item.endtime )}
{def $enddate = $item.endtime|datetime( 'custom', $dateFormat )}
{elseif is_set( $item.enddate )}
{def $enddate = $item.enddate}
{/if}
    <div id="ezpeventexcludeperiod_{$index}" class="ezpeventexcludeperiod">
{if $index|gt( 0 )}<hr class="ezpeventhr" />{/if}
        <button class="ezpevent_remove_exclude_period" id="ezpevent_remove_exclude_period_{$index}" data-index="{$index}"{if $index|eq(0)} style="display: none"{/if}>{'Remove'|i18n( 'design/admin/class/view' )}</button>
        <label for="date" class="labelstartdate">
            {'Start date'|i18n( 'extension/ezpublish-event' )}
            <input class="ezpublisheventdate" data-setdatefor="enddateexclude_{$index}" id="startdateexclude_{$index}" type="text" value="{if and( is_set( $startdate ), $startdate|ne('') )}{$startdate}{/if}" size="40" maxlength="40" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[exclude][{$index}][startdate]" placeholder="{'Choose date'|i18n( 'extension/ezpublish-event' )}" />
        </label>
        <label for="date" class="labelenddate">
            {'End date'|i18n( 'extension/ezpublish-event' )}
            <input class="ezpublisheventdate" id="enddateexclude_{$index}" data-mindate="startdateexclude_{$index}" type="text" value="{if and( is_set( $enddate ), $enddate|ne('') )}{$enddate}{/if}" size="40" maxlength="40" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[exclude][{$index}][enddate]" placeholder="{'Choose date'|i18n( 'extension/ezpublish-event' )}" />
        </label>
    </div>
{if is_set( $startdate )}{undef $startdate}{/if}
{if is_set( $enddate )}{undef $enddate}{/if}