<?php

class OCEditorialStuffMailer
{
    /**
     * @param OCEditorialStuffPostInterface $post
     * @param array $addressArray
     * @param null $message
     * @param null $mailActions
     */
    public static function sendMail( OCEditorialStuffPostInterface $post, array $addressArray, $message = null, $mailActions = null )
    {
        $recipients = array();
        $errors = array();
        $addresses = array();
        if ( !empty( $addressArray ) )
        {
            foreach( $addressArray as $address )
            {
                $address = trim( $address );
                if ( !empty( $address ) )
                {
                    if ( eZMail::validate( $address ) )
                    {
                        $recipients[$address] = array(
                            'address' => $address,
                            'user' => eZUser::fetchByEmail( $address )
                        );
                    }
                    else
                    {
                        $errors[] = "Wrong address $address";
                    }
                }
            }
        }

        if ( empty( $recipients ) )
        {
            $errors[] = "No recipients found";
        }
        else
        {
            $addresses = array();
            foreach( $recipients as $recipient )
            {
                $tpl = eZTemplate::factory();
                $tpl->resetVariables();

                $tpl->setVariable( 'post', $post );
                $tpl->setVariable( 'message', empty( $message ) ? false : $message );
                $tpl->setVariable( 'factory_configuration', $post->getFactory()->getConfiguration() );
                $tpl->setVariable( 'template_directory', $post->getFactory()->getTemplateDirectory() );

                if ( $recipient['user'] )
                {
                    $tpl->setVariable( 'user', $recipient['user'] );
                    $tpl->setVariable( 'current_user_allowed_assign_state_id_list', $post->getObject()->allowedAssignStateIDList( $recipient['user'] ) );
                    $tpl->setVariable( 'add_buttons', (bool) $mailActions );
                }
                else
                {
                    $tpl->setVariable( 'user', false );
                    $tpl->setVariable( 'add_buttons', false );
                    $tpl->setVariable( 'current_user_allowed_assign_state_id_list', array() );
                }

                $subject = $post->getObject()->attribute( 'name' );
                $body = $tpl->fetch( 'design:' . $post->getFactory()->getTemplateDirectory() . '/mail/notification.tpl' );

                if ( $tpl->hasVariable( 'subject' ) )
                {
                    $subject = $tpl->variable( 'subject' );
                }

                $parameters = array();
                if ( $tpl->hasVariable( 'message_id' ) )
                {
                    $parameters['message_id'] = $tpl->variable( 'message_id' );
                }
                if ( $tpl->hasVariable( 'reply_to' ) )
                {
                    $parameters['reply_to'] = $tpl->variable( 'reply_to' );
                }
                $parameters['content_type'] = 'text/html';

                $mail = new eZMail();
                $ini = eZINI::instance();

                $emailSender = eZUser::currentUser()->attribute('email');

                if ( !$emailSender )
                    $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );

                if ( !$emailSender )
                    $emailSender = $ini->variable( "MailSettings", "AdminEmail" );

                $mail->setSender( $emailSender );
                $mail->setSubject( $subject );
                $mail->setBody( $body );
                $mail->setReceiver( $recipient['address'] );

                if ( isset( $parameters['message_id'] ) )
                {
                    $mail->addExtraHeader( 'Message-ID', $parameters['message_id'] );
                }

                if ( isset( $parameters['reply_to'] ) )
                {
                    $mail->addExtraHeader( 'In-Reply-To', $parameters['reply_to'] );
                }

                if ( isset( $parameters['content_type'] ) )
                {
                    $mail->setContentType( $parameters['content_type'] );
                }

                $addresses[] = $recipient['address'];

                $mailResult = eZMailTransport::send( $mail );
                if ( !$mailResult )
                {
                    $errors[] = "Error sending mail";
                }
            }
        }
        OCEditorialStuffHistory::addNotificationHistoryToObjectId( $post->id(), $addresses, $message, $errors );
    }
}