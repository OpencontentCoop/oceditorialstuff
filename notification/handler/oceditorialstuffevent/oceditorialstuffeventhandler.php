<?php

class OCEditorialStuffEventHandler extends eZNotificationEventHandler
{
    const NOTIFICATION_HANDLER_ID = 'editorialstuff';

    /**
     * @param OCEditorialStuffEventType $event
     *
     * @return bool
     */
    function handle( $event )
    {

        if ( $event->attribute( 'event_type_string' ) == OCEditorialStuffEventType::NOTIFICATION_TYPE_STRING )
        {
            try
            {
                /** @var OCEditorialStuffEventTypeContent $content */
                $content = $event->attribute( 'content' );
                if ( $content->factory instanceof OCEditorialStuffPostNotifiableFactory )
                {
                    return $content->factory->handleEditorialStuffNotificationEvent( $event, $content->referPost );
                }
            }
            catch( Exception $e )
            {
            }
        }
        return false;
    }

    /**
     * @param eZHTTPTool $http
     * @param eZModule $module
     *
     * @return bool
     */
    function fetchHttpInput( $http, $module )
    {
        if ( $http->hasPostVariable( 'Factory_' . self::NOTIFICATION_HANDLER_ID  ) )
        {
            $factoryIdentifier = $http->postVariable( 'Factory_' . self::NOTIFICATION_HANDLER_ID  );
            try
            {
                $factory = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );
                if ( $factory instanceof OCEditorialStuffPostNotifiableFactory )
                {
                    return $factory->fetchHttpInput( $http, $module );
                }
            }
            catch ( Exception $e )
            {
            }
        }
        return true;
    }

    function cleanup()
    {
        OCEditorialStuffNotificationRule::cleanup();
    }

    function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'available_factories';
        return $attributes;
    }


    function attribute( $attr )
    {
        if ( $attr == 'available_factories' )
        {
            $factories = array();
            foreach( OCEditorialStuffHandler::instances() as $handler )
            {
                if ( $handler->getFactory() instanceof OCEditorialStuffPostNotifiableFactory )
                {
                    $factories[] = $handler->getFactory();
                }
            }
            return $factories;
        }
        return parent::attribute( $attr );
    }
}