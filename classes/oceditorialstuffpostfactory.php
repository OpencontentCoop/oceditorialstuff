<?php

abstract class OCEditorialStuffPostFactory implements OCEditorialStuffPostFactoryInterface
{
    /**
     * @var array
     */
    protected $configuration;

    private static $states;

    /**
     * @param string $factoryIdentifier
     *
     * @return OCEditorialStuffPostFactory
     * @throws Exception
     */
    public static function instance( $factoryIdentifier )
    {
        $ini = eZINI::instance( 'editorialstuff.ini' );
        $availableFactories = $ini->variable( 'AvailableFactories', 'Identifiers' );
        if ( in_array( $factoryIdentifier, $availableFactories ) && $ini->hasGroup( $factoryIdentifier ) )
        {
            $factoryConfiguration = $ini->group( $factoryIdentifier );
            $className = isset( $factoryConfiguration['ClassName'] ) ? $factoryConfiguration['ClassName'] : 'OCEditorialStuffPostDefaultFactory';
            if ( class_exists( $className ) )
            {
                return new $className( $factoryConfiguration );
            }
        }
        throw new Exception( "Factory '$factoryIdentifier'' not properly configured" );
    }

    protected function __construct( $configuration )
    {
        $this->configuration = $configuration;
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
        return $this->configuration['AttributeIdentifiers'];
    }

    /**
     * @return string
     */
    public function stateGroupIdentifier()
    {
        return $this->configuration['StateGroup'];
    }

    /**
     * @return array
     */
    public function stateIdentifiers()
    {
        return $this->configuration['States'];
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

    abstract public function onChangeState( OCEditorialStuffPost $post, eZContentObjectState $beforeState, eZContentObjectState $afterState );

    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return eZContentObjectState[]
     * @throws Exception
     */
    final public function states()
    {
        if ( self::$states === null )
        {
            $groupIdentifier = $this->stateGroupIdentifier();
            $stateIdentifiers = $this->stateIdentifiers();
            $states = array();
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

            self::$states = $states;
        }
        return self::$states;
    }

    /**
     * @param array $result
     *
     * @return OCEditorialStuffPost
     */
    final public function instanceFromEzfindResultArray( array $result )
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
        return new OCEditorialStuffPost( $data, $this );
    }

}