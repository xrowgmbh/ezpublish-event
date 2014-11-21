<?php

/**
 * Dual search plugin for eZ publish
 */
class eZDual extends eZSolr
{
    /**
     * Adds object $contentObject to the search database.
     *
     * @param eZContentObject $contentObject Object to add to search engine
     * @param bool $commit Whether to commit after adding the object.
              If set, run optimize() as well every 1000nd time this function is run.
     * @return bool True if the operation succeed.
     */
    function addObject( $contentObject, $commit = true )
    {
        eZPublishEventSearch::update( $contentObject, $commit );
        return parent::addObject( $contentObject, $commit );
    }

    /**
     * Performs a solr COMMIT
     */
    function commit()
    {
        eZPublishEventSearch::commit();
        parent::commit();
    }

    /**
     * Performs a solr OPTIMIZE call
     */
    function optimize( $withCommit = false )
    {
        //possible custom event code
        parent::optimize( $withCommit );
    }

    /**
     * Removes object $contentObject from the search database.
     *
     * @param eZContentObject $contentObject the content object to remove
     * @param bool $commit Whether to commit after removing the object
     * @return bool True if the operation succeed.
     */
    function removeObject( $contentObject, $commit = null )
    {
        eZPublishEventSearch::delete( $contentObject, $commit );
        return parent::removeObject( $contentObject, $commit );
    }

    /**
     * Clean up search index for current installation.
     * @return bool true if cleanup was successful
     * @todo:  handle multicore configs (need a parameter for it) for return values
    **/
    function cleanup( $allInstallations = false, $optimize = false )
    {
       eZPublishEventSearch::clean( $optimize );
       return parent::cleanup( $allInstallations, $optimize );
    }
}