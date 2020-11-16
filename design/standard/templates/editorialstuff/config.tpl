<div class="row">
    <div class="col-sm-12 px-lg-4">
        <h3>{'Mail customizations'|i18n('editorialstuff/dashboard')}</h3>
        <p class="lead">{'If the field is empty, the default text will be sent'|i18n('editorialstuff/dashboard')}</p>
        <div class="bg-light p-3 rounded mb-3">
            <h6>{'Placeholders:'|i18n('editorialstuff/dashboard')}</h6>
            <dl class="row m-0">
                {foreach $placeholders as $placeholder => $help_text}
                <dt class="col-sm-4">{$help_text|wash()}</dt><dd class="col-sm-6"><code>{$placeholder|wash()}</code></dd>
                {/foreach}
            </dl>
        </div>
        <form class="form" action="{concat('editorialstuff/config/', $factory_identifier)|ezurl(no)}" method="post">
            <table class="table list table-striped">
                <tr>
                    <th style="white-space: nowrap;vertical-align: middle" width="1">{'Before state'|i18n('editorialstuff/dashboard')}</th>
                    <th style="white-space: nowrap;vertical-align: middle" width="1">{'After state'|i18n('editorialstuff/dashboard')}</th>
                    <th style="white-space: nowrap;vertical-align: middle">{'Message'|i18n('editorialstuff/dashboard')}</th>
                </tr>
                {foreach $actions as $action}
                <tr>
                    <td style="white-space: nowrap;vertical-align: middle">{$action.before.current_translation.name|wash()}</td>
                    <td style="white-space: nowrap;vertical-align: middle">{$action.after.current_translation.name|wash()}</td>
                    <td>
                        <small>{$action.call_function|wash()}</small>
                        <div class="form-group">
                            <label for="messages-{$action.identifier|wash()}-subject">{'Subject'|i18n('editorialstuff/dashboard')}</label>
                            <input class="form-control box" type="text" id="messages-{$action.identifier|wash()}-subject" name="Subjects[{$action.identifier|wash()}]" value="{$action.subject|wash()}">
                        </div>
                        <div class="form-group">
                            <label style="width: 100%" class="w-100" for="messages-{$action.identifier|wash()}-body">{'Body'|i18n('editorialstuff/dashboard')}</label>
                            <textarea class="form-control box message-editor" rows="10" id="messages-{$action.identifier|wash()}-body" name="Messages[{$action.identifier|wash()}]">{$action.message}</textarea>
                        </div>
                    </td>
                </tr>
                {/foreach}
            </table>
            <p class="text-right">
                <input type="submit" class="defaultbutton btn btn-success btn-xl" name="Store" value="{'Store'|i18n('ocbootstrap')}"/>
            </p>
        </form>
    </div>
</div>

{ezscript_require(array('ezjsc::jquery', 'summernote-lite.js'))}
{ezcss_require(array('summernote-lite.css'))}
{run-once}
    <script>{literal}
        $(document).ready(function(){
            $('.message-editor').summernote({
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['insert', ['link']]
                ],
                callbacks: {
                    onPaste: function(e) {
                        var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                        e.preventDefault();
                        document.execCommand('insertText', false, bufferText);
                    }
                }
            });
        });
        {/literal}</script>
{/run-once}