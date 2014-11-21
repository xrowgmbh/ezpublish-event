<?php

class eZPublishEventSearch
{

    public static function update(eZContentObject $object, $commit = true)
    {
        if ($object->ClassIdentifier == "event") {
            $client = eZPublishSolarium::createSolariumClient();
            $query = $client->createSelect();
            
            $query->setQuery('meta_id_si:' . $object->ID);
            $resulttest = $client->select($query);
            
            if ($resulttest->getNumFound() != 0) {
                $update = $client->createUpdate();
                $update->addDeleteQuery('meta_id_si:' . $object->ID);
                $deleteResult = $client->update($update);
            }
            
            $ezpEvent = new eZPublishEvent();
            $update = $client->createUpdate();
            $docList = $ezpEvent->createSOLRDocument($object, $update);
            $update->addDocuments($docList);
            $update->addCommit();
            $result = $client->update($update);
            unset($docList);
        }
    }

    public static function delete(eZContentObject $object, $commit = true)
    {
        if ($object->ClassIdentifier == "event") {
            $client = eZPublishSolarium::createSolariumClient();
            $query = $client->createSelect();
            
            $query->setQuery('meta_id_si:' . $object->ID);
            $resulttest = $client->select($query);
            
            if ($resulttest->getNumFound() != 0) {
                $update = $client->createUpdate();
                $update->addDeleteQuery('meta_id_si:' . $object->ID);
                $update->addCommit();
                $deleteResult = $client->update($update);
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