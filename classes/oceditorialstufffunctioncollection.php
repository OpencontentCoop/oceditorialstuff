<?php

class OCEditorialStuffFunctionCollection
{
    public static function fetchPosts( $factoryIdentifier, $factoryParameters, $interval, $state, $query, $tag, $limit, $offset, $sort )
    {
        try
        {
            $result = OCEditorialStuffHandler::instance( $factoryIdentifier, $factoryParameters )->fetchItems(
                array(
                    'interval' => $interval,
                    'state' => $state,
                    'query' => $query,
                    'tag' => $tag,
                    'limit' => $limit,
                    'offset' => $offset,
                    'sort' => $sort
                )
            );
            return array( 'result' => $result );
        }
        catch( Exception $e )
        {
            return array( 'error' => $e->getMessage() );
        }
    }

    public static function fetchPostCount( $factoryIdentifier, $factoryParameters, $interval, $state, $query, $tag, $limit, $offset )
    {
        try
        {
            $result = OCEditorialStuffHandler::instance( $factoryIdentifier, $factoryParameters )->fetchItemsCount(
                array(
                    'interval' => $interval,
                    'state' => $state,
                    'query' => $query,
                    'tag' => $tag
                )
            );
            return array( 'result' => $result );
        }
        catch( Exception $e )
        {
            return array( 'error' => $e->getMessage() );
        }
    }

    public static function fetchPostStates( $factoryIdentifier, $factoryParameters )
    {
        try
        {
            $result = OCEditorialStuffHandler::instance( $factoryIdentifier, $factoryParameters )->getFactory()->states();
            return array( 'result' => $result );
        }
        catch( Exception $e )
        {
            return array( 'error' => $e->getMessage() );
        }
    }

    public static function fetchNotificationRules( $type, $userId, $postId )
    {
        return array( 'result' => OCEditorialStuffNotificationRule::fetchList( $type, $userId, $postId ) );
    }

    public static function fetchNotificationRulesPostIds( $type, $userId, $postId )
    {
        $items = OCEditorialStuffNotificationRule::fetchList( $type, $userId, $postId );
        $data = array();
        foreach( $items as $item )
        {
            $data[] = $item->attribute( 'post_id' );
        }
        return array( 'result' => $data );
    }
}