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
     * @param OCEditorialStuffPostInterface $post
     * @param eZContentObjectState $beforeState
     * @param eZContentObjectState $afterState
     *
     * @return bool
     */
    public function onChangeState( OCEditorialStuffPostInterface $post, eZContentObjectState $beforeState, eZContentObjectState $afterState );

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

    public function getTemplateDirectory();

    public function dashboardModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module );

    public function editModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module );
}