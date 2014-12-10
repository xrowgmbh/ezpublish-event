<?php

class eZPublishEventSearch
{
    public static function update(eZContentObject $object, $commit = true)
    {
        $ezpublishevent_ini = eZINI::instance( 'ezpublishevent.ini' );
        if($ezpublishevent_ini->hasVariable( 'Settings', 'EventClassIdentifier' ))
        {
            $classidentifier = $ezpublishevent_ini->variable( 'Settings', 'EventClassIdentifier' );
            $checkHidde=$object->mainNode()->IsInvisible;
            if($checkHidde == 1)
            {
                $client = eZPublishSolarium::createSolariumClient();
                $update = $client->createUpdate();
                $update->addDeleteQuery('meta_id_si:' . $object->ID);
                $update->addCommit();
                $deleteResult = $client->update($update);
            }
            if ($object->ClassIdentifier == $classidentifier && $object->Status == 1 && $checkHidde == 0)
            {   
                $client = eZPublishSolarium::createSolariumClient();
                $query = $client->createSelect();
                $update = $client->createUpdate();
                
                $query->setQuery('meta_id_si:' . $object->ID);
                $resulttest = $client->select($query);
                
                if ($resulttest->getNumFound() != 0) {
                    $update->addDeleteQuery('meta_id_si:' . $object->ID);
                    $deleteResult = $client->update($update);
                }
                
                $ezpEvent = new eZPublishEvent();
                $docList = $ezpEvent->createSOLRDocument($object, $update);
                $update->addDocuments($docList);
                if($commit)
                {
                   $update->addCommit();
                }
                $result = $client->update($update);
                unset($docList);
            }
        }
    }

    public static function delete(eZContentObject $object, $commit = true)
    {
        $ezpublishevent_ini = eZINI::instance( 'ezpublishevent.ini' );
        if($ezpublishevent_ini->hasVariable( 'Settings', 'EventClassIdentifier' ))
        {
            $classidentifier = $ezpublishevent_ini->variable( 'Settings', 'EventClassIdentifier' );
            if ($object->ClassIdentifier == $classidentifier)
            {
                $client = eZPublishSolarium::createSolariumClient();
                $query = $client->createSelect();
                $query->setQuery('meta_id_si:' . $object->ID);
                $resulttest = $client->select($query);
                if ($resulttest->getNumFound() != 0)
                {
                    $update = $client->createUpdate();
                    $update->addDeleteQuery('meta_id_si:' . $object->ID);
                    $update->addCommit();
                    $deleteResult = $client->update($update);
                }
            }
        }
    }

    public static function commit()
    {
        $client = eZPublishSolarium::createSolariumClient();
        $update = $client->createUpdate();
        $update->addCommit();
        $result = $client->update($update);
    }

    public static function clean($optimize)
    {
        $client = eZPublishSolarium::createSolariumClient();
        $update = $client->createUpdate();
        $update->addDeleteQuery('*:*');
        $update->addCommit();

        // this executes the query and returns the result
        $result = $client->update($update);
    }
}
