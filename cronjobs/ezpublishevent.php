<?php

$cli->output( "START ezpublshevent.php\n" );
$ini = eZINI::instance( 'site.ini' );
// Get user's ID who can remove subtrees. (Admin by default with userID = 14)
$userCreatorID = $ini->variable( 'UserSettings', 'UserCreatorID' );
$user = eZUser::fetch( $userCreatorID );
if ( ! $user )
{
    $cli->error( "Can not get user object by userID = '$userCreatorID'.\n(See site.ini[UserSettings].UserCreatorID)" );
    $script->shutdown( 1 );
}
eZUser::setCurrentlyLoggedInUser( $user, $userCreatorID );

// initialize solarium
$config = array(
        'adapteroptions' => array(
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr/',
        )
);
// create a client instance
$client = new Solarium_Client($config);

// remove all rows from SOLR where NodeID is Events
$ezpevent_ini = eZINI::instance( 'ezpublishevent.ini' );
$parentNodeID = $ezpevent_ini->variable( 'CronjobSettings', 'ParentNodeID' );
$classIdentifier = $ezpevent_ini->variable( 'CronjobSettings', 'EventClassIdentifier' );
$attributeName = $ezpevent_ini->variable( 'CronjobSettings', 'AttributeName' );
$limit = 500;
$offset = 0;
// get all events to set new SOLR index
/*$params = array( 'Limit' => $limit,
                 'Offset' => $offset,
                 'SortBy' => array('published', true),
                 'AttributeFilter' => array(array('published', '<', $creationTimestamp)),
                 'ClassFilterType' => 'include',
                 'ClassFilterArray' => array( $classIdentifier ) );*/
$config = array(
        'adapteroptions' => array(
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr/',
        )
);
// create a client instance
$client = new Solarium_Client($config);
// create a ping query
$ping = $client->createPing();
// execute the ping query
try{
    $result = $client->ping($ping);
    echo 'Ping query successful';
    echo '<br/><pre>';
    var_dump($result->getData());
}catch(Solarium_Exception $e){
    echo 'Ping query failed';
}
do
{
    $events = eZContentObjectTreeNode::subTreeByNodeID( array(
            'Limit' => $limit,
            'Offset' => $offset,
            'ClassFilterType' => 'include',
            'ClassFilterArray' => array(
                    $classIdentifier
            )
    ), $parentNodeID );
    $events_count = count( $events );
    foreach( $events as $event )
    {
        if( $event instanceof eZContentObjectTreeNode )
        {
            // get days
            $dataMap = $event->dataMap();
            if( isset( $dataMap[$attributeName] ) )
            {
                $ezpevent = $dataMap[$attributeName];
                if( $ezpevent->hasContent() )
                {
                    $requiredData = getRequiredIndexData( $event, $dataMap );
                    $indexArray = array();
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
                            $starttime->setTime( 00, 00 );
                            $start = $starttime->getTimestamp();
                            $endtime = new DateTime();
                            $endtime->setTimestamp( $ezpeventItem['endtime'] );
                            $endtime->setTime( 00, 00 );
                            $end = $endtime->getTimestamp();
                            // check all days
                            for( $day = $start; $day <= $end; $day=$day+86400 )
                            {
                                if( !isset( $excludeDays ) || ( isset( $excludeDays ) && !array_key_exists( $day, $excludeDays ) ) )
                                {
                                    if( !isset( $ezpeventItem['weekdays'] ) || ( isset( $ezpeventItem['weekdays'] ) && in_array( date( 'D', $day ), $ezpeventItem['weekdays'] ) ) )
                                    {
                                        $indexArray[$day] = $day;
                                    }
                                }
                            }
                        }
                    }
                    if( count( $indexArray ) > 0 )
                    {
                        setSOLRIndex( $indexArray, $dataMap, $event );
                    }
                }
            }
        }
    }
    $offset = $offset + $events_count;
}
while ( $events_count == $limit );

if ( !$isQuiet )
{
    $cli->output( "Performing cleanup operations." );
    $cli->output( "Cleaning up removed items ..." );
}

if ( !$isQuiet )
{
    $cli->output( "Number of removed items: $count" );
    $cli->output( "Done." );
}

eZUser::logoutCurrent();

function getRequiredIndexData( $event, $dataMap )
{
    /* Veranstaltungsort (Name Verlinktes Objekt oder Adresse) (FeldTyp StrField)
     Wo / Ort / Stadt (FeldTyp StrField)
    Was / Kategorie / Main Parent Name (FeldTyp StrField)
    Von (FeldTyp DateField)
    Bis (FeldTyp DateField)
    ContentObjectID (FeldTyp StrField)
    Name (FeldTyp StrField)
    Keywords aus Metadata (FeldTyp StrField, Multiterm)
    Text ( Daten aus teaser_text + description + metadata + short_name) (FeldTyp StrField oder BIG TEXT oder so) (Hier können die Daten aus Datatyp->metadata() ausgelesen werden)
    */
}

function setSOLRIndex( $indexArray, $dataMap, $event )
{

}