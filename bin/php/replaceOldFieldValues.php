<?php

/* 
 * if you don't set EventClassIdentifier or/and AttributeName or/and ParentNodeID in your ezpublishevent.ini or you would like to overwrite them, use this script with this options
 * php extension/ezpublish-event/bin/php/replaceOldFieldValues.php --replace --classidentifier=event --eventfield=ezpevent --startfield=start --endfield=end
 * otherwise like this
 * php extension/ezpublish-event/bin/php/replaceOldFieldValues.php --replace --startfield=start --endfield=end
 * 
 * to remove old attributes
 * if you don't set EventClassIdentifier in your ezpublishevent.ini or you would like to overwrite it, use this script with option "--classidentifier=xyz"
 * php extension/ezpublish-event/bin/php/replaceOldFieldValues.php --remove --classidentifier=event --deleteFields=start,end
 * otherwise like this
 * php extension/ezpublish-event/bin/php/replaceOldFieldValues.php --remove --deleteFields=start,end
 */

require 'autoload.php';
$cli = eZCLI::instance();
$script = eZScript::instance( array( 
    'description' => ( "\n" . "This script will replace old ezdatetime start and end with new field ezpublishevent. You can also delete the old fields with option --deleteFields.\n" ) , 
    'use-session' => false , 
    'use-modules' => true , 
    'use-extensions' => true 
) );

$options = $script->getOptions( "[replace][remove][parentNodeID:][classidentifier:][eventfield:][startfield:][endfield:][deleteFields:][limit:][offset:]",
        "",
        array() );

$script->startup();
$script->initialize();

$ini = eZINI::instance( 'site.ini' );
$userCreatorID = $ini->variable( 'UserSettings', 'UserCreatorID' );
$user = eZUser::fetch( $userCreatorID );
if ( !$user )
{
    $cli->error( "Cannot get user object by userID = '$userCreatorID'.\n(See site.ini[UserSettings].UserCreatorID)" );
    $script->shutdown( 1 );
}
eZUser::setCurrentlyLoggedInUser( $user, $userCreatorID );

$ezpublishevent_ini = eZINI::instance( 'ezpublishevent.ini' );
if(!isset($options['classidentifier']))
{
    if($ezpublishevent_ini->hasVariable( 'Settings', 'EventClassIdentifier' ))
    {
        $classidentifier = $ezpublishevent_ini->variable( 'Settings', 'EventClassIdentifier' );
    }
    else
    {
        $cli->error( "Please set a classidentifier" );
        $script->shutdown( 1 );
    }
}
else{
    $classidentifier = $options['classidentifier'];
}
if(isset($classidentifier))
{
    $class = eZContentClass::fetchByIdentifier($classidentifier);
    if($class instanceof eZContentClass)
    {
        //$limit = $options['limit'] ? (int)$options['limit'] : 1000;
        //$offset = $options['offset'] ? (int)$options['offset'] : 0;
        if(isset($options['replace']))
        {
            if(!isset($options['eventfield']))
            {
                if($ezpublishevent_ini->hasVariable( 'Settings', 'AttributeName' ))
                {
                    $eventfield = $ezpublishevent_ini->variable( 'Settings', 'AttributeName' );
                }
                else
                {
                    $cli->error( "Please set an eventfield" );
                    $script->shutdown( 1 );
                }
            }
            else
                $eventfield = $options['eventfield'];
            if(isset($eventfield))
            {
                // chech if attribute is set
                
                $eventattribute = $class->fetchAttributeByIdentifier($eventfield);
                if($eventattribute === null)
                {
                    $attrCreateInfo = array(
                            'identifier' => $eventfield,
                            'name' => 'Eventdaten',
                            'can_translate' => 1,
                            'is_required' => 0,
                            'is_searchable' => 0
                    );
                    $eventattribute = eZPublishEvent::addEventAttribute( $class, $attrCreateInfo );
                }
                if($eventattribute !== null)
                {
                   /* if(!isset($options['parentNodeID']))
                    {
                        if($ezpublishevent_ini->hasVariable( 'Settings', 'ParentNodeID' ))
                        {
                            $parentNodeID = $ezpublishevent_ini->variable( 'Settings', 'ParentNodeID' );
                        }
                        else
                        {
                            $cli->error( "Please set a parentNodeID" );
                            $script->shutdown( 1 );
                        }
                    }
                    else
                        $parentNodeID = $options['parentNodeID'];*/
                   // if(isset($parentNodeID))
                   // {
                        if(!isset($options['startfield']) || !isset($options['endfield']))
                        {
                            if(!isset($options['startfield']) && isset($options['endfield']))
                                $cli->error( "Please set startfield" );
                            if(isset($options['startfield']) && !isset($options['endfield']))
                                $cli->error( "Please set endfield" );
                            if(!isset($options['startfield']) && !isset($options['endfield']))
                                $cli->error( "Please set startfield and endfield" );
                            $script->shutdown( 1 );
                        }
                        else
                        {
                            if( strpos( 'start', $options['startfield'] ) !== false )
                                $startfield = $options[ 'startfield' ];
                            else
                            {
                                $cli->error( "startfield has to be the start date" );
                                $script->shutdown( 1 );
                            }
                            if( strpos( 'end', $options['endfield'] ) !== false )
                                $endfield = $options[ 'endfield' ];
                            else
                            {
                                $cli->error( "endfield has to be the end date" );
                                $script->shutdown( 1 );
                            }
                        }
                        /*$params = array( 'Limit' => $limit,
                                         'Offset' => $offset,
                                         'SortBy' => array('published', true),
                                         'IgnoreVisibility' => true,
                                         'ClassFilterType' => 'include',
                                         'ClassFilterArray' => array( $classidentifier ) );
                        $nodes = eZContentObjectTreeNode::subTreeByNodeID( $params, $parentNodeID );*/
                        $objects=eZContentObject::fetchList(true,array('contentclass_id'=>43));
                        if( count( $objects ) > 0 )
                        {
                            $cli->output( "Start fetching " . count( $objects ). " " . $classidentifier );
                            foreach( $objects as $object )
                            {
                                if( $object instanceof eZContentObject )
                                {
                                    $object_versions= $object->versions(true);
                                    foreach($object_versions as $object_version)
                                    {
                                        $dataMap = $object_version->dataMap();
                                        if( isset( $dataMap[$eventfield] ) )
                                        {
                                            if( isset( $dataMap[$startfield] ) && isset( $dataMap[$endfield] ) )
                                            {
                                                $startdate = $dataMap[$startfield];
                                                $enddate = $dataMap[$endfield];
                                                $start = new DateTime();
                                                $start->setTimestamp($startdate->attribute( 'data_int' ));
                                                $end = new DateTime();
                                                $end->setTimestamp($enddate->attribute( 'data_int' ));
                                                $include = array( 'include' => array( 0 => array( 'start' => $start->format( eZPublishEvent::DATE_FORMAT ),
                                                                                              'end' => $end->format( eZPublishEvent::DATE_FORMAT ) ) ) );
                                                $jsonString = json_encode( $include );
                                                $dataMap[$eventfield]->setAttribute( 'data_text', $jsonString );
                                                $dataMap[$eventfield]->store();
                                                $object_version->store();
                                                $cli->output( "Set value for Object " . $object_version->ID );
                                              }
                                              else
                                              {
                                                  $cli->error( "startfield and endfield have to be in the data map of the node" );
                                                  $script->shutdown( 1 );
                                                  //continue;
                                              }
                                          }
                                          else
                                          {
                                               $cli->error( "eventfield has to be in the data map of the node" );
                                               $script->shutdown( 1 );
                                              // echo $eventfield;
                                              // continue;
                                          }
                                     }
                                 }
                             }
                         }
                    //}
                }
            }
        }
        if(isset($options['remove']))
        {
            if(!isset($options['deleteFields']))
            {
                $cli->error( "Please set deleteFields" );
                $script->shutdown( 1 );
            }
            else{
                $deleteFields = $options['deleteFields'];
            }
            if( $deleteFields !== false && $deleteFields != '' )
            {
                $cli->output( "Start removing attributes " . $deleteFields );
                $deleteFieldsArray = explode( ',', $deleteFields );
                if( is_array( $deleteFieldsArray ) && count( $deleteFieldsArray ) > 0 )
                {
                    $deleteAttributes = array();
                    foreach( $deleteFieldsArray as $classAttributeIdentifier )
                    {
                        // get attributes of 'temporary' version as well
                        $classAttributeList = eZContentClassAttribute::fetchFilteredList( array(
                                'contentclass_id' => $class->ID ,
                                'identifier' => $classAttributeIdentifier
                        ), true );

                        foreach ( $classAttributeList as $classAttribute )
                        {
                            $dataType = $classAttribute->dataType();
                            if ( $dataType->isClassAttributeRemovable( $classAttribute ) )
                            {
                                $objectAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList( $classAttribute->attribute( 'id' ) );
                                foreach ( $objectAttributes as $objectAttribute )
                                {
                                    $objectAttributeID = $objectAttribute->attribute( 'id' );
                                    $objectAttribute->removeThis( $objectAttributeID );
                                }
                                $classAttribute->removeThis();
                            }
                            else
                            {
                                $removeInfo = $dataType->classAttributeRemovableInformation( $classAttribute );
                            }
                        }
                    }
                    $cli->output( "removed $deleteFields" );
                }
            }
        }
    }
}
$cli->output( "Ready" );
$script->shutdown();