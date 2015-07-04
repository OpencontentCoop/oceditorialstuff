<?php

interface OCEditorialStuffPostFileContainerInterface
{
    public function addFile( eZContentObject $object, $attributeIdentifier );

    public function removeFile( eZContentObject $object, $attributeIdentifier );

    /**
     * @return OCEditorialStuffPostFileFactoryInterface
     */
    public function fileFactory();
}