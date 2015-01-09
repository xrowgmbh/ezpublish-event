<?php
$module = $Params['Module'];
$tpl = eZTemplate::factory();

$Result = array();
$Result['content'] = $tpl->fetch( "design:content/searchevent.tpl" );
$Result['path'] = array( array( 'text' => ezpI18n::tr( 'extension/ezpublish-event/modules/event', 'Search' ),
                                'url' => false ) );