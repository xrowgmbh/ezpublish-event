<?php

/* 
 * php extension/ezpublish-event/bin/php/replaceOldFieldValues.php --parentNodeID=363959 --classidentifier=event --eventfield=ezpevent --field1=start --field2=end --limit=6000 --offset=0
 * php extension/ezpublish-event/bin/php/replaceOldFieldValues.php --classidentifier=event --deleteFields=start,end,event_series
 */

require 'autoload.php';
$cli = eZCLI::instance();
$script = eZScript::instance( array( 
    'description' => ( "\n" . "This script will replace old ezdatetime start and end with new field ezpublishevent. You can also delete the old fields with option --deleteFields.\n" ) , 
    'use-session' => false , 
    'use-modules' => true , 
    'use-extensions' => true 
) );

$options = $script->getOptions( "[parentNodeID:][classidentifier:][eventfield:][field1:][field2:][deleteFields:][limit:][offset:]",
        "",
        array() );

$script->startup();
$script->initialize();

$ini = eZINI::instance();
$userCreatorID = $ini->variable( 'UserSettings', 'UserCreatorID' );
$user = eZUser::fetch( $userCreatorID );
if ( !$user )
{
    $cli->error( "Cannot get user object by userID = '$userCreatorID'.\n(See site.ini[UserSettings].UserCreatorID)" );
    $script->shutdown( 1 );
}
eZUser::setCurrentlyLoggedInUser( $user, $userCreatorID );

if(!isset($options[ 'classidentifier' ]))
{
    $cli->error( "Please set a classidentifier" );
    $script->shutdown( 1 );
}
else
    $classidentifier = $options['classidentifier'];

$eventfield = $options['eventfield'] ? $options['eventfield'] : false;
if( $eventfield !== false )
{
    if(!isset($options[ 'parentNodeID' ]))
    {
        $cli->error( "Please set a parentNodeID" );
        $script->shutdown( 1 );
    }
    else
        $parentNodeID = (int)$options['parentNodeID'];
    if(!isset($options[ 'field1' ]) || !isset($options[ 'field2' ]))
    {
        if(!isset($options[ 'field1' ]) && isset($options[ 'field2' ]))
            $cli->error( "Please set field1" );
        if(isset($options[ 'field1' ]) && !isset($options[ 'field2' ]))
            $cli->error( "Please set field2" );
        if(!isset($options[ 'field1' ]) && !isset($options[ 'field2' ]))
            $cli->error( "Please set field1 and field2" );
        $script->shutdown( 1 );
    }
    else
    {
        if( strpos( 'start', $options[ 'field1' ] ) !== false )
            $field1 = $options[ 'field1' ];
        else
        {
            $cli->error( "field1 have to be the start date" );
            $script->shutdown( 1 );
        }
        if( strpos( 'end', $options[ 'field2' ] ) !== false )
            $field2 = $options[ 'field2' ];
        else
        {
            $cli->error( "field2 have to be the end date" );
            $script->shutdown( 1 );
        }
    }
}
$deleteFields = $options['deleteFields'] ? $options['deleteFields'] : false;
$limit = $options[ 'limit' ] ? (int)$options[ 'limit' ] : 1000;
$offset = $options[ 'offset' ] ? (int)$options[ 'offset' ] : 0;
if( $eventfield !== false )
{
    $params = array( 'Limit' => $limit,
                     'Offset' => $offset,
                     'SortBy' => array('published', true),
                     'IgnoreVisibility' => true,
                     'ClassFilterType' => 'include',
                     'ClassFilterArray' => array( $classidentifier ) );
    $nodes = eZContentObjectTreeNode::subTreeByNodeID( $params, $parentNodeID );
    if( count( $nodes ) > 0 )
    {
        $cli->output( "Start fetching " . count( $nodes ). " " . $classidentifier );
        foreach( $nodes as $node )
        {
            if( $node instanceof eZContentObjectTreeNode ) 
            {
                $dataMap = $node->dataMap();
                if( isset( $dataMap[$eventfield] ) )
                {
                    if( isset( $dataMap[$field1] ) && isset( $dataMap[$field2] ) )
                    {
                        $startdate = $dataMap[$field1];
                        $enddate = $dataMap[$field2];
                        $start = new DateTime();
                        $start->setTimestamp($startdate->attribute( 'data_int' ));
                        $end = new DateTime();
                        $end->setTimestamp($enddate->attribute( 'data_int' ));
                        $include = array( 'include' => array( 0 => array( 'start' => $start->format( eZPublishEvent::DATE_FORMAT ),
                                                                          'end' => $end->format( eZPublishEvent::DATE_FORMAT ) ) ) );
                        $jsonString = json_encode( $include );
                        $dataMap[$eventfield]->setAttribute( 'data_text', $jsonString );
                        $dataMap[$eventfield]->store();
                        $node->store();
                        $cli->output( "Set value for node " . $node->NodeID );
                    }
                    else
                    {
                        $cli->error( "field1 and field2 have to be in the data map of the node" );
                        $script->shutdown( 1 );
                    }
                }
                else
                {
                    $cli->error( "eventfield has to be in the data map of the node" );
                    $script->shutdown( 1 );
                }
            }
        }
    }
}
if( $deleteFields !== false && $deleteFields != '' )
{
    $cli->output( "Start removing attributes " . $deleteFields );
    $deleteFieldsArray = explode( ',', $deleteFields );
    if( is_array( $deleteFieldsArray ) && count( $deleteFieldsArray ) > 0 )
    {
        $deleteAttributes = array();
        $class = eZContentClass::fetchByIdentifier( $classidentifier );
        foreach( $deleteFieldsArray as $deleteField )
        {
            $deleteAttributes[] = $class->fetchAttributeByIdentifier( $deleteField );
        }
        $cli->output( "ES WURDE NICHT GELÖSCHT, WEIL DER BEFEHL DEAKTIVIERT IST" );
        #$class->removeAttributes( $deleteAttributes );
    }
}