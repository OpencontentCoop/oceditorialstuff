<?php

/** @var eZModule $module */
$module = $Params['Module'];
$stateID = $Params['StateID'];
$objectID = $Params['ObjectID'];

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier );

try
{
    $post = $handler->fetchByObjectId( $objectID );
    $post->setState( $stateID );
    $module->redirectTo( '/editorialstuff/edit/' . $factoryIdentifier . '/' . $objectID );
}
catch( Exception $e )
{
    eZDebug::writeError( $e->getMessage(), __FILE__ );
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}