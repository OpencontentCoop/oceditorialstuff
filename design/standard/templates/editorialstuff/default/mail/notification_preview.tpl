<tr>
    <th style="text-align;left">Link alla dashboard</th>
    <td>
        <a href="{$post.editorial_url|ezurl(no,full)}">{$post.object.name|wash()}</a>
    </td>
</tr>

<tr>
    <th style="text-align;left">Autore</th>
    <td>
        {if $post.object.owner}{$post.object.owner.name|wash()}{else}?{/if}
    </td>
</tr>
<tr>
    <th style="text-align;left">Data di pubblicazione</th>
    <td>
        <p>{$post.object.published|l10n(shortdatetime)}</p>
        {if $post.object.current_version|gt(1)}
            <small>Ultima modifica di {$post.object.main_node.creator.name} il {$post.object.modified|l10n(shortdatetime)}</small>
        {/if}
    </td>
</tr>

{if is_set($factory_configuration.NotificationAttributeIdentifiers)

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