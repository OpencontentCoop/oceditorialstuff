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
        if ( $parameters['trigger_name'] == 'post_publish' || $parameters['trigger_name'] == 'pre_delete' )
        {
            if ( isset( $parameters['object_id'] ) )
            {
                $object = eZContentObject::fetch( $parameters['object_id'] );
                if ( $object instanceof eZContentObject )
                {
                    foreach ( OCEditorialStuffHandler::instances() as $instance )
                    {
                        if ( $object->attribute( 'class_identifier' ) == $instance->getFactory(
                            )->classIdentifier()
                        )
                        {
                            try
                            {
                                $post = $instance->fetchByObjectId( $object->attribute( 'id' ) );
                                if ( $parameters['trigger_name'] == 'post_publish' )
                                {
                                    if ( $object->attribute( 'current_version' ) == 1 )
                                    {
                                        $post->onCreate();
                                    }
                                    else
                                    {
                                        $post->onUpdate();
                                    }
                                }
                                elseif( $parameters['trigger_name'] == 'pre_delete' )
                                {
                                    $post->onRemove();
                                }
                            }
                            catch ( Exception $e )
                            {

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