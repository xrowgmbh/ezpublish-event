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
                    $ezpeventContent = $ezpevent->content();
                    die(var_dump($ezpeventContent));
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