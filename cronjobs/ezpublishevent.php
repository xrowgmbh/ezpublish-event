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

// remove all rows from SOLR where NodeID is Events
$ezpevent_ini = eZINI::instance( 'ezpublishevent.ini' );
$parentNodeID = $ezpevent_ini->variable( 'Settings', 'ParentNodeID' );
$classIdentifier = $ezpevent_ini->variable( 'Settings', 'EventClassIdentifier' );
$limit = 500;
$offset = 0;

####### WICHTIG: wenn das auf true ist, wird der ganze SOLR-Index entfernt
$clearIndex = false;

// execute the ping query
try
{
    $client = eZPublishSolarium::createSolariumClient();
    if ( !$isQuiet )
    {
        $cli->output( "SOLR ping query successful\n" );
    }
    $query = $client->createSelect();
    $update = $client->createUpdate();
    // clear the old index first
    if( $clearIndex )
    {
        $update->addDeleteQuery( '*:*' );
        $update->addCommit();
        $deleteResult = $client->update( $update );
        if ( !$isQuiet )
        {
            $cli->output( "Clear SOLR index query executed\n" );
            $cli->output( "Query status: " . $deleteResult->getStatus() . "\n" );
            $cli->output( "Query time: " . $deleteResult->getQueryTime() . "\n" );
        }
    }
    // index all events
    do
    {
        $cli->output( "Get $limit events from offset $offset\n" );
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
                $object = $event->object();
                $ezpEvent = new eZPublishEvent();
                $docList = $ezpEvent->createSOLRDocument( $object, $update );
                #die(var_dump($docList));
                $update->addDocuments( $docList );
                $update->addCommit();
                $result = $client->update( $update );
                unset( $docList );
            }
        }
        $offset = $offset + $events_count;
    }
    while ( $events_count == $limit );
}
catch( Solarium_Exception $e )
{
    $cli->error( $e->getMessage() );
    $script->shutdown( 1 );
}

eZUser::logoutCurrent();
$cli->output( "READY!" );
$script->shutdown();