<?php
class Register {
	
	public static function onload() {
		global $Loader;
		$Loader->Event->registerClass("defaultActionEvent", new Register()); // Зарегистрировать событие defaultActionEvent
	}
	
	public function defaultActionEvent($test) {
		// print_r($test); // Выходной массив параметров GET
		return false; // Не отменять событие
	}
}