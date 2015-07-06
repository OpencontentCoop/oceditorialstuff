<?php

class OCEditorialStuffActionHandler
{

    private static $_instances = array();

    /**
     * @var OCEditorialStuffPostFactoryInterface
     */
    protected $factory;

    /**
     * @var array
     */
    protected $factoryActionConfiguration = array();

    protected $availableActions = array();

    protected function __construct( OCEditorialStuffPostFactoryInterface $factory )
    {
        $editorialIni = eZINI::instance( 'editorialstuff.ini' );
        $availableActions = (array)$editorialIni->variable( 'AvailableActions', 'Actions' );
        foreach( $availableActions as $actionName )
        {
            if ( $editorialIni->hasGroup( $actionName ) )
            {
                $className = $editorialIni->variable( $actionName, 'ClassName' );
                $methodName = $editorialIni->variable( $actionName, 'MethodName' );
                if ( method_exists( $className, $methodName ) )
                {
                    $this->availableActions[$actionName] = array( $className, $methodName );
                }
                else
                {
                    eZDebug::writeError( "{$className}::{$methodName} not callable", __METHOD__ );
                }
            }
        }
        $this->factory = $factory;
        $factoryConfiguration = $this->factory->getConfiguration();
        $statesConfigurations = isset( $factoryConfiguration['States'] ) ? $factoryConfiguration['States'] : array();
        $actionsConfiguration = isset( $factoryConfiguration['Actions'] ) ? $factoryConfiguration['Actions'] : array();
        foreach( $actionsConfiguration as $changeStates => $actionAndSettings )
        {
            $configurationItem = array();
            $changeStatesParts = explode( '-', $changeStates );
            $actionParameters = explode( ';', $actionAndSettings );
            $actionName = array_shift( $actionParameters );
            if ( count( $changeStatesParts ) == 2
                 && isset( $statesConfigurations[$changeStatesParts[0]] )
                 && isset( $statesConfigurations[$changeStatesParts[1]] )
                 && isset( $this->availableActions[$actionName] ) )
            {
                $configurationItem['before_state'] = $changeStatesParts[0];
                $configurationItem['after_state'] = $changeStatesParts[1];
                $configurationItem['call_function'] = $this->availableActions[$actionName];
                $configurationItem['call_function_parameters'] = $actionParameters;
                $this->factoryActionConfiguration[$changeStates] = $configurationItem;
            }
        }
    }

    public static function instance( OCEditorialStuffPostFactoryInterface $factory )
    {
        $factoryClassName = get_class( $factory );
        if ( !isset( self::$_instances[$factoryClassName] ) )
        {
            self::$_instances[$factoryClassName] = new OCEditorialStuffActionHandler(
                $factory
            );
        }
        return self::$_instances[$factoryClassName];
    }

    public function handleChangeState( OCEditorialStuffPost $post, eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
        $changeStateString = $beforeState->attribute( 'identifier' ) . '-' . $afterState->attribute( 'identifier' );
        if ( isset( $this->factoryActionConfiguration[$changeStateString] ) )
        {
            $parameters = array_merge( array( $post ), array( $this->factoryActionConfiguration[$changeStateString]['call_function_parameters'] ) );
            call_user_func_array( $this->factoryActionConfiguration[$changeStateString]['call_function'], $parameters );
        }
    }

    // out of the box actions
    public static function addLocation( OCEditorialStuffPost $post,
                                        $addLocationIds )
    {
        $object = $post->getObject();
        if ( $object instanceof eZContentObject )
        {
            eZContentOperationCollection::addAssignment(
                $object->attribute( 'main_node_id' ),
                $object->attribute( 'id' ),
                $addLocationIds
            );
        }
        else
        {
            eZDebug::writeError( "Object not found", __METHOD__ );
        }
    }

    public static function removeLocation( OCEditorialStuffPost $post,
                                           $removeLocationIds )
    {
        /** @var eZContentObjectTreeNode[] $nodes */
        $nodes = $post->getObject()->attribute( 'assigned_nodes' );
        $removeNodeIdList = array();
        if ( count( $nodes ) > 1 )
        {
            foreach ( $nodes as $node )
            {
                foreach( $removeLocationIds as $removeLocationId )
                {
                    if ( $node->attribute( 'parent_node_id' ) == $removeLocationId )
                    {
                        $removeNodeIdList[] = $node->attribute( 'node_id' );
                    }
                }
            }
        }
        if ( !empty( $removeNodeIdList ) )
        {
            eZContentOperationCollection::removeNodes( $removeNodeIdList );
        }
    }
}