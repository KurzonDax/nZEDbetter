
<!-- saved from url=(0089)https://raw.github.com/nZEDb/nZEDb/master/www/themes/alpha/templates/frontend/apicaps.tpl -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">&lt;?xml version="1.0" encoding="UTF-8" ?&gt;
&lt;caps&gt;
&lt;server appversion="{$site-&gt;version}" version="0.1" title="{$site-&gt;title|escape}" strapline="{$site-&gt;strapline|escape}" email="{$site-&gt;email}" url="{$serverroot}" image="{if $site-&gt;style != "" &amp;&amp; $site-&gt;style != "/"}{$serverroot}templates/{$site-&gt;style}/images/logo.png{else}{$serverroot}templates/Default/images/logo.png{/if}" /&gt;
&lt;limits max="100" default="100"/&gt;

&lt;registration available="yes" open="{if $site-&gt;registerstatus == 0}yes{else}no{/if}" /&gt;
&lt;searching&gt;
&lt;search available="yes"/&gt;
&lt;tv-search available="yes"/&gt;
&lt;movie-search available="yes"/&gt;
&lt;audio-search available="yes"/&gt;
&lt;/searching&gt;
&lt;categories&gt;
{foreach from=$parentcatlist item=parentcat}
&lt;category id="{$parentcat.ID}" name="{$parentcat.title|escape:html}"{if $parentcat.description != ""} description="{$parentcat.description|escape:html}"{/if}&gt;
{foreach from=$parentcat.subcatlist item=subcat}
&lt;subcat id="{$subcat.ID}" name="{$subcat.title|escape:html}"{if $subcat.description != ""} description="{$subcat.description|escape:html}"{/if}/&gt;
{/foreach}
&lt;/category&gt;
{/foreach}
&lt;/categories&gt;
&lt;/caps&gt;
</pre><div class="extLives"></div></body></html>