<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier );
$tpl->setVariable( 'factory_identifier', $factoryIdentifier );
$tpl->setVariable( 'factory_configuration', $handler->factory->getConfiguration() );

$objectId = $Params['ObjectID'];
$object = eZContentObject::fetch( $objectId );


if ( !$object instanceof eZContentObject )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

if ( !$object->attribute( 'can_read' ) )
{
    return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}
try
{
    $post = $handler->fetchByObjectId( $objectId );
}
catch( Exception $e )
{
    $post = null;
}
$tpl->setVariable( 'post', $post );

$Result = array();
$contentInfoArray = array( 'url_alias' => 'editorialstuff/dashboard' );
$contentInfoArray['persistent_variable'] = array( 'show_path' => true, 'site_title' => 'Dashboard' );
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$tpl->setVariable( 'persistent_variable', false );
$Result['content_info'] = $contentInfoArray;
$Result['content'] = $tpl->fetch( "design:editorialstuff/edit.tpl" );
$Result['path'] = array( array( 'url' => 'editorialstuff/dashboard',
                                'text' => 'Dashboard' ),
                         array( 'url' => false,
                                'text' => $object->attribute( 'name' ) ));
