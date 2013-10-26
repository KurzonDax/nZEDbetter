<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 10/17/13
 * Time: 8:45 AM
 * File: ajax-steps.php
 *
 * NOTE REGARDING RETURN VALUES OF STEP FUNCTIONS:
 * Each of the 'step' functions needs to return a basic array consisting of 3 elements:
 * [0] = (string) HTML to render on client side, or '' if no change should be made to the client HTML
 *          Note that if a template needs to be used for the html, it must be fetched and inserted into the string
 * [1] = (boolean) typically this will be the value of $cfg->error
 * [2] = (string) Text of error or success message.  No HTML tags required, but may use <strong> or <br /> if needed
 *
 */
require_once('../lib/installpage.php');
require_once('../lib/install.php');

$page = new Installpage();
$cfg = new Install();

if (!$cfg->isInitialized()) {
    header("Location: index.php");
    die();
}
$cfg = $cfg->getSession();

if(isset($_POST['action']) && !empty($_POST['action']))
{
    $results = array();
    switch ($_POST['action']){
        case 'step1':
            $results = step_1($page, $cfg);
            break;
        case 'step2':
            $results = step_2($page, $cfg);
            break;
        case 'step3':
            $results = step_3($page, $cfg);
            break;
        case 'step4':
            $results = step_4($page, $cfg);
            break;
        case 'step5':
            $results = step_5($page, $cfg);
            break;
        default:
            $results[] = "";
            $results[] = true;
            $results[] = "Error in ajax request. Please refresh the page and restart the wizard";
            break;
    }

    print json_encode($results);

die();
} // End of main if section

function step_1($page, $cfg){

    $page->title = "Preflight Checklist";
// Start checks
    $cfg->sha1Check = function_exists('sha1');
    if ($cfg->sha1Check === false) { $cfg->error = true; }

    $cfg->mysqlCheck = function_exists('mysql_connect');
    if ($cfg->mysqlCheck === false) { $cfg->error = true; }

    $cfg->gdCheck = function_exists('imagecreatetruecolor');

    $cfg->curlCheck = function_exists('curl_init');

    $cfg->cacheCheck = is_writable($cfg->SMARTY_DIR.'/templates_c');
    if ($cfg->cacheCheck === false) { $cfg->error = true; }

    $cfg->movieCoversCheck = is_writable($cfg->WWW_DIR.'/covers/movies');
    if ($cfg->movieCoversCheck === false) { $cfg->error = true; }

    $cfg->animeCoversCheck = is_writable($cfg->WWW_DIR.'/covers/anime');
    if ($cfg->animeCoversCheck === false) { $cfg->error = true; }

    $cfg->musicCoversCheck = is_writable($cfg->WWW_DIR.'/covers/music');
    if ($cfg->musicCoversCheck === false) { $cfg->error = true; }

    $cfg->configCheck = is_writable($cfg->WWW_DIR.'/config.php');
    if($cfg->configCheck === false) {
        $cfg->configCheck = is_file($cfg->WWW_DIR.'/config.php');
        if($cfg->configCheck === true) {
            $cfg->configCheck = false;
            $cfg->error = true;
        } else {
            $cfg->configCheck = is_writable($cfg->WWW_DIR);
            if($cfg->configCheck === false) {
                $cfg->error = true;
            }
        }
    }

    $cfg->lockCheck = is_writable($cfg->INSTALL_DIR.'/install.lock');
    if ($cfg->lockCheck === false) {
        $cfg->lockCheck = is_file($cfg->INSTALL_DIR.'/install.lock');
        if($cfg->lockCheck === true) {
            $cfg->lockCheck = false;
            $cfg->error = true;
        } else {
            $cfg->lockCheck = is_writable($cfg->INSTALL_DIR);
            if($cfg->lockCheck === false) {
                $cfg->error = true;
            }
        }
    }

    $cfg->pearCheck = @include('System.php');
    $cfg->pearCheck = class_exists('System');
    if (!$cfg->pearCheck) { $cfg->error = true; }

    $cfg->schemaCheck = is_readable($cfg->DB_DIR.'/schema.sql');
    if ($cfg->schemaCheck === false) { $cfg->error = true; }

// Dont set error = true for these as we only want to display a warning
    $cfg->phpCheck = (version_compare(PHP_VERSION, '5.4.0', '>=')) ? true : false;
    $cfg->timelimitCheck = (ini_get('max_execution_time') >= 120) ? true : false;
    $cfg->memlimitCheck = (ini_get('memory_limit') >= 1024 || ini_get('memory_limit') == -1) ? true : false;
    $cfg->opensslCheck = !extension_loaded("opensssl");
    $cfg->timezoneCheck = (ini_get('date.timezone') != "");

    $cfg->rewriteCheck = (function_exists("apache_get_modules") && in_array("mod_rewrite", apache_get_modules())) ? true : false;

//Load previous config.php
    if (file_exists($cfg->WWW_DIR.'/config.php') && is_readable($cfg->WWW_DIR.'/config.php')) {
        $tmpCfg = file_get_contents($cfg->WWW_DIR.'/config.php');
        $cfg->setConfig($tmpCfg);
    }

    if (!$cfg->error)
        $cfg->setSession();

    $page->smarty->assign('cfg', $cfg);

    $page->content = $page->smarty->fetch('step1.tpl');
    $page->step = '1';
    $page->smarty->assign('page', $page);
    $return = array();

    $return[] = $page->smarty->fetch('installpage.tpl');
    $return[] = $cfg->error;
    if($cfg->error)
        $return[] = "Major errors were encountered.  nZEDbetter will not function correctly unless these problems are resolved.";
    else
        $return[] = "No major problems were found.  You are ready to continue the installation.";
    return $return;
}

function step_2($page, $cfg){

    $cfg->doCheck = true;
    $cfg->DB_HOST = trim($_POST['host']);
    $cfg->DB_PORT = $_POST['sql_port'];
    $cfg->DB_SOCKET = trim($_POST['sql_socket']);
    $cfg->DB_USER = trim($_POST['user']);
    $cfg->DB_PASSWORD = trim($_POST['pass']);
    $cfg->DB_NAME = trim($_POST['db']);

    if(isset($_POST['debug']) && $_POST['debug'] == 'true')
    {
        $cfg->setSession();
        $return = array();
        $return[] = '';
        $return[] = false;
        $return[] = 'Database was successfully created.  Click the next button below to continue with the installation wizard.';
        return $return;
    } elseif(isset($_POST['debug']) && $_POST['debug'] == 'false')
    {
        $cfg->setSession();
        $return = array();
        $return[] = '';
        $return[] = true;
        $return[] = 'Debug mode, error = true.  Click the submit button below to try again.';
        return $return;
    }

    $sqlError = '';

    $mysqli = new mysqli($cfg->DB_HOST, $cfg->DB_USER, $cfg->DB_PASSWORD, null, $cfg->DB_PORT, $cfg->DB_SOCKET);
    $cfg->dbConnCheck = is_null($mysqli->connect_error);
    if(!isset($mysqli))
        die("Database Not Connected");
    if ($mysqli->connect_error)
    {
        $cfg->dbConnCheck = false;
        $cfg->error = true;
        // $sqlError = $mysqli->connect_error;
    }
    else
    {
        $cfg->dbNameCheck = $mysqli->select_db($cfg->DB_NAME);

        if ($cfg->dbNameCheck === false)
        {
            $result = $mysqli->query("CREATE DATABASE ".$cfg->DB_NAME." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
            $cfg->dbNameCheck = $mysqli->select_db($cfg->DB_NAME);
            if ($cfg->dbNameCheck === false)
            {
                $cfg->error = true;
            }
        }
        else
        {
            $result = $mysqli->query("DROP DATABASE ".$cfg->DB_NAME);
            if($result === true)
            {
                $result = $mysqli->query("CREATE DATABASE ".$cfg->DB_NAME." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
                if($result === true)
                {
                    $cfg->dbNameCheck = $mysqli->select_db($cfg->DB_NAME);
                    if ($cfg->dbNameCheck === false)
                        $cfg->error = true;
                }
                else
                    $cfg->error = true;
            }
            else
                $cfg->error = true;
        }
    }
    if (!$cfg->error) {
        $cfg->setSession();

        //Load schema.sql
        if (file_exists($cfg->DB_DIR.'/nzedbetter.sql')) {
            $dbData = file_get_contents($cfg->DB_DIR.'/nzedbetter.sql');
            //fix to remove BOM in UTF8 files
            $bom = pack("CCC", 0xef, 0xbb, 0xbf);
            if (0 == strncmp($dbData, $bom, 3)) {
                $dbData = substr($dbData, 3);
            }
            $queries = explode(";", $dbData);
            $queries = array_map("trim", $queries);
            foreach($queries as $q) {
                if($q !='')
                {
                    $result = $mysqli->query($q);
                    if(!$result)
                    {
                        $cfg->error = true;
                        $sqlError = $mysqli->error." ".$q;
                        break;
                    }
                }
            }
            if(!$cfg->error)
            {
                //check one of the standard tables was created and has data
                $dbInstallWorked = false;
                $reschk = $mysqli->query("select count(*) as num from category");
                if ($reschk === false)
                {
                    $cfg->dbCreateCheck = false;
                    $cfg->error = true;
                    $sqlError = $mysqli->error;
                }
                else
                {
                    while ($row = $reschk->fetch_assoc())
                    {
                        if ($row['num'] > 0)
                        {
                            $dbInstallWorked = true;
                            break;
                        }
                    }
                }

                if ($dbInstallWorked)
                {
                    $cfg->error = false;
                    $cfg->dbCreateCheck = true;
                }
                else
                {
                    $cfg->dbCreateCheck = false;
                    $cfg->error = true;
                    $sqlError = $mysqli->error;
                }
            }
        }
        else
        {
            $cfg->error = true;
            $sqlError = "Source database file does not exist.";
        }
    }
    else
    {
        $sqlError = ($cfg->dbConnCheck) ? $mysqli->error : $mysqli->connect_error;
    }
    unset($mysqli);
    $return = array();
    $return[] = '';
    $return[] = $cfg->error;
    if($cfg->error)
    {
        if($cfg->dbConnCheck === false)
            $return[] = "Unable to connect to database server.  Please be sure that you have the correct information for the host name, socket, and/or port.<br />SQL Error: ".$sqlError;
        elseif ($cfg->dbNameCheck === false)
            $return[] = "Unable to select the database name specified.  This usually means that we were unable to create a new database on the server.  Check your user name and password. In addition, you should verify that the user specified has the appropriate permissions to create databases.<br />SQL Error: ".$sqlError;
        elseif ($cfg->dbCreateCheck === false)
            $return[] = "Unable to successfully create tables and/or insert data.  This is typically due to the user specified above not having appropriate permissions to create tables or insert data.<br />SQL Error: ".$sqlError;
        else
            $return[] = "An unknown error occurred.<br />SQL Error: ".$sqlError;
    }
    else
        $return[] = "The database was successfully created.";

    return $return;
}

function step_3($page, $cfg)
{
    $cfg->doCheck = true;
    $errorMsg = '';
    $return = array();

    $cfg->NNTP_SERVER = trim($_POST['server']);
    $cfg->NNTP_USERNAME = trim($_POST['user']);
    $cfg->NNTP_PASSWORD = trim($_POST['pass']);
    $cfg->NNTP_PORT = (trim($_POST['port']) == '') ? 119 : trim($_POST['port']);
    $cfg->NNTP_SSLENABLED = (isset($_POST['ssl'])?(trim($_POST['ssl']) == '1' ? true : false):false);

    if(isset($_POST['useAltNNTP']) && $_POST['useAltNNTP'] == 'true')
    {
        $cfg->NNTP_SERVER_A = trim($_POST['servera']);
        $cfg->NNTP_USERNAME_A = trim($_POST['usera']);
        $cfg->NNTP_PASSWORD_A = trim($_POST['passa']);
        $cfg->NNTP_PORT_A = (trim($_POST['porta']) == '') ? 119 : trim($_POST['porta']);
        $cfg->NNTP_SSLENABLED_A = (isset($_POST['ssla'])?(trim($_POST['ssla']) == '1' ? true : false):false);
    }

    if(isset($_POST['debug']) && $_POST['debug'] == 'true')
    {
        $cfg->setSession();
        $return = array();
        $return[] = '';
        $return[] = false;
        $return[] = 'Config file was successfully created. [Debug Mode]';
        return $return;
    } elseif(isset($_POST['debug']) && $_POST['debug'] == 'false')
    {
        $cfg->setSession();
        $return = array();
        $return[] = '';
        $return[] = true;
        $return[] = 'Debug mode, error = true.  Click the submit button below to try again.';
        return $return;
    }

    include($cfg->WWW_DIR.'/lib/Net_NNTP/NNTP/Client.php');
    $test = new Net_NNTP_Client();

    $enc = false;
    if ($cfg->NNTP_SSLENABLED)
        $enc = "ssl";

    $cfg->nntpCheck = $test->connect($cfg->NNTP_SERVER, $enc, $cfg->NNTP_PORT);
    if(PEAR::isError($cfg->nntpCheck)){
        $cfg->error = true;
        $errorMsg = "Unable to connect to the primary NNTP server.  Please verify the information you entered above with your Usenet Service Provider";
    } else {
        $cfg->nntpCheck = $test->authenticate($cfg->NNTP_USERNAME, $cfg->NNTP_PASSWORD);
        if(PEAR::isError($cfg->nntpCheck)){
            $cfg->error = true;
            $errorMsg = "Unable to authenticate with the primary NNTP server.  Please verify that the user name and password you entered above is correct.";
        }
    }
    if($cfg->error)
    {
        $return[] = '';
        $return[] = true;
        $return[] = $errorMsg;
        return $return;
    }

    if(isset($_POST['useAltNNTP']) && $_POST['useAltNNTP'] == 'true')
    {
        unset($test);
        $test = new Net_NNTP_Client();
        $enc = false;
        if ($cfg->NNTP_SSLENABLED_A)
            $enc = "ssl";

        $cfg->nntpCheck_A = $test->connect($cfg->NNTP_SERVER_A, $enc, $cfg->NNTP_PORT_A);
        if(PEAR::isError($cfg->nntpCheck_A)){
            $cfg->error = true;
            $errorMsg = "Unable to connect to the secondary NNTP server.  Please verify the information you entered above with your Usenet Service Provider";
        } else {
            $cfg->nntpCheck_A = $test->authenticate($cfg->NNTP_USERNAME_A, $cfg->NNTP_PASSWORD_A);
            if(PEAR::isError($cfg->nntpCheck)){
                $cfg->error = true;
                $errorMsg = "Unable to authenticate with the secondary NNTP server.  Please verify that the user name and password you entered above is correct.";
            }
        }
    }

    if($cfg->error)
    {
        $return[] = '';
        $return[] = true;
        $return[] = $errorMsg;
        return $return;
    }

    $cfg->saveConfigCheck = $cfg->saveConfig();
    if ($cfg->saveConfigCheck === false) {
        $cfg->error = true;
        $errorMsg = "Unable to save the config.php file. A quick solution would be to temporarily give write permissions to everyone for the ".$cfg->WWW_DIR." directory.<br />";
        $errorMsg .= "<br />sudo chmod o+w ".$cfg->WWW_DIR;
    }
    else
    {
        $cfg->saveLockCheck = $cfg->saveInstallLock();
        if ($cfg->saveLockCheck === false) {
            $cfg->error = true;
            $errorMsg = "Unable to lock the installation directory. A quick solution would be to temporarily give write permissions to everyone for the ".$cfg->INSTALL_DIR." directory.<br />";
            $errorMsg .= "<br />sudo chmod o+w ".$cfg->INSTALL_DIR;
        }
    }
    $return[] = '';
    $return[] = $cfg->error;
    if (!$cfg->error) {
        $return[] = "Successfully connected to the NNTP server(s) and saved the database and NNTP configuration information to ".$cfg->WWW_DIR."/config.php. Click the Next button below to continue.";
        $cfg->setSession();
    }
    else
        $return[] = $errorMsg;

    return $return;
};

function step_4($page, $cfg)
{
    $errorMessage = '';
    $return = array();

    $cfg->doCheck = true;

    $cfg->ADMIN_USER = trim($_POST['adminUser']);
    $cfg->ADMIN_PASS = trim($_POST['adminPass']);
    $cfg->ADMIN_EMAIL = trim($_POST['adminEmail']);

    if ($cfg->ADMIN_USER == '' || $cfg->ADMIN_PASS == '' || $cfg->ADMIN_EMAIL == '') {
        $cfg->error = true;
        $errorMessage = "You may not use a blank username, password, or email address for the Admin user.  Please correct this above and click the submit button.";
    } else {
        require_once('../config.php');
        require_once($cfg->WWW_DIR.'/lib/users.php');

        $user = new Users();
        if (!$user->isValidUsername($cfg->ADMIN_USER)) {
            $cfg->error = true;
            $errorMessage = "The user name you supplied is invalid.  The user name must meet the following criteria:<br />".
                            "<ul><li>Must start with a letter</li><li>Must be a minimum of 5 characters long</li><li>May only contain letters and numbers</li></ul>";
            $cfg->ADMIN_USER = '';
        }
        elseif ($user->getByUsername($cfg->ADMIN_USER))
        {
            $cfg->error = true;
            $errorMessage = "The user name you provided already exists in the database.  Please provide a unique name for the Admin user.";
            $cfg->ADMIN_USER = '';
        }
        elseif (!$user->isValidEmail($cfg->ADMIN_EMAIL))
        {
            $cfg->error = true;
            $errorMessage = "The email address you provided does not appear to be valid.  Please enter a valid email address for the Admin user.";
            $cfg->ADMIN_EMAIL = '';
        }
        elseif (strlen($cfg->ADMIN_PASS)<5)
        {
            $cfg->error = true;
            $errorMessage = "The password you provided is too short.  Please enter a password for the Admin user that is longer than 5 characters.";
            $cfg->ADMIN_PASS = '';
        }

        if (!$cfg->error) {
            $cfg->adminCheck = $user->add($cfg->ADMIN_USER, $cfg->ADMIN_PASS, $cfg->ADMIN_EMAIL, 2, '');
            if (!is_numeric($cfg->adminCheck)) {
                $cfg->error = true;
                $errorMessage = "An error occurred while trying to add the user.";
            } else {
                $user->login($cfg->adminCheck, "", 1);
            }
        }
    }

    $return[] = '';
    $return[] = $cfg->error;
    if (!$cfg->error)
        $return[] = "Admin user has been successfully added.  You are now logged in as the new admin user.";
    else
        $return[] = $errorMessage;

    return $return;
}

function step_5($page, $cfg)
{
    $cfg->doCheck = true;
    $errorMsg = '';
    $return = array();

    $cfg->NZB_PATH = trim($_POST['nzbpath']);
    $cfg->TMPFS1_PATH = (isset($_POST['tmpfs1']) && !empty($_POST['tmpfs1'])) ? trim($_POST['tmpfs1']) : '';
    $cfg->TMPFS2_PATH = (isset($_POST['tmpfs2']) && !empty($_POST['tmpfs2'])) ? trim($_POST['tmpfs2']) : '';
    $cfg->TMPFS3_PATH = (isset($_POST['tmpfs3']) && !empty($_POST['tmpfs3'])) ? trim($_POST['tmpfs3']) : '';
    if ($cfg->NZB_PATH == '') {
        $cfg->error = true;
        $errorMsg = 'The NZB path you provided was blank. A path must be specified to store the NZB files.';
    }
    else
    {
        $cfg->nzbPathCheck = is_writable($cfg->NZB_PATH);
        if($cfg->nzbPathCheck === false)
        {
            $cfg->error = true;
            $errorMsg = 'The installation wizard is unable to write to the path you specified ('.$cfg->NZB_PATH.')<br />A quick solution is to give everyone write access to the directory.'.
                        'sudo chmod a+w '.$cfg->NZB_PATH;
        }
        else
        {
            $lastchar = substr($cfg->NZB_PATH, strlen($cfg->NZB_PATH) - 1);
            if ($lastchar != "/")
                $cfg->NZB_PATH = $cfg->NZB_PATH."/";

            if (!$cfg->error)
            {
                if (!file_exists($cfg->NZB_PATH."tmpunrar"))
                    mkdir($cfg->NZB_PATH."tmpunrar");
                require_once("../config.php");
                require_once(WWW_DIR.'/lib/framework/db.php');
                $db = new DB();
                $sql1 = sprintf("UPDATE site SET value = %s WHERE setting = 'nzbpath'", $db->escapeString($cfg->NZB_PATH));
                $sql2 = sprintf("UPDATE site SET value = %s WHERE setting = 'tmpunrarpath'", $db->escapeString($cfg->NZB_PATH."tmpunrar"));
                if ($db->query($sql1) === false || $db->query($sql2) === false)
                {
                    $cfg->error = true;
                    $errorMsg = 'Unable to update the database with the NZB path.<br />SQL Error: '.$db->Error();
                }
                else
                {
                    $sql1 = "UPDATE tmux SET value = ".$db->escapeString($cfg->TMPFS1_PATH)." WHERE setting = 'MONITOR_PATH'";
                    $sql2 = "UPDATE tmux SET value = ".$db->escapeString($cfg->TMPFS2_PATH)." WHERE setting = 'MONITOR_PATH_A'";
                    $sql3 = "UPDATE tmux SET value = ".$db->escapeString($cfg->TMPFS3_PATH)." WHERE setting = 'MONITOR_PATH_B'";
                    if ($db->query($sql1) === false || $db->query($sql2) === false || $db->query($sql3) === false)
                    {
                        $cfg->error = true;
                        $errorMsg = 'Unable to update database with tmpfs paths.<br />SQL Error: '.$db->Error();
                    }
                }
            }
        }
    }

    $return[] = '';
    $return[] = $cfg->error;
    if (!$cfg->error) {
        $cfg->setSession();
        $return[] = 'Database successfully updated with the provided paths.';
    }
    else
        $return[] = $errorMsg;

    return $return;
}

