<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$objectId = $Params['ObjectID'];
$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );
$factory = $handler->getFactory();
if ( $factory instanceof OCEditorialStuffPostDownloadableFactoryInterface )
    return $factory->downloadModuleResult( $objectId, $handler, $module );
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );