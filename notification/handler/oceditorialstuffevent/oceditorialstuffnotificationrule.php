<?php

class OCEditorialStuffNotificationRule extends eZPersistentObject
{

    static function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => null,
                                                        'required' => true ),
                                         'type' => array( 'name' => 'type',
                                                          'datatype' => 'string',
                                                          'default'  => null,
                                                          'required' => true ),
                                         "user_id" => array( 'name' => "UserID",
                                                             'datatype' => 'integer',
                                                             'default' => '',
                                                             'required' => true,
                                                             'foreign_class' => 'eZUser',
                                                             'foreign_attribute' => 'contentobject_id',
                                                             'multiplicity' => '1..*' ),
                                         "use_digest" => array( 'name' => "UseDigest",
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         "post_id" => array( 'name' => "PostID",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true,
                                                             'foreign_class' => 'eZContentObject',
                                                             'foreign_attribute' => 'id',
                                                             'multiplicity' => '1..*' ) ),
                      "keys" => array( "id" ),
                      "function_attributes" => array( 'post' => 'post' ),
                      "increment_key" => "id",
                      "sort" => array( "id" => "asc" ),
                      "class_name" => "OCEditorialStuffNotificationRule",
                      "name" => "oceditorialstuffnotificationrule" );
    }


    static function create( $type, $postID, $userID, $useDigest = 0 )
    {
        $rule = new OCEditorialStuffNotificationRule( array(
            'type' => $type,
            'user_id' => $userID,
            'use_digest' => $useDigest,
            'post_id' => $postID ) );
        return $rule;
    }

    static function fetchPostsForUserID( $type, $userID )
    {
        $postIDList = eZPersistentObject::fetchObjectList(
            OCEditorialStuffNotificationRule::definition(),
            array( 'post_id' ),
            array(
                'user_id' => $userID,
                'type' => $type
            ),
            null,
            null,
            false
        );
        $ids = array();
        foreach ( $postIDList as $row )
        {
            $ids[] = $row['post_id'];
        }
        return $ids;
    }

    /**
     * @param null $type
     * @param null $userID
     * @param null $postID
     * @param bool $asObject
     * @param bool $offset
     * @param bool $limit
     *
     * @return OCEditorialStuffNotificationRule[]|array
     */
    static function fetchList( $type = null, $userID = null, $postID = null, $asObject = true, $offset = false, $limit = false )
    {
        $conds = array();

        if ( $type )
            $conds['type'] = $type;

        if ( $userID )
            $conds['user_id'] = $userID;

        if ( $postID )
            $conds['post_id'] = $postID;

        return eZPersistentObject::fetchObjectList(
            OCEditorialStuffNotificationRule::definition(),
            null,
            $conds,
            null,
            array(
                'offset' => $offset,
                'length' => $limit ),
            $asObject
        );
    }

    static function fetchListCount( $type = null, $userID = null, $postID = null )
    {
        $conds = array();

        if ( $type )
            $conds['type'] = $type;

        if ( $userID )
            $conds['user_id'] = $userID;

        if ( $postID )
            $conds['post_id'] = $postID;

        $countRes = eZPersistentObject::fetchObjectList(
            OCEditorialStuffNotificationRule::definition(),
            array(),
            $conds,
            false,
            null,
            false,
            false,
            array(
                array(
                    'operation' => 'count( id )',
                    'name' => 'count'
                )
            )
        );
        return $countRes[0]['count'];
    }

    /** @todo ricavare id utenti validi */
    static function fetchUserIdList( $type, $postID, $offset = false, $limit = false )
    {
        $rows = eZPersistentObject::fetchObjectList(
            OCEditorialStuffNotificationRule::definition(),
            array( 'user_id' ),
            array(
                'post_id' => $postID,
                'type' => $type
            ),
            null,
            array(
                'offset' => $offset,
                'length' => $limit ),
            false
        );
        $ids = array();
        foreach ( $rows as $row )
        {
            $ids[] = $row['user_id'];
        }
        return $ids;

    }

    static function removeByPostAndUserID( $userID, $postID )
    {
        eZPersistentObject::removeObject(
            OCEditorialStuffNotificationRule::definition(),
            array(
                'user_id' => $userID,
                'post_id' => $postID
            )
        );
    }

    static function removeByUserID( $userID )
    {
        eZPersistentObject::removeObject(
            OCEditorialStuffNotificationRule::definition(),
            array( 'user_id' => $userID )
        );
    }

    static function removeByPostID( $postID )
    {
        eZPersistentObject::removeObject(
            OCEditorialStuffNotificationRule::definition(),
            array( 'post_id' => $postID )
        );
    }


    static function cleanup()
    {
        $db = eZDB::instance();
        $db->query( "DELETE FROM oceditorialstuffnotificationrule" );
    }

}

