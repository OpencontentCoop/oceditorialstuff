<tr>
    <th style="text-align;left">{'Dashboard'|i18n('editorialstuff/mail')}</th>
    <td>
        <a href="{$post.editorial_url|ezurl(no,full)}">{$post.object.name|wash()}</a>
    </td>
</tr>

<tr>
    <th style="text-align;left">{'Author'|i18n('editorialstuff/mail')}</th>
    <td>
        {if $post.object.owner}{$post.object.owner.name|wash()}{else}?{/if}
    </td>
</tr>
<tr>
    <th style="text-align;left">{'Publication date'|i18n('editorialstuff/mail')}</th>
    <td>
        <p>{$post.object.published|l10n(shortdatetime)}</p>
        {if $post.object.current_version|gt(1)}
            <small>{'Last editor'|i18n('editorialstuff/dashboard')} {$post.object.main_node.creator.name} {$post.object.modified|l10n(shortdatetime)}</small>
        {/if}
    </td>
</tr>

{if is_set($factory_configuration.NotificationAttributeIdentifiers)}

    {foreach $post.object.data_map as $identifier => $attribute}
        {if $factory_configuration.NotificationAttributeIdentifiers|contains( $identifier )}
            <tr>
                <th style="text-align;left">{$attribute.contentclass_attribute_name}</th>
                <td>
                    {attribute_view_gui attribute=$attribute image_class=medium}
                </td>
            </tr>
        {/if}
    {/foreach}

{else}

    {foreach $post.object.data_map as $identifier => $attribute}
        {if array('ezstring','eztext','ezxmltext','ezinteger')|contains( $attribute.data_type_string )}
        <tr>
        <th style="text-align;left">{$attribute.contentclass_attribute_name}</th>
        <td>
            {attribute_view_gui attribute=$attribute image_class=medium}
        </td>
        </tr>
        {/if}
    {/foreach}

{/if}