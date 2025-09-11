<?php

interface OCEditorialStuffPostInputActionPermissionInterface extends OCEditorialStuffPostInterface
{
    public function canExecuteAction( $actionIdentifier, $actionParameters, eZModule $module = null ): bool;
}