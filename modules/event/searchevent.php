<?php
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$ini = eZINI::instance( 'ezpublishevent.ini' );
$limit=10;
$heute=time();

if ( $http->hasVariable( 'fromDate' ) )
{
    if($http->variable( 'fromDate' ) !=="")
    {
        $startDate = $http->variable( 'fromDate' );
        $startDate_parts = explode('.',$startDate);
        $startDateTs=$startDate_parts[2]."-".$startDate_parts[1]."-".$startDate_parts[0]."T00:00:00Z";
    }else{
        if($http->variable( 'toDate' ) !=="")
        {
            $startDate = $http->variable( 'toDate' );
            $startDate_parts = explode('.',$startDate);
            $startDateTs=$startDate_parts[2]."-".$startDate_parts[1]."-".$startDate_parts[0]."T00:00:00Z";
        }else
        {
            $startDateTs=date("Y-m-d",$heute)."T00:00:00Z";
        }
    }
}else{
    $startDateTs=date("Y-m-d",$heute)."T00:00:00Z";
}

if ( $http->hasVariable( 'toDate' ) )
{
    if($http->variable( 'toDate' ) !=="")
    {
        $endDate = $http->variable( 'toDate' );
        $endDate_parts = explode('.',$endDate);
        $endDateTs= $endDate_parts[2]."-".$endDate_parts[1]."-".$endDate_parts[0]."T00:00:00Z";
    }else{
        if($http->variable( 'fromDate' ) !=="")
        {
            $endDate = $http->variable( 'fromDate' );
            $endDate_parts = explode('.',$endDate);
            $endDateTs= $endDate_parts[2]."-".$endDate_parts[1]."-".$endDate_parts[0]."T00:00:00Z";
        }else
        {
           $endDateTs=date("Y-m-d",$heute)."T00:00:00Z";
        }
    }
}else{
    $endDateTs=date("Y-m-d",$heute)."T00:00:00Z";
}

if( $http->hasVariable('SearchText'))
{
    $search_text = $http->variable('SearchText');
}else{
    $search_text = "*";
}

if( $http->hasVariable('event_city') && $http->variable('event_city') != "")
{
    $event_city = '"' . $http->variable('event_city') . '"';
}
else
{
    $event_city ="*";
}

if( $http->hasvariable( 'SubTreeArray' ) )
{
    $SubTreeArray = $http->variable( 'SubTreeArray' );
}
else
{
    $SubTreeArray = $ini->variable( 'Settings', 'ParentNodeID' );
}

if( $http->hasVariable('free_event'))
{
    $free_event = $http->variable('free_event');
}

if( $http->hasVariable('long_event'))
{
    $long_event = $http->variable('long_event');
}

if($long_event == 1){
    $long_event = "*";
}elseif(!isset($long_event) || $long_event == ''){
    $long_event ="false";
}

if($free_event == 1){
    $free_event ="1";
}elseif(!isset($free_event) || $free_event == ''){
    $free_event ="*";
}

if( $http->hasVariable('sort_type'))
{
    $sort_type = $http->variable('sort_type');
}else{
    $sort_type="event/date";
}

if( $http->hasVariable('offset') && $http->hasVariable('offset')!="")
{
    $Offset_temp = $http->variable('offset');
    $Offset = $Offset_temp*$limit;
}else{
    $Offset= 0;
}

$client = eZPublishSolarium::createSolariumClient();
$query = $client->createSelect();

if($sort_type == "event/date")
{
    $helper = $query->getHelper();
    $query_string='attr_currentday_dt:['.$startDateTs.' TO '.$endDateTs.']'.
                  ' AND '.$helper->escapePhrase($search_text).
                  ' AND '.'meta_path_si:'.'"'.$SubTreeArray.'"'.
                  ' AND '.'attr_xrowgis_s:'.$event_city.
                  ' AND '.'attr_long_date_b:'.$long_event.
                  ' AND '.'attr_prices_t:'.$free_event.
                  ' AND '.'-subattr_metadata___sitemap_use____t:"0"';
    $query->setQuery($query_string);
    
    $query->setStart($Offset)->setRows($limit);
    $query->setFields(array('meta_id_si','attr_currentday_dt'));
    $query->addSort('attr_currentday_dt', Solarium_Query_Select::SORT_ASC);
    $query->addSort('attr_currentday_with_time_dt', Solarium_Query_Select::SORT_ASC);
    
    $resultset = $client->select($query);
    $sum_nr=$resultset->getNumFound();
    $template_array=array();
    $facet_date="";
    $compare="";
    $single_key=5;
    foreach ($resultset as $document) 
    {
        $facet_date = strtotime($document["attr_currentday_dt"]);
        $date_temp = explode("T",$document["attr_currentday_dt"]);
        if($date_temp[0] != $compare)
        {
            $single_key=0;
        }else{
            $single_key=5;
        }
        $compare=$date_temp[0];
        $object = eZContentObject::fetch( $document["meta_id_si"] );
        if ( $object === null ){
            continue;
        }
        $obj_datamap=$object->dataMap();
        if(!empty($obj_datamap) && $object->canRead())
        {
            $tpl->setVariable( "object",$object);
            array_push($template_array,array($facet_date,preg_replace('/[\x20]{4,4}/', '', $tpl->fetch( "design:node/view/search_event.tpl" )),$single_key));
        }
    }
    $global_facet_arrays=array("result_nummer"=>$sum_nr,'facet_list'=>$template_array);
}else{
    $helper = $query->getHelper();
    $template_array=array();
    $last_array=array();
    $query_string='attr_currentday_dt:['.$startDateTs.' TO '.$endDateTs.']'.
                  ' AND '.$helper->escapePhrase($search_text).
                  ' AND '.'meta_path_si:'.'"'.$SubTreeArray.'"'.
                  ' AND '.'attr_xrowgis_s:'.$event_city.
                  ' AND '.'attr_long_date_b:'.$long_event.
                  ' AND '.'attr_prices_t:'.$free_event.
                  ' AND '.'-subattr_metadata___sitemap_use____t:"0"';
    $query->setQuery($query_string);
    $query->setStart($Offset)->setRows($limit);
    $query->addSorts('score', Solarium_Query_Select::SORT_ASC,'attr_currentday_dt', Solarium_Query_Select::SORT_ASC);
    
    $facetSet = $query->getFacetSet();
    $facetSet->setMinCount(1);
    
    $facetfield_sum=$facetSet->createFacetField('objectid_sum')->setField('meta_id_si');
    $resultset_sum = $client->select($query);
    $facet_sum = $resultset_sum->getFacetSet()->getFacet('objectid_sum');
    $summer=count($facet_sum);
    
    $facetfield=$facetSet->createFacetField('objectid')->setField('meta_id_si');
    $facetfield->setOffset($Offset);
    $facetfield->setLimit($limit);
    $resultset = $client->select($query);

    $facet = $resultset->getFacetSet()->getFacet('objectid');
    
    foreach($facet as $value => $count) 
    {
        $object=eZContentObject::fetch( $value );
        $obj_datamap=$object->dataMap();
        if(!empty($obj_datamap) && $object->canRead())
        {
            $tpl->setVariable( "object", $object );
            array_push($template_array,array("",preg_replace('/[\x20]{4,4}/', '', $tpl->fetch( "design:node/view/search_event.tpl" )),"5"));
        }
    }
    $global_facet_arrays=array("result_nummer"=>$summer,'facet_list'=>$template_array);
}
print(json_encode($global_facet_arrays));
eZExecution::cleanExit();
