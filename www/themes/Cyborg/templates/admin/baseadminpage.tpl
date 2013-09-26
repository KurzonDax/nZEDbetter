<!DOCTYPE html>
<html lang="en">
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{$page->meta_title}{if $site->metatitle != ""} - {$site->metatitle}{/if}</title>
    <meta name="keywords" content="{$page->meta_keywords}{if $site->metakeywords != ""},{$site->metakeywords}{/if}">
    <meta name="description" content="{$page->meta_description}{if $site->metadescription != ""} - {$site->metadescription}{/if}">
    <meta name="application-name" content="nZEDb-v{$site->version}">
    <meta name="viewport" content="width=device-width">

    <link href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/bootstrap-combined.no-icons.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Merriweather+Sans:400,400italic,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Istok+Web:400,700,400italic' rel='stylesheet' type='text/css'>
    <link href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/old-style.css" rel="stylesheet" media="screen">
    <link href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/admin.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/tabs.css" TYPE="text/css" MEDIA="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/jqui-admin-default/jquery-ui-1.10.3.custom.css" TYPE="text/css" MEDIA="screen">

    <link rel="shortcut icon" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/images/favicon.ico">


    <!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <script>window.html5 || document.write('<script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/vendor/html5shiv.js"><\/script>')</script>
    <![endif]-->


    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/tabber.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    {literal}<script>window.jQuery || document.write('<script src="{/literal}{$smarty.const.WWW_TOP}{literal}/../themes/{$site->style}/scripts/vendor/jquery-1.9.1.js"><\/script>')</script>{/literal}
    {*<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    {literal}<script>window.jQuery || document.write('<script src="{/literal}{$smarty.const.WWW_TOP}{literal}/../themes/alpha/scripts/vendor/bootstrap.min.js"><\/script>')</script>{/literal}*}
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/sorttable.js"></script>
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/utils-admin.js"></script>
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/jquery.multifile.js"></script>
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/jquery-ui-1.10.3.custom.js"></script>
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/jquery.jeditable.js"></script>
    <script>var WWW_TOP = "{$smarty.const.WWW_TOP}/..";</script>

	{$page->head}
</head>
<body>
	<div id="logo" style="cursor: pointer;">
		<h1>
			<a href="/"></a>
		</h1>
		<p> <em></em>
		</p>
	</div>
	<hr>

	<div id="header">
		<div id="menu"></div>
		<!-- end #menu -->
	</div>

	<div id="page">

		<div id="adpanel"></div>

        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

		<div id="content">{$page->content}</div>
		<!-- end #content -->

		<div id="sidebar">
			<ul>
				<li>{$admin_menu}</li>

			</ul>
		</div>
		<!-- end #sidebar -->

		<div style="clear: both;">&nbsp;</div>

	</div>
	<!-- end #page -->
    {*{literal}
    <script type="text/javascript">

        /* Since we specified manualStartup=true, tabber will not run after
         the onload event. Instead let's run it now, to prevent any delay
         while images load.
         */

        tabberAutomatic(tabberOptions);

    </script>
    {/literal}*}


    {if $site->google_analytics_acc != ''}
    {literal}
    <script>
    /* <![CDATA[ */
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', '{/literal}{$site->google_analytics_acc}{literal}']);
      _gaq.push(['_trackPageview']);
      _gaq.push(['_trackPageLoadTime']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    /* ]]> */
    </script>
    {/literal}{/if}
</body>
</html>