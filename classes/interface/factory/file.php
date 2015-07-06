<?php

interface OCEditorialStuffPostFileFactoryInterface extends OCEditorialStuffPostFactoryInterface
{
    public function handleFile( $filePath, $properties, $attributes );

    public function fileAttributeIdentifier();
}