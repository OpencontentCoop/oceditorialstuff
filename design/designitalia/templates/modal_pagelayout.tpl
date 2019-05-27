<div class="u-margin-bottom-s">
  <a href="{$module_result.uri|explode( '/layout/set/modal' )|implode('')}" class="Button" target="_blank">Visualizza sul sito</a>
  <button class="Button Button--danger js-fr-dialogmodal-close u-floatRight hidden">Chiudi</button>
</div>


{$module_result.content}

{* Codice extra usato da plugin javascript *}
{include uri='design:page_extra.tpl'}