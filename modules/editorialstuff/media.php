<?php
/** @var eZModule $module */
$module = $Params['Module'];

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );

$objectID = $Params['ObjectID'];
$action = $Params['Action'];
$param1 = $Params['Param1'];
$param2 = $Params['Param2'];
$http = eZHTTPTool::instance();

$object = eZContentObject::fetch( $objectID );
if ( $object instanceof eZContentObject )
{
    $post = $handler->fetchByObjectId( $objectID );

    if ( $post instanceof OCEditorialStuffPostMediaInterface )
    {

        if ( $action == 'upload' )
        {
            $response = array();
            $siteaccess = eZSiteAccess::current();
            $options['upload_dir'] = eZSys::cacheDirectory() . '/fileupload/';
            $options['download_via_php'] = true;
            $options['param_name'] = "MediaFile";
            $options['image_versions'] = array();
            $options['max_file_size'] = $http->variable( "upload_max_file_size", null );

            /** @var UploadHandler $uploadHandler */
            $uploadHandler = new UploadHandler( $options, false );
            $data = $uploadHandler->post( false );
            foreach( $data[$options['param_name']] as $file )
            {
                $filePath = $options['upload_dir'] . $file->name;
                $upload = new eZContentUpload();
                $upload->handleLocalFile( $response, $filePath, 'auto', false );
            }

            $file = eZClusterFileHandler::instance( $filePath );
            if ( $file->exists() ) $file->delete();

            $newObject = false;
            if ( isset( $response['contentobject'] ) && $response['contentobject'] instanceof eZContentObject )
            {
                $newObject = $response['contentobject'];
            }
            elseif ( isset( $response['contentobject_id'] ) )
            {
                $newObject = eZContentObject::fetch( $response['contentobject_id'] );
            }
            if ( $newObject instanceof eZContentObject )
            {
                $mimeData = eZMimeType::findByFileContents( $filePath );
                $mime = $mimeData['name'];
                list( $group, $type ) = explode( '/', $mime );
                switch( $group )
                {
                    case 'image':
                        $post->addImage( $newObject );
                        break;

                    case 'video':
                        $post->addVideo( $newObject );
                        break;

                    case 'audio':
                        $post->addAudio( $newObject );
                        break;

                    default:
                        eZContentObjectOperations::remove( $newObject->attribute( 'id' ), false );
                        $name = basename( $filePath );
                        $response['errors'][] = array( 'description' => "Tipo di file {$name} non riconosciuto" );
                }
            }
            else
            {
                $response['errors'][] = array( 'description' => 'Errore nella creazione del nuovo contenuto' );
            }

            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'post', $post );
            $tpl->setVariable( 'factory_identifier', $factoryIdentifier );
            $response['content'] = $tpl->fetch( "design:{$handler->getFactory()->getTemplateDirectory()}/parts/media/data.tpl" );

            if ( count( $response['errors'] ) )
            {
                eZLog::write( var_export( $response['errors'], 1 ), 'stampa_upload_errors.log' );
            }
            header('Content-Type: application/json');
            echo json_encode( $response );
            eZExecution::cleanExit();
        }
        elseif ( $action == 'browse' )
        {
            if ( $http->hasPostVariable( 'BrowseActionName' ) && $http->postVariable( 'BrowseActionName' ) == 'MultiUploadBrowse' )
            {
                $selectedArray = $http->postVariable( 'SelectedObjectIDArray' );
                foreach( $selectedArray as $selectedId )
                {
                    $selectObject = eZContentObject::fetch( $selectedId );
                    if ( $selectObject instanceof eZContentObject )
                    {
                        switch ( $selectObject->attribute( 'class_identifier' ) )
                        {
                            case 'image':
                                $post->addImage( $selectObject );
                                break;

                            case 'video':
                                $post->addVideo( $selectObject );
                                break;

                            case 'audio':
                                $post->addAudio( $selectObject );
                                break;
                        }
                    }
                }
            }
            else
            {
                eZContentBrowse::browse( array( 'action_name' => 'MultiUploadBrowse',
                                                'selection' => 'multiple',
                                                'return_type' => 'ObjectID',
                                                'from_page' => '/editorialstuff/media/' . $factoryIdentifier . '/browse/' . $objectID,
                                                'class_array' => array( 'image', 'audio', 'video', 'file' ),
                                                'start_node' => eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ),
                                                'cancel_page' => '/editorialstuff/edit/' . $factoryIdentifier . '/' . $objectID . '#tab_media' ),
                                    $module );
                return;
            }
        }
        elseif ( $action == 'updatepriority' )
        {
            if ( $param1 == 'image' )
            {
                $post->makeDefaultImage( $param2 );
            }
        }
        elseif ( $action == 'remove' )
        {
            if ( $param1 == 'image' )
            {
                $post->removeImage( $param2 );
            }
            elseif ( $param1 == 'audio' )
            {
                $post->removeImage( $param2 );
            }
            elseif ( $param1 == 'video' )
            {
                $post->removeImage( $param2 );
            }
        }
        elseif ( $action == 'edit' && is_numeric( $param2 ) )
        {
            $editObject = eZContentObject::fetch( $param2 );
            if ( $editObject instanceof eZContentObject )
            {
                $http->setSessionVariable( 'RedirectURIAfterPublish', '/editorialstuff/edit/' . $factoryIdentifier . '/' . $objectID . '#tab_media' );
                $http->setSessionVariable( 'RedirectIfDiscarded', '/editorialstuff/edit/' . $factoryIdentifier . '/' . $objectID . '#tab_media' );
                $module->redirectTo( 'content/edit/' . $editObject->attribute( 'id' ) . '/f/' . $editObject->attribute( 'current_language' ) );
                return;
            }
        }
        eZContentCacheManager::clearContentCacheIfNeeded( $objectID );
        $module->redirectTo( '/editorialstuff/edit/' . $factoryIdentifier . '/' . $objectID . '#tab_media' );
    }
    else
    {
        return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
}
else
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}
