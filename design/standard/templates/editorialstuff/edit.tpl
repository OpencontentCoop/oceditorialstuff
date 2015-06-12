<div class="row">
    <div class="col-md-12">
        <h1>{$post.object.name|wash()}</h1>
        {include uri='design:editorialstuff/parts/workflow.tpl' post=$post}
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-{if is_set( $post.object.data_map.internal_comments )}9{else}12{/if}">

        <div role="tabpanel">

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#content" aria-controls="home"
                       role="tab" data-toggle="tab">Contenuto</a>
                </li>
                {if fetch( 'user', 'has_access_to', hash( 'module', 'editorialstuff', 'function', 'media' ) )}
                    <li role="presentation">
                        <a href="#media" aria-controls="media" role="tab"
                           data-toggle="tab">Media</a></li>
                {/if}
                {if fetch( 'user', 'has_access_to', hash( 'module', 'editorialstuff', 'function', 'mail' ) )}
                    <li role="presentation">
                        <a href="#mail" aria-controls="mail" role="tab"
                           data-toggle="tab">Mail riservata</a></li>
                {/if}
                {if and( ezini_hasvariable('PushNodeSettings', 'Blocks', 'ngpush.ini'), fetch( 'user', 'has_access_to', hash( 'module', 'push', 'function', '*' ) ) )}
                    <li role="presentation">
                        <a href="#social" aria-controls="social" role="tab"
                           data-toggle="tab">Social Network</a></li>
                {/if}
                <li role="presentation">
                    <a href="#history" aria-controls="history" role="tab"
                       data-toggle="tab">Cronologia</a></li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="content">
                    {include uri='design:editorialstuff/parts/content.tpl' post=$post}
                </div>
                {if fetch( 'user', 'has_access_to', hash( 'module', 'editorialstuff', 'function', 'media' ) )}
                    <div role="tabpanel" class="tab-pane" id="media">
                        {include uri='design:editorialstuff/parts/media.tpl' post=$post}
                    </div>
                {/if}
                {if fetch( 'user', 'has_access_to', hash( 'module', 'editorialstuff', 'function', 'mail' ) )}
                    <div role="tabpanel" class="tab-pane" id="mail">
                        {include uri='design:editorialstuff/parts/mail.tpl' post=$post}
                    </div>
                {/if}
                <div role="tabpanel" class="tab-pane" id="history">
                    {include uri='design:editorialstuff/parts/history.tpl' post=$post}
                </div>
                {if and( ezini_hasvariable('PushNodeSettings', 'Blocks', 'ngpush.ini'), fetch( 'user', 'has_access_to', hash( 'module', 'push', 'function', '*' ) ) )}
                    <div role="tabpanel" class="tab-pane" id="social">
                        {include uri='design:editorialstuff/parts/social.tpl' post=$post}
                    </div>
                {/if}
            </div>

        </div>

    </div>

    {if is_set( $post.object.data_map.internal_comments )}
        <div class="col-md-3">
            {include uri='design:editorialstuff/parts/comments.tpl' post=$post}
        </div>
    {/if}

</div>


<div id="preview" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="previewlLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        </div>
    </div>
</div>

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'bootstrap-tabdrop.js' ) )}
<style>{literal}#navigation, .header-searchbox {  display: none  }  .nav-tabs, .nav-pills {  position: relative;  }{/literal}</style>
<script>{literal}
    $(document).ready(function () {
        var touch = false;
        if (window.Modernizr) {
            touch = Modernizr.touch;
        }
        if (!touch) {
            $("body").on("mouseenter", ".has-tooltip", function () {
                var el;
                el = $(this);
                if (el.data("tooltip") === undefined) {
                    el.tooltip({
                        placement: el.data("placement") || "top",
                        container: el.data("container") || "body"
                    });
                }
                return el.tooltip("show");
            });
            $("body").on("mouseleave", ".has-tooltip", function () {
                return $(this).tooltip("hide");
            });
        }
        $('.nav-pills, .nav-tabs').tabdrop();
    });

    var hash = document.location.hash;
    var prefix = "tab_";
    if (hash) {
        $('.nav-tabs a[href=' + hash.replace(prefix, "") + ']').tab('show');
    }
    // Change hash for page-reload
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash.replace("#", "#" + prefix);
    });
    $('[data-load-remote]').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        $($this.data('remote-target')).html('<em>Loading...</em>');
        var remote = $this.data('load-remote');
        if (remote) {
            $($this.data('remote-target')).load(remote);
        }
    });
{/literal}</script>

