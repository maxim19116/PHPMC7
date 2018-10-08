<?php
class Permission {
	
	public function check($permission, $user = false) {
		switch($permission) {
			case 'page:login':
				return true;
				break;
			case 'page:404':
				return true;
				break;
			case 'page:403':
				return true;
				break;
			case 'action:login':
				return true;
				break;
			default:
				if(!$user) {
					if(!PHPMC::User()->isLogin()) {
						return false;
					} else {
						$user = PHPMC::User()->getLoginUser();
					}
				}
				if(stristr($user->permission, $permission . ";")) {
					return true;
				} else {
					if(stristr($user->permission, "admin;")) {
						return true;
					} elseif(stristr($permission, "server:")) {
						$exp = explode(":", $permission);
						return $this->serverControlPerm($user->permission, $exp[1]);
					} else {
						return false;
					}
				}
		}
	}
	
	public function checkSession($permission) {
		if(!$this->check($permission)) {
			$Loader = new Loader();
			echo $Loader->loadPage("403.html", ROOT . "/content/" . Config::Theme() . "/error/");
			exit;
		}
	}
	
	/**
	 *
	 * Обнаружение разрешений сервера
	 *
	 * @param $permission	Права пользователя
	 * @param $server		Идентификатор сервера
	 * @return Boolean		У вас есть разрешение?
	 *
	 */
	public function serverControlPerm($permission, $server) {
		$gettag = explode(";", $permission);
		for($i = 0;$i < count($gettag);$i++) {
			$getkey = explode(":", $gettag[$i]);
			if($getkey[0] == "server" && $getkey[1] == $server) {
				return true;
			}
		}
		return false;
	}
}