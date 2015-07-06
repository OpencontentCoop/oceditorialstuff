<?php

class EditorialStuffType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "editorialstuff";

    public function __construct()
    {
        parent::eZWorkflowEventType( EditorialStuffType ::WORKFLOW_TYPE_STRING, 'Workflow Editorialstuff' );
    }

    /**
     * @param eZWorkflowProcess $process
     * @param eZEvent $event
     *
     * @return int
     */
    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        if ( $parameters['trigger_name'] == 'post_publish'
             || $parameters['trigger_name'] == 'pre_publish'
             || $parameters['trigger_name'] == 'pre_delete' )
        {
            if ( isset( $parameters['object_id'] ) )
            {
                $object = eZContentObject::fetch( $parameters['object_id'] );
                if ( $object instanceof eZContentObject )
                {
                    foreach ( OCEditorialStuffHandler::instances() as $instance )
                    {
                        if ( $object->attribute( 'class_identifier' ) == $instance->getFactory()->classIdentifier() )
                        {
                            try
                            {
                                $post = $instance->getFactory()->instancePost( array( 'object_id' => $object->attribute( 'id' ) ) );
                                if ( $parameters['trigger_name'] == 'pre_delete' )
                                {
                                    eZDebug::writeNotice( 'Call onRemove for object ' . get_class( $post ), __METHOD__  );
                                    $post->onRemove();
                                }
                                elseif ( $parameters['trigger_name'] == 'pre_publish' )
                                {
                                    if ( $object->attribute( 'current_version' ) == 1 )
                                    {
                                        eZDebug::writeNotice( 'Call onBeforeCreate for object ' . get_class( $post ), __METHOD__  );
                                        $post->onBeforeCreate();
                                    }
                                    else
                                    {
                                        eZDebug::writeNotice( 'Call onBeforeUpdate for object ' . get_class( $post ), __METHOD__  );
                                        $post->onBeforeUpdate();
                                    }
                                }
                                elseif ( $parameters['trigger_name'] == 'post_publish' )
                                {
                                    if ( $object->attribute( 'current_version' ) == 1 )
                                    {
                                        eZDebug::writeNotice( 'Call onCreate for object ' . get_class( $post ), __METHOD__  );
                                        $post->onCreate();
                                    }
                                    else
                                    {
                                        eZDebug::writeNotice( 'Call onUpdate for object ' . get_class( $post ), __METHOD__  );
                                        $post->onUpdate();
                                    }
                                }
                            }
                            catch ( Exception $e )
                            {
                                eZDebug::writeError( $e->getMessage(), __METHOD__  );
                            }
                        }
                    }
                }
            }
        }
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}
eZWorkflowEventType::registerEventType( EditorialStuffType ::WORKFLOW_TYPE_STRING, 'EditorialStuffType' );
?>