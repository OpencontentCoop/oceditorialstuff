<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$offset = $Params['Offset'];
$query = $http->getVariable( 'query', $Params['Query'] );
$state = $http->getVariable( 'state', $Params['State'] );
$interval = $http->getVariable( 'interval', $Params['Interval'] );
$tag = $http->getVariable( 'tag', $Params['Tag'] );

$viewParameters = array(
    'limit' => 30,
    'offset' => ( isset( $offset ) and is_numeric( $offset ) ) ? $offset : 0,
    'query'  => ( isset( $query ) and is_string( $query ) ) ? $query : false,
    'state' => ( isset( $state ) and is_numeric( $state ) ) ? $state : false,
    'interval'  => ( isset( $interval ) and is_string( $interval ) ) ? $interval : false,
    'tag'  => ( isset( $tag ) and is_string( $tag ) ) ? $tag : false
);

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );
return $handler->getFactory()->dashboardModuleResult( $viewParameters, $handler, $module );