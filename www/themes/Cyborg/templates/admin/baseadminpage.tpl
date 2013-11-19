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
    <link rel="stylesheet" href="{$smarty.const.WWW_TOP}/../themes/{$site->style}/styles/plugins/redmond.datepick.css" TYPE="text/css" MEDIA="all">
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
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/plugins/jquery.datepick.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/plugins/jquery.datepick.ext.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/plugins/bootstrap-select.min.js"></script>
    <script>var WWW_TOP = "{$smarty.const.WWW_TOP}/..";</script>
    <script type="text/javascript"
            src="http://rawmass.com:8080/s/d41d8cd98f00b204e9800998ecf8427e/en_US-z790hi-1988229788/6158/4/1.4.1/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=3f2554d5"></script>
    {$page->head}
</head>
<body>
<div id="topbar">
    <div class="container">
        <div id="top-nav">
            <a href="../index.php"><i class="icon-home"></i> Return to Main Site</a>
            <span style="margin-left: 15px; color: darkgray;"> <i class="icon-code-fork"></i> Version {$version}</span>
            <ul class="pull-right">
                <li><i class="icon-user"></i> Logged in as {$username}</li>
                <li><a href="{$smarty.const.WWW_TOP}/../logout">Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<div id="header">
    <a href="../index.php" class="logo">Admin Area</a>
    {* <div class="nav-collapse"> *}
    <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul id="main-nav" class="nav navbar-nav pull-right">
            <li class="nav-icon">
                <a href="./index.php">
                    <i class="icon-home"></i>
                    <span>Home</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-cog"></i>
                    Settings
                    <b class="caret"></b>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="site-edit.php">Site Settings</a></li>
                    <li><a href="tmux-edit.php">Script Settings</a></li>
                    <li><a href="group-list.php">Newsgroups</a></li>
                    <li><a href="category-list.php">Categories</a></li>
                    <li><a href="binaryblacklist-list.php">Blacklists</a></li>
                    <li><a id="itemUpdateNewsgroups" class="pointer" data-toggle="modal" data-target="#modalUpdateNewsgroups">Update NNTP List</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-group"></i>
                    <span>Users and Roles</span>
                    <b class="caret"></b>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="role-list.php">Roles</a></li>
                    <li><a href="user-list.php">Users</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-sitemap"></i>
                    <span>Content</span>
                    <b class="caret"></b>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="menu-list.php">Menu Items</a></li>
                    <li><a href="content-list.php">Content Pages</a></li>
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
<div class="modal fade" id="modalUpdateNewsgroups">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update NNTP Newsgroups</h3>
            </div>
            <div class="modal-body">
                <p class="warning-heading">The server needs to download updated newsgroups<br />
                </p>
                <p style="border-bottom: 1px solid #999">
                    <span style="font-size: 13px; color: #333;">
                        The server needs to download an updated copy of the newsgroups hosted by your Usenet Service Provider.  This only needs to happen one time.  If, however,
                        you wish to update the list in the future, just click the Update NNTP List button on the toolbar.  Please wait while this process completes...
                    </span>
                </p>
                <p id="modalUpdateNewsGroupsStatus" class="hidden"></p>
            </div> <!-- modal-body Final Tag -->
            <div class="modal-footer hidden">
                <a href="javascript:;" class="btn btn-tertiary" data-dismiss="modal">Close</a>
            </div>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div> <!-- modal main tag -->
{literal}
    <script>
        $("#itemUpdateNewsgroups").click( function() {
            $("#modalUpdateNewsgroups").find('.modal-footer').addClass('hidden');
            $("#modalUpdateNewsGroupsStatus").addClass('hidden').html('');
            $("#modalUpdateNewsgroups").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"})
                    .modal("show");
            $.ajax({
                url       : WWW_TOP + '/admin/ajax-group-ops.php',
                data      : "action=getnewsgroups",
                dataType  : "html",
                type      : "POST",
                success   : function(data)
                {
                    $("#modalUpdateNewsGroupsStatus").append(data);
                    $("#modalUpdateNewsgroups").find('.modal-footer').removeClass('hidden');
                    $("#modalUpdateNewsGroupsStatus").removeClass('hidden');
                },
                error   : function(xhr,err,e)
                {
                    // Need to add code here
                    $(document).scrollTop(0);
                }
            });

        });
    </script>
{/literal}

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

{if $page->title == "Newsgroups"}
<div id="footer">
    <div id="footer-contents" class="center">
        <table class="table-footer">
            <tr>
                <td class="footer-label">Total Groups:</td><td id="totalGroups" class="footer-data"></td>
                <td class="footer-label">Active:</td><td id="activeGroups" class="footer-data"></td>
                <td class="footer-label">Backfill:</td><td id="backfillGroups" class="footer-data"></td>
            </tr>
            <tr>
                <td class="footer-label">Not Updated:</td><td id="notUpdated" class="footer-data"></td>
                <td class="footer-label">Inactive:</td><td id="inactiveGroups" class="footer-data"></td>
                <td class="footer-label">Not Backfilling:</td><td id="inactiveBackfillGroups" class="footer-data"></td>
            </tr>
        </table>
    </div>
</div>
{/if}
</body>

</html>