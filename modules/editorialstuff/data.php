<?php
/** @var eZModule $module */
$module = $Params['Module'];
$factoryIdentifier = $Params['FactoryIdentifier'];
$id = $Params['ID'];
$templatePath = str_replace( ':', '/', $Params['TemplatePath'] );
$data = null;
$odg = null;
try
{
    /** @var OCEditorialStuffPostInterface $post */
    $post = OCEditorialStuffHandler::instance( $factoryIdentifier, $_GET )->fetchByObjectId( $id );
    if ( $post->getObject()->attribute( 'can_read' ) )
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'post', $post );
        if ( strpos( $templatePath, '/' ) > 0 )
        {
            $templatePath = $post->getFactory()->getTemplateDirectory() . '/' . $templatePath;
        }
        else
        {
            $templatePath = ltrim( $templatePath, '/' );
        }
        $data = $tpl->fetch( 'design:' . $templatePath . '.tpl' );
        
        
        if ( class_exists( 'ezxFormToken' ) &&  method_exists( 'ezxFormToken', 'getToken' ) )
        {
            $field = ezxFormToken::FORM_FIELD;
            $token = ezxFormToken::getToken();
            $data = preg_replace(
                '/(<form\W[^>]*\bmethod=(\'|"|)POST(\'|"|)\b[^>]*>)/i',
                '\\1' . "\n<input type=\"hidden\" name=\"{$field}\" value=\"{$token}\" />\n",
                $data
            );
        }
    }
}
catch ( Exception $e )
{
    $data = $e->getMessage();
}

echo $data;
if ( eZHTTPTool::instance()->hasGetVariable( 'debug' ) ) eZDisplayDebug();
eZExecution::cleanExit();