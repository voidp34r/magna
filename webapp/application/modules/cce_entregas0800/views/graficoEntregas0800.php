<style>
    .linkDetail{
        text-align : center;
        font-size : 14px;
        color : white;
        border-radius : 8px;
        background : #34495e;
        padding : 5px;
        margin-bottom : 50px;       
        border : none;
    }
    
    .linkDetail:hover{
        text-decoration : none;
        color : white;
        background : #2c3e50;
        border : none;
    }
</style>
<div class="panel panel-default">
    <div id="filtro">
        
        <!--DIV QUE AVISA O CAMPO OBRIGATÓRIOS-->
        <div id="infoCampos" class="panel-heading">
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <label id="infowar">Info!</label>
            </div>
        </div>
        <!-- FIM DA DIV DO FILTRO -->
        
        <!-- Campo que serão filtrados -->
        <div class="panel-body" >
            <form action="cce_entregas0800/graficoEntregas0800/null">
                <div class="row"> 
                    <div id="dateIni" class="col-sm-2">
                        <label>	Data Início</label>
                        <?= campo_filtro($filtro, 'DTINI', 'date'); ?>
                    </div>
                    <div id="dateFim" class="col-sm-2">
                        <label>Data Fim</label>
                        <?= campo_filtro($filtro, 'DTFIM', 'date'); ?>
                    </div>  
                    <div id="dateFim" class="col-sm-2">
                        <label>Código da filial</label>
                        <input type='number' class="form-control input-sm" name="cdempresa" required>
                    </div>                      
                    <div class="col-sm-2">
                    	<label>&nbsp</label>
                        <input type="submit" value="Filtrar" class="btn btn-sm btn-primary btn-block">
                    </div>
                </div>
            </form>
        </div>    
        <!-- Fim dos Campos que serão filtrados -->
                
    </div>
    <div class="panel-body">

        <div class="col-md-12" id="divGraficos"> 
            <!-- Gráfico de Entregas -->
        </div>

        <div class="col-md-6"> 
            <h3>Entregas Central</h3>
            <table class="table table-hover" id="tableCentral" >
                <tr>
                    <th>Cte</th>
                    <th>Empresa Emitente</th>
                    <th>Motorista</th>
                    <th>Cpf Motorista</th>
                    <th>Documento de Entrega</th>
                </tr>
            </table>
        </div>

        <div class="col-md-6"> 
            <h3>Entregas Softran</h3>
            
            <table class="table table-hover" id="tableSoftran" >
                <tr>
                    <th>Cte</th>
                    <th>Empresa Emitente</th>
                    <th>Motorista</th>
                    <th>Cpf Motorista</th>
                    <th>Documento de Entrega</th>
                </tr>
            </table>
            
        </div>
        
        
    </div>        
</div>
<script>
    var quantidadeSoftran = 0;
    var quantidadeWebApp = 0;
    var data;
    var chart;
    var dtIniDetalhe;
    var dtFimDetalhe;
    
    $( document ).ready(function(){
        
        $('#infoLabel').hide(); 
        $("#dateFim").find("input").mask("99/99/9999");
        $("#dateIni").find("input").mask("99/99/9999");
        $("#dateFim").find("input").on("focusout",verificaData);
        $("#dateIni").find("input").on("focusout",verificaData);
        $("#infoCampos").toggle(false)

        dtIniDetalhe =  "<?php isset($dtIni) ? $dtIni : '' ?>";
        dtFimDetalhe =  "<?php isset($dtFim) ? $dtFim : '' ?>";
        
        //Caso não tenha aspas simlples(') adiciona, pois tornara parametro do link
        dtIniDetalhe = dtIniDetalhe.indexOf("'") ? "'"+dtIniDetalhe+"'" : dtIniDetalhe; 
        dtFimDetalhe = dtFimDetalhe.indexOf("'") ? "'"+dtFimDetalhe+"'" : dtFimDetalhe;

        //Retira a (') da data para passar para o link
        dtIniDetalhe = dtIniDetalhe.replace("'DD/MM/YYYY'",'DD/MM/YYYY');
        dtFimDetalhe = dtFimDetalhe.replace("'DD/MM/YYYY'",'DD/MM/YYYY');

    });     
    
    function setDescWarning(info){
         $("#infowar").text(info);
    }
    
    /*
    * Verifica se as Datas são válidas, Compara se a Data Inicio é menor que a data final.
    */
    function verificaData(){

       var dtIni =  $("#dateIni").find("input").val();       
       var dtFim =  $("#dateFim").find("input").val();
       
       if(dtFim != "" && dtIni != ""){
           
        var arr1 = dtIni.split('/');
        var dataInicio = new Date(arr1[2], arr1[1]-1 , arr1[0]);

        var arr2 = dtFim.split('/');
        var dataFim = new Date(arr2[2], arr2[1]-1, arr2[0]);

        if(dataInicio > dataFim ){
            setDescWarning("A Data inicial não pode ser maior que a Data final!");
            $("#infoCampos").toggle(true);
            $("#dateIni").find("input").val("");
            $("#dateFim").find("input").val("");
            $("#dateIni").find("input").focus();
           
       }else{ 
          $("#infoCampos").toggle(false);
       }
        
    }else{
        $("#infoCampos").toggle(false);
    }
    
    }
    

    const graficoSoftran = <?= json_encode($graficoEntregas)?>;
    const descEmp =  <?= json_encode($descEmp)?>; 


    if(graficoSoftran.central || graficoSoftran.softran){

        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChartMov);
                
    }else{
        $("#divGraficos").append("<h1>Não houve resultado para sua busca.</h1>");
    } 

    
    function drawChartMov(){

        data = new google.visualization.DataTable();
        data.addColumn('string','Tipo de Entrega');
        data.addColumn('number','Quantidade');
        let qtCentral = parseInt(graficoSoftran.central.length);
        let qtSoftran = parseInt(graficoSoftran.softran.length);
        data.addRow([` Softran - ${qtSoftran}`,qtSoftran ]);
        data.addRow([` Central - ${qtCentral}`,qtCentral ]);
        var options = {
            title:   descEmp,
            colors: ['#c0392b', '#2ecc71', '#f1c40f','#2980b9']
        };

        $("#divGraficos").append('<div id="grafico" style=" display: inline-block width: 500px; height: 300px"> </div>');
        chart = new google.visualization.PieChart(document.getElementById("grafico"));
        chart.draw(data,options);
        addRowGrid(qtCentral,qtSoftran);
        
    }

    function addRowGrid(qtCentral,qtSoftran){
        
        if(qtCentral){
            graficoSoftran.central.forEach( item => {
                let html = getHtml(item);
                $('#tableCentral').append(html);
            });
        }

        if(qtSoftran){
            graficoSoftran.softran.forEach( item => {
                let html = getHtml(item);
                $('#tableSoftran').append(html);
            });
        }

    }

    function getHtml(data){
        return  `<tr>
                    <td>${data.CTE}</td>
                    <td>${data.CDEMPRESACTE}</td>
                    <td>${data.DSNOME}</td>
                    <td>${data.MOT}</td>
                    <td>${data.DOCFINAL}</td>
                 </tr>` 
    }
    
</script>
