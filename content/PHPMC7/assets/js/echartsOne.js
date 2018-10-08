var myChart;
function echartStr(names,brower){
	if (myChart != null && myChart != "" && myChart != undefined) {  
	    myChart.dispose();  
	} 
	myChart = echarts.init(document.getElementById('canvas_main'));
	var option = {
		title : {
		    text: 'PHPMC7 Статистика',
		    subtext: 'Статистика данных сервера',
		    x:'center',
			color: '#FFFFFF'
		},
		tooltip : {
		    trigger: 'item',
		    formatter: "{a} <br/>{b} : {c} ({d}%)"
		},
		legend: {
		    orient: 'vertical',
		    left: 'left',
		    data: names
		},
		series : [
		{
		    name: 'Статистика',
		    type: 'pie',
		    radius : '55%',
		    center: ['50%', '60%'],
		    data: brower,
		    itemStyle: {
				emphasis: {
					shadowBlur: 16,
					shadowOffsetX: 0,
					shadowColor: 'rgba(0, 0, 0, 0.7)'
				}
			},
		    label: {
				normal: {
					show: false,
				}
	        },
		}]
	};
	myChart.setOption(option);
};

function getPie(that){
	var brower = [], names = [];
	var index = $(that).data('index');
	$.ajax({
	    type: 'get',
	    url: '/?action=getserverinfo&type=1',
	    dataType: "json",
	    success: function (result) {
	        $.each(eval('result.list'), function (index, item) {
	            names.push(item.name);
	            brower.push({
	                name: item.name,
	                value: item.value
	            });
	        });
	        echartStr(names,brower);
	    },
	    error: function (errorMsg) {
	        window.parent.frames.showmsg("Не удалось получить информацию о графике сервера!");
		}
	});
}