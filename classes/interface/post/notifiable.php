<?php

abstract class OCEditorialStuffPostNotifiable extends OCEditorialStuffPost
{
    public function createNotificationEvent( $type, OCEditorialStuffPostInterface $refer = null )
    {
        $event = null;
        $factory = $this->getFactory();
        if ( $factory instanceof OCEditorialStuffPostNotifiableFactory )
        {
            $availableNotificationEventTypes = $factory->availableNotificationEventTypes();
            if ( in_array( $type, $availableNotificationEventTypes ) )
            {
                $params = array(
                    'type' => $type,
                    'factory_identifier' => $factory->identifier(),
                    'factory_params_serialized' => serialize( $factory->getRuntimeParameters() ),
                    'object_id' => $this->id(),
                    'version' => $this->getObject()->attribute( 'current_version' )
                );

                if ( $refer instanceof OCEditorialStuffPostInterface )
                {
                    $params['refer_factory_identifier'] = $refer->getFactory()->identifier();
                    $params['refer_object_id'] = $refer->id();
                    $params['refer_version'] = $refer->getObject()->attribute( 'current_version' );
                }

                $event = eZNotificationEvent::create(
                    OCEditorialStuffEventType::NOTIFICATION_TYPE_STRING,
                    $params
                );
                $event->store();
                eZDebug::writeWarning( "Created event $type", __METHOD__ );
            }
            else
            {
                eZDebug::writeError(
                    "'$type' not defined in " . get_class( $factory ) . "::availableNotificationEventTypes()",
                    __METHOD__
                );
            }
        }
        else
        {
            eZDebug::writeError( "A OCEditorialStuffPostNotifiable requires a OCEditorialStuffPostNotifiableFactory", __METHOD__  );
        }
        return $event;
    }

    /**
     * @param $type
     * @param $event
     * @param OCEditorialStuffPostInterface $refer
     *
     * @return bool
     */
    public function handleNotificationEventByType( $type, $event, OCEditorialStuffPostInterface $refer = null )
    {
        $result = false;
        $factory = $this->getFactory();
        if ( $factory instanceof OCEditorialStuffPostNotifiableFactory )
        {
            $configuration = $factory->notificationEventTypesConfiguration();
            if ( isset( $configuration[$type]['handler_method'] )
                 && method_exists( $this, $configuration[$type]['handler_method'] ) )
            {
                $result = $this->{$configuration[$type]['handler_method']}( $event, $refer, $configuration[$type] );
            }
            else
            {
                eZDebug::writeError( "handler_method not found for type $type in " . get_called_class( $factory ) . "::notificationEventTypesConfiguration", __METHOD__  );
            }
        }
        return $result;
    }

    /**
     * @param string $type
     * @param int[] $userIds eZUser::id() array
     */
    public function createNotificationTypeRule( $type, $userIds )
    {
        $factory = $this->getFactory();
        if ( $factory instanceof OCEditorialStuffPostNotifiableFactory )
        {
            if ( in_array( $type, $factory->availableNotificationEventTypes() ) )
            {
                $type = $factory->identifier() . '/' . $type;
                $db = eZDB::instance();
                $db->begin();
                foreach ( $userIds as $userId )
                {
                    $exists = OCEditorialStuffNotificationRule::fetchPostsForUserID( $type, $userId );
                    if ( !in_array( $this->id(), $exists ) )
                    {
                        $rule = OCEditorialStuffNotificationRule::create( $type, $this->id(), $userId );
                        $rule->store();
                        OCEditorialStuffHistory::addHistoryToObjectId( $this->id(), 'add_notification_rule', array( 'type' => $type, 'user_id' => $userId ) );
                    }
                }
                $db->commit();
            }
        }
    }

    public function onRemove()
    {
        OCEditorialStuffNotificationRule::removeByPostID( $this->id() );
    }

}