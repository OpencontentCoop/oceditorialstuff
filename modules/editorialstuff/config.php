<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$factoryIdentifier = $Params['FactoryIdentifier'];

$handler = OCEditorialStuffHandler::instance($factoryIdentifier, $_GET);
$factory = $handler->getFactory();
$states = $factory->states();
$actionHandler = OCEditorialStuffActionHandler::instance($factory);
$actionList = $actionHandler->getFactoryActionConfiguration();
$availableActions = array();
$messages = array();

$siteData = eZSiteData::fetchByName('config_' . $factoryIdentifier);
if (!$siteData instanceof eZSiteData){
    $siteData = new eZSiteData([
        'name' => 'config_' . $factoryIdentifier,
        'value' => json_encode(array())
    ]);
}
if ($http->hasPostVariable('Store')){
    $subjects = $http->postVariable('Subjects');
    $texts = $http->postVariable('Messages');
    $messages = [
        'subjects' => $subjects,
        'texts' => $texts,
    ];
    $siteData->setAttribute('value', json_encode($messages));
    $siteData->store();
}

$messages = json_decode($siteData->attribute('value'), true);

foreach ($actionList as $identifier => $actions) {
    foreach ($actions as $index => $action) {
        $beforeStatus = false;
        $afterStatus = false;
        foreach ($states as $state) {
            if ($state->attribute('identifier') == $action['before_state']) {
                $beforeStatus = $state;
            }
            if ($state->attribute('identifier') == $action['after_state']) {
                $afterStatus = $state;
            }
        }

        $actionIdentifier = $identifier . '_' . implode('::', $action['call_function']);
        $availableActions[] = [
            'call_function' => implode('::', $action['call_function']),
            'identifier' => $actionIdentifier,
            'before' => $beforeStatus,
            'after' => $afterStatus,
            'message' => isset($messages['texts'][$actionIdentifier]) ? $messages['texts'][$actionIdentifier] : '',
            'subject' => isset($messages['subjects'][$actionIdentifier]) ? $messages['subjects'][$actionIdentifier] : '',
        ];
        $messages[$identifier] = '';
    }
}

$configuration = $factory->getConfiguration();

$tpl = eZTemplate::factory();
$tpl->setVariable('factory_identifier', $factoryIdentifier);
$tpl->setVariable('factory_configuration', $configuration);
$tpl->setVariable('actions', $availableActions);
$tpl->setVariable('site_title', false);
$tpl->setVariable('messages', $messages);
$tpl->setVariable('placeholders', OCEditorialStuffOperators::placeholders());

$Result = array();
$Result['content'] = $tpl->fetch("design:editorialstuff/config.tpl");
$contentInfoArray = array(
    'node_id' => null,
    'class_identifier' => null
);
$contentInfoArray['persistent_variable'] = array(
    'show_path' => true,
    'site_title' => $configuration['Name'] . ' dashboard config'
);
if (is_array($tpl->variable('persistent_variable'))) {
    $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'], $tpl->variable('persistent_variable'));
}
if (isset($configuration['PersistentVariable']) && is_array($configuration['PersistentVariable'])) {
    $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'], $configuration['PersistentVariable']);
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array(
    array(
        'url' => false,
        'text' => $configuration['Name'] . ' dashboard config'
    )
);
return $Result;