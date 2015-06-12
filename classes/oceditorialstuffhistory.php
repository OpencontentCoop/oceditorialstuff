<?php

class OCEditorialStuffHistory extends eZPersistentObject
{    
    
    /**
     * @var eZUser
     */
    protected $user;
    
    /**
     * @var array
     */
    protected $params;

    function OCUfficioStampaHistory( $row = array() )
    {
        $this->PersistentDataDirty = false;
        if ( !empty( $row ) )
            $this->fill( $row );
    }
    
    public static function definition()
    {
        return array( 'fields'       => array( 'handler'             => array( 'name'     => 'handler',
                                                                               'datatype' => 'string',
                                                                               'default'  => null,
                                                                               'required' => true ),
                                               
                                               'object_id'           => array( 'name'     => 'object_id',
                                                                               'datatype' => 'integer',
                                                                               'default'  => null,
                                                                               'required' => true ),
                                               
                                               'user_id'             => array( 'name'     => 'user_id',
                                                                               'datatype' => 'integer',
                                                                               'default'  => null,
                                                                               'required' => true ),
                                               
                                               'created_time'        => array( 'name'     => 'created_time',
                                                                               'datatype' => 'integer',
                                                                               'default'  => time(),
                                                                               'required' => false ),
                                               
                                               'type'                => array( 'name'     => 'type',
                                                                               'datatype' => 'string',
                                                                               'default'  => null,
                                                                               'required' => true ),
                                                                                              

                                               'params_serialized'   => array( 'name'     => 'params_serialized',
                                                                               'datatype' => 'string',
                                                                               'default'  => null,
                                                                               'required' => false ),
                                            ),

                      'keys'                 => array(),
                      'class_name'           => 'OCEditorialStuffHistory',
                      'name'                 => 'oceditorialstuffhistory',
                      'function_attributes'  => array( 'params' => 'getParams',
                                                       'user' => 'getUser' ),
                      'set_functions'        => array( 'params' => 'setParams',
                                                       'user' => 'setUser' )
        );
    }

    public function __get( $name )
    {
        $ret = null;
        if( $this->hasAttribute( $name ) )
            $ret = $this->attribute( $name );
            
        return $ret;
    }
    
    public function getParams()
    {
        $ini = eZINI::instance();
        if ( $ini->variable( 'DatabaseSettings', 'DatabaseImplementation' ) == 'ezpostgresql' )
        {
            if ( $this->attribute( 'params_serialized' ) )
            {
                $serialized = str_replace( "~~NULL_BYTE~~", "\0", $this->attribute( 'params_serialized' ) );
                $this->params = unserialize( $serialized ); 
            }
        }
        else
        {
            if ( !is_array( $this->params ) && $this->attribute( 'params_serialized' ) )
                $this->params = unserialize( $this->attribute( 'params_serialized' ) );
        }    
        return $this->params;
    }
    
    public function setParams( array $params )
    {
        $this->params = $params;
        $ini = eZINI::instance();
        if ( $ini->variable( 'DatabaseSettings', 'DatabaseImplementation' ) == 'ezpostgresql' )
        {
            $serialized = serialize( $params );
            $safeSerialized = str_replace( "\0", "~~NULL_BYTE~~", $serialized );
            $this->setAttribute( 'params_serialized', $safeSerialized );
        }
        else
        {
            $this->setAttribute( 'params_serialized', serialize( $params ) );
        }
    }

    public function getUser()
    {
        if ( !$this->user instanceof eZUser )
            $this->user = eZUser::fetch( $this->attribute( 'user_id' ) );
        
        return $this->user;
    }
    
    public function setUser( eZUser $user )
    {
        $this->user = $user;
        $this->setAttribute( 'user_id', $user->attribute( 'contentobject_id' ) );
    }

    /**
     * @param $objectID
     * @param $handler
     * @param int $offset
     * @param int $limit
     *
     * @return OCEditorialStuffHistory[]
     */
    public static function fetchByHandler( $objectID, $handler, $offset = 0, $limit = 0 )
    {
        return self::fetchList( $offset, $limit, array( 'handler' => $handler, 'object_id' => $objectID ) );
    }
    
    public static function fetchList( $offset = 0, $limit = 0, $conds = null )
    {
        if( !$limit )
            $aLimit = null;
        else
            $aLimit = array( 'offset' => $offset, 'length' => $limit );
        
        $sort = array( 'created_time' => 'asc' );
        $aImports = self::fetchObjectList( self::definition(), null, $conds, $sort, $aLimit );
        
        return $aImports;
    }


    /**
     * @param string $action
     * @param array $parameters
     */
    public static function addHistoryToObjectId( $objectId, $action, $parameters )
    {
        $item = new OCEditorialStuffHistory( array() );
        $item->setAttribute( 'params', $parameters );
        $item->setAttribute( 'user_id', eZUser::currentUserID() );
        $item->setAttribute( 'object_id', $objectId );
        $item->setAttribute( 'type', $action );
        $item->setAttribute( 'handler', 'history' );
        $item->setAttribute( 'created_time', time() );
        $item->store();
    }

    public static function deleteHistoryByObjectId( $objectId )
    {
        eZPersistentObject::removeObject( OCEditorialStuffHistory::definition(), array( 'object_id' => $objectId, 'handler' => 'history' ) );
    }

    /**
     * @return array
     */
    public static function getHistoryByObjectId( $objectID )
    {
        $history = array();
        $items = OCEditorialStuffHistory::fetchByHandler( $objectID, 'history' );
        foreach( $items as $item )
        {
            $time = $item->attribute( 'created_time' );
            if ( !isset( $history[$time] ) )
            {
                $history[$time] = array();
            }
            $history[$time][] = array(
                'action' => $item->attribute( 'type' ),
                'user' => $item->attribute( 'user_id' ),
                'parameters' => $item->attribute( 'params' )
            );
        }
        $versionList = eZPersistentObject::fetchObjectList(
            eZContentObjectVersion::definition(),
            null,
            array(  'contentobject_id' => $objectID ),
            null,
            null,
            false
        );

        foreach( $versionList as $version )
        {
            $time = $version['created'];
            if ( !isset( $history[$time] ) )
            {
                $history[$time] = array();
            }
            $history[$time][] = array(
                'action' => 'createversion',
                'user' => $version['creator_id'],
                'parameters' => array( 'version' => $version['version'] )
            );
        }
        ksort( $history );
        return $history;
    }

    /**
     * @param array $addresses
     * @param string $message
     * @param array $errors
     */
    public static function addNotificationHistoryToObjectId( $objectID, $addresses, $message, $errors )
    {
        $item = new OCEditorialStuffHistory( array() );
        $item->setAttribute( 'params', array( 'recipients' => $addresses, 'message' => $message, 'errors' => $errors ) );
        $item->setAttribute( 'user_id', eZUser::currentUserID() );
        $item->setAttribute( 'object_id', $objectID );
        $item->setAttribute( 'type', 'mail' );
        $item->setAttribute( 'handler', 'notifications' );
        $item->setAttribute( 'created_time', time() );
        $item->store();
    }

    public function deleteNotificationHistoryByObjectId( $objectID )
    {
        eZPersistentObject::removeObject( OCEditorialStuffHistory::definition(), array( 'object_id' => $objectID, 'handler' => 'notifications' ) );
    }

    /**
     * @return OCEditorialStuffHistory[]
     */
    public static function getNotificationHistoryByObjectId( $objectID )
    {
        return OCEditorialStuffHistory::fetchByHandler( $objectID, 'notifications' );
    }

    /**
     * @param string $type
     * @param mixed $response
     */
    public static function addSocialHistoryToObjectId( $objectID, $type, $response )
    {
        $item = new OCEditorialStuffHistory( array() );
        $item->setAttribute( 'params', array( 'response' => $response ) );
        $item->setAttribute( 'user_id', eZUser::currentUserID() );
        $item->setAttribute( 'object_id', $objectID );
        $item->setAttribute( 'type', $type );
        $item->setAttribute( 'handler', 'social_push' );
        $item->setAttribute( 'created_time', time() );
        $item->store();
    }

    public static function deleteSocialHistoryByObjectId( $objectID )
    {
        eZPersistentObject::removeObject( OCEditorialStuffHistory::definition(), array( 'object_id' => $objectID, 'handler' => 'social_push' ) );
    }

    /**
     * @return array
     */
    public static function getSocialHistoryByObjectId( $objectID )
    {
        $data = array();
        $list = OCEditorialStuffHistory::fetchByHandler( $objectID, 'social_push' );
        foreach( $list as $item )
        {
            $params = $item->attribute( 'params' );
            $link = false;
            $type = '';
            if ( $item->attribute( 'type' ) == 'twitter' )
            {
                if ( $params['response']['status'] == 'success' )
                    $link = "http://twitter.com/{$params['response']['response']->user->id}/status/{$params['response']['response']->id}";
                $type = 'Twitter';
            }
            elseif(  $item->attribute( 'type' ) == 'facebook_feed' )
            {
                if ( $params['response']['status'] == 'success' )
                    $link = "https://www.facebook.com/{$params['response']['response']['id']}";
                $type = 'Facebook';
            }
            $itemNormalized = array(
                'created_time' => $item->attribute( 'created_time' ),
                'user' => $item->attribute( 'user' ),
                'type' => $type,
                'params' => $params,
                'link' => $link
            );
            $data[] = $itemNormalized;
        }
        return $data;
    }

}