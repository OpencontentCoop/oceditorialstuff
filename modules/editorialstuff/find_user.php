<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$result = array();
if ( $http->hasVariable( 'q' ) )
{
    $query = $http->getVariable( 'q' );
    $solrSearch = new eZSolr();    
    $search = $solrSearch->search( $query, array(
        'SearchContentClassID' => eZUser::fetchUserClassNames(),
        'SearchSubTreeArray' => array( 1 )
    ) );
    if ( $search['SearchCount'] > 0 )
    {
        foreach( $search['SearchResult'] as $item )
        {
            $user = eZUser::fetch( $item->attribute( 'contentobject_id' ) );
            if ( $user instanceof eZUser )
            {
                $result[] = $user->attribute( 'email' );
            }
        }
    }
}
header('Content-Type: application/json');
echo json_encode( $result );
eZExecution::cleanExit();