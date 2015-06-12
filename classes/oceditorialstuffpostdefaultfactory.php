<?php


class OCEditorialStuffPostDefaultFactory extends OCEditorialStuffPostFactory
{
    public function onChangeState( OCEditorialStuffPost $post, eZContentObjectState $beforeState, eZContentObjectState $afterState )
    {
        eZDebug::writeNotice( '(dummy action) Change state for ' . $post->id() . ' from ' . $beforeState->attribute( 'identifier' ) . ' to ' . $afterState->attribute( 'identifier' ), __METHOD__ );
    }
}