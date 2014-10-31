<?php

class eZPublishEventType extends eZDataType
{
    const DATA_TYPE_STRING = 'ezpublishevent';
    const DEFAULT_FIELD = 'data_text';

    function eZPublishEventType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', 'Event', 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $now = time();
        if ( $http->hasPostVariable( $base . '_ezpeventdate_data_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_ezpeventdate_data_' . $contentObjectAttribute->attribute( 'id' ) );
            $data_text = array();
            $include = array();
            $exclude = array();
            $days = array();
            // get include date
            if( isset( $data['include'] ) )
            {
                foreach( $data['include'] as $key => $includeItem )
                {
                    $validate = array();
                    if( trim( $includeItem['startdate']) != '' )
                    {
                        if( trim( $includeItem['starttime-hour'] ) != '' )
                        {
                            $timeString = trim( $includeItem['startdate'] ) . ' ' . trim( $includeItem['starttime-hour'] );
                            try
                            {
                                $starttime = eZPublishEvent::createDateTime( $timeString, $includeItem, 'start', $contentObjectAttribute->LanguageCode );
                                $validate = $this->validateDateTime( $now, $starttime );
                                if( isset( $validate['state'] ) )
                                {
                                    if( trim( $includeItem['enddate'] ) != '' )
                                    {
                                        $timeString = trim( $includeItem['enddate'] );
                                        if( trim( $includeItem['endtime-hour'] ) != '' )
                                        {
                                            $timeString .= ' ' . trim( $includeItem['endtime-hour'] );
                                        }
                                        else
                                        {
                                            $timeString .= ' 00';
                                        }
                                        $endtime = eZPublishEvent::createDateTime( $timeString, $includeItem, 'end', $contentObjectAttribute->LanguageCode );
                                    }
                                    else
                                    {
                                        $endtime = clone $starttime;
                                        if( trim( $includeItem['endtime-hour'] ) == '00' || trim( $includeItem['endtime-hour'] ) == '' )
                                        {
                                            $endtime->modify( '+1 day' );
                                            $endtime->setTime( 00, 00 );
                                        }
                                        elseif( trim( $includeItem['endtime-hour'] ) != '' && trim( $includeItem['endtime-minute'] ) != '' )
                                        {
                                            $endtime->setTime( trim( $includeItem['endtime-hour'] ), trim( $includeItem['endtime-minute'] ) );
                                        }
                                        elseif( trim( $includeItem['endtime-hour'] ) != '' && trim( $includeItem['endtime-minute'] ) == '' )
                                        {
                                            $endtime->setTime( trim( $includeItem['endtime-hour'] ), 00 );
                                        }
                                    }
                                    $validate = $this->validateDateTime( $now, $starttime, $endtime );
                                    if( isset( $validate['state'] ) )
                                    {
                                        $include[$key] = array( 'start' => $starttime->format( eZPublishEvent::DATE_FORMAT ),
                                                                'end' => $endtime->format( eZPublishEvent::DATE_FORMAT ) );
                                        if( isset( $includeItem['weekdays'] ) && count( $includeItem['weekdays'] ) < 7 )
                                        {
                                            $include[$key]['weekdays'] = $includeItem['weekdays'];
                                        }
                                    }
                                }
                            }
                            catch ( Exception $e )
                            {
                                $validate['error'] = $e->getMessage();
                            }
                        }
                        else
                        {
                            $validate['error'] = ezpI18n::tr( 'extension/ezpublish-event', 'Set a start time.' );
                        }
                    }
                    else
                    {
                        $validate['error'] = ezpI18n::tr( 'extension/ezpublish-event', 'Select a start date.' );
                    }
                    if( isset( $validate['error'] ) )
                    {
                        $contentObjectAttribute->setValidationError( $validate['error'] );
                        return eZInputValidator::STATE_INVALID;
                    }
                    if( isset( $data['exclude'] ) )
                    {
                        foreach( $data['exclude'] as $key => $excludeItem )
                        {
                            $validate = array();
                            if( isset( $excludeItem['startdate'] ) && trim( $excludeItem['startdate'] ) != '' && isset( $excludeItem['enddate'] ) && trim( $excludeItem['enddate'] ) != '' )
                            {
                                $timeString = trim( $excludeItem['startdate'] ) . ' 00';
                                $starttimeExc = eZPublishEvent::createDateTime( $timeString, null, 'start', $contentObjectAttribute->LanguageCode );
                                $validate = $this->validateDateTime( $now, $starttimeExc );
                                if( isset( $validate['state'] ) )
                                {
                                    $timeString = trim( $excludeItem['enddate'] ) . ' 00';
                                    $endtimeExc = eZPublishEvent::createDateTime( $timeString, null, 'end', $contentObjectAttribute->LanguageCode );
                                    $validate = $this->validateDateTime( $now, $starttimeExc, $endtimeExc );
                                    if( isset( $validate['state'] ) )
                                    {
                                        $exclude[$key] = array( 'start' => $starttimeExc->format( eZPublishEvent::DATE_FORMAT ),
                                                                'end' => $endtimeExc->format( eZPublishEvent::DATE_FORMAT ) );
                                    }
                                }
                            }
                            elseif( isset( $excludeItem['startdate'] ) && trim( $excludeItem['startdate'] ) != '' && isset( $excludeItem['enddate'] ) && trim( $excludeItem['enddate'] ) == '' )
                            {
                                $validate['error'] = ezpI18n::tr( 'extension/ezpublish-event', 'Select an end date.' );
                            }
                            if( isset( $validate['error'] ) )
                            {
                                $contentObjectAttribute->setValidationError( $validate['error'] );
                                return eZInputValidator::STATE_INVALID;
                            }
                        }
                    }
                    if( isset( $include ) && count( $include ) > 0 )
                    {
                        ksort( $include );
                        $data_array['include'] = $include;
                    }
                    if( isset( $exclude ) && count( $exclude ) > 0 )
                    {
                        ksort( $exclude );
                        $data_array['exclude'] = $exclude;
                    }
                    if( count( $data_array ) > 0 )
                    {
                        $jsonString = json_encode( $data_array );
                        $contentObjectAttribute->setAttribute( 'data_text', $jsonString );
                    }
                }
            }
        }
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return true;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $contentTmp = json_decode( $contentObjectAttribute->attribute( 'data_text' ) );
        $content = array( 'json' => array(), 
                          'perioddetails' => array() );
        if( isset( $contentTmp->include ) && count( $contentTmp->include ) > 0 )
        {
            $include = array();
            $firststartdate = $lastenddate = 0;
            foreach( $contentTmp->include as $key => $contentIncludeItem )
            {
                // initialize include
                $startdate = new DateTime( $contentIncludeItem->start );
                $starttimestamp = $startdate->getTimestamp();
                $include[$key]['starttime'] = $starttimestamp;
                $include[$key]['start'] = $contentIncludeItem->start;
                $enddate = new DateTime( $contentIncludeItem->end );
                $endtimestamp = $enddate->getTimestamp();
                $include[$key]['endtime'] = $endtimestamp;
                $include[$key]['end'] = $contentIncludeItem->end;
                if( isset( $contentIncludeItem->weekdays ) )
                {
                    $include[$key]['weekdays'] = $contentIncludeItem->weekdays;
                }
                // get the first start date and the last end date of all periods
                if( $starttimestamp < $firststartdate || $firststartdate == 0 )
                {
                    $firststartdate = $starttimestamp;
                }
                if( $endtimestamp > $lastenddate || $lastenddate == 0 )
                {
                    $lastenddate = $endtimestamp;
                }
            }
        }
        if( isset( $contentTmp->exclude ) && count( $contentTmp->exclude ) > 0 )
        {
            $exclude = array();
            foreach( $contentTmp->exclude as $key => $contentExcludeItem )
            {
                // initialize exclude
                $startdateExc = new DateTime( $contentExcludeItem->start );
                $starttimestamp = $startdateExc->getTimestamp();
                $exclude[$key]['starttime'] = $starttimestamp;
                $exclude[$key]['start'] = $contentExcludeItem->start;
                $enddateExc = new DateTime( $contentExcludeItem->end );
                $endtimestamp = $enddateExc->getTimestamp();
                $exclude[$key]['endtime'] = $endtimestamp;
                $exclude[$key]['end'] = $contentExcludeItem->end;
            }
        }
        if( isset( $include ) && count( $include ) > 0 )
        {
            $content['json']['include'] = $include;
        }
        if( isset( $exclude ) && count( $exclude ) > 0 )
        {
            $content['json']['exclude'] = $exclude;
        }
        if( isset( $firststartdate ) )
        {
            $content['perioddetails']['firststartdate'] = $firststartdate;
        }
        if( isset( $lastenddate ) )
        {
            $content['perioddetails']['lastenddate'] = $lastenddate;
        }
        return $content;
    }

    /*
     * Returns the meta data used for storing search indeces.
     */
    function metaData( $contentObjectAttribute )
    {
        $content = $this->objectAttributeContent( $this );
        return $content;
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" ) != '';
    }

    /*
     * Return string representation of an contentobjectattribute data for simplified export
     */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function validateDateTime( $now, $checktime1, $checktime2 = false )
    {
        if( $checktime1 instanceof DateTime && ( $checktime2 === false || ( $checktime2 !== false && $checktime2 instanceof DateTime ) ) )
        {
            // validation for start time in the future disabled because you should change content of an event during the whole period
            #if( $checktime2 === false && $checktime1->getTimestamp() < $now )
            #{
            #    return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'Select a start date in the future.' ) );
            #}
            if( $checktime2 !== false )
            {
                if( $checktime2->getTimestamp() < $now )
                {
                    return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'Select an end date in the future.' ) );
                }
                if( $checktime1->getTimestamp() > $checktime2->getTimestamp() )
                {
                    return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'Select an end time newer then the start time.' ) );
                }
                $tmpChecktime1 = clone $checktime1;
                $tmpChecktime1->modify( '+1 year' );
                if( $tmpChecktime1->getTimestamp() < $checktime2->getTimestamp() )
                {
                    return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'Maximum period of an event is one year.' ) );
                }
            }
        }
        else
        {
            if( !$checktime1 instanceof DateTime )
            {
                return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'Start date is not instanceof DateTime.' ) );
            }
            if( $checktime2 !== false && !$checktime2 instanceof DateTime )
            {
                return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'End date is not instanceof DateTime.' ) );
            }
        }
        return array( 'state' => true );
    }
}

eZDataType::register( eZPublishEventType::DATA_TYPE_STRING, "eZPublishEventType" );