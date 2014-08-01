    <div id="ezpeventexcludeperiod_{$index}">
{if $index|gt( 0 )}<hr class="ezpeventhr" />{/if}
        <button class="ezpevent_remove_exclude_period" id="ezpevent_remove_exclude_period_{$index}" data-index="{$index}"{if $index|eq(0)} style="display: none"{/if}>{'Remove'|i18n( 'design/admin/class/view' )}</button>
        <label for="date" class="labelstartdate">
            {'Start date'|i18n( 'extension/ezpublish-event' )}
            <input class="ezpublisheventdate" type="text" value="{if is_set( $item )}{if and( is_set( $item.startdate ), $item.startdate|ne('') )}{$item.startdate}{/if}{/if}" size="40" maxlength="40" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[exclude][{$index}][startdate]" placeholder="{'Choose date'|i18n( 'extension/ezpublish-event' )}" />
        </label>
        <label for="date" class="labelenddate">
            {'End date'|i18n( 'extension/ezpublish-event' )}
            <input class="ezpublisheventdate" type="text" value="{if is_set( $item )}{if and( is_set( $item.enddate ), $item.enddate|ne('') )}{$item.enddate}{/if}{/if}" size="40" maxlength="40" name="{$attribute_base}_ezpeventdate_data_{$attribute.id}[exclude][{$index}][endate]" placeholder="{'Choose date'|i18n( 'extension/ezpublish-event' )}" />
        </label>
    </div>