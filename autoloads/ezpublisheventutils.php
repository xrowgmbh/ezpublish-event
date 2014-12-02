<?php

class eZPEventUtils
{
    function eZPEventUtils()
    {
    }

    function operatorList()
    {
        return array( 'ezpevent_locale_vars', 'ezpevent_show_weekdays', 'ezpevent_format_date' );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array( 'ezpevent_locale_vars' => array( 'variable_names' => array( 'type' => 'mixed',
                                                                                  'required' => true,
                                                                                  'default' => array( 'LocaleCode' ) ) ),
                      'ezpevent_show_weekdays' => array(),
                      'ezpevent_format_date' => array( 'startdate' => array( 'type' => 'string',
                                                                             'required' => true,
                                                                             'default' => '' ),
                                                       'enddate' => array( 'type' => 'string',
                                                                           'required' => true,
                                                                           'default' => '' ),
                                                       'view' => array( 'type' => 'boolean',
                                                                        'required' => false,
                                                                        'default' => false ) ) );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'ezpevent_locale_vars':
                $variable_names = !is_array( $namedParameters['variable_names'] ) ? array( $namedParameters['variable_names'] ) : $namedParameters['variable_names'];
                $localeVars = array();
                foreach( $variable_names  as $variable_name )
                {
                    $locale = eZLocale::instance();
                    if( isset( $locale->$variable_name ) )
                    {
                        $localeVars[$variable_name] = $locale->$variable_name;
                    }
                }
                $operatorValue = $localeVars;
                break;
            case 'ezpevent_show_weekdays':
                $item = $operatorValue;
                if( !isset( $item['starttime-hour'] ) || ( isset( $item['starttime-hour'] ) && $item['starttime-hour'] == '' ) )
                    $item['starttime-hour'] = ' 00';
                if( $item['enddate'] == '' )
                    $item['enddate'] = trim( $item['startdate'] );
                if( !isset( $item['endtime-hour'] ) || ( isset( $item['endtime-hour'] ) && $item['endtime-hour'] == '' ) )
                    $item['endtime-hour'] = ' 00';
                $startTimeString = trim( $item['startdate'] ) . ' ' . trim( $item['starttime-hour'] );
                $tmpStarttime = eZPublishEvent::createDateTime( $startTimeString, $item, 'start' );
                if( $tmpStarttime instanceof DateTime )
                {
                    $tmpStarttime->setTime( 00, 00 );
                }
                $endTimeString = trim( $item['enddate'] ) . ' ' . trim( $item['endtime-hour'] );
                $tmpEndtime = eZPublishEvent::createDateTime( $endTimeString, $item, 'end' );
                if( $tmpEndtime instanceof DateTime )
                {
                    $tmpEndtime->setTime( 00, 00 );
                    $operatorValue = ( $tmpEndtime->getTimestamp() - $tmpStarttime->getTimestamp() ) / 86400;
                }
                break;
            case 'ezpevent_format_date':
                if( isset( $namedParameters['startdate'] ) && isset( $namedParameters['enddate'] ) )
                {
                    $operatorValue = $this->ezpevent_format_date( $namedParameters['startdate'], $namedParameters['enddate'], $namedParameters['view'] );
                }
                else
                {
                    $operatorValue = false;
                    eZDebug::writeError( 'Please set startdate and enddate for operator "ezpevent_format_date"', __METHOD__ );
                }
                break;
        }
    }

    function ezpevent_format_date( $startdate, $enddate, $view )
    {
        $locale = eZLocale::instance();
        $dateFormat = preg_replace( '/%/', '', $locale->ShortDateFormat );
        $timeFormat = preg_replace( '/%/', '', $locale->ShortTimeFormat );
        $days = round( ( $enddate - $startdate ) / 3600 / 24 );
        if( $days == 1 && date( $timeFormat, $enddate ) == "00:00" )
        {
            $startDate = date( $dateFormat, $startdate);
            $startTime = date( $timeFormat, $startdate);
            $dateItems = array( 'startDate' => $startDate, 'startTime' => $startTime );
        }
        elseif( date( $dateFormat, $startdate) === date( $dateFormat, $enddate) && date( $timeFormat, $enddate ) != "00:00" )
        {
            $startDate = date( $dateFormat, $startdate );
            $startTime = date( $timeFormat, $startdate );
            $endTime = date( $timeFormat, $enddate );
            $dateItems = array( 'startDate' => $startDate, 'startTime' => $startTime, 'endTime' => $endTime );
        }
        else
        {
            if( $view )
            {
                $startDate = date( $dateFormat, $startdate );
                $endDate = date( $dateFormat, $enddate );
                $startTime = date( $timeFormat, $startdate );
                $endTime = date( $timeFormat,$enddate);
                $dateItems = array( 'startDate' => $startDate, 'endDate' => $endDate, 'startTime' => $startTime, 'endTime' => $endTime );
            }
            else
            {
                $startDate = date( $dateFormat, $startdate );
                $endDate = date( $dateFormat, $enddate );
                $dateItems = array( 'startDate' => $startDate, 'endDate' => $endDate );
            }
        }
        return $dateItems;
    }
}