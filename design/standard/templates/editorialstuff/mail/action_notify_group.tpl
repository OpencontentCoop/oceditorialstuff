{set-block scope=root variable=reply_to}{fetch(user,current_user).email}{/set-block}
{set-block scope=root variable=message_id}{concat('<node.',$post.object.main_node_id,'.editorialstuff_actionnotify','@',ezini("SiteSettings","SiteURL"),'>')}{/set-block}
{set-block scope=root variable=subject}{'Notification about'|i18n('editorialstuff/mail')} {$post.object.name|wash()}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}

{'The content'|i18n('editorialstuff/mail')} <a href="{$post.editorial_url|ezurl(no,full)}">{$post.object.name|wash()}</a> {'has been changed status'|i18n('editorialstuff/mail')}.
{'The current state is'|i18n('editorialstuff/mail')} {$post.current_state.current_translation.name|wash}