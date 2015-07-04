<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$objectId = $Params['ObjectID'];
$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );
return $handler->getFactory()->editModuleResult( $objectId, $handler, $module );
