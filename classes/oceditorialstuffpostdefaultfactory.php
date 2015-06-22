<?php


class OCEditorialStuffPostDefaultFactory extends OCEditorialStuffPostFactory
{
    public function onChangeState( OCEditorialStuffPost $post,
                                   eZContentObjectState $beforeState,
                                   eZContentObjectState $afterState )
    {
        return true;
    }
}