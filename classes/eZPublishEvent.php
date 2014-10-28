<?php

class eZPublishEvent
{
    const DATE_FORMAT = DateTime::ISO8601;
    const DATE_FORMAT_SOLR = 'Y-m-d\TH:i:s.000\Z';
    static private $currentDateFormat = null;
    private $FindINI = null;
    private $eZPEventINI = null;
    private $FieldsForSOLRIndex = null;

    function __construct()
    {
        $this->FindINI = eZINI::instance( 'ezfind.ini' );
        $this->eZPEventINI = eZINI::instance( 'ezpublishevent.ini' );
        $this->FieldsForSOLRIndex = $this->eZPEventINI->variable( 'CronjobSettings', 'FieldsForSOLRIndex' );
    }

    static function createDateTime( $timeString, $includeItem, $type, $locale )
    {
        $currentDateFormat = self::getCurrentDateFormat( $locale );
        if( is_string( $currentDateFormat ) )
        {
            if( isset( $includeItem[$type.'time-minute'] ) && trim($includeItem[$type.'time-minute']) != '' )
            {
                $timeString .= ':' . trim( $includeItem[$type.'time-minute'] ) . ':00';
            }
            else
            {
                $timeString .= ':00:00';
            }
            return DateTime::createFromFormat( $currentDateFormat, $timeString );
        }
        elseif( isset( $currentDateFormat['error'] ) )
        {
            throw new Exception( $currentDateFormat['error'] );
        }
    }

    static private function getCurrentDateFormat( $locale )
    {
        if( self::$currentDateFormat === null )
        {
            $ezpe_ini = eZINI::instance( 'ezpublishevent.ini' );
            if( $ezpe_ini->hasVariable( 'Settings', 'DateFormat' ) )
            {
                $dateFormatArray = $ezpe_ini->variable( 'Settings', 'DateFormat' );
                $timeFormatArray = $ezpe_ini->variable( 'Settings', 'TimeFormat' );
                if( isset( $dateFormatArray[$locale] ) )
                {
                    $dateFormat = preg_replace( '/%/', '', $dateFormatArray[$locale] );
                    self::$currentDateFormat = $dateFormat . ' ' . preg_replace( '/%/', '', $timeFormatArray[$locale] );
                }
                else
                {
                    return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'Please set your locale in dateformat in ezpublishevent.ini' ) );
                }
            }
            else
            {
                return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'Please set a dateformat in ezpublishevent.ini' ) );
            }
        }
        return self::$currentDateFormat;
    }

    function createSOLRDocument( eZContentObjectTreeNode $node, $update )
    {
        $attributeName = $this->eZPEventINI->variable( 'Settings', 'AttributeName' );
        $fieldsForSOLRIndex = $this->eZPEventINI->variable( 'CronjobSettings', 'FieldsForSOLRIndex' );
        $dataMap = $node->dataMap();
        $long_date=$dataMap["long_dated"];
        $prices_string = $dataMap["gratis"];
        if( isset( $dataMap[$attributeName] ) )
        {
            $ezpevent = $dataMap[$attributeName];
            if( $ezpevent->hasContent() )
            {
                $docList = array();
                $contentObject = $node->object();
                $currentVersion = $contentObject->currentVersion();
                $contentClassIdentifier = $contentObject->attribute( 'class_identifier' );

                // Loop over each language version and create an eZSolrDoc for it
                #foreach ( $availableLanguages as $languageCode )
                #{ 
                
                $languageCode = 'ger-DE';
                $parent = $node->fetchParent();
                $defaultData = array( 'attr_name_t' => $contentObject->name( false, $languageCode ),
                                      'meta_id_si' => $contentObject->ID,
                                      'meta_url_alias_ms' => $node->attribute( 'url_alias' ),
                                      'meta_main_parent_node_id_si' => $parent->NodeID ,
                                      'meta_path_si' => array_map('intval',explode('/',$node->PathString)),
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
                        for( $day = $ezpeventExcludeItem['starttime']; $day <= $ezpeventExcludeItem['endtime']; $day=$day+86400 )
                        {
                            $excludeDays[$day] = $day;
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
                        $start_time_temp=explode("T",$starttime->format( self::DATE_FORMAT_SOLR ));
                        $start_time = $start_time_temp[1];
                        $start_0=$starttime->setTime(0,0);
                        $start = $start_0->getTimestamp();
                        $endtime = new DateTime();
                        $endtime->setTimestamp( $ezpeventItem['endtime'] );
                        $defaultData['attr_end_dt'] = $endtime->format( self::DATE_FORMAT_SOLR );
                        $end_0=$endtime->setTime(0,0);
                        $end = $end_0->getTimestamp();
                        // check all days
                        for( $day = $start; $day <= $end; $day = $day+86400 )
                        {
                            if( !isset( $excludeDays ) || ( isset( $excludeDays ) && !array_key_exists( $day, $excludeDays ) ) )
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
}