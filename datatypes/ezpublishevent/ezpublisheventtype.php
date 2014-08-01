<?php

class eZPublishEventType extends eZDataType
{
    const DATA_TYPE_STRING = 'ezpublishevent';
    const DEFAULT_FIELD = 'data_text';
    const DATE_FORMAT = DateTime::ISO8601;

    function eZPublishEventType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', 'eZ Publish Event', 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $locale = $contentObjectAttribute->LanguageCode;
        $ezpe_ini = eZINI::instance( 'ezpublishevent.ini' );
        if( $ezpe_ini->hasVariable( 'Settings', 'DateFormat' ) )
        {
            $dateFormatArray = $ezpe_ini->variable( 'Settings', 'DateFormat' );
            $timeFormatArray = $ezpe_ini->variable( 'Settings', 'TimeFormat' );
            $now = time();
            if ( $http->hasPostVariable( $base . '_ezpeventdate_data_' . $contentObjectAttribute->attribute( 'id' ) ) )
            {
                if( isset( $dateFormatArray[$locale] ) )
                {
                    $data = $http->postVariable( $base . '_ezpeventdate_data_' . $contentObjectAttribute->attribute( 'id' ) );
                    $dateFormat = $dateFormatArray[$locale] . $timeFormatArray[$locale];
                    $data_text = array();
                    $include = array();
                    $exclude = array();
                    $days = array();
                    // get include date
                    if( isset($data['include'] ) )
                    {
                        foreach( $data['include'] as $key => $includeItem )
                        {
                            $validate = array();
                            if( isset( $includeItem['startdate'] ) && trim( $includeItem['startdate']) != '' )
                            {
                                if( isset( $includeItem['starttime-hour'] ) && trim( $includeItem['starttime-hour'] ) != '' )
                                {
                                    $timeString = trim( $includeItem['startdate'] ) . ' ' . trim( $includeItem['starttime-hour'] );
                                    $starttime = $this->createDateTime( $timeString, $includeItem, 'start', $dateFormat );
                                    $validate = $this->validateDateTime( $now, $starttime );
                                    if( $validate['state'] )
                                    {
                                        if( isset( $includeItem['enddate'] ) && trim( $includeItem['enddate']) != '' )
                                        {
                                            $timeString = trim( $includeItem['enddate'] );
                                            if( isset( $includeItem['endtime-hour'] ) && trim( $includeItem['endtime-hour']) != '' )
                                            {
                                                $timeString .= trim( $includeItem['endtime-hour'] );
                                            }
                                            else
                                            {
                                                $timeString .= ' 00';
                                            }
                                            $endtime = $this->createDateTime( $timeString, $includeItem, 'end', $dateFormat );
                                        }
                                        else
                                        {
                                            $endtime = clone $starttime;
                                            $endtime->modify( '+1 day' );
                                            $endtime->setTime( 00, 00 );
                                        }
                                        $validate = $this->validateDateTime( $now, $starttime, $endtime );
                                        if( $validate['state'] )
                                        {
                                            $include[$key] = array( 'start' => $starttime->format( DATE_FORMAT ),
                                                                    'end' => $endtime->format( DATE_FORMAT ) );
                                            if( isset( $includeItem['weekdays'] ) && count( $includeItem['weekdays'] ) < 7 )
                                            {
                                                $include[$key]['weekdays'] = $includeItem['weekdays'];
                                            }
                                            $tmpStarttime = clone $starttime;
                                            $tmpStarttime->setTime( 00, 00 );
                                            $tmpEndtime = clone $endtime;
                                            $tmpEndtime->setTime( 00, 00 );
                                            $days[$key] = ( $tmpEndtime->getTimestamp() - $tmpStarttime->getTimestamp() ) / 86400;
                                        }
                                    }
                                }
                                else
                                {
                                    $validate['error'] = ezpI18n::tr( 'extension/ezpublish-event', 'set a start time.' );
                                }
                            }
                            else
                            {
                                $validate['error'] = ezpI18n::tr( 'extension/ezpublish-event', 'select a start date.' );
                            }
                            if( isset( $validate['error'] ) )
                            {
                                if( isset( $days ) )
                                    $http->setPostVariable( $base . '_ezpe_valid_days_' . $contentObjectAttribute->attribute( 'id' ), $days );
                                $contentObjectAttribute->setValidationError( $validate['error'] );
                                return eZInputValidator::STATE_INVALID;
                            }
                        }
                    }
                    if( isset( $data['exclude'] ) )
                    {
                        foreach( $data['exclude'] as $key => $excludeItem )
                        {
                            $validate = array();
                            if( isset( $excludeItem['startdate'] ) && trim( $excludeItem['startdate'] ) != '' )
                            {
                                $timeString = trim( $excludeItem['startdate'] ) . ' 00:00:00';
                                $starttime = $this->createDateTime( $timeString, null, 'start', $dateFormat );
                                $validate = $this->validateDateTime( $now, $starttime );
                                if( $validate['state'] )
                                {
                                    if( isset( $excludeItem['enddate'] ) && trim( $excludeItem['enddate'] ) != '' )
                                    {
                                        $timeString = trim( $excludeItem['enddate'] ) . ' 00:00:00';
                                        $endtime = $this->createDateTime( $timeString, null, 'end', $dateFormat );
                                    }
                                    else
                                    {
                                        $endtime = clone $starttime;
                                        $endtime->modify( '+1 day' );
                                        $endtime->setTime( 00, 00 );
                                    }
                                    $validate = $this->validateDateTime( $now, $starttime, $endtime );
                                    if( $validate['state'] )
                                    {
                                        $exclude[$key] = array( 'start' => $starttime->format( DATE_FORMAT ),
                                                                'end' => $endtime->format( DATE_FORMAT ) );
                                    }
                                }
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
                        sort( $include );
                        $data_array['include'] = $include;
                    }
                    if( isset( $exclude ) && count( $exclude ) > 0 )
                    {
                        sort( $exclude );
                        $data_array['exclude'] = $exclude;
                    }
                    if( count( $data_array ) > 0 )
                    {
                        $jsonString = json_encode( $data_array );
                        $http->setPostVariable( $base . '_ezpe_valid_data_' . $contentObjectAttribute->attribute( 'id' ), $jsonString );
                    }
                }
                else
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'extension/ezpublish-event', 'please set your locale in dateformat in ezpublishevent.ini' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }
        }
        else
        {
            $contentObjectAttribute->setValidationError( ezpI18n::tr( 'extension/ezpublish-event', 'please set a dateformat in ezpublishevent.ini' ) );
            return eZInputValidator::STATE_INVALID;
        }
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_ezpe_valid_data_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $jsonString =  $http->postVariable( $base . '_ezpe_valid_data_' . $contentObjectAttribute->attribute( 'id' ) );
            $contentObjectAttribute->setAttribute( 'data_text', $jsonString );
            return true;
        }
        return false;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $locale = $contentObjectAttribute->LanguageCode;
        $contentTmp = json_decode( $contentObjectAttribute->attribute( 'data_text' ) );
        $content = array();
        $ezpe_ini = eZINI::instance( 'ezpublishevent.ini' );
        $dateFormatArray = $ezpe_ini->variable( 'Settings', 'DateFormat' );
        if( isset( $dateFormatArray[$locale] ) )
        {
            $dateFormat = $dateFormatArray[$locale];
            if( isset( $contentTmp->include ) && count( $contentTmp->include ) > 0 )
            {
                $include = array();
                foreach( $contentTmp->include as $key => $contentIncludeItem )
                {
                    // initialize include
                    $startdate = new DateTime( $contentIncludeItem->start );
                    $starttimestamp = $startdate->getTimestamp();
                    $include[$key] = array( 'startdate' => date( $dateFormat, $starttimestamp ),
                                            'starttime-hour' => date( 'H', $starttimestamp ),
                                            'starttime-minute' => date( 'i', $starttimestamp ) );
                    $enddate = new DateTime( $contentIncludeItem->end );
                    $endtimestamp = $enddate->getTimestamp();
                    // check if event is only one day
                    $tmpStartdate = clone $startdate;
                    $tmpStartdate->modify( '+1 day' );
                    $tmpStartdate->setTime( 00, 00 );
                    if( $endtimestamp > $tmpStartdate->getTimestamp() )
                    {
                        $include[$key]['enddate'] = date( $dateFormat, $endtimestamp );
                        $include[$key]['endtime-hour'] = date( 'H', $endtimestamp );
                        $include[$key]['endtime-minute'] = date( 'i', $endtimestamp );
                    }
                    if( isset( $contentIncludeItem->weekdays ) )
                    {
                        $include[$key]['weekdays'] = $contentIncludeItem->weekdays;
                    }
                }
            }
            if( isset( $include ) && count( $include ) > 0 )
            {
                $content['include'] = $include;
            }
            if( isset( $exclude ) && count( $exclude ) > 0 )
            {
                $content['exclude'] = $exclude;
            }
        }
        return $content;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return (int)$contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        return $contentObjectAttribute->setAttribute( 'data_text', $string );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" ) != '';
    }

    function validateDateTime( $now, $checktime1, $checktime2 = false )
    {
        if( $checktime1 instanceof DateTime && ( $checktime2 === false || ( $checktime2 !== false && $checktime2 instanceof DateTime ) ) )
        {
            if( $checktime2 === false && $checktime1->getTimestamp() < $now )
            {
                return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'select a start date in the future.' ) );
            }
            if( $checktime2 !== false )
            {
                if( $checktime2->getTimestamp() < $now )
                {
                    return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'select an end date in the future.' ) );
                }
                if( $checktime1->getTimestamp() > $checktime2->getTimestamp() )
                {
                    return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'select an end time newer then the start time.' ) );
                }
            }
        }
        else
        {
            if( !$checktime1 instanceof DateTime )
            {
                return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'start date is not instanceof DateTime.' ) );
            }
            if( $checktime2 !== false && !$checktime2 instanceof DateTime )
            {
                return array( 'error' => ezpI18n::tr( 'extension/ezpublish-event', 'end date is not instanceof DateTime.' ) );
            }
        }
        return array( 'state' => true );
    }

    function createDateTime( $timeString, $includeItem, $type, $dateFormat )
    {
        if( isset( $includeItem[$type.'time-minute'] ) && trim($includeItem[$type.'time-minute']) != '' )
        {
            $timeString .= ':' . trim( $includeItem[$type.'time-minute'] ) . ':00';
        }
        else
        {
            $timeString .= ':00:00';
        }
        $dateTime = DateTime::createFromFormat( $dateFormat, $timeString );
        return $dateTime;
    }

    function sortKeyType()
    {
        return 'int';
    }
}

eZDataType::register( eZPublishEventType::DATA_TYPE_STRING, "eZPublishEventType" );