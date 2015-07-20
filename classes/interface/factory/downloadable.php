<?php


interface OCEditorialStuffPostDownloadableFactoryInterface extends OCEditorialStuffPostFactoryInterface
{
    public function downloadModuleResult( $parameters, OCEditorialStuffHandlerInterface $handler, eZModule $module, $version = false );
}