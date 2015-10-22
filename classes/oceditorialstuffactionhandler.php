<?php

class OCEditorialStuffActionHandler
{

    private static $currentStateChange;

    private static $_instances = array();

    /**
     * @var OCEditorialStuffPostFactoryInterface
     */
    protected $factory;

    /**
     * @var array
     */
    protected $factoryActionConfiguration = array();

    protected $availableActions = array();

    protected function __construct( OCEditorialStuffPostFactoryInterface $factory )
    {
        $editorialIni = eZINI::instance( 'editorialstuff.ini' );
        $availableActions = $editorialIni->hasVariable( 'AvailableActions', 'Actions' ) ? $editorialIni->variable( 'AvailableActions', 'Actions' ) : array();
        foreach ( $availableActions as $actionName )
        {
            if ( $editorialIni->hasGroup( $actionName ) )
            {
                $className = $editorialIni->variable( $actionName, 'ClassName' );
                $methodName = $editorialIni->variable( $actionName, 'MethodName' );
                if ( method_exists( $className, $methodName ) )
                {
                    $this->availableActions[$actionName] = array( $className, $methodName );
                }
                else
                {
                    eZDebug::writeError( "{$className}::{$methodName} not callable", __METHOD__ );
                }
            }
        }
        $this->factory = $factory;
        $factoryConfiguration = $this->factory->getConfiguration();
        $statesConfigurations = isset( $factoryConfiguration['States'] ) ? $factoryConfiguration['States'] : array();
        $actionsConfiguration = isset( $factoryConfiguration['Actions'] ) ? $factoryConfiguration['Actions'] : array();
        foreach ( $actionsConfiguration as $changeStates => $actionsString )
        {
            $actions = explode( '|', $actionsString );
            foreach ( $actions as $actionAndSettings )
            {
                $configurationItem = array();
                $changeStatesParts = explode( '-', $changeStates );
                $actionParameters = explode( ';', $actionAndSettings );
                $actionName = array_shift( $actionParameters );
                if ( count( $changeStatesParts ) == 2
                     && isset( $statesConfigurations[$changeStatesParts[0]] )
                     && isset( $statesConfigurations[$changeStatesParts[1]] )
                     && isset( $this->availableActions[$actionName] )
                )
                {
                    $configurationItem['before_state'] = $changeStatesParts[0];
                    $configurationItem['after_state'] = $changeStatesParts[1];
                    $configurationItem['call_function'] = $this->availableActions[$actionName];
                    $configurationItem['call_function_parameters'] = $actionParameters;
                    if ( !isset( $this->factoryActionConfiguration[$changeStates] ) )
                    {
                        $this->factoryActionConfiguration[$changeStates] = array();
                    }
                    $this->factoryActionConfiguration[$changeStates][] = $configurationItem;
                }
            }
        }
    }

    public static function instance( OCEditorialStuffPostFactoryInterface $factory )
    {
        $factoryClassName = get_class( $factory );
        if ( !isset( self::$_instances[$factoryClassName] ) )
        {
            self::$_instances[$factoryClassName] = new OCEditorialStuffActionHandler(
                $factory
            );
        }

        return self::$_instances[$factoryClassName];
    }

    public function handleChangeState(
        OCEditorialStuffPost $post,
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    )
    {
        self::$currentStateChange = $beforeState->attribute( 'identifier' ) . '-' . $afterState->attribute( 'identifier' );
        if ( isset( $this->factoryActionConfiguration[self::$currentStateChange] ) )
        {
            foreach ( $this->factoryActionConfiguration[self::$currentStateChange] as $action )
            {
                $parameters = array_merge(
                    array( $post ),
                    array( $action['call_function_parameters'] )
                );
                call_user_func_array( $action['call_function'], $parameters );
            }
        }
    }

    // out of the box actions
    public static function addLocation(
        OCEditorialStuffPost $post,
        $addLocationIds
    )
    {
        $object = $post->getObject();
        if ( $object instanceof eZContentObject )
        {
            eZContentOperationCollection::addAssignment(
                $object->attribute( 'main_node_id' ),
                $object->attribute( 'id' ),
                $addLocationIds
            );
        }
        else
        {
            eZDebug::writeError( "Object not found", __METHOD__ );
        }
    }

    public static function removeLocation(
        OCEditorialStuffPost $post,
        $removeLocationIds
    )
    {
        $object = $post->getObject();
        if ( $object instanceof eZContentObject )
        {
            /** @var eZContentObjectTreeNode[] $nodes */
            $nodes = $object->attribute( 'assigned_nodes' );
            $removeNodeIdList = array();
            if ( count( $nodes ) > 1 )
            {
                foreach ( $nodes as $node )
                {
                    foreach ( $removeLocationIds as $removeLocationId )
                    {
                        if ( $node->attribute( 'parent_node_id' ) == $removeLocationId )
                        {
                            $removeNodeIdList[] = $node->attribute( 'node_id' );
                        }
                    }
                }
            }
            if ( !empty( $removeNodeIdList ) )
            {
                eZContentOperationCollection::removeNodes( $removeNodeIdList );
            }
        }
        else
        {
            eZDebug::writeError( "Object not found", __METHOD__ );
        }
    }

    public static function notifyOwner( OCEditorialStuffPost $post )
    {
        $object = $post->getObject();
        if ( $object instanceof eZContentObject )
        {
            $owner = $object->owner();
            if ( $owner instanceof eZContentObject )
            {
                /** @var eZUser $user */
                $user = eZUser::fetch( $owner->attribute( 'id' ) );
                if ( $user instanceof eZUser )
                {
                    $templatePath = 'design:editorialstuff/mail/action_notify_owner.tpl';
                    if ( !self::sendMail(
                        $post,
                        array( $user ),
                        $templatePath,
                        array(
                            'post' => $post,
                            'change_state_identifier' => self::$currentStateChange
                        )
                    ) )
                    {
                        eZDebug::writeError( "Fail sending mail", __METHOD__ );
                    }
                }
                else
                {
                    eZDebug::writeError( "Owner user not found", __METHOD__ );
                }
            }
            else
            {
                eZDebug::writeError( "Owner object not found", __METHOD__ );
            }
        }
        else
        {
            eZDebug::writeError( "Object not found", __METHOD__ );
        }
    }

    public static function notifyGroup( OCEditorialStuffPost $post, $groupLocationIds )
    {
        $object = $post->getObject();
        if ( $object instanceof eZContentObject )
        {
            $users = array();
            foreach ( $groupLocationIds as $groupLocationId )
            {
                $groupLocation = eZContentObjectTreeNode::fetch( $groupLocationId );
                if ( $groupLocation instanceof eZContentObjectTreeNode )
                {
                    $userClasses = eZUser::contentClassIDs();
                    $children = $groupLocation->subTree(
                        array(
                            'ClassFilterType' => 'include',
                            'ClassFilterArray' => $userClasses,
                            'Limitation' => array(),
                            'AsObject' => false
                        )
                    );                    
                    foreach ( $children as $child )
                    {
                        $id = isset( $child['contentobject_id'] ) ? $child['contentobject_id'] : $child['id'];
                        $user = eZUser::fetch( $id );
                        if ( $user instanceof eZUser )
                        {
                            $users[] = $user;
                        }
                        else
                        {
                            eZDebug::writeError(
                                "User {$id} not found",
                                __METHOD__
                            );
                        }
                    }
                }
                else
                {
                    eZDebug::writeError( "Group node {$groupLocationId} not found", __METHOD__ );
                }
            }

            if ( !empty( $users ) )
            {
                $templatePath = 'design:editorialstuff/mail/action_notify_group.tpl';
                if ( !self::sendMail(
                    $post,
                    $users,
                    $templatePath,
                    array(
                        'post' => $post,
                        'change_state_identifier' => self::$currentStateChange
                    )
                ) )
                {
                    eZDebug::writeError( "Fail sending mail", __METHOD__ );
                }
            }
            else
            {
                eZDebug::writeError( "Users not found", __METHOD__ );
            }

        }
        else
        {
            eZDebug::writeError( "Object not found", __METHOD__ );
        }
    }

    public static function addLocationFromRelationList(
        OCEditorialStuffPost $post,
        $relationListAttributeIdentifier
    )
    {
        $locations = self::getLocationsFromPostAttribute( $post, $relationListAttributeIdentifier );
        if ( !empty( $locations ) )
        {
            self::addLocation( $post, $locations );
        }
    }

    public static function removeLocationFromRelationList(
        OCEditorialStuffPost $post,
        $relationListAttributeIdentifier
    )
    {
        $locations = self::getLocationsFromPostAttribute( $post, $relationListAttributeIdentifier );
        if ( !empty( $locations ) )
        {
            self::removeLocation( $post, $locations );
        }
    }

    protected static function getLocationsFromPostAttribute(
        OCEditorialStuffPost $post,
        $relationListAttributeIdentifier
    )
    {
        $locations = array();
        $object = $post->getObject();
        if ( $object instanceof eZContentObject )
        {
            /** @var eZContentObjectAttribute[] $postDataMap */
            $postDataMap = $object->attribute( 'data_map' );
            if ( is_array( $relationListAttributeIdentifier ) )
            {
                $relationListAttributeIdentifier = $relationListAttributeIdentifier[0];
            }
            if ( isset( $postDataMap[$relationListAttributeIdentifier] )
                 && $postDataMap[$relationListAttributeIdentifier]->attribute(
                    'data_type_string'
                ) == 'ezobjectrelationlist'
            )
            {
                $content = $postDataMap[$relationListAttributeIdentifier]->attribute( 'content' );
                if ( isset( $content['relation_list'] ) )
                {
                    foreach ( $content['relation_list'] as $relation )
                    {
                        if ( isset( $relation['node_id'] ) )
                        {
                            $locations[] = $relation['node_id'];
                        }
                    }
                }
            }
            else
            {
                eZDebug::writeError(
                    "Attribute $relationListAttributeIdentifier not found",
                    __METHOD__
                );
            }
        }
        else
        {
            eZDebug::writeError( "Object not found", __METHOD__ );
        }

        return $locations;
    }

    /**
     * @param OCEditorialStuffPost $post
     * @param eZUser[] $users
     * @param string $templatePath
     * @param array $templateVariables
     *
     * @return bool
     */
    public static function sendMail( OCEditorialStuffPost $post, $users, $templatePath, $templateVariables = array() )
    {
        $addressList = array();
        foreach ( $users as $user )
        {
            $email = $user->attribute( 'email' );
            if ( eZMail::validate( $email ) )
            {
                $addressList[] = $email;
            }
            else
            {
                eZDebug::writeError( "Mail {$email} not valid", __METHOD__ );
            }
        }

        if ( !empty( $addressList ) )
        {
            $tpl = eZTemplate::factory();
            $tpl->resetVariables();

            $object = $post->getObject();
            $keyArray = array(
                array( 'object', $object->attribute( 'id' ) ),
                array( 'class', $object->attribute( 'contentclass_id' ) ),
                array( 'class_identifier', $object->attribute( 'class_identifier' ) ),
                array( 'class_identifier', $object->attribute( 'class_identifier' ) ),
                array( 'change_state_identifier', isset( $templateVariables['change_state_identifier'] ) ? $templateVariables['change_state_identifier'] : null )
            );

            $res = eZTemplateDesignResource::instance();
            $res->setKeys( $keyArray );

            foreach ( $templateVariables as $key => $value )
            {
                $tpl->setVariable( $key, $value );
            }

            $body = $tpl->fetch( $templatePath );

            if ( trim( $body ) != '' )
            {
                $ini = eZINI::instance();
                $mail = new eZMail();
                $notificationINI = eZINI::instance( 'notification.ini' );
                $emailSender = $notificationINI->variable( 'MailSettings', 'EmailSender' );
                if ( !$emailSender )
                {
                    $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
                }
                if ( !$emailSender )
                {
                    $emailSender = $ini->variable( "MailSettings", "AdminEmail" );
                }

                foreach ( $addressList as $addressItem )
                {
                    $mail->extractEmail( $addressItem, $email, $name );
                    $mail->addBcc( $email, $name );
                }

                $subject = $tpl->variable( 'subject' );

                $mail->setSender( $emailSender );
                $mail->setSubject( $subject );
                $mail->setBody( $body );
                if ( $tpl->hasVariable( 'message_id' ) )
                {
                    $mail->addExtraHeader( 'Message-ID', $tpl->variable( 'message_id' ) );
                }
                if ( $tpl->hasVariable( 'references' ) )
                {
                    $mail->addExtraHeader( 'References', $tpl->variable( 'references' ) );
                }
                if ( $tpl->hasVariable( 'reply_to' ) )
                {
                    $mail->addExtraHeader( 'In-Reply-To', $tpl->variable( 'reply_to' ) );
                }
                if ( $tpl->hasVariable( 'from' ) )
                {
                    $mail->setSenderText( $tpl->variable( 'from' ) );
                }
                if ( $tpl->hasVariable( 'content_type' ) )
                {
                    $mail->setContentType( $tpl->variable( 'content_type' ) );
                }
                $mailResult = eZMailTransport::send( $mail );

                return $mailResult;

            }
            else
            {
                eZDebug::writeError( "Template {$templatePath} returns empty content", __METHOD__ );
            }
        }
        else
        {
            eZDebug::writeError( "Mail addresses not found", __METHOD__ );
        }

        return false;
    }

}