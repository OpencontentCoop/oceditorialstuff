<?php

abstract class OCEditorialStuffPost implements OCEditorialStuffPostInterface
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

        $this->dataMap = $this->object->attribute( 'data_map' );
        $this->factory = $factory;
        $this->actionHandler = OCEditorialStuffActionHandler::instance( $this->factory );
    }

    public function getMainNode()
    {
        if ( $this->mainNode == null )
        {
            $this->mainNode = $this->object->attribute( 'main_node' );
        }
        return $this->mainNode;
    }

    public function id()
    {
        return (int) $this->object->attribute( 'id' );
    }

    public function getObject()
    {
        return $this->object;
    }

    public function flushObject()
    {
        eZContentObject::clearCache( array( $this->data['object_id'] ) );
        $this->object = eZContentObject::fetch( $this->data['object_id'] );
    }

    public function setObjectLastModified()
    {
        $this->getObject()->setAttribute( 'modified', time() );
        $this->getObject()->store();
        eZSearch::addObject( $this->object, true );
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
        $this->flushObject();
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
                if ( $this->onChangeState( $beforeState, $afterState ) )
                {
                    $this->actionHandler->handleChangeState( $this, $beforeState, $afterState );
                }
            }
            else
            {
                eZDebug::writeError( "State id $beforeStateId and $afterStateId not found" );
            }
        }
    }

    public function onBeforeCreate(){}

    public function onCreate(){}

    public function onUpdate(){}

    public function onBeforeUpdate(){}

    public function onRemove(){}

    public function onBeforeRemove(){}

    abstract public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState );

    /**
     * @return eZContentObjectState[]
     */
    protected function states()
    {
        return $this->factory->states();
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

    public function is( $stateIdentifier )
    {
        $currentState = $this->currentState();
        if ( $currentState instanceof eZContentObjectState )
        {
            return $currentState->attribute( 'identifier' ) == $stateIdentifier;
        }
        return false;
    }

    public function isAfter( $stateIdentifier, $orEqual = false )
    {
        $currentPosition = $this->stateOffset();
        $findPosition = $this->stateOffset( $stateIdentifier );
        if ( $orEqual )
        {
            return $findPosition >= $currentPosition;
        }
        else
        {
            return $findPosition > $currentPosition;
        }
    }

    public function isBefore( $stateIdentifier, $orEqual = false )
    {
        $currentPosition = $this->stateOffset();
        $findPosition = $this->stateOffset( $stateIdentifier );
        if ( $orEqual )
        {
            return $findPosition <= $currentPosition;
        }
        else
        {
            return $findPosition < $currentPosition;
        }
    }

    protected function stateOffset( $stateIdentifier = null )
    {
        if ( $stateIdentifier === null )
        {
            $stateIdentifier = $this->currentState()->attribute( 'identifier' );
        }
        $index = 0;
        foreach ( $this->states() as $state )
        {
            $index++;

            if ( $state->attribute( 'identifier' ) == $stateIdentifier )
            {
                return $index;
            }
        }
        return -1;
    }

    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tabs = array(
            array(
                'identifier' => 'content',
                'name' => 'Contenuto',
                'template_uri' => "design:{$templatePath}/parts/content.tpl"
            )
        );
        $tabs[] = array(
            'identifier' => 'history',
            'name' => 'Cronologia',
            'template_uri' => "design:{$templatePath}/parts/history.tpl"
        );
        return $tabs;
    }


    public function attribute( $property )
    {
        switch( $property )
        {
            case 'object':
                return $this->object;

            case 'node':
                return $this->getMainNode();

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

            case 'tabs':
                return $this->tabs();

            case 'content_attributes':
                return $this->contentAttributes();

            case 'content_attributes_grouped_data_map':
                return eZContentObject::createGroupedDataMap( $this->dataMap );

            case 'editorial_url':
                return 'editorialstuff/edit/' . $this->getFactory()->identifier() . '/' . $this->id();

            case 'factory_identifier':
                return $this->getFactory()->identifier();

            case 'template_directory':
                return $this->getFactory()->getTemplateDirectory();

            default:
                if ( array_key_exists( $property, $this->data ) )
                {
                    return $this->data[$property];
                }
                eZDebug::writeError( "Attribute '$property' not found" );
                return false;
        }
    }

    public function attributes()
    {
        return array_merge(
            array(
                'object',
                'node',
                'states',
                'current_state',
                'history',
                'notification_history',
                'social_history',
                'content_attributes',
                'content_attributes_grouped_data_map',
                'tabs',
                'hashtags',
                'editorial_url',
                'factory_identifier',
                'template_directory'
            ),
            array_keys( $this->data )
        );
    }

    public function contentAttributes()
    {
        $data = array();
        foreach( $this->dataMap as $identifier => $attribute )
        {
            $category = $attribute->attribute( 'contentclass_attribute' )->attribute( 'category' );
            if ( $category == 'content' || empty( $category ) )
            {
                $data[$identifier] = $attribute;
            }
        }
        return $data;
    }

    public function hasAttribute( $property )
    {
        return in_array( $property, $this->attributes() );
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    public function executeAction( $actionIdentifier, $actionParameters, eZModule $module = null )
    {
    }
}
