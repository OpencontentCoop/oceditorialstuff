<?php

class EditorialStuffIndexPlugin implements ezfIndexPlugin
{
    /** @var  eZContentObject */
    protected $object;

    protected $availableLanguages;

    public function modify( eZContentObject $object, &$docList )
    {
        $this->object = $object;
        foreach ( OCEditorialStuffHandler::instances() as $instance )
        {
            if ( $this->object->attribute( 'class_identifier' ) == $instance->getFactory(
                )->classIdentifier()
            )
            {
                foreach( $instance->getFactory()->fields() as $field )
                {
                    if ( $field['index_extra'] )
                    {
                        try
                        {
                            $post = $instance->fetchByObjectId( $object->attribute( 'id' ) );
                            if ( method_exists( $post, $field['index_plugin_call_function'] ) )
                            {
                                $value = $post->{$field['index_plugin_call_function']}();
                                $this->addIndex( $docList, $field['solr_identifier'], $value );
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

    protected function currentAvailableLanguages()
    {
        if ( $this->availableLanguages === null )
        {
            /** @var eZContentObjectVersion $version */
            $version = $this->object->currentVersion();
            if( $version === false )
            {
                return null;
            }
            $this->availableLanguages = $version->translationList( false, false );
        }
        return $this->availableLanguages;
    }

    protected function addIndex( &$docList, $key, $value )
    {
        $availableLanguages = $this->currentAvailableLanguages();
        if ( is_array( $availableLanguages ) )
        {
            foreach ( $availableLanguages as $languageCode )
            {
                if ( $docList[$languageCode] instanceof eZSolrDoc )
                {
                    /** @var eZSolrDoc[] $docList */
                    if ( $docList[$languageCode]->Doc instanceof DOMDocument )
                    {
                        $xpath = new DomXpath( $docList[$languageCode]->Doc );
                        if ( $xpath->evaluate(
                                '//field[@name="' . $key . '"]'
                            )->length == 0
                        )
                        {
                            $docList[$languageCode]->addField( $key, $value );
                        }
                    }
                    elseif ( is_array( $docList[$languageCode]->Doc )
                             && !isset( $docList[$languageCode]->Doc[$key] )
                    )
                    {
                        $docList[$languageCode]->addField( $key, $value );
                    }
                }
            }
        }
    }
}

?>
