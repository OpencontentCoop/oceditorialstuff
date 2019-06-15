<div id="dashboard-filters-container">
    <form class="form-inline" role="form" method="get"
          action={concat('editorialstuff/dashboard/', $factory_identifier )|ezurl()}>

        <div class="Grid Grid--withGutter u-margin-bottom-m">
            {if $factory_configuration.CreationRepositoryNode}
                <div class="Grid-cell u-size2of6">
                    <a href="{concat('editorialstuff/add/',$factory_identifier)|ezurl(no)}" class="btn btn-primary">{$factory_configuration.CreationButtonText|wash()}</a>
                </div>
            {/if}

            <div class="Grid-cell  u-size1of6">
                <input type="text" class="form-control" name="query" placeholder="{'Search'|i18n('editorialstuff/dashboard')}"
                       value="{$view_parameters.query|wash()}"/>
            </div>

            {if $states|count()}
            <div class="Grid-cell u-size1of6">
                <select class="form-control" name="state" id="dashboard-state-select" style="font-size: .8em;">
                    <option value="">{'All'|i18n('editorialstuff/dashboard')}</option>
                    {foreach $states as $state}
                        <option value="{$state.id}" {if $view_parameters.state|eq($state.id)} selected="selected"{/if} >{$state.current_translation.name|wash}</option>
                    {/foreach}
                </select>
            </div>
            {/if}

            {def $intervals = array(
                hash( 'value', '-P1D', 'name', 'Ultimo giorno' ),
                hash( 'value', '-P1W', 'name', 'Ultimi 7 giorni' ),
                hash( 'value', '-P1M', 'name', 'Ultimi 30 giorni' ),
                hash( 'value', '-P2M', 'name', 'Ultimi 2 mesi' )
            )}

            <div class="Grid-cell u-size1of6">
                <select class="form-control" name="interval" id="dashboard-interval-select"  style="font-size: .8em;">
                    <option value="">{'Period'|i18n('editorialstuff/dashboard')}</option>
                    {foreach $intervals as $interval}
                        <option value="{$interval.value}" {if $view_parameters.interval|eq($interval.value)} selected="selected"{/if}>{$interval.name|wash()}</option>
                    {/foreach}
                </select>
            </div>

            <div class="Grid-cell u-size1of6">
                <button type="submit" class="btn btn-info" id="dashboard-search-button">{'Search'|i18n('editorialstuff/dashboard')}</button>

            </div>
        </div>
    </form>
</div>

{if $post_count|gt(0)}

<div class="editorialstuff u-margin-bottom-xl">
    
    <div class="table-responsive">
    <table class="table table-striped" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <th><small></small></th>
        <th><small>{'Author'|i18n('editorialstuff/dashboard')}</small></th>
        <th><small>{'Date'|i18n('editorialstuff/dashboard')}</small></th>
          {if $states|count()}<th><small>{'State'|i18n('editorialstuff/dashboard')}</small></th>{/if}
        <th><small>{'Title'|i18n('editorialstuff/dashboard')}</small></th>
        {*<th><small></small></th>*}
      </tr>
    {foreach $posts as $post}
      <tr>
          <td class="text-center">            
            <a href="{concat( 'editorialstuff/edit/', $factory_identifier, '/', $post.object.id )|ezurl('no')}" title="{'Detail'|i18n('editorialstuff/dashboard')}" class="btn btn-info">
                {'Detail'|i18n('editorialstuff/dashboard')}
            </a>            
          </td>
          
          <td>
            {if $post.object.owner}{$post.object.owner.name|wash()}{else}?{/if}
          </td>

          {*Data*}
          <td>{$post.object.published|l10n('shortdate')}</td>

          {if $states|count()}
          {*Stato*}
          <td>
            {include uri=concat('design:', $template_directory, '/parts/state.tpl')}
          </td>
          {/if}
          
          <td>
            <a data-toggle="modal" 
               data-load-remote="{concat( 'layout/set/modal/content/view/full/', $post.object.main_node_id )|ezurl('no')}" 
               data-remote-target="#preview .modal-content" href="#{*$post.url*}" 
               data-target="#preview"
               aria-controls="preview" 
               class="js-fr-dialogmodal-open">
               {$post.object.name}
             </a>
          </td>
      </tr>
    {/foreach}
    </table>
    </div>
    
    {include name=navigator
            uri='design:navigator/google.tpl'
            page_uri=concat('editorialstuff/dashboard/', $factory_identifier )
            item_count=$post_count
            view_parameters=$view_parameters
            item_limit=$view_parameters.limit}
    
</div>

{else}
<div class="alert alert-warning  u-margin-bottom-xl">Nessun contenuto</div>
{/if}

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

{ezscript_require( array( 'modernizr.min.js', 'ezjsc::jquery', 'plugins/chosen.jquery.js', 'jquery.editorialstuff_default.js' ) )}
