<?php

interface OCEditorialStuffHandlerInterface
{
    /**
     * @return OCEditorialStuffPostFactoryInterface
     */
    public function getFactory();

    /**
     * @param $id
     *
     * @return OCEditorialStuffPostInterface
     */
    public function fetchByObjectId( $id );

    /**
     * @param $id
     *
     * @return OCEditorialStuffPostInterface
     */
    public function fetchByNodeId( $id );

    /**
     * @param $parameters
     * @param $limitation
     *
     * @return OCEditorialStuffPostInterface[]
     */
    public function fetchItems( $parameters, $limitation = null );

    /**
     * @param $parameters
     *
     * @return int
     */
    public function fetchItemsCount( $parameters );

    /**
     * @return string
     */
    public static function timezone();

    public static function getLastFetchData();

}