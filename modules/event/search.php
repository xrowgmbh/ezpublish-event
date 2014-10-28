<?php
$http = eZHTTPTool::instance();
$module = $Params['Module'];
$tpl = eZTemplate::factory();

$heute=time();
if ( $http->hasVariable( 'fromDate' ) )
{
    $startDate = $http->variable( 'fromDate' );
    $startDate_parts = explode('.',$startDate);
    $startDateTs=strtotime($startDate_parts[2]."-".$startDate_parts[1]."-".$startDate_parts[0])+date("Z");
}else{
    $startDateTs=strtotime(date("Y-m-d",$heute))+date("Z");
}
if ( $http->hasVariable( 'toDate' ) )
{
    $endDate = $http->variable( 'toDate' );
    $endDate_parts = explode('.',$endDate);
    $endDateTs=strtotime($endDate_parts[2]."-".$endDate_parts[1]."-".$endDate_parts[0])+date("Z");;
}else{
    $endDateTs=strtotime(date("Y-m-d",$heute))+date("Z");
}

if ( $http->hasVariable( 'SearchText' ) )
{
    $search_text = $http->variable( 'SearchText');
}else{
    $search_text = "*:*";
}

if ( $http->hasVariable( 'event_city' ) )
{
    $event_city = $http->variable( 'event_city');
}

$Result = array();
$Result['content'] = $tpl->fetch( "design:content/searchevent.tpl" );
$Result['path'] = array( array( 'text' => ezpI18n::tr( 'extension/ezpublish-event/modules/event', 'Search' ),
                                'url' => false ) );