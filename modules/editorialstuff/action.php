<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$factoryIdentifier = $Params['FactoryIdentifier'];
$id = $Params['ID'];

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
                $http->postVariable( 'ActionParameters', array() )
            );
        }
    }
}
catch ( Exception $e )
{

}
if ( $http->hasPostVariable( 'AjaxMode' ) )
    eZExecution::cleanExit();
else
    $module->redirectTo( $http->postVariable( 'RedirectUrl', "editorialstuff/edit/{$factoryIdentifier}/{$id}" ) );
