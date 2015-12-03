<h2>Editorial Dashboard</h2>
{def $factories = ezini( 'AvailableFactories', 'Identifiers', 'editorialstuff.ini' )}
<ul class="list-unstyled">
{foreach $factories as $factory}
  {def $name = $factory}
    {if ezini_hasvariable( $factory, 'Name', 'editorialstuff.ini' )}
      {set $name = ezini( $factory, 'Name', 'editorialstuff.ini' )}
    {/if}
    <li><a href="{concat('editorialstuff/dashboard/',$factory)|ezurl(no)}">{$name|wash()}</a></li>
  {undef $name}
{/foreach}
</ul>