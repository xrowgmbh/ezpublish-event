<?php

class eZPEventUtils
{
    function eZPEventUtils()
    {
    }

    function operatorList()
    {
        return array( 'ezpevent_locale_vars', 'ezpevent_show_weekdays' );
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
                      'ezpevent_show_weekdays' => array() );
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
        }
    }
}