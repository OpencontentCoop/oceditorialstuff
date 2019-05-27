{ezscript_require( array( 'jquery.opendataTools.js', 'moment.js' ) )}
<div class="Grid Grid--withGutter u-margin-bottom-m">
    <h1 class="u-text-h2">{$post.object.name|wash()}</h1>

    {include uri=concat('design:', $template_directory, '/parts/workflow.tpl') post=$post}

    <div class="Grid-cell u-size{if is_set( $post.object.data_map.internal_comments )}9{else}12{/if}of12">
        <div id="dashboard-tabs">
            <ul class="nav nav-tabs">
                {foreach $post.tabs as $index=> $tab}
                    <li role="presentation" class="nav-tab{if $index|eq(0)} active{/if}">
                        <a href="#{$tab.identifier}" aria-controls="{$tab.identifier}" data-toggle="tab">{$tab.name}</a>
                    </li>
                {/foreach}
            </ul>

            <div class="tab-content">
                {foreach $post.tabs as $index=> $tab}
                <div class="tab-pane{if $index|eq(0)} active{/if}" id="{$tab.identifier}">
                    {include uri=$tab.template_uri post=$post}
                </div>
                {/foreach}
            </div>

        </div>

    </div>

    {if is_set( $post.object.data_map.internal_comments )}
        <div class="Grid-cell u-size3of12">
            {include uri=concat('design:', $template_directory, '/parts/comments.tpl') post=$post}
        </div>
    {/if}

</div>

<div class="Dialog js-fr-dialogmodal" id="preview">
    <div class="
      Dialog-content
      Dialog-content--centered
      u-background-white
      u-layout-medium
      u-margin-all-xl
      u-padding-all-xl
      js-fr-dialogmodal-modal" aria-labelledby="modal-title">
    <div class="modal-content" role="document"></div>
</div>

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'jquery.editorialstuff_default.js' ) )}
<script type="text/javascript">
    {literal}
    $(document).ready(function() {
        var hash = document.location.hash;
        var prefix = "tab_";
        if (hash) {
            var index = $('.nav-tabs a[href=' + hash.replace(prefix, "") + ']').parent().index();
            $("#dashboard-tabs").tabs({
                active: index,
                activate: function( event, ui ) {
                    event.preventDefault();
                    window.location.hash = event.currentTarget.hash.replace("#", "#" + prefix);
                }
            });
        } else {
            $("#dashboard-tabs").tabs({
                activate: function( event, ui ) {
                    event.preventDefault();
                    window.location.hash = event.currentTarget.hash.replace("#", "#" + prefix);
                }
            });
        }

    });
    {/literal}
</script>

