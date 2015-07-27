<?php

interface OCEditorialStuffPostInputActionInterface extends OCEditorialStuffPostInterface
{
    public function executeAction( $actionIdentifier, $actionParameters, eZModule $module = null );
}