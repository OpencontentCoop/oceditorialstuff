<?php

interface OCEditorialStuffPostMediaInterface
{
    public function addImage( eZContentObject $object );

    public function makeDefaultImage( $objectId );

    public function removeImage( $objectId );

    public function addVideo( eZContentObject $object );

    public function removeVideo( $objectId );

    public function addAudio( eZContentObject $object );

    public function removeAudio( $objectId );
}