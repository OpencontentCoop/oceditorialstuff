<?php

abstract class OCEditorialStuffPostFactory implements OCEditorialStuffPostFactoryInterface
{
    /**
     * @var array
     */
    protected $configuration;

    private static $states = array();

    /**
     * @param string $factoryIdentifier
     * @param array $factoryParameters
     *
     * @return OCEditorialStuffPostFactory
     * @throws Exception
     */
    final public static function instance( $factoryIdentifier, $factoryParameters = array() )
    {
        $ini = eZINI::instance( 'editorialstuff.ini' );
        $availableFactories = $ini->variable( 'AvailableFactories', 'Identifiers' );
        if ( in_array( $factoryIdentifier, $availableFactories ) && $ini->hasGroup( $factoryIdentifier ) )
        {
            $factoryConfiguration = $ini->group( $factoryIdentifier );
            $factoryConfiguration['identifier'] = $factoryIdentifier;
            $factoryConfiguration['RuntimeParameters'] = $factoryParameters;
            $className = isset( $factoryConfiguration['ClassName'] ) ? $factoryConfiguration['ClassName'] : 'OCEditorialStuffPostDefaultFactory';
            if ( class_exists( $className ) )
            {
                return new $className( $factoryConfiguration );
            }
            else
                throw new Exception( "Factory class '$className' not found" );
        }
        throw new Exception( "Factory '$factoryIdentifier'' not properly configured" );
    }

    public function __construct( $configuration )
    {
        $this->configuration = $configuration;
    }

    public function identifier()
    {
        return $this->configuration['identifier'];
    }

    public function getRuntimeParameters()
    {
        return $this->configuration['RuntimeParameters'];
    }

    public function creationRepositoryNode()
    {
        return $this->configuration['CreationRepositoryNode'];
    }

    /**
     * @return int[]
     */
    public function repositoryRootNodes()
    {
        return $this->configuration['RepositoryNodes'];
    }

    /**
     * @return string
     */
    public function classIdentifier()
    {
        return $this->configuration['ClassIdentifier'];
    }

    /**
     * @return array
     */
    public function attributeIdentifiers()
    {
        return isset( $this->configuration['AttributeIdentifiers'] ) ? $this->configuration['AttributeIdentifiers'] : array();
    }

    /**
     * @return string
     */
    public function stateGroupIdentifier()
    {
        return isset( $this->configuration['StateGroup'] ) ? $this->configuration['StateGroup'] : null;
    }

    /**
     * @return array
     */
    public function stateIdentifiers()
    {
        return isset( $this->configuration['States'] ) ? $this->configuration['States'] : array();
    }

    /**
     * @return array[]
     */
    public function fields()
    {
        $fields = array(
            array(
                'solr_identifier' => 'meta_object_states_si',
                'object_property' => 'states',
                'attribute_identifier' => null,
                'index_extra' => false
            )
        );
        if ( isset( $this->configuration['AttributeIdentifiers']['tags'] ) )
        {
            $tagsIdentifier = $this->configuration['AttributeIdentifiers']['tags'];
            $fields[] = array(
                'solr_identifier' => "attr_{$tagsIdentifier}_lk",
                'object_property' => 'tags',
                'attribute_identifier' => $tagsIdentifier,
                'index_extra' => false
            );
        }
        return $fields;
    }

    abstract public function onChangeState(
        OCEditorialStuffPostInterface $post,
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    );

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setConfiguration( $key, $value )
    {
        $this->configuration[$key] = $value;
        return $this;
    }

    /**
     * @return eZContentObjectState[]
     * @throws Exception
     */
    final public function states()
    {
        if ( !isset( self::$states[get_called_class()] ) )
        {
            $groupIdentifier = $this->stateGroupIdentifier();
            $stateIdentifiers = $this->stateIdentifiers();
            $states = array();
            if ( $groupIdentifier && $stateIdentifiers )
            {
                $transStates = array();
                foreach ( $stateIdentifiers as $key => $state )
                {
                    if ( is_string( $key ) )
                    {
                        $transStates[$key] = $state;
                    }
                    else
                    {
                        $transStates[$state] = str_replace( '_', ' ', ucfirst( $state ) );
                    }
                }

                $group = array(
                    'identifier' => $groupIdentifier,
                    'name' => str_replace( '_', ' ', ucfirst( $groupIdentifier ) ),
                    'states' => $transStates
                );

                $stateGroup = eZContentObjectStateGroup::fetchByIdentifier( $group['identifier'] );
                if ( !$stateGroup instanceof eZContentObjectStateGroup )
                {
                    $stateGroup = new eZContentObjectStateGroup();
                    $stateGroup->setAttribute( 'identifier', $group['identifier'] );
                    $stateGroup->setAttribute( 'default_language_id', 2 );

                    /** @var eZContentObjectStateLanguage[] $translations */
                    $translations = $stateGroup->allTranslations();
                    foreach ( $translations as $translation )
                    {
                        $translation->setAttribute( 'name', $group['name'] );
                        $translation->setAttribute( 'description', $group['name'] );
                    }

                    $messages = array();
                    $isValid = $stateGroup->isValid( $messages );
                    if ( !$isValid )
                    {
                        throw new Exception( implode( ',', $messages ) );
                    }
                    $stateGroup->store();
                }

                foreach ( $group['states'] as $StateIdentifier => $StateName )
                {
                    $stateObject = $stateGroup->stateByIdentifier( $StateIdentifier );
                    if ( !$stateObject instanceof eZContentObjectState )
                    {
                        $stateObject = $stateGroup->newState( $StateIdentifier );
                        $stateObject->setAttribute( 'default_language_id', 2 );
                        /** @var eZContentObjectStateLanguage[] $stateTranslations */
                        $stateTranslations = $stateObject->allTranslations();
                        foreach ( $stateTranslations as $translation )
                        {
                            $translation->setAttribute( 'name', $StateName );
                            $translation->setAttribute( 'description', $StateName );
                        }
                        $messages = array();
                        $isValid = $stateObject->isValid( $messages );
                        if ( !$isValid )
                        {
                            throw new Exception( implode( ',', $messages ) );
                        }
                        $stateObject->store();
                    }
                    $id = $group['identifier'] . '.' . $StateIdentifier;
                    $states[$id] = $stateObject;
                }
            }

            self::$states[get_called_class()] = $states;
        }
        return self::$states[get_called_class()];
    }

    /**
     * @param array $result
     *
     * @return OCEditorialStuffPostInterface
     */
    public function instanceFromEzfindResultArray( array $result )
    {
        $data = array();
        if ( isset( $result['id_si'] ) )
        {
            $data['object_id'] = $result['id_si'];
        }
        else
        {
            $data['object_id'] = $result['id'];
        }
        foreach( $this->fields() as $field )
        {
            $data[$field['object_property']] = isset( $result['fields'][$field['solr_identifier']] ) ? $result['fields'][$field['solr_identifier']] : null;
        }
        return $this->instancePost( $data );
    }

    public function instancePost( $data )
    {
        return new OCEditorialStuffPost( $data, $this );
    }

    public function dashboardModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        $tpl = $this->dashboardModuleResultTemplate( $parameters, $handler, $module );
        $Result = array();
        $contentInfoArray = array(
            'node_id' => null,
            'class_identifier' => null
        );
        $contentInfoArray['persistent_variable'] = array(
            'show_path' => true,
            'site_title' => 'Dashboard Ufficio Stampa'
        );
        if ( $tpl->variable( 'persistent_variable' ) !== false )
        {
            $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['content'] = $tpl->fetch( "design:{$this->getTemplateDirectory()}/dashboard.tpl" );
        $Result['path'] = array( array( 'url' => false, 'text' => isset( $this->configuration['Name'] ) ? $this->configuration['Name'] : 'Dashboard' ) );
        return $Result;
    }

    protected function dashboardModuleResultTemplate( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'factory_identifier', $this->configuration['identifier'] );
        $tpl->setVariable( 'factory_configuration', $this->getConfiguration() );
        $tpl->setVariable( 'template_directory', $this->getTemplateDirectory() );
        $tpl->setVariable( 'view_parameters', $parameters );
        $tpl->setVariable( 'post_count', $handler->fetchItemsCount( $parameters ) );
        $tpl->setVariable( 'posts', $handler->fetchItems( $parameters ) );
        $tpl->setVariable( 'states', $this->states() );
        return $tpl;
    }

    protected function editModuleResultTemplate( $currentPost, $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'factory_identifier', $this->configuration['identifier'] );
        $tpl->setVariable( 'factory_configuration', $this->getConfiguration() );
        $tpl->setVariable( 'template_directory', $this->getTemplateDirectory() );
        $tpl->setVariable( 'post', $currentPost );
        return $tpl;
    }

    public function getEditCurrentPost( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        $object = eZContentObject::fetch( $parameters );
        if ( !$object instanceof eZContentObject )
        {
            return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        }
        if ( !$object->attribute( 'can_read' ) )
        {
            return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
        }
        try
        {
            return $handler->fetchByObjectId( $object->attribute( 'id' ) );
        }
        catch( Exception $e )
        {
            return null;
        }
    }

    public function editModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module )
    {
        $currentPost = $this->getEditCurrentPost( $parameters, $handler, $module );
        $tpl = $this->editModuleResultTemplate( $currentPost, $parameters, $handler, $module );

        $Result = array();
        $contentInfoArray = array( 'url_alias' => 'editorialstuff/dashboard' );
        $contentInfoArray['persistent_variable'] = array( 'show_path' => true, 'site_title' => 'Dashboard' );
        if ( $tpl->variable( 'persistent_variable' ) !== false )
        {
            $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        }
        $tpl->setVariable( 'persistent_variable', false );
        $Result['content_info'] = $contentInfoArray;
        $Result['content'] = $tpl->fetch( "design:{$this->getTemplateDirectory()}/edit.tpl" );
        $Result['path'] = array(
            array( 'url' => 'editorialstuff/dashboard/' . $this->configuration['identifier'],
                   'text' => isset( $this->configuration['Name'] ) ? $this->configuration['Name'] : 'Dashboard'
            )
        );
        if ( $currentPost instanceof OCEditorialStuffPostInterface )
        {
            $Result['path'][] = array(
                'url' => false,
                'text' => $currentPost->getObject()->attribute( 'name' )
            );
        }
        return $Result;
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/default';
    }

}