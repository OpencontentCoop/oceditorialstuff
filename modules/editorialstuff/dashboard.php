<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier );
$tpl->setVariable( 'factory_identifier', $factoryIdentifier );
$tpl->setVariable( 'factory_configuration', $handler->getFactory()->getConfiguration() );
$tpl->setVariable( 'template_directory', $handler->getFactory()->getTemplateDirectory() );

$offset = $Params['Offset'];
$query = $http->getVariable( 'query', $Params['Query'] );
$state = $http->getVariable( 'state', $Params['State'] );
$interval = $http->getVariable( 'interval', $Params['Interval'] );
$tag = $http->getVariable( 'tag', $Params['Tag'] );

$viewParameters = array(
    'limit' => 20,
    'offset' => ( isset( $offset ) and is_numeric( $offset ) ) ? $offset : 0,
    'query'  => ( isset( $query ) and is_string( $query ) ) ? $query : false,
    'state' => ( isset( $state ) and is_numeric( $state ) ) ? $state : false,
    'interval'  => ( isset( $interval ) and is_string( $interval ) ) ? $interval : false,
    'tag'  => ( isset( $tag ) and is_string( $tag ) ) ? $tag : false
);
$tpl->setVariable( 'view_parameters', $viewParameters );

$postCount = $handler->fetchItemsCount( $viewParameters );
$tpl->setVariable( 'post_count', $postCount );

$posts = $handler->fetchItems( $viewParameters );
$tpl->setVariable( 'posts', $posts );

$tpl->setVariable( 'states', $handler->getFactory()->states() );

$Result = array();
$contentInfoArray = array(
    'node_id' => null,
    'class_identifier' => null
);
$contentInfoArray['persistent_variable'] = array(
    'show_path' => true,
    'site_title' => 'Dashboard Ufficio Stampa'
);
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['content'] = $tpl->fetch( "design:{$handler->getFactory()->getTemplateDirectory()}/dashboard.tpl" );
$Result['path'] = array( array( 'url' => false, 'text' => 'Dashboard' ) );
