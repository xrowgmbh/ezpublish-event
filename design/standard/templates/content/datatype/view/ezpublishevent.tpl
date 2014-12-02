{*Full View for Ansicht*}

<table>
    <tbody>
        {if $attribute.has_content}
            <tr>
                <th style="vertical-align:top;text-align:left">{'Periods'|i18n( 'extension/ezpublish-event' )}:</th>
                <td style="vertical-align:top;">
                    {foreach $attribute.content.json.include as $item_date}
                        <span style="display:block; margin-bottom:8px;">
                        {def $dateString = ''
                             $timeString = ''
                             $dateContent = ezpevent_format_date( $item_date["starttime"], $item_date["endtime"], true )}
                        {if and( count( $dateContent )|eq( 2 ), is_set( $dateContent['startDate'] ), is_set( $dateContent['startTime'] ) )}
                            {set $dateString = concat( $dateContent['startDate'], ' ',
                                                       'starting from'|i18n( 'extension/ezpublish-event' ), ' ', 
                                                       $dateContent['startTime'], ' ', 
                                                       "o'clock"|i18n( "extension/ezpublish-event" ) )}
                        {elseif and( count( $dateContent )|eq( 3 ), is_set( $dateContent['startDate'] ), is_set( $dateContent['startTime'] ), is_set( $dateContent['endTime'] ) )}
                            {set $dateString = concat( $dateContent['startDate'], ' ',
                                                      'from'|i18n( 'extension/ezpublish-event' ), ' ',
                                                       $dateContent['startTime'], ' ', 
                                                       'to'|i18n( 'extension/ezpublish-event' ), ' ',
                                                       $dateContent['endTime'], ' ', 
                                                       "o'clock"|i18n( "extension/ezpublish-event" ) )}
                        {else}
                            {set $dateString = concat( $dateContent['startDate'], ' ', 
                                                       'to'|i18n( 'extension/ezpublish-event' ), ' ',
                                                       $dateContent['endDate'] )
                                 $timeString = concat( 'from'|i18n( 'extension/ezpublish-event' ), ' ',
                                                       $dateContent['startTime'], ' ', 
                                                       'to'|i18n( 'extension/ezpublish-event' ), ' ',
                                                       $dateContent['endTime'], ' ', 
                                                       "o'clock"|i18n( "extension/ezpublish-event" ) )}
                        {/if}
                        {$dateString}
                        {if count( $item_date["weekdays"] )gt( 0 )}
                            <br />
                            {foreach $item_date["weekdays"] as $weekday}
                                {switch match = $weekday}
                                    {case match='Mon'}
                                        {'Mondays'|i18n( 'extension/ezpublish-event' )}&nbsp;
                                    {/case}
                                    {case match='Tue'}
                                        {'Tuesdays'|i18n( 'extension/ezpublish-event' )}&nbsp;
                                    {/case}
                                    {case match='Wed'}
                                        {'Wednesdays'|i18n( 'extension/ezpublish-event' )}&nbsp;
                                    {/case}
                                    {case match='Thu'}
                                        {'Thursdays'|i18n( 'extension/ezpublish-event' )}&nbsp;
                                    {/case}
                                    {case match='Fri'}
                                        {'Fridays'|i18n( 'extension/ezpublish-event' )}&nbsp;
                                    {/case}
                                    {case match='Sat'}
                                        {'Saturdays'|i18n( 'extension/ezpublish-event' )}&nbsp;
                                    {/case}
                                    {case match='Sun'}
                                        {'Sundays'|i18n( 'extension/ezpublish-event' )}&nbsp;
                                    {/case}
                                {/switch}
                            {/foreach}
                        {/if}
                        {if $timeString|ne( '' )}
                            <br />
                            {$timeString}
                        {/if}
                        </span>
                        {undef $dateContent}
                    {/foreach}
                </td>
            </tr>
            {if $attribute.content.json.exclude}
                <tr>
                    <th style="vertical-align:top;text-align:left">{'Excluded periods'|i18n( 'extension/ezpublish-event' )}:</th>
                    <td style="vertical-align:top;text-align:left">
                        {foreach $attribute.content.json.exclude as $item_exdate}
                           <span style="margin-bottom:8px;display:block;">
                            {if $item_exdate["starttime"]|l10n( 'shortdate' )|eq($item_exdate["endtime"]|l10n( 'shortdate' ))}
                                {$item_exdate["starttime"]|l10n( 'shortdate' )}<br />
                            {else}
                                {$item_exdate["starttime"]|l10n( 'shortdate' )} {'to'|i18n( 'extension/ezpublish-event' )} {$item_exdate["endtime"]|l10n( 'shortdate' )}<br />
                            {/if}
                           </span>
                        {/foreach}
                    </td>
                </tr>
            {/if}
        {else}
            <tr>
                <th>{'Start'|i18n( 'extension/ezpublish-event' )}:</th>
                <td>{$attribute.object.data_map.start.content.timestamp|l10n( 'shortdate' )}</td>
            </tr>
            <tr>
                <th>{'End'|i18n( 'extension/ezpublish-event' )}:</th>
                <td>{$attribute.object.data_map.end.content.timestamp|l10n( 'shortdate' )}</td>
            </tr>
            <tr>
                <th>{'Time'|i18n( 'extension/ezpublish-event' )}: </th>
                <td>{'from'|i18n( 'extension/ezpublish-event' )} {$attribute.object.data_map.start.content.timestamp|l10n( 'shorttime' )} {'to'|i18n( 'extension/ezpublish-event' )} {$attribute.object.data_map.end.content.timestamp|l10n( 'shorttime' )}</td>
            </tr>
        {/if}
        {if is_set( $attribute.content.perioddetails )}
            <tr>
                <th style="vertical-align:top;text-align:left">{'First date'|i18n( 'extension/ezpublish-event' )}:</th>
                <td style="vertical-align:top;text-align:left">{$attribute.content.perioddetails.firststartdate|l10n( 'shortdate' )}</td>
            </tr>
            <tr>
                <th style="vertical-align:top;text-align:left">{'Last date'|i18n( 'extension/ezpublish-event' )}:</th>
                <td style="vertical-align:top;text-align:left">{$attribute.content.perioddetails.lastenddate|l10n( 'shortdate' )}</td>
            </tr>
        {/if}
   </tbody>
</table>