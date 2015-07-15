<?php

abstract class OCEditorialStuffPostNotifiableFactory extends OCEditorialStuffPostFactory
{

    /**
     * @return string[]
     */
    final public function availableNotificationEventTypes()
    {
        $configuration = $this->notificationEventTypesConfiguration();
        return array_keys( $configuration );
    }

    /**
     * @return array[] array( 'type' => array( 'handler_method' => <methodName> ) )
     */
    abstract public function notificationEventTypesConfiguration();

    /**
     * @param OCEditorialStuffEventType $event
     *
     * @return bool
     */
    public function handleEditorialStuffNotificationEvent( $event, OCEditorialStuffPostInterface $refer = null )
    {
        $type = $event->attribute( OCEditorialStuffEventType::FIELD_TYPE );
        if ( in_array(
            $type,
            $this->availableNotificationEventTypes()
        ) )
        {
            try
            {
                $post = OCEditorialStuffHandler::instanceFromFactory( $this )
                    ->fetchByObjectId( $event->attribute( OCEditorialStuffEventType::FIELD_OBJECT_ID ) );
                if ( $post instanceof OCEditorialStuffPostNotifiable )
                {
                    return $post->handleNotificationEventByType( $type, $event, $refer );
                }
                else
                {
                    eZDebug::writeError(
                        "OCEditorialStuffPostNotifiableFactory works only with OCEditorialStuffPostNotifiable objects",
                        __METHOD__
                    );
                }
            }
            catch( Exception $e )
            {
                eZDebug::writeError( $this->identifier() . ': ' . $e->getMessage() );
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
    public function fetchHttpInput( $http, $module )
    {
        return true;
    }

}