<?php

interface OCEditorialStuffPostInterface
{
    /**
     * @return int
     */
    public function id();

    /**
     * @return eZContentObject
     */
    public function getObject();

    /**
     * @return OCEditorialStuffPostFactoryInterface
     */
    public function getFactory();

    public function setState( $stateIdentifier );

    public function onChangeState( $beforeStateId, $afterStateId );

    public function addImage( eZContentObject $object );

    public function makeDefaultImage( $objectId );

    public function removeImage( $objectId );

    public function addVideo( eZContentObject $object );

    public function removeVideo( $objectId );

    public function addAudio( eZContentObject $object );

    public function removeAudio( $objectId );

    public function currentState();

    public function attribute( $property );

    public function attributes();

    public function hasAttribute( $property );
}