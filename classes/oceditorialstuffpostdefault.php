<?php

class OCEditorialStuffPostDefault extends OCEditorialStuffPostChangeStateDeferred implements OCEditorialStuffPostMediaInterface
{
    protected $attributeMapKeys = array(
        'images',
        'videos',
        'audios',
        'tags'
    );

    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tabs = array(
            array(
                'identifier' => 'content',
                'name' => ezpI18n::tr('editorialstuff/dashboard', 'Content'),
                'template_uri' => "design:{$templatePath}/parts/content.tpl"
            )
        );
        $access = $currentUser->hasAccessTo( 'editorialstuff', 'media' );
        if ( $access['accessWord'] == 'yes' && in_array( 'image', $this->factory->attributeIdentifiers() ) )
        {
            $tabs[] = array(
                'identifier' => 'media',
                'name' => ezpI18n::tr('editorialstuff/dashboard', 'Media'),
                'template_uri' => "design:{$templatePath}/parts/media.tpl"
            );
        }

        $access = $currentUser->hasAccessTo( 'editorialstuff', 'mail' );
        if ( $access['accessWord'] == 'yes' )
        {
            $tabs[] = array(
                'identifier' => 'mail',
                'name' => ezpI18n::tr('editorialstuff/dashboard', 'Mail'),
                'template_uri' => "design:{$templatePath}/parts/mail.tpl"
            );
        }
        $access = $currentUser->hasAccessTo( 'push', '*' );
        if ( eZINI::instance( 'ngpush.ini' )->hasVariable( 'PushNodeSettings', 'Blocks' ) && $access['accessWord'] == 'yes' )
        {
            $tabs[] = array(
                'identifier' => 'social',
                'name' => ezpI18n::tr('editorialstuff/dashboard', 'Social Network'),
                'template_uri' => "design:{$templatePath}/parts/social.tpl"
            );
        }
        $tabs[] = array(
            'identifier' => 'history',
            'name' => ezpI18n::tr('editorialstuff/dashboard', 'History'),
            'template_uri' => "design:{$templatePath}/parts/history.tpl"
        );
        return $tabs;
    }

    public function contentAttributes()
    {
        $data = array();
        $configuration = $this->getFactory()->getConfiguration();
        if ( isset( $configuration['AttributeIdentifiers'] ) )
        {
            foreach( $this->dataMap as $identifier => $attribute )
            {
                if ( !in_array( $identifier, $configuration['AttributeIdentifiers'] ) )
                {
                    $data[$identifier] = $attribute;
                }
            }
        }
        else
        {
            $data = $this->dataMap;
        }
        return $data;
    }

    public function attributes()
    {
        return array_merge( parent::attributes(), $this->attributeMapKeys );
    }

    public function attribute( $property )
    {
        if ( in_array( $property, $this->attributeMapKeys )
             && isset( $this->dataMap[$this->attributeIdentifier( $property )] )
             && $this->dataMap[$this->attributeIdentifier( $property )] instanceof eZContentObjectAttribute )
        {
            if ( $this->dataMap[$this->attributeIdentifier( $property )]->hasContent() )
            {
                return $this->dataMap[$this->attributeIdentifier( $property )];
            }
            else
            {
                eZDebug::writeError( "Object attribute '{$this->attributeIdentifier( $property )}' is empty" );
            }
        }
        return parent::attribute( $property );
    }

    protected function attributeIdentifier( $identifier )
    {
        $attributeIdentifiers = $this->factory->attributeIdentifiers();
        if ( isset( $attributeIdentifiers[$identifier] ) )
            return $attributeIdentifiers[$identifier];
        return false;
    }

    public function addImage( eZContentObject $object )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'images' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'images' )]->toString() );
            $ids[] = $object->attribute( 'id' );
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'addimage', array( 'object_id' => $object->attribute( 'id' ), 'name' => $object->attribute( 'name' ) ) );
        }
    }

    public function makeDefaultImage( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'images' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'images' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_merge( array( $objectId ), array_unique( $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'defaultimage', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }

    public function removeImage( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'images' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'images' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'images' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'removeimage', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }

    public function addVideo( eZContentObject $object )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'videos' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'videos' )]->toString() );
            $ids[] = $object->attribute( 'id' );
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'addvideo', array( 'object_id' => $object->attribute( 'id' ), 'name' => $object->attribute( 'name' ) ) );
        }
    }

    public function removeVideo( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'videos' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'videos' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'videos' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'removevideo', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }

    public function addAudio( eZContentObject $object )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'audios' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'audios' )]->toString() );
            $ids[] = $object->attribute( 'id' );
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'audios' )]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'audios' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'addaudio', array( 'object_id' => $object->attribute( 'id' ), 'name' => $object->attribute( 'name' ) ) );
        }
    }

    public function removeAudio( $objectId )
    {
        if ( isset( $this->dataMap[$this->attributeIdentifier( 'audios' )] ) )
        {
            $ids = explode( '-', $this->dataMap[$this->attributeIdentifier( 'audios' )]->toString() );
            foreach( $ids as $index => $id )
            {
                if ( $id == $objectId )
                {
                    unset( $ids[$index] );
                }
            }
            $ids = array_unique( $ids );
            $this->dataMap[$this->attributeIdentifier( 'audios')]->fromString( implode( '-', $ids ) );
            $this->dataMap[$this->attributeIdentifier( 'audios' )]->store();
            eZContentCacheManager::clearObjectViewCacheIfNeeded( $this->object->attribute( 'id' ) );
            $objectIdName = eZContentObject::fetch( $objectId );
            if ( $objectIdName instanceof eZContentObject )  $objectIdName = $objectIdName->attribute( 'name' );
            OCEditorialStuffHistory::addHistoryToObjectId( $this->object->attribute( 'id' ), 'removeaudio', array( 'object_id' => $objectId, 'name' => $objectIdName ) );
        }
    }
}
