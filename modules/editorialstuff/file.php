<?php
/** @var eZModule $module */
$module = $Params['Module'];

$factoryIdentifier = $Params['FactoryIdentifier'];
$handler = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET );

$objectID = $Params['ObjectID'];
$action = $Params['Action'];
$attributeIdentifier = $Params['AttributeIdentifier'];
$fileID = $Params['FileID'];
$http = eZHTTPTool::instance();

$object = eZContentObject::fetch( $objectID );
if ( $object instanceof eZContentObject )
{
    $post = $handler->fetchByObjectId( $objectID );

    if ( $post instanceof OCEditorialStuffPostFileContainerInterface )
    {

        if ( $action == 'upload' )
        {
            $response = array();
            $siteaccess = eZSiteAccess::current();
            $options['upload_dir'] = eZSys::cacheDirectory() . '/fileupload/';
            $options['download_via_php'] = true;
            $options['param_name'] = "DocFile";
            $options['image_versions'] = array();
            $options['max_file_size'] = $http->variable( "upload_max_file_size", null );

            /** @var UploadHandler $uploadHandler */
            $uploadHandler = new UploadHandler( $options, false );
            $data = $uploadHandler->post( false );
            foreach( $data[$options['param_name']] as $file )
            {
                $filePath = $options['upload_dir'] . $file->name;
                $response = $post->fileFactory( $attributeIdentifier )->handleFile( $filePath, $http->postVariable( 'FileProperties', array() ), $http->postVariable( 'FileAttributes', array() ) );
                $file = eZClusterFileHandler::instance( $filePath );
                if ( $file->exists() )
                {
                    $file->delete();
                }
            }

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
                if ( !$post->addFile( $newObject, $attributeIdentifier ) )
                {
                    $response['errors'][] = array( 'description' => 'Errore associando il nuovo contenuto al oggetto' );
                }
            }
            else
            {
                $response['errors'][] = array( 'description' => 'Errore nella creazione del nuovo contenuto' );
            }

            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'post', $post );
            $tpl->setVariable( 'factory_identifier', $factoryIdentifier );
            $response['content'] = $tpl->fetch( "design:{$handler->getFactory()->getTemplateDirectory()}/parts/{$post->fileFactory( $attributeIdentifier )->identifier()}/data.tpl" );

            if ( count( $response['errors'] ) )
            {
                eZLog::write( var_export( $response['errors'], 1 ), 'file_upload_errors.log' );
            }
            header('Content-Type: application/json');
            echo json_encode( $response );
            eZExecution::cleanExit();
        }
//        elseif ( $action == 'browse' )
//        {
//            if ( $http->hasPostVariable( 'BrowseActionName' ) && $http->postVariable( 'BrowseActionName' ) == 'MultiUploadBrowse' )
//            {
//                $selectedArray = $http->postVariable( 'SelectedObjectIDArray' );
//                foreach( $selectedArray as $selectedId )
//                {
//                    $selectObject = eZContentObject::fetch( $selectedId );
//                    $post->addFile( $selectObject, $attributeIdentifier );
//                }
//            }
//            else
//            {
//                eZContentBrowse::browse( array( 'action_name' => 'MultiUploadBrowse',
//                                                'selection' => 'multiple',
//                                                'return_type' => 'ObjectID',
//                                                'from_page' => '/consiglio/doc/' . $factoryIdentifier . '/browse/' . $objectID,
//                                                'class_array' => array( 'file_pdf', 'file' ),
//                                                'start_node' => eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ),
//                                                'cancel_page' => '/consiglio/doc/' . $factoryIdentifier . '/' . $objectID . '#tab_doc' ),
//                    $module );
//                return;
//            }
//        }
        elseif ( $action == 'remove' )
        {
            $selectObject = eZContentObject::fetch( $fileID );
            $post->removeFile( $selectObject, $attributeIdentifier );
        }
        elseif ( $action == 'edit' && is_numeric( $fileID ) )
        {
            $editObject = eZContentObject::fetch( $fileID );
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
