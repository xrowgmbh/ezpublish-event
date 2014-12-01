<?php

$eZTemplateOperatorArray = array();

$eZTemplateOperatorArray[] = array( 'script' => 'extension/ezpublish-event/autoloads/ezpublisheventutils.php',
                                    'class' => 'eZPEventUtils',
                                    'operator_names' => array( 'ezpevent_locale_vars', 'ezpevent_show_weekdays' ) );