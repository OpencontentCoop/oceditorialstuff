<?php


class OCEditorialStuffPostDefaultFactory extends OCEditorialStuffPostFactory
{
    public function onChangeState( OCEditorialStuffPost $post,
                                   eZContentObjectState $beforeState,
                                   eZContentObjectState $afterState )
    {
        OCEditorialStuffHistory::addHistoryToObjectId( $post->id(), 'dummy', array() );
        return true;
    }

    public function postChangeStateActions(
        OCEditorialStuffPost $post,
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {
        return false;
    }
}