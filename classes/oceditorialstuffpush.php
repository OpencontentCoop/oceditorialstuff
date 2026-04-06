<?php

// @phpstan-ignore class.notFound
// @phpstan-ignore class.notFound
class OCEditorialStuffPush extends ezjscoreNGPush
{
    private static $noAccessResponse = array(
		'status' => 'error',
		'message' => 'You do not have access to the requested module.<br /><a href="#" onclick="window.close()">Close this window</a>');

	private static function userHasAccessToModule() {
		$user = eZUser::currentUser();
		if ( $user instanceof eZUser ) {
			$result = $user->hasAccessTo('push');
			if ($result['accessWord'] == 'no') return false;
		}
		return true;
	}
    
    public static function push( $args )
	{
		if (!self::userHasAccessToModule()) return self::$noAccessResponse;

        $message = 'Account not found!';
		$http = eZHTTPTool::instance();
        $factoryIdentifier = $http->postVariable('factoryIdentifier');

        try
        {
            $handler = OCEditorialStuffHandler::instance( $factoryIdentifier );
            if ( $http->hasPostVariable( 'nodeID' ) && $http->hasPostVariable( 'accountID' ) )
            {
                $NGPushIni = eZINI::instance( 'ngpush.ini' );
                $NGPushAccount = $http->postVariable( 'accountID' );
                $NGPushNodeID = $http->postVariable( 'nodeID' );

                switch ( $NGPushIni->variable( $NGPushAccount, 'Type' ) )
                {

                    case 'twitter':
                        $TwitterStatus = $http->postVariable( 'tw_status' );
                        // @phpstan-ignore class.notFound
                        $response = ngPushTwitterStatus::push( $NGPushAccount, $TwitterStatus );
                        try
                        {
                            $post = $handler->fetchByNodeId(
                                $NGPushNodeID
                            );
                            OCEditorialStuffHistory::addSocialHistoryToObjectId(
                                $post->id(),
                                $NGPushIni->variable( $NGPushAccount, 'Type' ),
                                $response
                            );
                        }
                        catch ( Exception $e )
                        {

                        }

                        return $response;
                        break;

                    case 'facebook_feed':
                        $Arguments = array(
                            'name' => $http->postVariable( 'fb_name' ),
                            'description' => $http->postVariable( 'fb_description' ),
                            'message' => $http->postVariable( 'fb_message' ),
                            'link' => $http->postVariable( 'fb_link' ),
                            'picture' => $http->postVariable( 'fb_picture' )
                        );
                        // @phpstan-ignore class.notFound
                        $response = ngPushFacebookFeed::push( $NGPushAccount, $Arguments );
                        try
                        {
                            $post = $handler->fetchByNodeId(
                                $NGPushNodeID
                            );
                            OCEditorialStuffHistory::addSocialHistoryToObjectId(
                                $post->id(),
                                $NGPushIni->variable( $NGPushAccount, 'Type' ),
                                $response
                            );
                        }
                        catch ( Exception $e )
                        {

                        }

                        return $response;
                        break;

                    default:
                        break;
                }
            }
        }
        catch( Exception $e )
        {
            $message = $e->getMessage();
        }

		return array('status' => 'error', 'message' => $message );
	}
}