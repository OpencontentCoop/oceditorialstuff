<?php

/** @var eZModule $module */
$module = $Params['Module'];
$stateID = $Params['StateID'];
$objectID = $Params['ObjectID'];

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );

$isAjaxRequest = eZHTTPTool::instance()->hasVariable( 'Ajax' );

try
{
    $post = $handler->fetchByObjectId( $objectID );
    $post->setState( $stateID );
    if ( $isAjaxRequest )
    {
        header('Content-Type: application/json');
        echo json_encode( array( 'result' => 'success' ) );
        eZExecution::cleanExit();
    }
    $module->redirectTo( '/editorialstuff/edit/' . $factoryIdentifier . '/' . $objectID );
}
catch( Exception $e )
{
    if ( $isAjaxRequest )
    {
        header('Content-Type: application/json');
        echo json_encode( array(
            'result' => 'error',
            'error_message' => $e->getMessage()
        ) );
        eZExecution::cleanExit();
    }
    eZDebug::writeError( $e->getMessage(), __FILE__ );
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}