// Здесь установлена задержка AJAX, которая может быть скорректирована в соответствии с вашей ситуацией
// Миллисекунды, например 1000, представляют собой одну секунду
var ajaxtimeout = 1000;
var oldlog;
var ConnectURL;
var server;
var errorLevel = 0;
var Interval;

function ajaxload() {
	if(ConnectURL == undefined) {
		return;
	}
	try {
		$(document).ready(function(){
			var start = new Date();
			var htmlobj = $.ajax({url:ConnectURL, async:true, timeout:10000, error: function(){
				$("#ping").html("Время ожидания соединения");
				window.parent.frames.showmsg("Соединение с сервером Daemon отключено.");
				clearInterval(Interval);
			}, success: function() {
				var end = new Date() - start;
				$("#ping").html(end + " Миллисекунды.");
				if(oldlog != htmlobj.responseText) {
					$("#debug").html("<code style='color: #FFF;background-color: none;padding: 0px;'>" 
					+ htmlobj.responseText.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g,"<br />")
					.replace("Token Error", "Ошибка: проверка авторизации не удалась, проверьте настройки демона.") + "</code>");
					if(autoflush.checked == true) {
						debug.scrollTop = debug.scrollHeight;
					}
					oldlog += htmlobj.responseText.replace(oldlog, "");
				}
				return;
			}});
		});
	} catch(Exception) {
		if(errorLevel >= 5) {
			window.parent.frames.showmsg("Соединение с сервером Daemon отключено.");
			clearInterval(Interval);
		} else {
			errorLevel++;
			return;
		}
	}
};

window.onkeydown = function(event){
	if(event.keyCode == 13) {
		var command = $("#command").val();
		sendCommand(command);
		$("#command").val("");
		return false;                               
	}
};

function sendCommand(cmd) {
	var htmlobj = $.ajax({url:"?action=sendcommand&id=" + server + "&cmd=" + encodeURIComponent(cmd), async:true, timeout:10000, error: function(){
		window.parent.frames.showmsg(htmlobj.responseText);
	}});
}

window.onload = function() {
	$("#debug").html("<code style='color: #FFF;background-color: none;padding: 0px;'>Добро пожаловать в PHPMC <span class='text-success'>7</span> Minecraft Server Manager.<br>Выберите сервер.</code>");
	ajaxload();
	serverStatus();
};

function startServer() {
	var htmlobj = $.ajax({url:"?action=start&id=" + server, async:true, timeout:10000, error: function(){
		window.parent.frames.showmsg(htmlobj.responseText);
	}});
};

function stopServer() {
	var htmlobj = $.ajax({url:"?action=stop&id=" + server, async:true, timeout:10000, error: function(){
		window.parent.frames.showmsg(htmlobj.responseText);
	}});
};

function restartServer() {
	var htmlobj = $.ajax({url:"?action=restart&id=" + server, async:true, timeout:10000, error: function(){
		window.parent.frames.showmsg(htmlobj.responseText);
	}});
};

function selectServer(id, element) {
	window.parent.frames.progressshow("Пожалуйста, загружайте позже...");
	clearInterval(Interval);
	$(".server-hover").attr("style", "");
	element.style.border = "1px solid rgba(255,255,255,0.3)";
	var htmlobj = $.ajax({
		url:"?action=getserver&id=" + id,
		async:true,
		timeout:10000,
		error: function() {
			window.parent.frames.showmsg(htmlobj.responseText);
		},
		success: function() {
			var result = htmlobj.responseText;
			var obj = JSON.parse(result);
			console.log(obj);
			server = obj.id;
			gamehost.innerHTML = obj.gamehost;
			ftpuser.innerHTML = obj.ftpuser;
			ftppass.innerHTML = obj.ftppass;
			oldlog = "";
			ConnectURL = obj.host + "?action=getlogs&token=" + obj.token + "&name=" + obj.uuid;
			window.parent.frames.progressunshow();
			Interval = setInterval("ajaxload()", ajaxtimeout);
			return;
		}
	});
};

function serverStatus() {
	var htmlobjs = $.ajax({url:"?action=status&id=" + server, async:true, timeout:10000, success: function(){
		var rpt = htmlobjs.responseText;
		var fallback = rpt.split("\/");
		$("#online").html(fallback[0]);
		$("#max").html(fallback[1]);
		setTimeout(serverStatus, 10000);
	}});
};