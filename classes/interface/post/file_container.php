<?php

interface OCEditorialStuffPostFileContainerInterface
{
    public function addFile( eZContentObject $object, $attributeIdentifier );

    public function removeFile( eZContentObject $object, $attributeIdentifier );

    /**
     * @param string $attributeIdentifier
     *
     * @return OCEditorialStuffPostFileFactoryInterface
     */
    public function fileFactory( $attributeIdentifier );
}