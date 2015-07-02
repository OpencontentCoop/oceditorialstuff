<?php


class OCEditorialStuffPostDefaultFactory extends OCEditorialStuffPostFactory
{
    public function onChangeState( OCEditorialStuffPostInterface $post,
                                   eZContentObjectState $beforeState,
                                   eZContentObjectState $afterState )
    {
        return true;
    }

    public function getTemplateDirectory()
    {
        return 'editorialstuff/default';
    }
}