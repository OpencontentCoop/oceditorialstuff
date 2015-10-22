<?php

class OCEditorialStuffPost
{
    
    const STATE_PUBLISHED = 'published';
    const STATE_SENT = 'sent';

    /**
     * @var eZContentObject
     */
    protected $object;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $dataMap;
    
    /**
     * @var eZContentObjectTreeNode
     */
    protected $mainNode;
    
    /**
     * @var array
     */
    protected $data;

    /**
     * @var OCEditorialStuffPostFactoryInterface
     */
    protected $factory;

    /**
     * @var OCEditorialStuffActionHandler
     */
    protected $actionHandler;

    protected $attributeMapKeys = array(
        'images',
        'videos',
        'audios',
        'tags'
    );

    public function __construct( array $data = array(), OCEditorialStuffPostFactoryInterface $factory )
    {
        $this->data = $data;
        if ( isset( $data['object_id'] ) )
        {
            $this->object = eZContentObject::fetch( $data['object_id'] );
        }
        if ( !$this->object instanceof eZContentObject )
        {
            throw new Exception( 'Object not found' );
        }
        $this->mainNode = $this->object->attribute( 'main_node' );
        if ( !$this->mainNode instanceof eZContentObjectTreeNode )
        {
            throw new Exception( 'Node not found' );
        }
        $this->dataMap = $this->object->attribute( 'data_map' );
        $this->factory = $factory;
        $this->actionHandler = OCEditorialStuffActionHandler::instance( $this->factory );
    }

    public function id()
    {
        return $this->object->attribute( 'id' );
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function setState( $stateIdentifier )
    {
        $states = $this->states();
        $beforeStateId = $this->currentState() instanceof eZContentObjectState ? $this->currentState()->attribute( 'id' ) : '?';
        $beforeStateName = $this->currentState() instanceof eZContentObjectState ? $this->currentState()->attribute( 'current_translation' )->attribute( 'name' ) : '?';
        if ( isset( $states[$stateIdentifier] ) )
        {
            if ( eZOperationHandler::operationIsAvailable( 'content_updateobjectstate' ) )
            {
                eZOperationHandler::execute(
                    'content',
                    'updateobjectstate',
                    array(
                        'object_id' => $this->object->attribute( 'id' ),
                        'state_id_list' => array( $states[$stateIdentifier]->attribute( 'id' ) )
                    )
                );
            }
            else
            {
                eZContentOperationCollection::updateObjectState(
                    $this->object->attribute( 'id' ),
                    array( $states[$stateIdentifier]->attribute( 'id' ) )
                );
            }
        }
        $afterStateId = $this->currentState() instanceof eZContentObjectState ? $this->currentState()->attribute( 'id' ) : '?';
        $afterStateName = $this->currentState() instanceof eZContentObjectState ? $this->currentState()->attribute( 'current_translation' )->attribute( 'name' ) : '?';

        if ( $afterStateId != $beforeStateId )
        {
            OCEditorialStuffHistory::addHistoryToObjectId(
                $this->id(),
                'updateobjectstate',
                array(
                    'before_state_id' => $beforeStateId,
                    'before_state_name' => $beforeStateName,
                    'after_state_id' => $afterStateId,
                    'after_state_name' => $afterStateName
                )
            );

            $this->onChangeState( $beforeStateId, $afterStateId );
        }
    }
    
    public function onChangeState( $beforeStateId, $afterStateId )
    {
        $beforeState = $afterState = false;
        foreach ( $this->states() as $state )
        {
            if ( $state->attribute( 'id' ) == $beforeStateId )
            {
                $beforeState = $state;
            }
            if ( $state->attribute( 'id' ) == $afterStateId )
            {
                $afterState = $state;
            }
        }
        if ( $beforeState && $afterState )
        {
            if ( $this->factory->onChangeState( $this, $beforeState, $afterState ) )
            {
                $this->actionHandler->handleChangeState( $this, $beforeState, $afterState );
            }
        }
        else
        {
            eZDebug::writeError( "State id $beforeStateId and $afterStateId not found" );
        }
    }

    /**
     * @return eZContentObjectState[]
     */
    protected function states()
    {
        return $this->factory->states();
    }

    protected function attributeIdentifier( $identifier )
    {
        $attributeIdentifiers = $this->factory->attributeIdentifiers();
        if ( isset( $attributeIdentifiers[$identifier] ) )
            return $attributeIdentifiers[$identifier];
        return false;
    }
    
    public function addImage( eZContentObject $object )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'images' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'images' )]->toString() );
            $ids[] = $object->attribute( 'id' );
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'addimage', array( 'object_id' => $object->attribute( 'id' ), 'name' => $object->attribute( 'name' ) ) );
        }
    }
        
    public function makeDefaultImage( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'images' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'images' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_merge( array( $objectId ), array_unique( $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'defaultimage', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }
    
    public function removeImage( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'images' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'images' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'removeimage', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }
    
    public function addVideo( eZContentObject $object )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'videos' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'videos' )]->toString() );
            $ids[] = $object->attribute( 'id' );
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'addvideo', array( 'object_id' => $object->attribute( 'id' ), 'name' => $object->attribute( 'name' ) ) );
        }
    }
    
    public function removeVideo( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'videos' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'videos' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'removevideo', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }
    
    public function addAudio( eZContentObject $object )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'audios' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'audios' )]->toString() );
            $ids[] = $object->attribute( 'id' );
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'audios' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'audios' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'addaudio', array( 'object_id' => $object->attribute( 'id' ), 'name' => $object->attribute( 'name' ) ) );
        }
    }
    
    public function removeAudio( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'audios' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'audios' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'audios')]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'audios' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'removeaudio', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }

    /**
     * @return eZContentObjectState|null
     */
    public function currentState()
    {
        foreach ( $this->states() as $state )
        {
            /** @var eZContentObject $object */
            $object = $this->attribute( 'object' );
            if ( in_array( $state->attribute( 'id' ), $object->attribute( 'state_id_array' ) ) )
            {
                return $state;
            }
        }
        return null;
    }


    final public function attribute( $property )
    {
        switch( $property )
        {
            case 'object':
                return $this->object;
        
            case 'node':
                return $this->mainNode;
        
            case 'states':
                return $this->states();
            
            case 'current_state':
                return $this->currentState();
            
            case 'history':
                return OCEditorialStuffHistory::getHistoryByObjectId( $this->object->attribute( 'id' ) );
            
            case 'notification_history':
                return OCEditorialStuffHistory::getNotificationHistoryByObjectId( $this->object->attribute( 'id' ) );
            
            case 'social_history':
                return OCEditorialStuffHistory::getSocialHistoryByObjectId( $this->object->attribute( 'id' ) );

            case 'hashtags':
                //@todo
                return false;
            
            case 'editorial_url':
                return 'editorialstuff/edit/' . $this->getFactory()->identifier() . '/' . $this->id();

            default:
                if ( in_array( $property, $this->attributeMapKeys )
                     && isset( $this->dataMap[$this->attributeIdentifier( $property )] )
                     && $this->dataMap[$this->attributeIdentifier( $property )] instanceof eZContentObjectAttribute )
                {
                    if ( $this->dataMap[$this->attributeIdentifier( $property )]->hasContent() )
                    {
                        return $this->dataMap[$this->attributeIdentifier( $property )];
                    }
                    else
                    {
                        eZDebug::writeError( "Object attribute '{$this->attributeIdentifier( $property )}' is empty" );
                    }
                }
                if ( array_key_exists( $property, $this->data ) )
                {
                    return $this->data[$property];
                }
                eZDebug::writeError( "Attribute '$property' not found" );
                return false;
        }     
    }

    final public function attributes()
    {
        return array_merge(
            array(
                'object',
                'node',
                'current_state',
                'history',
                'notification_history',
                'social_history',
                'hashtags',
                'editorial_url'
            ),
            array_keys( $this->data ),
            $this->attributeMapKeys
        );
    }

    final public function hasAttribute( $property )
    {
        return in_array( $property, $this->attributes() );
    }
}