<?php

$FunctionList = array();
$FunctionList['posts'] = array(
    'name' => 'posts',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'include_file' => 'extension/pat_stampa/classes/oceditorialstufffunctioncollection.php',
        'class' => 'OCEditorialStuffFunctionCollection',
        'method' => 'fetchPosts' ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'interval',
            'type' =>'string',
            'required' => false,
            'default' => false
        ),
        array(
            'name' => 'state',
            'type' =>'integer',
            'required' => false,
            'default' => false
        ),
        array(
            'name' => 'query',
            'type' =>'string',
            'required' => false,
            'default' => false
        ),
        array(
            'name' => 'tag',
            'type' =>'string',
            'required' => false,
            'default' => false
        ),          
        array(
            'name' => 'limit',
            'type' =>'integer',
            'required' => false,
            'default' => 10
        ),
        array(
            'name' => 'offset',
            'type' =>'integer',
            'required' => false,
            'default' => 0
        )
    )
);

$FunctionList['post_count'] = array(
    'name' => 'post_count',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'include_file' => 'extension/pat_stampa/classes/oceditorialstufffunctioncollection.php',
        'class' => 'OCEditorialStuffFunctionCollection',
        'method' => 'fetchPostCount' ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'interval',
            'type' =>'string',
            'required' => false,
            'default' => false
        ),
        array(
            'name' => 'state',
            'type' =>'integer',
            'required' => false,
            'default' => false
        ),
        array(
            'name' => 'query',
            'type' =>'string',
            'required' => false,
            'default' => false
        ),
        array(
            'name' => 'tag',
            'type' =>'string',
            'required' => false,
            'default' => false
        ),          
    )
);

$FunctionList['post_states'] = array(
    'name' => 'post_states',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'include_file' => 'extension/pat_stampa/classes/oceditorialstufffunctioncollection.php',
        'class' => 'OCEditorialStuffFunctionCollection',
        'method' => 'fetchPostStates' ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['post_tags'] = array(
    'name' => 'post_tags',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'include_file' => 'extension/pat_stampa/classes/oceditorialstufffunctioncollection.php',
        'class' => 'OCEditorialStuffFunctionCollection',
        'method' => 'fetchPostTags' ),
    'parameter_type' => 'standard',
    'parameters' => array()
);