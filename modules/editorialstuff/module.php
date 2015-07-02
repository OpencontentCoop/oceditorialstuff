<?php

$Module = array( 'name' => 'editorial stuff' );

$ViewList = array();
$ViewList['dashboard'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'dashboard.php',
    'params' => array( 'FactoryIdentifier' ),
    'unordered_params' => array(
        'offset' => 'Offset',        
        'query' => 'Query',
        'state' => 'State',
        'interval' => 'Interval',
        'tag' => 'Tag'
    )
);

$ViewList['edit'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'edit.php',
    'params' => array( 'FactoryIdentifier', 'ObjectID' ),
    'unordered_params' => array()
);

$ViewList['state_assign'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'state_assign.php',
    'params' => array( 'FactoryIdentifier', 'StateID', 'ObjectID' ),
    'unordered_params' => array()
);

$ViewList['media'] = array(
    'functions' => array( 'media' ),
    'script' => 'media.php',
    'params' => array( 'FactoryIdentifier', 'Action', 'ObjectID', 'Param1', 'Param2' ),
    'unordered_params' => array()
);

$ViewList['find_user'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'find_user.php',
    'params' => array( 'FactoryIdentifier' ),
    'unordered_params' => array()
);

$ViewList['send_mail'] = array(
    'functions' => array( 'mail' ),
    'script' => 'send_mail.php',
    'params' => array( 'FactoryIdentifier', 'ObjectID' ),
    'unordered_params' => array()
);

$FunctionList = array();
$FunctionList['dashboard'] = array();
$FunctionList['full_dashboard'] = array();
$FunctionList['media'] = array();
$FunctionList['mail'] = array();