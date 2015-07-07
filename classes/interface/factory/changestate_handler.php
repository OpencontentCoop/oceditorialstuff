<?php

interface OCEditorialStuffPostChangeStateHandlerFactoryInterface extends OCEditorialStuffPostFactoryInterface
{
    /**
     * @param OCEditorialStuffPostInterface $post
     * @param eZContentObjectState $beforeState
     * @param eZContentObjectState $afterState
     *
     * @return bool
     */
    public function onChangeState( OCEditorialStuffPostInterface $post, eZContentObjectState $beforeState, eZContentObjectState $afterState );
}