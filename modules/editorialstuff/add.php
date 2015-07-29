<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier );

$class = eZContentClass::fetchByIdentifier( $handler->getFactory()->classIdentifier() );
if ( !$class instanceof eZContentClass )
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );

$queryString = '';
if ( $_SERVER['QUERY_STRING'] )
{
    $queryStringParts =  explode( '/', $_SERVER['QUERY_STRING'] );
    $queryString = '?' . array_pop( $queryStringParts );
}

$parent = $handler->getFactory()->creationRepositoryNode();
if ( $http->hasGetVariable( 'parent' ) )
{
    $parent = $http->getVariable( 'parent' );
}
$node = eZContentObjectTreeNode::fetch( intval( $parent ) );
if ( $node instanceof eZContentObjectTreeNode && $class->attribute( 'id' ) && $node->canCreate() )
{
    $languageCode = eZINI::instance()->variable( 'RegionalSettings', 'Locale' );
    $object = eZContentObject::createWithNodeAssignment( $node,
        $class->attribute( 'id' ),
        $languageCode,
        false );
    if ( $object )
    {
        $http->setSessionVariable( 'RedirectURIAfterPublish', '/editorialstuff/edit/' . $factoryIdentifier . '/' . $object->attribute( 'id' ) );
        if ( $http->hasGetVariable( 'parent' ) )
            $http->setSessionVariable( 'RedirectIfDiscarded', $http->sessionVariable( 'LastAccessesURI', '/' ) );
        else
            $http->setSessionVariable( 'RedirectIfDiscarded', '/editorialstuff/dashboard/' . $factoryIdentifier );

        $module->redirectTo( 'content/edit/' . $object->attribute( 'id' ) . '/' . $object->attribute( 'current_version' ) . $queryString );
        return;
    }
    else
        return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
