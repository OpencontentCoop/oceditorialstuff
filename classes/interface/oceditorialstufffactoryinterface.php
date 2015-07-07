<?php

interface OCEditorialStuffPostFactoryInterface
{
    /**
     * @param array $configuration
     */
    public function __construct( $configuration );

    /**
     * @return string
     */
    public function identifier();

    /**
     * @return array
     */
    public function getRuntimeParameters();

    /**
     * @return int
     */
    public function creationRepositoryNode();

    /**
     * @return int[] of node ids
     */
    public function repositoryRootNodes();

    /**
     * @return string this dashboard class identifier
     */
    public function classIdentifier();

    /**
     * @return array maps OCEditorialStuffPost::$attributeMapKeys to attribute identifiers
     */
    public function attributeIdentifiers();

    /**
     * @return string
     */
    public function stateGroupIdentifier();

    /**
     * @return string[]
     */
    public function stateIdentifiers();

    /**
     * @return array[] of extra fiedls @see OCEditorialStuffPostFactory::fields
     */
    public function fields();

    /**
     * @return eZContentObjectState[] with key group.identifier for each state
     * @throws Exception
     */
    public function states();

    /**
     * @param array $result AsObjects=>false ezfind result item
     *
     * @return OCEditorialStuffPostInterface
     */
    public function instanceFromEzfindResultArray( array $result );

    /**
     * @param array $data
     *
     * @return OCEditorialStuffPostInterface
     */
    public function instancePost( $data );

    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @param $key
     * @param $value
     *
     * @return OCEditorialStuffPostFactoryInterface
     */
    public function setConfiguration( $key, $value );

    public function getTemplateDirectory();

    public function dashboardModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module );

    public function editModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module );
}