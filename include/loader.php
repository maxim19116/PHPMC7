<?php
include(ROOT . "/include/core/PHPMC/Main.php");
include(ROOT . "/include/data/config.php");
class Loader {
	
	public $Event;
	
	public function __construct() {
		$this->Event = new Event();
	}
	
	/**
	 *
	 *	Функция контроля отображения фрейма страницы
	 *
	 **/
	public function frame() {
		echo $this->loadPage("panel.html", ROOT . "/content/" . Config::Theme() . "/");
	}
	
	/**
	 *
	 *	Функция загрузки страницы
	 *
	 *	$pageName	Имя файла страницы
	 *
	 *	$pagePath	Путь к странице
	 *
	 **/
	public function loadPage($pageName, $pagePath) {
		SESSION_START();
		$Option = new Option();
		$Profile = new Profile($_SESSION["user"]);
		if(!file_exists($pagePath . $pageName)) {
			$pageName = "404.html";
			$pagePath = ROOT . "/content/" . $Option->getOption("Theme") . "/error/";
		}
		if(!PHPMC::User()->isLogin() && $pageName !== "login.html" && $pageName !== "404.html" && $pageName !== "403.html") {
			$pageName = "login.html";
			$pagePath = ROOT . "/content/" . $Option->getOption("Theme") . "/";
		}
		$str = file_get_contents($pagePath . $pageName);
		$str = str_replace("{CONTENTDIR}", "./content", $str);
		$str = str_replace("{USERNAME}", $Profile->username, $str);
		$str = str_replace("{USERMAIL}", $Profile->email, $str);
		$str = str_replace("{AVATAR_HASH}", md5($Profile->email), $str);
		$str = str_replace("{CSRF_TOKEN}", $_SESSION['token'], $str);
		preg_match_all("/\{User\:(.*)\}/U", $str, $arr);
		for($i = 0;$i < count($arr[0]);$i++) {
			$User = new User();
			$str = str_replace($arr[0][$i], call_user_func(Array($User, $arr[1][$i])), $str);
		}
		preg_match_all("/\{Daemon\:(.*)\}/U", $str, $arr);
		for($i = 0;$i < count($arr[0]);$i++) {
			$Daemon = new Daemon();
			$str = str_replace($arr[0][$i], call_user_func(Array($Daemon, $arr[1][$i])), $str);
		}
		preg_match_all("/\{Server\:(.*)\}/U", $str, $arr);
		for($i = 0;$i < count($arr[0]);$i++) {
			$Server = new Server();
			$str = str_replace($arr[0][$i], call_user_func(Array($Server, $arr[1][$i])), $str);
		}
		preg_match_all("/\{System\:(.*)\}/U", $str, $arr);
		for($i = 0;$i < count($arr[0]);$i++) {
			$System = new System();
			$str = str_replace($arr[0][$i], call_user_func(Array($System, $arr[1][$i])), $str);
		}
		preg_match_all("/\{Option\:(.*)\}/U", $str, $arr);
		for($i = 0;$i < count($arr[0]);$i++) {
			$str = str_replace($arr[0][$i], $Option->getOption($arr[1][$i]), $str);
		}
		preg_match_all("/\{\{(.*)\}\}/U", $str, $arr);
		for($i = 0;$i < count($arr[0]);$i++) {
			$code = "return {$arr[1][$i]}; ";
			$str = str_replace($arr[0][$i], eval($code), $str);
		}
		return $str;
	}
	
	/**
	 *
	 *	Функция маршрутизации главной страницы
	 *
	 **/
	public function router() {
		if(PHPMC::Csrf()->isemptyCsrfToken()) {
			PHPMC::Csrf()->createCsrfToken();
		}
		$Option = new Option();
		if(preg_match("/^[A-Za-z0-9\-\_]+$/", $_GET["page"])) {
			PHPMC::Permission()->checkSession("page:" . $_GET['page']);
			$this->Event->EventHandle("viewPageEvent", array($_GET));
			exit;
		} elseif($_GET['action']) {
			switch($_GET['action']) {
				case 'login':
					if(!PHPMC::Csrf()->verifyCsrfToken($_POST)) {
						PHPMC::Error()->Println("Csrf Не удалось проверить, обновите страницу или повторите попытку через некоторое время.");
					}
					$this->Event->EventHandle("LoginEvent", array($_POST));
					break;
				case 'logout':
					$this->Event->EventHandle("LogoutEvent", array());
					break;
				case 'getserver':
					PHPMC::Permission()->checkSession("server:" . $_GET['id']);
					$this->Event->EventHandle("getServerEvent", array($_GET));
					break;
				case 'start':
					PHPMC::Permission()->checkSession("server:" . $_GET['id']);
					$this->Event->EventHandle("startServerEvent", array($_GET));
					break;
				case 'stop':
					PHPMC::Permission()->checkSession("server:" . $_GET['id']);
					$this->Event->EventHandle("stopServerEvent", array($_GET));
					break;
				case 'restart':
					PHPMC::Permission()->checkSession("server:" . $_GET['id']);
					$this->Event->EventHandle("restartServerEvent", array($_GET));
					break;
				case 'sendcommand':
					PHPMC::Permission()->checkSession("server:" . $_GET['id']);
					$this->Event->EventHandle("onCommandEvent", array($_GET));
					break;
				case 'status':
					PHPMC::Permission()->checkSession("server:" . $_GET['id']);
					$this->Event->EventHandle("getStatusEvent", array($_GET));
					break;
				case 'getserverinfo':
					PHPMC::Permission()->checkSession("server:" . $_GET['id']);
					$this->Event->EventHandle("getServerInfoEvent", array($_GET));
					break;
				case 'getdaemoninfo':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("getDaemonInfoEvent", array($_GET));
					break;
				case 'getuserinfo':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("getUserInfoEvent", array($_GET));
					break;
				case 'saveconfig':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("saveConfigEvent", array($_GET));
					break;
				case 'createserver':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("createServerEvent", array($_GET));
					break;
				case 'updateserver':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("updateServerEvent", array($_GET));
					break;
				case 'deleteserver':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("deleteServerEvent", array($_GET));
					break;
				case 'createdaemon':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("createDaemonEvent", array($_GET));
					break;
				case 'updatedaemon':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("updateDaemonEvent", array($_GET));
					break;
				case 'deletedaemon':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("deleteDaemonEvent", array($_GET));
					break;
				case 'createuser':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("createUserEvent", array($_GET));
					break;
				case 'updateuser':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("updateUserEvent", array($_GET));
					break;
				case 'deleteuser':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("deleteUserEvent", array($_GET));
					break;
				case 'update':
					PHPMC::Permission()->checkSession("action:" . $_GET['action']);
					$this->Event->EventHandle("updateEvent", array());
					break;
				default:
					$this->Event->EventHandle("defaultActionEvent", array($_GET));
			}
			exit;
		} elseif(empty($_GET['page'])) {
			$this->Event->EventHandle("defaultPageEvent", array($_GET));
		}
	}
}