<?php
class Update {
	
	public function checkUpdate() {
		$data = json_decode(PHPMC::Http()->Request("https://www.phpmc.cn/update.php?version=" . PHPMC_VERSION), true);
		if(!$data) {
			return false;
		} else {
			if($data['version'] == PHPMC_VERSION) {
				return false;
			} else {
				return true;
			}
		}
	}
	
	public function getUpdateInfo() {
		$data = json_decode(PHPMC::Http()->Request("https://www.phpmc.cn/update.php?version=" . PHPMC_VERSION), true);
		if(!$data) {
			return false;
		} else {
			if($data['version'] == PHPMC_VERSION) {
				return false;
			} else {
				return $data;
			}
		}
	}
	
	public function updateExecute() {
		$data = $this->getUpdateInfo();
		if(!$data) {
			PHPMC::Error()->Println("Не удается обновить, проверьте правильность работы сети.");
		} elseif(!$this->checkPermission("./")) {
			PHPMC::Error()->Println("Каталог сайта не доступен для записи, измените разрешения или обновите вручную.");
		} elseif(!class_exists("ZipArchive")) {
			PHPMC::Error()->Println("Компонент ZipArchive не обнаружен, сначала измените php.ini включает расширение php_zip.");
		} else {
			$file = @PHPMC::Http()->Request($data['download']);
			if(strlen($file) == 0) {
				PHPMC::Error()->Println("Загруженный файл имеет длину 0, проверьте, работает ли сеть.");
			} elseif(file_put_contents('update-temp.zip', $file) === false) {
				PHPMC::Error()->Println("Произошла ошибка при записи файла, проверьте, есть ли в каталоге разрешения на чтение и запись.");
			} elseif(md5_file('update-temp.zip') !== $data['filemd5']) {
				@unlink('update-temp.zip');
				PHPMC::Error()->Println("Ошибка проверки файла MD5, попробуйте повторно обновить.");
			} else {
				if($this->unzipUpdateFiles('update-temp.zip', './')) {
					@unlink('update-temp.zip');
					PHPMC::Error()->Println("PHPMC Обновление прошло успешно, обновите веб-страницу.");
				} else {
					@unlink('update-temp.zip');
					PHPMC::Error()->Println("Произошла ошибка при извлечении файла, не удалось открыть файл или не удалось извлечь файл.");
				}
			}
		}
	}
	
	public function checkPermission($file) {
		if(is_dir($file)){
			$dir = $file;
			if($fp = @fopen("{$dir}/.writetest", 'w')) {
				@fclose($fp);
				@unlink("{$dir}/.writetest");
				return true;
			} else {
				return false;
			}
		} else {
			if($fp = @fopen($file, 'a+')) {
				@fclose($fp);
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function unzipUpdateFiles($fileName, $unzipPath) {
		$zip = new ZipArchive();
		$open = $zip->open($fileName);
		if($open === true) {
			return $zip->extractTo($unzipPath);
		}
		return false;
	}
}