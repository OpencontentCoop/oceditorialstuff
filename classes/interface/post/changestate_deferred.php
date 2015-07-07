<?php

abstract class OCEditorialStuffPostChangeStateDeferred extends OCEditorialStuffPost
{

    public function onChangeState( eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
        if ( $this->factory instanceof OCEditorialStuffPostChangeStateHandlerFactoryInterface
             && $this->factory->onChangeState( $this, $beforeState, $afterState ) )
        {
            $this->actionHandler->handleChangeState( $this, $beforeState, $afterState );
        }
        return false;
    }
}