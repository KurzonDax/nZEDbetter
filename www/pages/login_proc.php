<?php
// require_once('../config.php');
// require_once(WWW_DIR."/lib/users.php");

$login = array();

	if (!isset($_POST["username"]) || !isset($_POST["password"]))
        $login['login'] = "Bad Request";
	else
	{
		$users = new Users();
		$res = $users->getByUsername($_POST["username"]);

		if (!$res)
			$res = $users->getByEmail($_POST["username"]);

        $dis = $users->isDisabled($res['ID']);

		if ($res)
		{
			if ($users->checkPassword($_POST["password"], $res["password"]))
			{
                if ($dis)
                {
                    $login['login']="Your account has been disabled.";
                }
                else
                {
                $rememberMe = (isset($_POST['rememberme']) && $_POST['rememberme'] == 'on') ? 1 : 0;
				$loginGuid = $users->login($res["ID"], $_SERVER['REMOTE_ADDR'], $rememberMe);
				$login['login'] = $loginGuid;
                }
            }
			else
			{
				$login['login']="Login failed. Please try again.";
			}
		}
		else
		{
            $login['login']="Login failed. Please try again.";
		}
	}
print json_encode($login);
die();

?>
