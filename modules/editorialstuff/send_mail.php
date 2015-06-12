<?php

$module = $Params['Module'];
$objectID = $Params['ObjectID'];

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier );
try
{
    $http = eZHTTPTool::instance();
    $extraRecipientsText = $http->postVariable( 'ExtraRecipients', '' );
    $recipients = explode( "\n", $extraRecipientsText );
    $message = $http->postVariable( 'Message' );
    $mailActions = $http->hasPostVariable( 'AddApproveButton' ) ? $http->postVariable( 'AddApproveButton' ) : false;

    $post = $handler->fetchByObjectId( $objectID );
    OCEditorialStuffMailer::sendMail( $post, $recipients, $message, $mailActions );
    $module->redirectTo( '/editorialstuff/edit/' . $factoryIdentifier . '/' . $objectID . '#tab_mail' );
}
catch( Exception $e )
{
    eZDebug::writeError( $e->getMessage(), __FILE );
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}