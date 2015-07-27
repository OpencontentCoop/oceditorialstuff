<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$factoryIdentifier = $Params['FactoryIdentifier'];
$id = $Params['ObjectID'];

try
{
    /** @var OCEditorialStuffPostInputActionInterface $post */
    $post = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET )->fetchByObjectId( $id );
    if ( $post instanceof OCEditorialStuffPostInputActionInterface && $post->getObject()->attribute( 'can_edit' ) )
    {
        if ( $http->hasPostVariable( 'ActionIdentifier' ) && $http->hasPostVariable( $http->postVariable( 'ActionIdentifier' ) ) )
        {
            $post->executeAction(
                $http->postVariable( 'ActionIdentifier' ),
                $http->postVariable( 'ActionParameters', array() ),
                $module
            );
        }
    }
}
catch ( Exception $e )
{
    eZDebug::writeNotice( $e->getMessage(), __FILE__ );
}
if ( $http->hasPostVariable( 'AjaxMode' ) )
    eZExecution::cleanExit();
elseif ( $module->exitStatus() != eZModule::STATUS_REDIRECT  )
    $module->redirectTo( $http->postVariable( 'RedirectUrl', "editorialstuff/edit/{$factoryIdentifier}/{$id}" ) );
