{*Full View for Ansicht*}

<table>
    <tbody>
        {if $attribute.has_content}
            <tr>
                <th>{'Event(s)'|i18n("extension/hannover/events")}:</th>
                <td>
                    {foreach $attribute.content.json.include as $item_date}
                        <span style="display:block;margin-bottom:8px;">
                        {def $dateContent=format_event_date($item_date["starttime"],$item_date["endtime"],true)|explode('-')}
                        {$dateContent[0]}<br />
                        {if gt(count($item_date["weekdays"]),0)}
                            {foreach $item_date["weekdays"] as $weekday}
                                {switch match=$weekday}
                                    {case match='Mon'}
                                        {'Mondays'|i18n("extension/hannover")}&nbsp;
                                    {/case}
                                    {case match='Tue'}
                                        {'Tuesdays'|i18n("extension/hannover")}&nbsp;
                                    {/case}
                                    {case match='Wed'}
                                        {'Wednesdays'|i18n("extension/hannover")}&nbsp;
                                    {/case}
                                    {case match='Thu'}
                                        {'Thursdays'|i18n("extension/hannover")}&nbsp;
                                    {/case}
                                    {case match='Fri'}
                                        {'Fridays'|i18n("extension/hannover")}&nbsp;
                                    {/case}
                                    {case match='Sat'}
                                        {'Saturdays'|i18n("extension/hannover")}&nbsp;
                                    {/case}
                                    {case match='Sun'}
                                        {'Sundays'|i18n("extension/hannover")}&nbsp;
                                    {/case}
                                {/switch}
                            {/foreach}<br />
                        {/if}
                        {if $dateContent[1]}
                            {$dateContent[1]}
                        {/if}
                        </span>
                        {undef $dateContent}
                    {/foreach}
                </td>
            </tr>
            {if $attribute.content.json.exclude}
                <tr>
                    <th>{'There are no events on'|i18n("extension/hannover/events")}:</th>
                    <td>
                        {foreach $attribute.content.json.exclude as $item_exdate}
                           <span style="margin-top:8px;display:block;">
                            {if $item_exdate["starttime"]|l10n( 'shortdate' )|eq($item_exdate["endtime"]|l10n( 'shortdate' ))}
                                {$item_exdate["starttime"]|l10n( 'shortdate' )}<br />
                            {else}
                                {$item_exdate["starttime"]|l10n( 'shortdate' )} {'to'|i18n("extension/hannover")} {$item_exdate["endtime"]|l10n( 'shortdate' )}<br />
                            {/if}
                           </span>
                        {/foreach}
                    </td>
                </tr>
            {/if}
        {else}
           <tr>
               <th>{'Start'|i18n("extension/hannover")}:</th>
               <td>{$attribute.object.data_map.start.content.timestamp|l10n( 'shortdate' )}</td>
           </tr>
           <tr>
               <th>{'End'|i18n("extension/hannover")}:</th>
               <td>{$attribute.object.data_map.end.content.timestamp|l10n( 'shortdate' )}</td>
           </tr>
           <tr>
               <th>{'Time'|i18n("extension/hannover")}: </th>
               <td>{'from'|i18n("extension/hannover")} {$attribute.object.data_map.start.content.timestamp|l10n( 'shorttime' )} {'to'|i18n("extension/hannover")} {$attribute.object.data_map.end.content.timestamp|l10n( 'shorttime' )}</td>
           </tr>
       {/if}
   </tbody>
</table>