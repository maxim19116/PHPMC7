<?php
class Server {
	
	public $server;
	public $id;
	public $name;
	public $daemon;
	public $maxram;
	public $jar;
	public $startcommand;
	public $stopcommand;
	public $owner;
	public $status;
	public $port;
	public $ftppass;
	public $uuid;
	
	/**
	 * Выберите сервер, с которым вы хотите работать.
	 *
	 * @param $server Идентификатор сервера
	 * @param $daemon Deamon.
	 */
	public function setServer($server, $daemon = false) {
		$this->server = $server;
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		// Method 1 Идентификатор сервера Поиск сервера
		$rs = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers` WHERE `id`='" . $this->server . "'"));
		if($rs) {
			$this->id = $rs['id'];
			$this->name = $rs['name'];
			$this->daemon = $rs['daemon'];
			$this->maxram = $rs['maxram'];
			$this->jar = $rs['jar'];
			$this->startcommand = $rs['startcommand'];
			$this->stopcommand = $rs['stopcommand'];
			$this->owner = $rs['owner'];
			$this->status = $rs['status'];
			$this->port = $rs['port'];
			$this->ftppass = $rs['ftppass'];
			$this->uuid = $rs['uuid'];
		} else {
			// Method 2 Поиск сервера через сервер UUID
			$rs = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers` WHERE `uuid`='" . $this->server . "'"));
			if($rs) {
				$this->id = $rs['id'];
				$this->name = $rs['name'];
				$this->daemon = $rs['daemon'];
				$this->maxram = $rs['maxram'];
				$this->jar = $rs['jar'];
				$this->startcommand = $rs['startcommand'];
				$this->stopcommand = $rs['stopcommand'];
				$this->owner = $rs['owner'];
				$this->status = $rs['status'];
				$this->port = $rs['port'];
				$this->ftppass = $rs['ftppass'];
				$this->uuid = $rs['uuid'];
			} else {
				// Method 3 Поиск сервера по имени сервера
				$rs = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers` WHERE `name`='" . $this->server . "'"));
				if($rs) {
					$this->id = $rs['id'];
					$this->name = $rs['name'];
					$this->daemon = $rs['daemon'];
					$this->maxram = $rs['maxram'];
					$this->jar = $rs['jar'];
					$this->startcommand = $rs['startcommand'];
					$this->stopcommand = $rs['stopcommand'];
					$this->owner = $rs['owner'];
					$this->status = $rs['status'];
					$this->port = $rs['port'];
					$this->ftppass = $rs['ftppass'];
					$this->uuid = $rs['uuid'];
				} else {
					// Method 4 Поиск по порту сервера
					if($daemon) {
						$rs = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers` WHERE `port`='" . $this->server . "' AND `daemon`='{$daemon}'"));
						if($rs) {
							$this->id = $rs['id'];
							$this->name = $rs['name'];
							$this->daemon = $rs['daemon'];
							$this->maxram = $rs['maxram'];
							$this->jar = $rs['jar'];
							$this->startcommand = $rs['startcommand'];
							$this->stopcommand = $rs['stopcommand'];
							$this->owner = $rs['owner'];
							$this->status = $rs['status'];
							$this->port = $rs['port'];
							$this->ftppass = $rs['ftppass'];
							$this->uuid = $rs['uuid'];
						} else {
							// Данные не найдены, возвращается значение null
							$this->id = null;
							$this->name = null;
							$this->daemon = null;
							$this->maxram = null;
							$this->jar = null;
							$this->startcommand = null;
							$this->stopcommand = null;
							$this->owner = null;
							$this->status = null;
							$this->port = null;
							$this->ftppass = null;
							$this->uuid = null;
						}
					} else {
						// Данные не найдены, возвращается значение null
						$this->id = null;
						$this->name = null;
						$this->daemon = null;
						$this->maxram = null;
						$this->jar = null;
						$this->startcommand = null;
						$this->stopcommand = null;
						$this->owner = null;
						$this->status = null;
						$this->port = null;
						$this->ftppass = null;
						$this->uuid = null;
					}
				}
			}
		}
	}
	
	/**
	 * Отмена выбора сервера
	 */
	public function unselectServer() {
		$this->id = null;
		$this->name = null;
		$this->daemon = null;
		$this->maxram = null;
		$this->jar = null;
		$this->startcommand = null;
		$this->stopcommand = null;
		$this->owner = null;
		$this->status = null;
		$this->port = null;
		$this->ftppass = null;
		$this->uuid = null;
	}
	
	/**
	 * Создание нового сервера в базе данных
	 *
	 * @param $name 		Отображаемое имя сервера
	 * @param $daemon 		Deamon, на котором расположен сервер
	 * @param $maxram		Максимальная память сервера
	 * @param $jar			Имя Jar ядра сервера
	 * @param $startcommand	Команда запуска сервера
	 * @param $stopcommand	Команда остановки сервера
	 * @param $owner		Владелец сервера, Идентификатор пользователя
	 * @param $status		Состояние сервера
	 * @param $port			Порт сервера
	 * @param $ftppass		FTP Пароль
	 * @return Boolean		Статус
	 */
	public function createServer($name, $daemon, $maxram, $jar, $startcommand, $stopcommand, $owner, $status, $port, $ftppass) {
		$uuid = md5(uniqid(rand(0, 10000000), TRUE));
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		mysqli_query($conn, "INSERT INTO `{$db['name']}`.`servers` (`id`, `name`, `daemon`, `maxram`, `jar`, `startcommand`, `stopcommand`, `owner`, `status`, `port`, `uuid`, `ftppass`) "
			. "VALUES (NULL, '{$name}', '{$daemon}', '{$maxram}', '{$jar}', '{$startcommand}', '{$stopcommand}', '{$owner}', '{$status}', '{$port}', '{$uuid}', '{$ftppass}')");
		$this->setServer($uuid);
		$this->Init();
		return true;
	}
	
	/**
	 * Обновление данных сервера в базе данных
	 *
	 * @param $id			Идентификатор сервера
	 * @param $name 		Отображаемое имя сервера
	 * @param $maxram		Максимальная память сервера
	 * @param $jar			Имя Jar ядра сервера
	 * @param $startcommand	Команда запуска сервера
	 * @param $stopcommand	Команда остановки сервера
	 * @param $owner		Владелец сервера, Идентификатор пользователя
	 * @param $status		Состояние сервера
	 * @param $port			Порт сервера
	 * @param $ftppass		FTP Пароль
	 * @return Boolean		Статус
	 */
	public function updateServer($id, $name, $maxram, $jar, $startcommand, $stopcommand, $owner, $status, $port, $ftppass) {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		mysqli_query($conn, "UPDATE `{$db['name']}`.`servers` SET `name`='{$name}', `maxram`='{$maxram}', `jar`='{$jar}', `startcommand`='{$startcommand}', "
			."`stopcommand`='{$stopcommand}', `owner`='{$owner}', `status`='{$status}', `port`='{$port}', `ftppass`='{$ftppass}' WHERE `id`='{$id}'");
		return true;
	}
	
	/**
	 * Удаление сервера в базе данных и всех данных
	 *
	 * @param $id		ID сервера
	 * @return Boolean	Статус
	 */
	public function deleteServer($id) {
		$this->setServer($id);
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$Daemon = new Daemon();
		if($Daemon->setDaemon($this->daemon) == null) {
			return false;
		}
		$this->sendCommand($this->stopcommand);
		sleep(1);
		$Http = new Http();
		$result = $Http->Request($Daemon->host . "?action=file-exist&name=" . urlencode("data/" . $this->uuid) . "&token=" . md5($Daemon->pass));
		echo "Delete file: {$result}<br>";
		if($result == 'true') {
			if($Daemon->type == "linux") {
				$this->sendCommand("cd ../");
				$this->sendCommand("rm -rf " . $this->uuid);
			} else {
				$this->sendCommand("cd ../");
				$this->sendCommand("rmdir /s/q " . $this->uuid);
			}
		}
		sleep(1);
		$this->close();
		mysqli_query($conn, "DELETE FROM `{$db['name']}`.`servers` WHERE `id`='{$id}'");
		return true;
	}
	
	/**
	 * Определяет, инициализирован ли сервер
	 *
	 * @return Boolean Состояние инициализации
	 */
	public function isCreated() {
		if(empty($this->server)) {
			return false;
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($this->daemon) == null) {
			return false;
		}
		$Http = new Http();
		$result = $Http->Request($Daemon->host . "?action=exist&name=" . $this->uuid . "&token=" . md5($Daemon->pass));
		return $result == 'true' ? true : false;
	}
	
	/**
	 * Инициализация канала связи сервера
	 *
	 * @return String/Boolean Возвращает результат выполнения
	 */
	public function Init() {
		if(empty($this->server)) {
			return false;
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($this->daemon) == null) {
			return false;
		}
		if($this->isCreated()) {
			return false;
		}
		$Daemon->setUser(mb_substr($this->uuid, 0, 8), $this->ftppass, "data/" . $this->uuid);
		$Http = new Http();
		return $Http->Request($Daemon->host . "?action=create&name=" . $this->uuid . "&token=" . md5($Daemon->pass));
	}
	
	/**
	 * Отправка команд на сервер
	 *
	 * @param $cmd Команды, которые необходимо выполнить
	 * @return String/Boolean Возвращает результат выполнения
	 */
	public function sendCommand($cmd) {
		if(empty($this->server)) {
			return false;
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($this->daemon) == null) {
			return false;
		}
		if(!$this->isCreated()) {
			return false;
		}
		$Http = new Http();
		return $Http->Request($Daemon->host . "?action=command&name=" . $this->uuid . "&token=" . md5($Daemon->pass) . "&cmd=" . urlencode($cmd));
	}
	
	/**
	 * Закрыть Deamon
	 *
	 * @return String/Boolean Возвращает результат выполнения
	 */
	public function close() {
		if(empty($this->server)) {
			return false;
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($this->daemon) == null) {
			return false;
		}
		if(!$this->isCreated()) {
			return false;
		}
		$Http = new Http();
		return $Http->Request($Daemon->host . "?action=close&name=" . $this->uuid . "&token=" . md5($Daemon->pass));
	}
	
	/**
	 * Получение маркера вывода журнала сервера
	 *
	 * @return String/Boolean Токен
	 */
	public function getToken() {
		if(empty($this->server)) {
			return false;
		}
		$Daemon = new Daemon();
		if($Daemon->setDaemon($this->daemon) == null) {
			return false;
		}
		return md5($Daemon->pass . $this->uuid);
	}
	
	/**
	 * Количество серверов в базе данных
	 *
	 * @return Int Общее количество серверов
	 */
	public function getCounts() {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers`");
		$i = 0;
		while($rw = mysqli_fetch_row($rs)) {
			$i++;
		}
		mysqli_close($conn);
		return $i;
	}
	
	/**
	 * Получить количество серверов, указанных в базе данных Daemon
	 *
	 * @return Int Общее количество серверов
	 */
	public function getCountsByDaemon($id) {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers` WHERE `daemon`='{$id}'");
		$i = 0;
		while($rw = mysqli_fetch_row($rs)) {
			$i++;
		}
		mysqli_close($conn);
		return $i;
	}
	
	/**
	 * Получение количества серверов для указанных пользователей в базе данных
	 *
	 * @return Int Общее количество серверов
	 */
	public function getCountsByOwner($id) {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers` WHERE `owner`='{$id}'");
		$i = 0;
		while($rw = mysqli_fetch_row($rs)) {
			$i++;
		}
		mysqli_close($conn);
		return $i;
	}
	
	/**
	 * Вывод списка серверов, которыми может управлять пользователь
	 *
	 * @return String Список серверов
	 */
	public function getServerList() {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$User = new User();
		$Profile = $User->getLoginUser();
		$ownerid = $Profile->id;
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers`");
		$data = "";
		while($rw = mysqli_fetch_row($rs)) {
			if(PHPMC::Permission()->check("server:" . $rw[0])) {
				$Daemon = new Daemon();
				if($Daemon->setDaemon($rw[2]) == null) {
					PHPMC::Error()->Println("500 Server Internal Error");
				}
				$data .= "<div class='server-hover' onclick='selectServer({$rw[0]}, this)'>
					<h5>{$rw[1]}</h5>
					<p>" . $Daemon->fqdn . ":{$rw[9]}</p>
				</div>";
			}
		}
		mysqli_close($conn);
		return $data;
	}
	
	/**
	 * Список серверов администрации
	 *
	 * @return String Список серверов
	 */
	public function getServerListAdmin() {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$User = new User();
		$Profile = $User->getLoginUser();
		$ownerid = $Profile->id;
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`servers`");
		$data = "";
		while($rw = mysqli_fetch_row($rs)) {
			$Daemon = new Daemon();
			if($Daemon->setDaemon($rw[2]) == null) {
				PHPMC::Error()->Println("500 Server Internal Error");
			}
			$Profile = new Profile($rw[7]);
			$data .= "<div class='server-hover' onclick='selectServer({$rw[0]}, this)'>
				<h5>{$rw[1]}</h5>
				<p>" . $Daemon->fqdn . ":{$rw[9]} | Владелец： " . $Profile->username . "</p>
			</div>";
		}
		mysqli_close($conn);
		return $data;
	}
}