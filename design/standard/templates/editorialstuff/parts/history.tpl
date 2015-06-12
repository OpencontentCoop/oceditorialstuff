<div class="panel-body" style="background: #fff">
  <div class="table-responsive">
	<table class="table table-striped">
	  <tr>    
		  <th>Data</th>
		  <th>Autore</th>
		  <th>Azione</th>        
	  </tr>
	  
	  {foreach $post.history as $time => $history_items}    
		{foreach $history_items as $item}    
		<tr>          
		  <td>{$time|l10n( shortdatetime )}</td>
		  <td>{fetch( content, object, hash( 'object_id', $item.user ) ).name|wash()}</td>
		  {switch match=$item.action}
			
			{case match='createversion'}
			  <td>Creata versione <a href={concat( '/content/versionview/', $post.object.id, '/', $item.parameters.version )|ezurl}">{$item.parameters.version}</a> del contenuto</td>
			{/case}
			
			{case match='updateobjectstate'}
			  <td>Modificato stato da {cond( and( is_set( $item.parameters.before_state_name ), $item.parameters.before_state_name|null|not() ), $item.parameters.before_state_name, $item.parameters.before_state_id )} a {cond( and( is_set($item.parameters.after_state_name), $item.parameters.after_state_name|null|not() ), $item.parameters.after_state_name, $item.parameters.after_state_id )}</td>
			{/case}
			
			{case match='addimage'}
			  <td>Aggiunta immagine {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}</td>
			{/case}
			
			{case match='removeimage'}
			  <td>Rimossa immagine {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}</td>
			{/case}
			
			{case match='addvideo'}
			  <td>Aggiunto video {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}</td>
			{/case}
			
			{case match='removevideo'}
			  <td>Rimosso video {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}</td>
			{/case}
			
			{case match='addaudio'}
			  <td>Aggiunto audio {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}</td>
			{/case}

			{case match='removeaudio'}
			  <td>Rimosso audio {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}</td>
			{/case}
      
            {case match='defaultimage'}
			  <td>Impostata immagine default {cond( and( is_set( $item.parameters.name ), $item.parameters.name|null|not() ), $item.parameters.name, $item.parameters.object_id )}</td>
			{/case}

			{case}{/case}
			
		  {/switch}
		</tr>    
		{/foreach}
	  {/foreach}
	</table>
  </div>
</div>