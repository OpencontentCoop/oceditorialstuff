<?php

class OCEditorialStuffEventType extends eZNotificationEventType
{
    const NOTIFICATION_TYPE_STRING = 'editorialstuff';

    const FIELD_TYPE = 'data_text1';
    const FIELD_FACTORY_IDENTIFIER = 'data_text2';
    const FIELD_FACTORY_PARAMS = 'data_text3';
    const FIELD_OBJECT_ID = 'data_int1';
    const FIELD_VERSION = 'data_int2';

    const FIELD_REFER_FACTORY_IDENTIFIER = 'data_text4';
    const FIELD_REFER_OBJECT_ID = 'data_int3';
    const FIELD_REFER_VERSION = 'data_int4';

    function __construct()
    {
        parent::__construct( self::NOTIFICATION_TYPE_STRING );
    }

    /**
     * @param eZNotificationEvent $event
     * @param array $params
     */
    function initializeEvent( $event, $params )
    {
        $event->setAttribute( self::FIELD_TYPE, $params['type'] );
        $event->setAttribute( self::FIELD_FACTORY_IDENTIFIER, $params['factory_identifier'] );
        $event->setAttribute( self::FIELD_FACTORY_PARAMS, $params['factory_params_serialized'] );
        $event->setAttribute( self::FIELD_OBJECT_ID, $params['object_id'] );
        $event->setAttribute( self::FIELD_VERSION, $params['version'] );

        if ( isset( $params['refer_factory_identifier'] ) )
            $event->setAttribute( self::FIELD_REFER_FACTORY_IDENTIFIER, $params['refer_factory_identifier'] );
        if ( isset( $params['refer_object_id'] ) )
            $event->setAttribute( self::FIELD_REFER_OBJECT_ID, $params['refer_object_id'] );
        if ( isset( $params['refer_version'] ) )
            $event->setAttribute( self::FIELD_REFER_VERSION, $params['refer_version'] );
    }

    /**
     * @param eZNotificationEvent $event
     * @return OCEditorialStuffPostNotifiableFactory|null
     */
    function eventContent( $event )
    {
        $content = new OCEditorialStuffEventTypeContent();
        try
        {
            $factory = OCEditorialStuffHandler::instance(
                $event->attribute( self::FIELD_FACTORY_IDENTIFIER ),
                unserialize( $event->attribute( self::FIELD_FACTORY_PARAMS ) )
            )->getFactory();

            if ( $factory instanceof OCEditorialStuffPostNotifiableFactory )
            {
                $content->factory = $factory;
            }
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage() );
        }

        if ( $event->hasAttribute( self::FIELD_REFER_FACTORY_IDENTIFIER ) )
        {
            try
            {
                $factory = OCEditorialStuffHandler::instance(
                    $event->attribute( self::FIELD_REFER_FACTORY_IDENTIFIER )
                )->getFactory();

                if ( $factory instanceof OCEditorialStuffPostFactoryInterface )
                {
                    $content->referPost = $factory->instancePost(
                        array( 'object_id' => $event->attribute( self::FIELD_REFER_OBJECT_ID ) )
                    );
                }
            }
            catch ( Exception $e )
            {
                eZDebug::writeError( $e->getMessage() );
            }
        }
        return $content;
    }
}

class OCEditorialStuffEventTypeContent
{
    public $factory;

    public $referPost;
}

eZNotificationEventType::register( OCEditorialStuffEventType::NOTIFICATION_TYPE_STRING, 'OCEditorialStuffEventType' );
