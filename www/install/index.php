<?php
@session_start();
require_once('../lib/install.php');
$page_title = "Welcome";

$cfg = new Install();
if ($cfg->isLocked()) {
	$cfg->error = true;
}

$cfg->cacheCheck = is_writable($cfg->SMARTY_DIR.'/templates_c');
if ($cfg->cacheCheck === false) { $cfg->error = true; }

if (!$cfg->error) {
	$cfg->setSession();
}
$nzbpath = realpath(__DIR__.'/../..').'/nzbfiles/';
?>
<!DOCTYPE html>
<html lang="en">
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="application-name" content="nZEDbetter">
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href='http://fonts.googleapis.com/css?family=Roboto:400,500,700,400italic,500italic,900' type='text/css' MEDIA="screen">
    <link rel="stylesheet" href="../themes/Cyborg/Bootstrap-3.0.0/css/bootstrap.css" MEDIA="screen">
    <link rel="stylesheet" href="../themes/Cyborg/styles/font-awesome/css/font-awesome.css" MEDIA="screen">
    <link rel="stylesheet" href="./styles/jquery.steps.css" MEDIA="screen">
    <link rel="stylesheet" href="../themes/Cyborg/styles/admin-new.css" TYPE="text/css" MEDIA="screen">
    <link rel="stylesheet" href="./styles/install.css" MEDIA="screen">
    <link rel="shortcut icon" href="../themes/Cyborg/images/favicon.ico">

    <!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <script>window.html5 || document.write('<script src="../themes/Default/scripts/vendor/html5shiv.js"><\/script>')</script>
    <![endif]-->

    <script type="text/javascript" src="//code.jquery.com/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="./scipts/jquery.color.plus-names-2.1.2.min.js"></script>
    <script type="text/javascript" src="../themes/Cyborg/Bootstrap-3.0.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../themes/Cyborg/scripts/plugins/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="./scipts/jquery.steps.js"></script>
    <script type="text/javascript" src="./scipts/jquery.passstrength.js"></script>
    <script type="text/javascript" src="./scipts/install-js.js"></script>
    <script>
        var WWW_TOP = "..";
        var errorStatus = false;
        var errorHTML = '';
    </script>
</head>
<body>
    <div id="topbar">
        <div class="container">
            <div id="top-nav">
                <a href="https://github.com/KurzonDax/nZEDbetter"><i class="icon-github-sign icon-large"></i> nZEDbetter On Github</a>
                <ul class="pull-right">
                    <li><i class="icon-user"></i> Welcome, New Admin</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="header" style="height: 160px;">
        <a href="../index.php" class="logo" style="height: 80px;">Admin Area</a>
        <div id="masthead" style="padding: 0;">
            <div class="container" >
                <div class="masthead-pad">
                    <div class="masthead-text" style="padding: 20px 0;">
                        <h2>Installation Wizard</h2>
                    </div> <!-- /.masthead-text -->
                </div>
            </div> <!-- /.container -->
        </div> <!-- /#masthead -->
    </div> <!-- #header -->

    <div id="page">
        <!--[if lt IE 7]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->
        <div class="content">
            <form id="frmInstall">
            <div id="installWizard" style="width: 70%">

                    <h3>Welcome</h3>
                    <section>
                        <div id="step0">
                            <p>Welcome to nZEDbetter.</p><p>Before getting started, you need to make sure that the server meet's
                                the <a href="http://nzedbetter.org/index.php?title=Main_Page" target="_blank">minimum requirements</a>.</p>
                            <p> You will also need...</p>
                            <ol>
                                <li>Your database credentials.</li>
                                <li>Your news server credentials.</li>
                                <li>SSH & root ability on your server (in case you need to install missing packages).</li>
                                <li>API keys for the following services if you plan to perform info lookups:</li>
                                <ul style="margin-left: 20px;">
                                    <li><a href="https://affiliate-program.amazon.com/" target="_blank" >Amazon Associates Program</a> </li>
                                    <li><a href="http://www.themoviedb.org/documentation/api" target="_blank" >The Movie Database</a></li>
                                    <li><a href="http://developer.rottentomatoes.com/" target="_blank" >Rotten Tomatoes</a></li>
                                    <li><a href="http://trakt.tv/" target="_blank" >Trakt.tv</a></li>
                                </ul>
                            </ol>
                            <div class="well clearfix">
                                <i class="icon-warning-sign icon-3x pull-left" style="padding: 12px 5px; color: #DAA520;"></i>
                                <p style="margin: 0;">This software is not practical for
                                    use on shared hosting. You should only use this on a server where YOU have the required
                                    privileges and knowledge to solve any challenges that might appear.
                                </p>
                            </div>
                            <br />
                            <div align="center">
                                <?php if (!$cfg->error) { ?>
                                    <p>Click the Next button below to begin.</p>
                                <?php } else {
                                    if (!$cfg->cacheCheck) { ?>
                                        <script>
                                            errorStatus = true;
                                            errorHTML = 'The template cache folder must be writable. A quick solution is to run:<br />chmod 777 <?php echo $cfg->SMARTY_DIR; ?>/templates_c'
                                        </script>
                                    <?php } else { ?>
                                        <script>
                                            errorStatus = true;
                                            errorHTML = 'Installation Locked! If reinstalling, please remove www/install/install.lock and then refresh this page.'
                                        </script>
                                    <?php }
                                } ?>
                            </div>
                        </div>
                    </section>

                    <h3>Preflight Checks</h3>
                    <section>
                        <div id="step1"></div>
                    </section>

                    <h3>Database Setup</h3>
                    <section>
                        <div id="step2">
                            <h2>Database Setup</h2>
                            <p>We need to gather some information about your MySQL database, please provide the information below.</p>
                            <div class="well clearfix">
                                <i class="icon-warning-sign icon-3x pull-left" style="padding: 12px 5px; color: #DAA520;"></i>
                                <p style="margin: 0;">If you are reinstalling nZEDbetter, your existing database <strong>will be dropped (deleted)</strong> and replaced
                                    with this version. If you are setting this up to be a separate instance of nZEDbetter, you can specify
                                    a different database name to prevent an existing database from being overwritten.
                                </p>
                            </div>
                            <form id="dbConfig">
                                <table style="margin-top:10px;" class="table-bordered table-form">
                                    <tr>
                                        <td><label for="host">Hostname:</label></td>
                                        <td><input type="text" name="host" id="host" value="localhost" /></td>
                                    </tr>
                                    <tr>
                                        <td><label for="host">Database Location:</label></td>
                                        <td>
                                            <input id="dbLocal" type="radio" name="dbLocation" value="local" checked style="display: inline-block"><label for="dbLocal" style="margin-right: 15px">On same server as nZEDbetter</label>
                                            <input id="dbRemote" type="radio" name="dbLocation" value="remote" style="display: inline-block"><label for="dbRemote">On remote server</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="sql_socket">Socket Path:</label></td>
                                        <td><input type="text" name="sql_socket" id="sql_socket" value="<?php echo ini_get('mysqli.default_socket'); ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td><label for="sql_port">Port Number:</label></td>
                                        <td><input type="text" name="sql_port" id="sql_port" value="<?php echo ini_get('mysqli.default_port'); ?>" disabled/></td>
                                    </tr>
                                    <tr>
                                        <td><label for="user">Username:</label></td>
                                        <td><input type="text" name="user" id="user" value="root" /></td>
                                    </tr>
                                    <tr>
                                        <td><label for="pass">Password:</label></td>
                                        <td><input type="text" name="pass" id="pass" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td><label for="db">Database Name:</label></td>
                                        <td><input type="text" name="db" id="db" value="nzedbetterTest" /></td>
                                    </tr>
                                </table>

                                <!-- <div style="padding-top:20px; text-align:center;">
                                    <div>
                                        The following error(s) were encountered:<br />
                                        {if $cfg->dbConnCheck === false}<span class="error">&bull; Unable to connect to database</span><br />{/if}
                                        {if $cfg->dbNameCheck === false}<span class="error">&bull; Unable to select database</span><br />{/if}
                                        {if $cfg->dbCreateCheck === false}<span class="error">&bull; Unable to create database and data. Check permissions of your mysql user.</span><br />{/if}
                                    </div>
                                </div> -->
                            </form>
                        </div>
                    </section>

                    <h3>News Server</h3>
                    <section>
                        <div id="step3">
                            <h2>News Server Setup</h2>
                            <div id="divPrimaryNNTP">
                                <p>Please provide the information below for your NNTP server.  If you are unsure about any of the required fields below,
                                please check with you NNTP service provider.</p>
                                <table style="margin-top:10px; margin-bottom: 10px" class="table-bordered table-form">
                                    <tr class="">
                                        <td><label for="server">Server:</label></td>
                                        <td>
                                            <input type="text" name="server" id="server" placeholder="(e.g. news.supernews.com)"/>
                                        </td>
                                    </tr>
                                    <tr class="alt">
                                        <td><label for="user">Username:</label></td>
                                        <td>
                                            <input type="text" name="user" id="user"/>
                                        </td>
                                    </tr>
                                    <tr class="">
                                        <td><label for="pass">Password:</label></td>
                                        <td>
                                            <input type="text" name="pass" id="pass"/>
                                        </td>
                                    </tr>
                                    <tr class="alt">
                                        <td><label for="port">Port:</label></td>
                                        <td>
                                            <input type="text" name="port" id="port"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="ssl">Use SSL:</label></td>
                                        <td>
                                            <input type="checkbox" name="ssl" id="ssl" value="1" />
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <input id="chkUseAlternateNNTP" type="checkbox" style="display: inline; margin-right: 7px;">
                            <label for="chkUseAlternateNNTP">Check here to specify a secondary NNTP service provider.</label>

                            <div id="divAlternateNNTP" hidden>
                                <table style="margin-top:10px;" class="table-bordered table-form">
                                    <tr class="">
                                        <td><label for="servera">Server:</label></td>
                                        <td>
                                            <input type="text" name="servera" id="servera" placeholder="(e.g. news.supernews.com)"/>
                                        </td>
                                    </tr>
                                    <tr class="alt">
                                        <td><label for="usera">Username:</label></td>
                                        <td>
                                            <input type="text" name="usera" id="usera"/>
                                        </td>
                                    </tr>
                                    <tr class="">
                                        <td><label for="passa">Password:</label></td>
                                        <td>
                                            <input type="text" name="passa" id="passa"/>
                                        </td>
                                    </tr>
                                    <tr class="alt">
                                        <td><label for="porta">Port:</label></td>
                                        <td>
                                            <input type="text" name="porta" id="porta"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="ssla">Use SSL:</label></td>
                                        <td>
                                            <input type="checkbox" name="ssla" id="ssla" value="1"/>
                                        </td>
                                    </tr>
                                </table>
                            </div> <!-- divAlternateNNTP -->
                        </div>
                    </section>

                    <h3>Admin User</h3>
                    <section>
                        <div id="step4">
                            <h2>Admin User Setup</h2>
                                <p>Please provide the information below to create an administrative user for your site.  It is strongly recommended that you use a reasonably complex
                                password, especially if your site will be exposed to the public Internet.</p>
                                <table style="margin-top:10px; margin-bottom: 10px" class="table-bordered table-form">
                                    <tr>
                                        <td><label for="adminUser">Username:</label></td>
                                        <td><input autocomplete="off" type="text" name="adminUser" id="adminUser" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td><label for="adminPass">Password:</label></td>
                                        <td><input autocomplete="off" type="password" name="adminPass" id="adminPass" value="" style="display: inline-block;"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for="adminPassConfirm">Confirm Password:</label></td>
                                        <td><input autocomplete="off" type="password" name="adminPass" id="adminPassConfirm" value="" /></td>
                                    </tr>
                                    <tr>
                                        <td><label for="adminEmail">Email:</label> </td>
                                        <td><input autocomplete="off" type="text" name="adminEmail" id="adminEmail" value="" /></td>
                                    </tr>
                                </table>
                        </div>
                    </section>

                    <h3>NZB Path</h3>
                    <section>
                        <div id="step5">
                            <h2>NZB and Tmpfs Paths</h2>
                            <p>You must set the NZB file path. This is the location where the NZB files are stored. Under most circumstances, the default is acceptable.
                            Only change the path if you with to store the NZB files on a different physical drive (or RAID array) than the web site.</p>
                            <table style="margin-top:10px; margin-bottom: 10px" class="table-bordered table-form">
                                <tr>
                                    <td><label for="nzbpath">Location:</label></td>
                                    <td><input type="text" id="nzbpath" name="nzbpath" value="<?php echo $nzbpath; ?>"></td>
                                </tr>
                            </table>
                            <p>nZEDbetter can optionally monitor up to three tmpfs (RAM drive) paths.  It is a good idea to use RAM drives for the temporary unrar path
                            used by the scripts for examining the contents of NZB payloads.  This eliminates additional load on your actual hard drive subsytem.
                            If you do not plan to use deep rar inspection, then it is unnecessary.  Other uses for RAM drives include moving the parts table completely
                            in to RAM. It is debateable if this is a wise use of RAM, or to allocate the space to the InnoDB buffer pool.  For deep rar inspection, a 200MB
                            tmpfs partition is more than sufficient.</p>
                            <table style="margin-top:10px; margin-bottom: 10px" class="table-bordered table-form">
                                <tr>
                                    <td><label for="tmpfs1">Tmpfs Path 1:</label></td>
                                    <td><input type="text" id="tmpfs1" name="tmpfs" value=""  /></td>
                                </tr>
                                <tr>
                                    <td><label for="tmpfs1">Tmpfs Path 2:</label></td>
                                    <td><input type="text" id="tmpfs2" name="tmpfs" value=""  /></td>
                                </tr>
                                <tr>
                                    <td><label for="tmpfs1">Tmpfs Path 3:</label></td>
                                    <td><input type="text" id="tmpfs3" name="tmpfs" value=""  /></td>
                                </tr>
                            </table>
                        </div>

                    </section>

                    <h3>Finished</h3>
                    <section>
                        <div id="finished">
                            <h2>Congratulations!</h2>
                            <p>You have completed the installation wizard successfully!  Once you click the finish button below, you will be taken to the Admin area of
                            the web site.  From there you can configure all of the nZEDbetter settings, including managing the groups you want to index.  Once you are
                            ready to get started with the indexing process, we <strong>strongly</strong> recommend you follow the guidelines below:</p>
                            <ol>
                                <li>
                                    Do <strong>not</strong> activate all groups at once.  nZEDbetter comes preconfigured with over 300 groups that can be indexed.  However,
                                    it is not best to activate them all initially.  Instead, choose 20 or so that you would like to start with and activate those, but don't
                                    enable backfill immediately.
                                </li>
                                <li>
                                    It may take some time for your initial groups to completely update, depending on how prolific they are, and how far back you configured
                                    nZEDbetter to start new groups.  Generally, a good way to configure the system is to start new groups to go back 1 day, or 500,000 posts.
                                    You should also enable the option to automatically switch to posts if days are selected (this will make more sense when you look at the
                                    configuration options).
                                </li>
                                <li>
                                    Once your initial groups have completely updated, go ahead and enable backfill for them.  Ideally, you should let them completely backfill
                                    before activating more groups.  However, if you have a large amount of RAM, and a fast hard disk subsystem (i.e. RAID 10 array), you can
                                    go ahead and activate another batch of groups to be updated.
                                </li>
                                <li>
                                    Continue this process over the course of several days, or a week or more, until all of the groups you want to index are activated and backfilled.
                                    Remember, however, the less RAM you have installed, the less total groups your can realistically index.
                                </li>
                            </ol>
                        </div>
                    </section>

            </div> <!-- Install Wizard -->
                <input id="errorStatus" type="hidden" value="1">  <!-- Set to True if javascript validation fails -->
            </form>
        </div> <!-- #content -->
    </div> <!-- #page -->
</body>
</html>
