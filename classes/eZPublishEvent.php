<?php

class eZPublishEvent
{
    const DATE_FORMAT = DateTime::ISO8601;
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
        $ezpevent_ini = eZINI::instance( 'ezpublishevent.ini' );
        $attributeName = $this->eZPEventINI->variable( 'CronjobSettings', 'AttributeName' );
        $fieldsForSOLRIndex = $this->eZPEventINI->variable( 'CronjobSettings', 'FieldsForSOLRIndex' );
        $dataMap = $node->dataMap();
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
                                      'meta_main_parent_node_id_si' => $parent->NodeID );
    
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
                        #$defaultData['attr_start_dt'] = $starttime->format( eZPublishEvent::DATE_FORMAT );
                        $starttime->setTime( 00, 00 );
                        $start = $starttime->getTimestamp();
                        $endtime = new DateTime();
                        $endtime->setTimestamp( $ezpeventItem['endtime'] );
                        #$defaultData['attr_end_dt'] = $endtime->format( eZPublishEvent::DATE_FORMAT );
                        $endtime->setTime( 00, 00 );
                        $end = $endtime->getTimestamp();
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
                                    #$doc->attr_start_dt = $daytime->format( eZPublishEvent::DATE_FORMAT );
                                    #$doc->attr_end_dt = $daytime->format( eZPublishEvent::DATE_FORMAT );
                                    foreach( $defaultData as $defaultDataName => $defaultDataItem )
                                    {
                                        $doc->$defaultDataName = $defaultDataItem;
                                    }
                                    $docList[$day] = $doc;
                                    unset( $doc );
                                }
                            }
                        }
                        #die(var_dump($docList));
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
}