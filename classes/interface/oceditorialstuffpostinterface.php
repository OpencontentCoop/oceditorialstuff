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

    public function onCreate();

    public function onUpdate();

    public function onRemove();

    public function onChangeState( $beforeStateId, $afterStateId );

    public function currentState();

    public function is( $stateIdentifier );

    public function tabs();

    public function attribute( $property );

    public function attributes();

    public function hasAttribute( $property );

    public function jsonSerialize();

}