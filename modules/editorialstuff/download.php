<?php

/** @var eZModule $module */
$module = $Params['Module'];
$objectVersion = isset($Params['ObjectVersion']) ? intval($Params['ObjectVersion']) : false;
$http = eZHTTPTool::instance();
$objectId = $Params['ObjectID'];
$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );
$factory = $handler->getFactory();
if ( $factory instanceof OCEditorialStuffPostDownloadableFactoryInterface )
    return $factory->downloadModuleResult( $objectId, $handler, $module, $objectVersion );
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );