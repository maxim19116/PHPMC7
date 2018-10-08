<?php
class Event {
	
	public $eventList;
	
	public function registerClass($name, $class) {
		$arr = $this->eventList;
		$plug_one = @count($arr[$name]);
		$arr[$name][$plug_one] = $class;
		$this->eventList = $arr;
	}
	
	public function EventHandle($name, $args) {
		try {
			// Если событие уже зарегистрировано
			if(isset($this->eventList[$name])) {
				$breaked = false;
				// Обход списка событий для выполнения обработчика событий для каждого подключаемого модуля
				foreach($this->eventList[$name] as $class) {
					$rs = call_user_func_array(array($class, $name), $args);
					// Если событие отменяется, то
					if($rs == true) {
						$breaked = true;
						break;
					}
				}
				// Если событие не было отменено
				if(!$breaked) {
					@call_user_func_array(array($this, $name), $args);
				}
			} else {
				// Если событие не зарегистрировано
				call_user_func_array(array($this, $name), $args);
			}
		} catch(Exception $ex) {
			// Когда произошла ошибка
			call_user_func_array(array($this, $name), $args);
		}
	}
	
	public function defaultActionEvent($data) {
		$Loader = new Loader();
		$Option = new Option();
		echo $Loader->loadPage("404.html", ROOT . "/content/" . $Option->getOption("Theme") . "/error/");
		exit;
	}
	
	public function defaultPageEvent($data) {
		$Loader = new Loader();
		$Option = new Option();
		echo $Loader->loadPage("panel.html", ROOT . "/content/" . $Option->getOption("Theme") . "/");
		exit;
	}
	
	public function viewPageEvent($data) {
		$Loader = new Loader();
		$Option = new Option();
		echo $Loader->loadPage($data["page"] . ".html", ROOT . "/content/" . $Option->getOption("Theme") . "/");
		exit;
	}
	
	public function LoginEvent($Data) {
		if(preg_match("/^[A-Za-z0-9\-\_]+$/", $Data['username'])) {
			if(PHPMC::User()->Login($Data['username'], $Data['password'])) {
				SESSION_START();
				$_SESSION['user'] = $Data['username'];
				echo "Successful";
				PHPMC::Csrf()->createCsrfToken();
				exit;
			} else {
				$Option = new Option();
				$newValue = Intval($Option->getOption('loginFailed')) + 1;
				$Option->updateOption("loginFailed", $newValue);
				PHPMC::Error()->Println("Неправильное имя пользователя или пароль!");
			}
		}
	}
	
	public function LogoutEvent() {
		if(PHPMC::User()->isLogin()) {
			PHPMC::User()->Logout();
		}
		echo "<script>location='./';</script>";
	}
	
	public function getServerEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Server = new Server();
			$Server->setServer($data['id']);
			if(empty($Server->uuid)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$Daemon = new Daemon();
			if($Daemon->setDaemon($Server->daemon) == null) {
				PHPMC::Error()->Println("500 Server Internal Error");
			}
			$info = Array(
				'id' => $Server->id,
				'name' => $Server->name,
				'uuid' => $Server->uuid,
				'daemon' => $Server->daemon,
				'host' => $Daemon->host,
				'gamehost' => $Daemon->fqdn . ":" . $Server->port,
				'ftpuser' => mb_substr($Server->uuid, 0, 8),
				'ftppass' => $Server->ftppass,
				'token' => $Server->getToken()
			);
			echo json_encode($info);
		} else {
			$Loader = new Loader();
			$Option = new Option();
			echo $Loader->loadPage("404.html", ROOT . "/content/" . $Option->getOption("Theme") . "/error/");
		}
	}
	
	public function getServerInfoEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Server = new Server();
			$Server->setServer($data['id']);
			if(empty($Server->uuid)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$info = Array(
				'id' => $Server->id,
				'name' => $Server->name,
				'maxram' => $Server->maxram,
				'jar' => $Server->jar,
				'startcommand' => $Server->startcommand,
				'stopcommand' => $Server->stopcommand,
				'port' => $Server->port,
				'ftppass' => $Server->ftppass,
				'owner' => $Server->owner
			);
			echo json_encode($info);
		} else {
			$Loader = new Loader();
			$Option = new Option();
			echo $Loader->loadPage("404.html", ROOT . "/content/" . $Option->getOption("Theme") . "/error/");
		}
	}
	
	public function getDaemonInfoEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Daemon = new Daemon();
			$Daemon->setDaemon($data['id']);
			if(empty($Daemon->name)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$info = Array(
				'id' => $Daemon->id,
				'name' => $Daemon->name,
				'host' => $Daemon->host,
				'pass' => $Daemon->pass,
				'fqdn' => $Daemon->fqdn,
				'type' => $Daemon->type
			);
			echo json_encode($info);
		} else {
			$Loader = new Loader();
			$Option = new Option();
			echo $Loader->loadPage("404.html", ROOT . "/content/" . $Option->getOption("Theme") . "/error/");
		}
	}
	
	public function getUserInfoEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Profile = new Profile($data['id']);
			if(empty($Profile->username)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$info = Array(
				'id' => $Profile->id,
				'username' => $Profile->username,
				'email' => $Profile->email,
				'permission' => $Profile->permission
			);
			echo json_encode($info);
		} else {
			$Loader = new Loader();
			$Option = new Option();
			echo $Loader->loadPage("404.html", ROOT . "/content/" . $Option->getOption("Theme") . "/error/");
		}
	}
	
	public function startServerEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Server = new Server();
			$Server->setServer($data['id']);
			if(empty($Server->uuid)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$Server->Init();
			$startCommand = $Server->startcommand;
			$startCommand = str_replace("{maxram}", $Server->maxram, $startCommand);
			$startCommand = str_replace("{jar}", $Server->jar, $startCommand);
			$Server->sendCommand($startCommand);
			echo "Successful";
		} else {
			echo "Не найден";
			exit;
		}
	}
	
	public function stopServerEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Server = new Server();
			$Server->setServer($data['id']);
			if(empty($Server->uuid)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$stopcommand = $Server->stopcommand;
			$Server->sendCommand($stopcommand);
			echo "Successful";
		} else {
			echo "Не найден";
			exit;
		}
	}
	
	public function restartServerEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Server = new Server();
			$Server->setServer($data['id']);
			if(empty($Server->uuid)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$stopcommand = $Server->stopcommand;
			$Server->sendCommand($stopcommand);
			sleep(5);
			$startCommand = $Server->startcommand;
			$startCommand = str_replace("{maxram}", $Server->maxram, $startCommand);
			$startCommand = str_replace("{jar}", $Server->jar, $startCommand);
			$Server->sendCommand($startCommand);
			echo "Successful";
		} else {
			echo "Не найден";
			exit;
		}
	}
	
	public function onCommandEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Server = new Server();
			$Server->setServer($data['id']);
			if(empty($Server->uuid)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			if(empty($data['cmd'])) {
				PHPMC::Error()->Println("Содержимое команды не может быть пустым.");
			}
			$Server->Init();
			$Server->sendCommand($data['cmd']);
			echo "Successful";
		} else {
			echo "Не найден";
			exit;
		}
	}
	
	public function getStatusEvent($data) {
		if(isset($data['id']) && preg_match("/^[0-9]+$/", $data['id'])) {
			$Server = new Server();
			$Server->setServer($data['id']);
			if(empty($Server->uuid)) {
				PHPMC::Error()->Println("404 Не найден");
			}
			$Daemon = new Daemon();
			if($Daemon->setDaemon($Server->daemon) == null) {
				PHPMC::Error()->Println("500 Server Internal Error");
			}
			$Utils = new Utils();
			$sinfo = $Utils->Query($Daemon->fqdn, $Server->port);
			echo $sinfo['online'] . "/" . $sinfo['max'] . "/" . $Daemon->fqdn . ":" . $Server->port;
			exit;
		} else {
			echo "/";
			exit;
		}
	}
	
	public function createServerEvent($data) {
		if(!preg_match("/^[0-9]+$/", $data['daemon'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: Daemon");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['name'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: имя сервера, может содержать только английский регистр, цифры, подчеркивания и -"); 
		}
		if(!preg_match("/^[0-9]+$/", $data['maxram'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: максимальная память");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.]+$/", $data['jar'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: имя основного файла, может содержать только английский регистр, номер, _, . И -");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.\{\}\:\/\\\= ]+$/", $data['startcommand'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: основная команда запуска");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.\{\} ]+$/", $data['stopcommand'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: команда Stop");
		}
		if(!preg_match("/^[0-9]+$/", $data['port'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: порт сервера");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['ftppass'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: сервер FTP пароль, может содержать только английский регистр, цифры, подчеркивания и -"); 
		}
		if(!preg_match("/^[0-9]+$/", $data['owner'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: владелец сервера");
		}
		$Server = new Server();
		$Server->setServer($data['name']);
		if($Server->uuid !== null) {
			PHPMC::Error()->Println("Серверы с одинаковыми именами уже существуют.");
		}
		$Server->unselectServer();
		$Server->setServer($data['port'], $data['daemon']);
		if($Server->uuid !== null) {
			PHPMC::Error()->Println("Сервер с тем же портом и тем же Daemon уже существует.");
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($data['daemon']) == null) {
			PHPMC::Error()->Println("Daemon не существует, проверьте правильность параметров.");
		}
		PHPMC::Server()->createServer($data['name'], $data['daemon'], $data['maxram'], 
			$data['jar'], $data['startcommand'], $data['stopcommand'], $data['owner'], "normal", $data['port'], $data['ftppass']);
		echo "Сервер создан успешно!";
		exit;
	}
	
	public function updateServerEvent($data) {
		if(!preg_match("/^[0-9]+$/", $data['id'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: server ID");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['name'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: имя сервера, которое может содержать только английский регистр, пробелы,цифры, подчеркивания и -"); 
		}
		if(!preg_match("/^[0-9]+$/", $data['maxram'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поле: Максимальная память");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.]+$/", $data['jar'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: имя основного файла, может содержать только английский регистр, номер, _, . И -");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.\{\}\:\/\\\= ]+$/", $data['startcommand'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: основная команда запуска");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.\{\} ]+$/", $data['stopcommand'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: команда Stop");
		}
		if(!preg_match("/^[0-9]+$/", $data['port'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: порт сервера");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['ftppass'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: сервер FTP пароль, может содержать только английский регистр, цифры, подчеркивания и -"); 
		}
		if(!preg_match("/^[0-9]+$/", $data['owner'])) {
			PHPMC::Error()->Println("Заполните поля: владелец сервера");
		}
		$Server = new Server();
		$Server2 = new Server();
		$Server->setServer($data['id']);
		if($Server->uuid == null) {
			PHPMC::Error()->Println("Сервер не найден");
		}
		$Server2->setServer($data['name']);
		if($Server2->uuid !== null && $Server2->id !== $data['id']) {
			PHPMC::Error()->Println("Сервер с тем же именем уже существует.");
		}
		$Server2->unselectServer();
		$Server2->setServer($data['port'], $Server->daemon);
		if($Server2->uuid !== null && $Server2->id !== $data['id']) {
			PHPMC::Error()->Println("Тот же порт, тот же самый сервер Daemon уже существует.");
		}
		PHPMC::Server()->updateServer($data['id'], $data['name'], $data['maxram'], $data['jar'], $data['startcommand'], 
			$data['stopcommand'], $data['owner'], "normal", $data['port'], $data['ftppass']);
		echo "Настройки сервера меняются успешно, и настройки не вступят в силу, пока вы не обновите страницу.";
		exit;
	}
	
	public function deleteServerEvent($data) {
		if(!preg_match("/^[0-9]+$/", $data['id'])) {
			PHPMC::Error()->Println("Заполните поле: ID сервера");
		}
		PHPMC::Server()->deleteServer($data['id']);
		echo "Сервер был успешно удален!";
		exit;
	}
	
	public function createDaemonEvent($data) {
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['name'])) {
			PHPMC::Error()->Println("Заполните поле: Daemon name");
		}
		if(!preg_match('/^^((https|http)?:\/\/)[^\s]+$/', $data['host'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: AJAX адрес запроса"); 
		}
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['pass'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: пароль Daemon, который может содержать только английский регистр, цифры, подчеркивания и -");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.]+$/", $data['fqdn'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: доменное имя или IP-адрес");
		}
		if(!preg_match("/^[a-z]+$/", $data['type'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: тип операционной системы сервера");
		}
		PHPMC::Daemon()->createDaemon($data['name'], $data['host'], $data['pass'], $data['fqdn'], $data['type']);
		echo "Daemon создан успешно!";
		exit;
	}
	
	public function updateDaemonEvent($data) {
		if(!preg_match("/^[0-9]+$/", $data['id'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Daemon ID");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['name'])) {
			PHPMC::Error()->Println("Заполните поле: Daemon name");
		}
		if(!preg_match('/^^((https|http)?:\/\/)[^\s]+$/', $data['host'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: AJAX адрес запроса"); 
		}
		if(!preg_match("/^[A-Za-z0-9\-\_ ]+$/", $data['pass'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: пароль Daemon, который может содержать только английский регистр, цифры, подчеркивания и -");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_\.]+$/", $data['fqdn'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: доменное имя или IP-адрес");
		}
		if(!preg_match("/^[a-z]+$/", $data['type'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля: тип операционной системы сервера");
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($data['id']) == null) {
			PHPMC::Error()->Println("Daemon Не найден");
		}
		PHPMC::Daemon()->updateDaemon($data['id'], $data['name'], $data['host'], $data['pass'], $data['fqdn'], $data['type']);
		echo "Настройки Daemon были изменены успешно, и вам нужно обновить веб-страницу, прежде чем настройки вступят в силу.";
		exit;
	}
	
	public function deleteDaemonEvent($data) {
		if(!preg_match("/^[0-9]+$/", $data['id'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Daemon ID");
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($data['id']) == null) {
			PHPMC::Error()->Println("Daemon Не найден");
		}
		if(PHPMC::Server()->getCountsByDaemon($data['id']) > 0) {
			PHPMC::Error()->Println("Есть серверы в текущем Демоне, пожалуйста, удалите сервер перед удалением демона.");
		}
		PHPMC::Daemon()->deleteDaemon($data['id']);
		echo "Daemon Удаление успешно!";
		exit;
	}
	
	public function createUserEvent($data) {
		if(!preg_match("/^[A-Za-z0-9\-\_]+$/", $data['username'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Имя пользователя");
		}
		if(empty($data['password'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Пароль пользователя
"); 
		}
		if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Почтовый ящик пользователя");
		}
		if(!preg_match("/^[A-Za-z0-9\_\-\;\:]+$/", $data['permission'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Права пользователя");
		}
		$Profile = new Profile($data['username']);
		if($Profile->username == $data['username'] && $Profile->id !== $data['id']) {
			PHPMC::Error()->Println("Этот пользователь уже существует");
		}
		$Profile = new Profile($data['email']);
		if($Profile->email == $data['email'] && $Profile->id !== $data['id']) {
			PHPMC::Error()->Println("Этот почтовый ящик уже существует.");
		}
		$password = password_hash(md5($data['password']), PASSWORD_BCRYPT);
		PHPMC::User()->createUser($data['username'], $password, $data['email'], $data['permission']);
		echo "Пользователь создан успешно!";
		exit;
	}
	
	public function updateUserEvent($data) {
		if(!preg_match("/^[0-9]+$/", $data['id'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Идентификатор пользователя");
		}
		if(!preg_match("/^[A-Za-z0-9\-\_]+$/", $data['username'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Имя пользователя");
		}
		if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Почтовый ящик пользователя");
		}
		if(!preg_match("/^[A-Za-z0-9\_\-\;\:]+$/", $data['permission'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Права пользователя");
		}
		$Profile = new Profile($data['id']);
		if($Profile->username == null) {
			PHPMC::Error()->Println("User Не найден");
		}
		$Profile = new Profile($data['username']);
		if($Profile->username == $data['username'] && $Profile->id !== $data['id']) {
			PHPMC::Error()->Println("Имя пользователя уже существует");
		}
		$Profile = new Profile($data['email']);
		if($Profile->email == $data['email'] && $Profile->id !== $data['id']) {
			PHPMC::Error()->Println("Этот почтовый ящик уже существует.");
		}
		if(empty($data['password'])) {
			PHPMC::User()->updateUser($data['id'], $data['username'], false, $data['email'], $data['permission']);
		} else {
			$password = password_hash(md5($data['password']), PASSWORD_BCRYPT);
			PHPMC::User()->updateUser($data['id'], $data['username'], $password, $data['email'], $data['permission']);
		}
		echo "Настройки пользователя изменяются успешно, и вам нужно обновить веб-страницу, прежде чем настройки вступят в силу.";
		exit;
	}
	
	public function deleteUserEvent($data) {
		if(!preg_match("/^[0-9]+$/", $data['id'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Идентификатор пользователя");
		}
		$Profile = new Profile($data['id']);
		if($Profile->username == null) {
			PHPMC::Error()->Println("Пользователь не найден");
		}
		if(PHPMC::Server()->getCountsByOwner($data['id']) > 0) {
			PHPMC::Error()->Println("Текущий пользователь также имеет сервер, удалите сервер перед удалением пользователя.");
		}
		if($data['id'] == "1") {
			PHPMC::Error()->Println("Этот пользователь является суперадминистратором и не может быть удален.");
		}
		PHPMC::User()->deleteUser($data['id']);
		echo "Пользователь удален успешно!";
		exit;
	}
	
	public function saveConfigEvent($data) {
		if(!preg_match("/^[a-zA-Z0-9_\x7f-\xff ]+$/", $data['SiteName'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Название сайта");
		}
		if(!preg_match("/^[a-zA-Z0-9_\x7f-\xff ]+$/", $data['Description'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Описание сайта");
		}
		if(!preg_match("/^[a-zA-Z0-9\-\_]+$/", $data['Theme'])) {
			PHPMC::Error()->Println("Пожалуйста, заполните поля： Системные темы");
		}
		PHPMC::Option()->saveConfig($data['SiteName'], $data['Description'], $data['Theme']);
		echo "Системные настройки были изменены успешно, и вам нужно обновить веб-страницу, прежде чем настройки вступят в силу.";
		exit;
	}
	
	public function updateEvent() {
		if(PHPMC::Update()->checkUpdate()) {
			PHPMC::Update()->updateExecute();
		}
	}
}