<?php

// remove SOLR rows if event is offline
if ( !$isQuiet )
{
    $cli->output( "Performing cleanup operations." );
    $cli->output( "Cleaning up removed items ..." );
}

$count = eZFlowOperations::cleanupRemovedItems();

if ( !$isQuiet )
{
    $cli->output( "Number of removed items: $count" );
    $cli->output( "Done." );
}