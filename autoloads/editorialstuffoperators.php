<?php

class OCEditorialStuffOperators
{
    private $Operators = array();

    function __construct()
    {
        $this->Operators = array(
            'editorialstuff_has_custom_message_subject', 'editorialstuff_get_custom_message_subject',
            'editorialstuff_has_custom_message_text', 'editorialstuff_get_custom_message_text',
        );
    }

    public static function placeholders()
    {
        return [
            '%post_title%' => ezpI18n::tr('editorialstuff/dashboard', 'Current post title:'),
            '%post_url%' => ezpI18n::tr('editorialstuff/dashboard', 'Current post url:'),
            '%post_state%' => ezpI18n::tr('editorialstuff/dashboard', 'Current post status:'),
            '%factory_url%' => ezpI18n::tr('editorialstuff/dashboard', 'Dashboard url:'),
            '%site_url%' => ezpI18n::tr('editorialstuff/dashboard', 'Site url:'),
        ];
    }

    private static function replacePlaceholders($message, $post)
    {
        if ($post instanceof OCEditorialStuffPost) {
            $editorialUrl = $post->attribute('editorial_url');
            eZURI::transformURI($editorialUrl, false, 'full');
            $factoryUrl = 'editorialstuff/dashboard/' . $post->getFactory()->identifier();
            eZURI::transformURI($factoryUrl, false, 'full');
            $siteUrl = '/';
            eZURI::transformURI($siteUrl, false, 'full');

            $placeholders = [
                '%post_title%' => $post->getObject()->attribute('name'),
                '%post_url%' => $editorialUrl,
                '%post_state%' => $post->attribute('current_state')->attribute('current_translation')->attribute('name'),
                '%factory_url%' => $factoryUrl,
                '%site_url%' => rtrim($siteUrl, '/'),
            ];
            foreach ($placeholders as $placeholder => $value) {
                $message = str_replace($placeholder, $value, $message);
            }
        }

        return $message;
    }

    function operatorList()
    {
        return $this->Operators;
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'editorialstuff_has_custom_message_text' => array(
                'factory' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'action' => array(
                    'type' => 'string',
                    'required' => true
                )
            ),
            'editorialstuff_get_custom_message_text' => array(
                'factory' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'action' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'post' => array(
                    'type' => 'object',
                    'required' => true
                )
            ),
            'editorialstuff_has_custom_message_subject' => array(
                'factory' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'action' => array(
                    'type' => 'string',
                    'required' => true
                )
            ),
            'editorialstuff_get_custom_message_subject' => array(
                'factory' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'action' => array(
                    'type' => 'string',
                    'required' => true
                ),
                'post' => array(
                    'type' => 'object',
                    'required' => true
                )
            ),
        );
    }

    function modify(&$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters)
    {

        switch ($operatorName) {

            case 'editorialstuff_has_custom_message_text':
            case 'editorialstuff_get_custom_message_text':
            case 'editorialstuff_has_custom_message_subject':
            case 'editorialstuff_get_custom_message_subject':
                {
                    $factoryIdentifier = $namedParameters['factory'];
                    $actionIdentifier = $namedParameters['action'];
                    $type = strpos($operatorName, 'subject') === false ? 'texts' : 'subjects';
                    $messages = $this->getCustomTexts($factoryIdentifier, $type);
                    if (isset($messages[$actionIdentifier]) && trim(strip_tags($messages[$actionIdentifier])) !== ''){
                        if (strpos($operatorName, '_has_') !== false){
                            $operatorValue = true;
                        }else{
                            $post = $namedParameters['post'];
                            $text = $messages[$actionIdentifier];
                            $operatorValue = self::replacePlaceholders($text, $post);
                        }
                    }else{
                        $operatorValue = false;
                    }
                }
                break;
        }
    }

    private function getCustomTexts($factoryIdentifier, $type)
    {
        $messages = array($type => array());
        $siteData = eZSiteData::fetchByName('config_' . $factoryIdentifier);
        if ($siteData instanceof eZSiteData){
            $customMessages = json_decode($siteData->attribute('value'), true);
            if (isset($customMessages[$type])){
                $messages = $customMessages[$type];
            }
        }

        return $messages;
    }
}
