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
    <meta name="application-name" content="nZEDbetter-v{$site->version}">
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href='http://fonts.googleapis.com/css?family=Roboto:400,500,700,400italic,500italic,900' type='text/css' MEDIA="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/Bootstrap-3.0.0/css/bootstrap.css" MEDIA="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.css" MEDIA="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/font-awesome/css/font-awesome.css" MEDIA="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/admin.css" MEDIA="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/plugins/tabs.css" TYPE="text/css" MEDIA="screen">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/plugins/jquery.pnotify.default.css" TYPE="text/css" MEDIA="all">
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/admin-new.css" TYPE="text/css" MEDIA="screen">
    <link rel="shortcut icon" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/images/favicon.ico">

    <!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <script>window.html5 || document.write('<script src="{$smarty.const.WWW_TOP}/../themes/Default/scripts/vendor/html5shiv.js"><\/script>')</script>
    <![endif]-->

    <script type="text/javascript" src="//code.jquery.com/jquery-1.10.1.min.js"></script>

    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/Bootstrap-3.0.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/sorttable.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/utils-admin.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/jquery.multifile.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/plugins/tabber.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/plugins/jquery.pnotify.js"></script>

    <script>var WWW_TOP = "{$smarty.const.WWW_TOP}/..";</script>

    {$page->head}
</head>
<body>
<div id="topbar">
    <div class="container">
        <div id="top-nav">
            <a href="../index.php"><i class="icon-home"></i> Return to Main Site</a>
            <ul class="pull-right">
                <li><i class="icon-user"></i> Logged in as {$username}</li>
                <li><a href="{$smarty.const.WWW_TOP}/../logout">Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<div id="header">
    <a href="../index.php" class="logo">Admin Area</a>

    <div class="nav-collapse">
        <ul id="main-nav" class="nav pull-right">
            <li class="nav-icon">
                <a href="./index.php">
                    <i class="icon-home"></i>
                    <span>Home</span>
                </a>
            </li>

            <li class="dropdown active">
                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-cog"></i>
                    <span>Settings</span>
                    <b class="caret"></b>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="./site-edit.php">Site Settings</a></li>
                    <li><a href="./tmux-edit.php">Script Settings</a></li>
                    <li><a href="./group-list.php">News Groups</a></li>
                    <li><a href="./category-list.php">Categories</a></li>
                    <li><a href="./binaryblacklist-list.php">Blacklists</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-group"></i>
                    <span>Users and Roles</span>
                    <b class="caret"></b>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="./role-list.php">Roles</a></li>
                    <li><a href="./user-list.php">Users</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-sitemap"></i>
                    <span>Content</span>
                    <b class="caret"></b>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="./menu-list.php">Menu Items</a></li>
                    <li><a href="./content-list.php">Content Pages</a></li>
                </ul>
            </li>
        </ul>

    </div>
    <!-- end #menu -->
</div>
<div id="masthead">

    <div class="container">

        <div class="masthead-pad">

            <div class="masthead-text">
                <h2>{$page->title}</h2>

            </div> <!-- /.masthead-text -->

        </div>

    </div> <!-- /.container -->

</div> <!-- /#masthead -->
<div id="page">


    <!--[if lt IE 7]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
    <![endif]-->

    <div id="content">{$page->content}</div>
    <!-- end #content -->

    {* <div id="sidebar">
        <ul>
            <li>{$admin_menu}</li>

        </ul>
    </div>*}
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