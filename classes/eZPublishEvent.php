<?php

class eZPublishEvent
{
    const DATE_FORMAT = DateTime::ISO8601;
    const DATE_FORMAT_SOLR = 'Y-m-d\TH:i:s.000\Z';
    static private $currentDateFormat = null;
    static $dateInWeekDays = array();
    private $FindINI = null;
    private $eZPEventINI = null;
    private $FieldsForSOLRIndex = null;

    function __construct()
    {
        $this->FindINI = eZINI::instance( 'ezfind.ini' );
        $this->eZPEventINI = eZINI::instance( 'ezpublishevent.ini' );
        $this->FieldsForSOLRIndex = $this->eZPEventINI->variable( 'CronjobSettings', 'FieldsForSOLRIndex' );
    }

    static function createDateTime( $timeString, $dataItem, $type )
    {
        $currentDateFormat = self::getCurrentDateFormat();
        if( trim( $dataItem[$type.'time-minute'] ) != '' )
        {
            if( is_numeric( trim( $dataItem[$type.'time-minute'] ) ) )
            {
                $timeString .= ':' . trim( $dataItem[$type.'time-minute'] ) . ':00';
            }
            elseif( isset( $currentDateFormat['error'] ) )
            {
                throw new Exception( ezpI18n::tr( 'extension/ezpublish-event', 'Select a numeric time.' ) );
            }
        }
        else
        {
            $timeString .= ':00:00';
        }
        return DateTime::createFromFormat( $currentDateFormat, $timeString );
    }

    static private function getCurrentDateFormat()
    {
        if( self::$currentDateFormat === null )
        {
            $locale = eZLocale::instance();
            $dateFormat = preg_replace( '/%/', '', $locale->ShortDateFormat );
            $timeFormat = preg_replace( '/%/', '', $locale->TimeFormat );
            self::$currentDateFormat = $dateFormat . ' ' . $timeFormat;
        }
        return self::$currentDateFormat;
    }

    function createSOLRDocument( eZContentObject $object, $update )
    {
        $attributeName = $this->eZPEventINI->variable( 'Settings', 'AttributeName' );
        $fieldsForSOLRIndex = $this->eZPEventINI->variable( 'CronjobSettings', 'FieldsForSOLRIndex' );
        $contentObject = eZContentObject::fetch($object->ID);
        $node = eZContentObjectTreeNode::fetch( $contentObject->attribute('main_node_id'));
        $connectedDates = "0";
        if($node)
        {
            $dataMap = $node->dataMap();
            $long_date=$dataMap["long_dated"];
            $prices_string = $dataMap["gratis"];
            if(isset($dataMap["connected_dates"]))
            {
                $connectedDates = $dataMap["connected_dates"]->DataInt;
            }
        }
        if( isset( $dataMap[$attributeName] ) )
        {
            $ezpevent = $dataMap[$attributeName];
            if( $ezpevent->hasContent() )
            {
                $docList = array();
                $path_array = array();
                $currentVersion = $contentObject->currentVersion();
                $contentClassIdentifier = $contentObject->attribute( 'class_identifier' );

                // Loop over each language version and create an eZSolrDoc for it
                #foreach ( $availableLanguages as $languageCode )
                #{

                $languageCode = 'ger-DE';
                $parent = $node->fetchParent();

                if($contentObject->attribute('has_visible_node'))
                {
                    $nodes=$contentObject->attribute('visible_nodes');
                    foreach($nodes as $node)
                    {
                        foreach(array_map('intval',explode('/',$node->PathString)) as $node_element)
                        {
                            array_push($path_array,$node_element);
                        }
                    }
                }
                else
                {
                    $path_array=array_map('intval',explode('/',$node->PathString));
                }

                $defaultData = array( 'attr_name_t' => $contentObject->name( false, $languageCode ),
                                      'meta_id_si' => $contentObject->ID,
                                      'meta_url_alias_ms' => $node->attribute( 'url_alias' ),
                                      'meta_main_parent_node_id_si' => $parent->NodeID ,
                                      'meta_path_si' => array_unique( $path_array ),
                                      'attr_prices_t'=> $prices_string->DataInt,
                                      'attr_long_date_b'=> $long_date->DataInt);

                /*if( !$node->isMain() )
                {
                    $mainNode = $contentObject->attribute( 'main_node' );
                    if ( !$mainNode )
                    {
                        eZDebug::writeError( 'Unable to fetch main node for object: ' . $contentObject->attribute( 'id' ), __METHOD__ );
                        return false;
                    }
                    $mainNodeID = $mainNode->attribute( 'node_id' );
                    $defaultData['main_node_id'] = $mainNodeID;
                    $defaultData['main_url_alias'] = $mainNode->attribute( 'url_alias' );
                    $defaultData['main_path_string'] = $mainNode->attribute( 'path_string' );
                }*/
                foreach ( $this->FieldsForSOLRIndex as $attributeIdentifier )
                {
                    if( isset( $dataMap[$attributeIdentifier] ) )
                    {
                        $attribute = $dataMap[$attributeIdentifier];
                        $metaData = false;
                        switch( $attribute->DataTypeString )
                        {
                            case 'xrowmetadata':
                                $ident = 'attr_keywords____k';
                                $content = $attribute->content();
                                $metaData = $content->keywords;
                                $defaultData['subattr_metadata___sitemap_use____t'] = $content->sitemap_use;
                                break;
                            break;
                            case 'ezxmltext':
                                $ident = 'attr_' . $attributeIdentifier . '_s';
                                // need _ms field too
                                #$metaData = $attribute->metaData();
                                #$defaultData["attr_" . $attributeIdentifier . "_ms"] = $metaData;
                                break;
                            case 'eztext':
                                $ident = 'attr_' . $attributeIdentifier . '_t';
                                break;
                            case 'ezstring':
                                $ident = 'attr_' . $attributeIdentifier . '_s';
                                break;
                            case 'xrowgis':
                                $ident = 'attr_' . $attributeIdentifier . '_s';
                                $xrowgis_att = $attribute->metaData();
                                foreach($xrowgis_att as $att)
                                {
                                    if($att['id'] == "city")
                                    {
                                        $metaData = $att['text'];
                                    
                                    }
                                }
                                break;
                            default:
                                $ident = 'attr_' . $attributeIdentifier . '_t';
                            break;
                        }
                        if( $metaData === false )
                        {
                            $metaData = $attribute->metaData();
                        }
                        $defaultData[$ident] = $metaData;
                    }
                }
                //}
                // get days
                $ezpeventContent = $ezpevent->content();
                // get at first exclude periods for better handling
                if( isset( $ezpeventContent['json']['exclude'] ) )
                {
                    $excludeDays = array();
                    foreach( $ezpeventContent['json']['exclude'] as $ezpeventExcludeItem )
                    {
                      /* PHP Bug #63311 DateTime::add() adds wrong interval when switching from summer to winter time 
                       * This method is currently unavailable!
                       * $excludeStartDays= new DateTime($ezpeventExcludeItem['start'],);
                        $excludeEndDays = new DateTime($ezpeventExcludeItem['end']);
                        for( $day = $excludeStartDays; $day <= $excludeEndDays; $day=$day->add(new DateInterval('P1D')))
                        {
                            $excludeItems= $day->format("Y-m-d");
                            $excludeDays[$excludeItems] = $excludeItems;
                        }*/
                        $excludeDays_temp = self::checkSummertime($ezpeventExcludeItem['starttime'], $ezpeventExcludeItem['endtime']);
                        for( $day = $excludeDays_temp["start"]; $day <= $excludeDays_temp["end"]; $day = $day + 86400 )
                        {
                            $excludeItems=new DateTime();
                            $excludeItems->setTimestamp($day);
                            $excludeDays[$excludeItems->format("Y-m-d")] =$excludeItems->format("Y-m-d");
                        }
                    }
                }
                
                if( isset( $ezpeventContent['json']['include'] ) )
                {
                    foreach( $ezpeventContent['json']['include'] as $ezpeventItem )
                    {
                        $starttime = new DateTime();
                        $starttime->setTimestamp( $ezpeventItem['starttime'] );
                        $defaultData['attr_start_dt'] = $starttime->format( self::DATE_FORMAT_SOLR );
                        
                        $start_time_temp = explode("T", $starttime->format( self::DATE_FORMAT_SOLR ));
                        $start_time = $start_time_temp[1];
                        
                        $startimeItem = $starttime->format("Y-m-d");
                        $start_temp = strtotime($startimeItem);
                        
                        $endtime = new DateTime();
                        $endtime->setTimestamp( $ezpeventItem['endtime'] );
                        $defaultData['attr_end_dt'] = $endtime->format( self::DATE_FORMAT_SOLR );
                        
                        $endtimeItem = $endtime->format("Y-m-d");
                        $end_temp = strtotime($endtimeItem);
                        
                        $checkSummerTime = self::checkSummertime($start_temp, $end_temp);
                        $start = $checkSummerTime["start"];
                        $end = $checkSummerTime["end"];
                        // check all days
                        if(($end-$start) == 86400 && $endtime->setTimestamp( $ezpeventItem['endtime'] )->format('H:i') == '00:00')
                        {
                            $end = $start;
                        }
                        for( $day = $start; $day <= $end; $day = $day+86400 )
                        {
                            if( !isset( $excludeDays ) || ( isset( $excludeDays ) && !array_key_exists( date("Y-m-d",$day), $excludeDays ) ) )
                            {
                                if( !isset( $ezpeventItem['weekdays'] ) || ( isset( $ezpeventItem['weekdays'] ) && in_array( date( 'D', $day ), $ezpeventItem['weekdays'] ) ) )
                                {
                                    $doc = $update->createDocument();
                                    $daytime = new DateTime();
                                    $daytime->setTimestamp( $day );
                                    $current_day_temp=explode("T",$daytime->format( self::DATE_FORMAT_SOLR ));
                                    $current_day= $current_day_temp[0]."T".$start_time;
                                    $current_day_zero=$current_day_temp[0]."T00:00:00Z";
                                    $defaultData['attr_currentday_dt'] = $current_day_zero;
                                    $defaultData['attr_currentday_with_time_dt'] = $current_day;
                                    $defaultData['meta_guid_ms']= $contentObject->attribute( 'remote_id' )."::".$daytime->format( self::DATE_FORMAT_SOLR );
                                    foreach( $defaultData as $defaultDataName => $defaultDataItem )
                                    {
                                        $doc->$defaultDataName = $defaultDataItem;
                                    }
                                    $docList[$day] = $doc;
                                    unset( $doc );
                                }
                            }
                            if($connectedDates === "1")
                            {
                                break;
                            }
                        }
                        if($connectedDates === "1")
                        {
                            break;
                        }
                    }
                }
                if( count( $docList ) > 0 )
                {
                    return $docList;
                }
                else
                {
                    return false;
                }
            }
        }
    }

    public static function addEventAttribute( $class, $attrCreateInfo )
    {
        if ( ! is_object( $class ) )
        {
            return;
        }
        $classID = $class->attribute( 'id' );
        $newAttribute = eZContentClassAttribute::create( $classID, eZPublishEventType::DATA_TYPE_STRING, $attrCreateInfo );
        $dataType = $newAttribute->dataType();
        $dataType->initializeClassAttribute( $newAttribute );
        // store attribute, update placement, etc...
        $attributes = $class->fetchAttributes();
        $attributes[] = $newAttribute;

        // remove temporary version
        if ( $newAttribute->attribute( 'id' ) !== null )
        {
            $newAttribute->remove();
        }

        $newAttribute->setAttribute( 'version', eZContentClass::VERSION_STATUS_DEFINED );
        $newAttribute->setAttribute( 'placement', count( $attributes ) );

        $class->adjustAttributePlacements( $attributes );
        foreach ( $attributes as $attribute )
        {
            $attribute->storeDefined();
        }

        // update objects
        $classAttributeID = $newAttribute->attribute( 'id' );
        $count = eZContentObject::fetchSameClassListCount( $class->ID );
        $output = new ezcConsoleOutput();
        $bar = new ezcConsoleProgressbar( $output, (int) $count );
        $offset = 0;
        $limit = 50;
        while ( true )
        {
            if ( $offset > $count )
            {
                break;
            }
            $objects = eZContentObject::fetchSameClassList( $classID, true, $offset, $limit );
            foreach ( $objects as $object )
            {
                $contentobjectID = $object->attribute( 'id' );
                $objectVersions = $object->versions();
                foreach ( $objectVersions as $objectVersion )
                {
                    $translations = $objectVersion->translations( false );
                    $version = $objectVersion->attribute( 'version' );
                    foreach ( $translations as $translation )
                    {
                        $objectAttribute = eZContentObjectAttribute::create( $classAttributeID, $contentobjectID, $version );
                        $objectAttribute->setAttribute( 'language_code', $translation );
                        $objectAttribute->initialize();
                        $objectAttribute->store();
                        $objectAttribute->postInitialize();
                    }
                }
                $bar->advance();
            }
            eZContentObject::clearCache();
            $offset += $limit;
        }
        $bar->finish();
        return $newAttribute;
    }

    static function getEndTimestampForWorkflow( $object, $http, $contentObjectAttribute )
    {
        if ($contentObjectAttribute->attribute('data_text') != '') {
            $ezpevent = json_decode($contentObjectAttribute->attribute('data_text'), true);
            if( isset( $ezpevent['include'] ) )
            {
                $lastenddate = self::getFirstLastIncludeTimestamp( $ezpevent['include'], 'lastenddate' );
                if( $lastenddate !== false && $lastenddate > 0 )
                {
                    if( ( $object->CurrentVersion > 1 && $lastenddate > 0 ) || ( $object->CurrentVersion == 1 && $lastenddate > time() ) )
                        return $lastenddate;
                }
            }
        }
        return false;
    }

    static function getFirstLastIncludeTimestamp( $eventData, $index )
    {
        $firststartdate = $lastenddate = 0;
        if( is_array( $eventData ) )
        {
            foreach( $eventData as $key => $ezpeventItem )
            {
                $starttime = DateTime::createFromFormat(self::DATE_FORMAT, $ezpeventItem['start']);
                $endtime = DateTime::createFromFormat(self::DATE_FORMAT, $ezpeventItem['end']);
                if( $endtime instanceof DateTime )
                {
                    $endtimestamp = $endtime->getTimestamp();
                    if( isset( $ezpeventItem['weekdays'] ) && count( $ezpeventItem['weekdays'] ) > 0 && count( $ezpeventItem['weekdays'] ) < 7 )
                    {
                        $endtimeTmp = clone $endtime;
                        self::checkWeekday( $endtimeTmp, $ezpeventItem['weekdays'], $index );
                        $endtimestamp = $endtimeTmp->getTimestamp();
                        unset($endtimeTmp);
                    }
                    if( $endtimestamp > $lastenddate || $lastenddate == 0 )
                        $lastenddate = $endtimestamp;
                }
                if( $starttime instanceof DateTime )
                {
                    $starttimestamp = $starttime->getTimestamp();
                    if( isset( $ezpeventItem['weekdays'] ) && count( $ezpeventItem['weekdays'] ) > 0 && count( $ezpeventItem['weekdays'] ) < 7 )
                    {
                        $starttimeTmp = clone $starttime;
                        self::checkWeekday( $starttimeTmp, $ezpeventItem['weekdays'], $index );
                        $starttimestamp = $starttimeTmp->getTimestamp();
                        unset($starttimeTmp);
                    }
                    if( $starttimestamp < $firststartdate || $firststartdate == 0 )
                        $firststartdate = $starttimestamp;
                }
            }
            switch( $index )
            {
                case 'firststartdate':
                    if( $firststartdate > 0 )
                       return $firststartdate;
                    break;
                case 'lastenddate':
                    if( $lastenddate > 0 )
                       return $lastenddate;
                    break;
            }
        }
        else
        {
            eZDebug::writeError( 'Variable eventData has to be an array', __METHOD__ );
        }
        return false;
    }

    static function checkWeekday( DateTime $dateTime, $weekdays, $index )
    {
        $weekday = $dateTime->format( 'D' );
        if( in_array( $weekday, $weekdays ) )
        {
            if( !isset( self::$dateInWeekDays[$index] ) )
                self::$dateInWeekDays[$index] = array();
            self::$dateInWeekDays[$index][] = $dateTime->format( 'd.m.Y' );
            return $dateTime;
        }
        else
        {
            if( in_array( $dateTime->format( 'd.m.Y' ), self::$dateInWeekDays[$index] ) )
                return $dateTime;

            if( $index == 'firststartdate' )
            {
                $dateTime->modify( '+1 day' );
            }
            if( $index == 'lastenddate' )
            {
                $dateTime->modify( '-1 day' );
            }
            self::checkWeekday( $dateTime, $weekdays, $index );
        }
    }

    static function checkSummertime($start_Timestamp, $end_Timestamp)
    {
        $correctDays = array();
        $startItem = Date("I", $start_Timestamp);
        if ($startItem)
        {
            $StartDays = $start_Timestamp + 3600;
        }
        else
        {
            $StartDays = $start_Timestamp;
        }

        $endItem = Date("I",$end_Timestamp);
        if($endItem)
        {
            $EndDays = $end_Timestamp + 3600;
        }
        else
        {
            $EndDays = $end_Timestamp;
        }
        $correctDays = array("start"=>$StartDays, "end"=>$EndDays);
        return $correctDays;
    }
}