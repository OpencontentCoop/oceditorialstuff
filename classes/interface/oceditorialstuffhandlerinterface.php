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
     *
     * @return OCEditorialStuffPostInterface[]
     */
    public function fetchItems( $parameters );

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