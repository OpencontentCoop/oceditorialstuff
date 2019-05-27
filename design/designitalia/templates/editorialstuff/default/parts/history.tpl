<div class="panel-body">
    <div class="table-responsive">
        <table class="Table Table--striped Table--hover Table--withBorder">
            <thead>
                <tr class="u-border-bottom-xs">
                    <th class="u-textLeft">Data</th>
                    <th class="u-textLeft">Autore</th>
                    <th class="u-textLeft">Azione</th>
                </tr>
            </thead>
            <tbody>
                {foreach $post.history as $time => $history_items}
                    {foreach $history_items as $item}
                        <tr>
                            <td class="u-textLeft">{$time|l10n( shortdatetime )}</td>
                            <td class="u-textLeft">{fetch( content, object, hash( 'object_id', $item.user ) ).name|wash()}</td>
                            <td class="u-textLeft">
                                {include uri=concat( 'design:editorialstuff/history/', $item.action,'.tpl')}
                                {*$item.action|wash()} {if $item.parameters|count()}{foreach $item.parameters as $name => $value}{$name|wash()}: {$value|wash()} {/foreach}{/if*}
                            </td>
                        </tr>
                    {/foreach}
                {/foreach}
            </tbody>
        </table>
    </div>
</div>