<?php

class eZPublishEvent
{
    const DATE_FORMAT = DateTime::ISO8601;
    static private $currentDateFormat = null;

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
}