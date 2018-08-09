<!DOCTYPE html>
<html>
  	<body>
  		<div class="panel-heading">
        	<?= $titulo; ?>
           	<small><?= anchor(current_url(), '1 hora'); ?></small>
        	<small><?= anchor(current_url(), '6 horas'); ?></small>
        	<small><?= anchor(current_url(), '24 horas'); ?></small>
   		</div>
		<div id="chart_div" class="panel-body table-responsive"></div>
	</body>
  
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script src="bower_components/chartist/dist/chartist.min.js"></script>
	<script>
		google.charts.load('current', {'packages':['corechart']});
    	google.charts.setOnLoadCallback(drawChart);

      	function drawChart() {
			var jsonData = $.ajax({
	            url: "webservice/getChartData",
	            dataType: "json",
	            async: false
	            }).responseText;

	        var data = new google.visualization.DataTable(jsonData);

	        //console.log(data.toJSON());
	        
	        var options = {
	          	height: 200,
	          	hAxis: {
	           	format: 'HH:mm',
	            	gridlines: {count: 10}
	          	},
	          	vAxis: {
	            	gridlines: {count: 3},
	            	minValue: 0
	          	}
	        };
	
	        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
	        chart.draw(data, options);
	        
	        var button = document.getElementById('change');
	        button.onclick = function(){
	          	// If the format option matches, change it to the new option,
	          	// if not, reset it to the original format.
	          	options.hAxis.format === 'M/d/yy' ?
	          	options.hAxis.format = 'MMM dd, yyyy' :
	          	options.hAxis.format = 'M/d/yy';
	
	          	chart.draw(data, options);
	        };
      	}
	</script>
</html>