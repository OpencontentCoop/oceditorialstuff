<?php


class OCEditorialStuffPostDefaultFactory extends OCEditorialStuffPostFactory implements OCEditorialStuffPostChangeStateHandlerFactoryInterface
{
    public function onChangeState( OCEditorialStuffPostInterface $post,
                                   eZContentObjectState $beforeState,
                                   eZContentObjectState $afterState )
    {
        return true;
    }

    public function instancePost( $data )
    {
        return new OCEditorialStuffPostDefault( $data, $this );
    }
}