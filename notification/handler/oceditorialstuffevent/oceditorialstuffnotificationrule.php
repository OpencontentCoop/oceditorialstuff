<?php

class OCEditorialStuffNotificationRule extends eZPersistentObject
{
    /*!
     Constructor
    */
    function OCEditorialStuffNotificationRule( $row )
    {
        $this->eZPersistentObject( $row );
    }

    static function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
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

    static function fetchList( $type, $userID, $asObject = true, $offset = false, $limit = false )
    {
        return eZPersistentObject::fetchObjectList(
            OCEditorialStuffNotificationRule::definition(),
            null,
            array(
                'user_id' => $userID,
                'type' => $type
            ),
            null,
            array(
                'offset' => $offset,
                'length' => $limit ),
            $asObject
        );
    }

    static function fetchListCount( $type, $userID )
    {
        $countRes = eZPersistentObject::fetchObjectList(
            OCEditorialStuffNotificationRule::definition(),
            array(),
            array(
                'user_id' => $userID,
                'type' => $type
            ),
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

