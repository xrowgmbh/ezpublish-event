<?php

class eZPEventUtils
{
    function eZPEventUtils()
    {
    }

    function operatorList()
    {
        return array( 'ezpevent_show_weekdays' );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 'ezpevent_show_weekdays' => array( 'languageCode' => array( 'type' => 'string',
                                                                                  'required' => true,
                                                                                  'default' => '' ) ) );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'ezpevent_show_weekdays':
            {
                $item = $operatorValue;
                if( !isset( $item['starttime-hour'] ) || ( isset( $item['starttime-hour'] ) && $item['starttime-hour'] == '' ) )
                    $item['starttime-hour'] = ' 00';
                if( $item['enddate'] == '' )
                    $item['enddate'] = trim( $item['startdate'] );
                if( !isset( $item['endtime-hour'] ) || ( isset( $item['endtime-hour'] ) && $item['endtime-hour'] == '' ) )
                    $item['endtime-hour'] = ' 00';
                $startTimeString = trim( $item['startdate'] ) . ' ' . trim( $item['starttime-hour'] );
                $tmpStarttime = eZPublishEvent::createDateTime( $startTimeString, $item, 'start', $namedParameters['languageCode'] );
                if( $tmpStarttime instanceof DateTime )
                {
                    $tmpStarttime->setTime( 00, 00 );
                }
                $endTimeString = trim( $item['enddate'] ) . ' ' . trim( $item['endtime-hour'] );
                $tmpEndtime = eZPublishEvent::createDateTime( $endTimeString, $item, 'end', $namedParameters['languageCode'] );
                if( $tmpEndtime instanceof DateTime )
                {
                    $tmpEndtime->setTime( 00, 00 );
                    $operatorValue = ( $tmpEndtime->getTimestamp() - $tmpStarttime->getTimestamp() ) / 86400;
                }
            } break;
        }
    }
}