<?php
class Daemon {
	
	public $daemon;
	public $id;
	public $name;
	public $host;
	public $pass;
	public $type;
	public $fqdn;
	
	/**
	 * Выберите Daemon для работы
	 *
	 * @param $daemon Daemon ID
	 * @return Boolean Возвращает результат выполнения
	 */
	public function setDaemon($daemon) {
		if(!empty($daemon)) {
			$this->daemon = $daemon;
			$db = Config::MySQL();
			$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
			$rs = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`daemon` WHERE `id`='{$daemon}'"));
			if($rs) {
				$this->id = $rs['id'];
				$this->name = $rs['name'];
				$this->host = $rs['host'];
				$this->pass = $rs['pass'];
				$this->type = $rs['type'];
				$this->fqdn = $rs['fqdn'];
				return true;
			} else {
				return null;
			}
		}
	}
	
	/**
	 * Настройка информации о пользователе FTP
	 *
	 * @param $user Имя пользователя
	 * @param $pass Пароль
	 * @param $home Каталог
	 * @return String/Boolean Возвращает результат выполнения
	 */
	public function setUser($user, $pass, $home) {
		if(empty($this->daemon)) {
			return false;
		}
		$Http = new Http();
		return $Http->Request($this->host . "?action=setuser&token=" . md5($this->pass) . "&user=" . urlencode($user) . "&pass=" . urlencode($pass) . "&home=" . urlencode($home));
	}
	
	/**
	 * Получение количества Daemon в базе данных
	 *
	 * @return Int Возвращает количество Daemon
	 */
	public function getCounts() {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`daemon`");
		$i = 0;
		while($rw = mysqli_fetch_row($rs)) {
			$i++;
		}
		return $i;
	}
	
	/**
	 * Получить все Daemon в базе данных и создать список
	 *
	 * @return String Возврат список демонов
	 */
	public function getOptionList() {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`daemon`");
		$data = "";
		while($rw = mysqli_fetch_row($rs)) {
			$data .= "<option value='{$rw[0]}'>{$rw[1]} ({$rw[5]})</option>";
		}
		return $data;
	}
	
	/**
	 * Создание нового Daemon в базе данных
	 *
	 * @param $name 		Отображаемое имя Daemon
	 * @param $host 		AJAX запрос адреса подключения
	 * @param $pass			Daemon Пароль подключения
	 * @param $fqdn			Доменное имя или IP-адрес
	 * @param $type			Тип операционной системы сервера
	 * @return Boolean		Состояние
	 */
	public function createDaemon($name, $host, $pass, $fqdn, $type) {
		$uuid = md5(md5(time() . rand(0, 999999)));
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		mysqli_query($conn, "INSERT INTO `{$db['name']}`.`daemon` (`id`, `name`, `host`, `pass`, `fqdn`, `type`) "
			. "VALUES (NULL, '{$name}', '{$host}', '{$pass}', '{$fqdn}', '{$type}')");
		return true;
	}
	
	/**
	 * Обновление данных Daemon в базе данных
	 *
	 * @param $id			ID сервера
	 * @param $name 		Отображаемое имя Daemon
	 * @param $host 		AJAX запрос адреса подключения
	 * @param $pass			Daemon Пароль подключения
	 * @param $fqdn			Доменное имя или IP-адрес
	 * @param $type			Тип операционной системы сервера
	 * @return Boolean		Состояние
	 */
	public function updateDaemon($id, $name, $host, $pass, $fqdn, $type) {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		mysqli_query($conn, "UPDATE `{$db['name']}`.`daemon` SET `name`='{$name}', `host`='{$host}', " 
			. "`pass`='{$pass}', `fqdn`='{$fqdn}', `type`='{$type}' WHERE `id`='{$id}'");
		return true;
	}
	
	/**
	 * Удаление Daemon из базы данных
	 *
	 * @param $id		Daemon ID
	 * @return Boolean	Статус удаления
	 */
	public function deleteDaemon($id) {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		mysqli_query($conn, "DELETE FROM `{$db['name']}`.`daemon` WHERE `id`='{$id}'");
		return true;
	}
	
	/**
	 * Список Daemon управления
	 *
	 * @return String Список Daemon
	 */
	public function getDaemonListAdmin() {
		$db = Config::MySQL();
		$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
		$rs = mysqli_query($conn, "SELECT * FROM `{$db['name']}`.`daemon`");
		$data = "";
		while($rw = mysqli_fetch_row($rs)) {
			$data .= "<div class='server-hover' onclick='selectDaemon({$rw[0]}, this)'>
				<h5>{$rw[1]}</h5>
				<p>{$rw[5]} | Операционная система： {$rw[4]}</p>
			</div>";
		}
		mysqli_close($conn);
		return $data;
	}
}