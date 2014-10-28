<?php

class eZPublishSolarium
{
    static private $solariumClient = null;

    static function createSolariumClient()
    {
        if( self::$solariumClient === null )
        {
            $solrINI = eZINI::instance( 'solr.ini' );
            $solrURL = $solrINI->variable( 'SolrBaseEvents', 'SearchServerURI' );
            $url = new ezcUrl( $solrURL );
            $config = array(
                    'adapteroptions' => array(
                            'host' => $url->host,
                            'port' => $url->port,
                            'path' => '/' . implode( '/', $url->path ) . '/'
                    )
            );
            // create a client instance
            self::$solariumClient = new Solarium_Client( $config );
        }
        try {
            $ping = self::$solariumClient->createPing();
            $result = self::$solariumClient->ping($ping);
            return self::$solariumClient;
        }
        catch( Solarium_Exception $e )
        {
            throw new Solarium_Exception('Ping query failed. Try a restart.');
        }
    }
}