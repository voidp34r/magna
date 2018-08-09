<div class="panel panel-default">
    <div class="panel-heading">
        Central de Entregas
        &nbsp
        &nbsp
        <small><a href="cce_entregas0800/graficoEntregas0800/hoje/<?=$tipoGrafico;?>">Movimentações de Hoje</a></small>&nbsp-&nbsp&nbsp
        <small><a href="cce_entregas0800/graficoEntregas0800/mes/<?=$tipoGrafico;?>">Movimentações do Mês</a></small>
        -&nbsp&nbsp<small><a id="aFiltro" data-toggle="collapse" href="#filtro">Movimentações por período</a></small>
        -&nbsp&nbsp<small><a data-toggle="modal" data-toggle="modal" data-target="#myModal" href="">Mudar tipo do gráfico</a></small>
    </div>
    <div class="collapse" id="filtro">
        
        <!--DIV QUE AVISA O CAMPO OBRIGATÓRIOS-->
        <div id="infoCampos" class="panel-heading">
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">< span aria-hidden="true">&times;</span></button>
                <label id="infowar">Info!</label>
            </div>
        </div>
        <!-- FIM DA DIV DO FILTRO -->
        
        <!-- Campo que serão filtrados -->
        <div class="panel-body" >
            <form action="cce_entregas0800/graficoEntregas0800/null/<?=$tipoGrafico;?>">
                <div class="row"> 
                    <div id="dateIni" class="col-sm-2">
                        <label>	Data Início</label>
                        <?= campo_filtro($filtro, 'DTINI', 'date'); ?>
                    </div>
                    <div id="dateFim" class="col-sm-2">
                        <label>Data Fim</label>
                        <?= campo_filtro($filtro, 'DTFIM', 'date'); ?>
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
        <div id="divGraficos"> 
            <!--AQUI VEM O GRÁFICO-->
        </div>
        
        <!-- Modal para o usuário escolher o tipo de gráfico-->
        <div class="modal fade" tabindex="-1" role="dialog" id="myModal" >
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Selecione um tipo de gráfico</h4>
              </div>
                <div class="modal-body">
                    <div style="margin : auto 110px">
                      <form>
                        <button type="submit" class="btn btn-primary" formaction="cce_entregas0800/graficoEntregas0800/null/romaneio">Data do romaneio</button>
                        <button type="submit" class="btn btn-primary" formaction="cce_entregas0800/graficoEntregas0800/null/movimento">Data do movimento</button>                        
                      </form>
                    </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Fim do Modal-->
        
        
    </div>        
</div>
<script>
    var quantidadeSoftran = 0;
    var quantidadeWebApp = 0;
    var data;
    var chart;
    
    $( document ).ready(function(){
        $("#dateFim").find("input").mask("99/99/9999");
        $("#dateIni").find("input").mask("99/99/9999");
        $("#dateFim").find("input").on("focusout",verificaData);
        $("#dateIni").find("input").on("focusout",verificaData);
        $("#infoCampos").toggle(false);
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
    
    var tipoGrafico = '<?= $tipoGrafico?>';
    
    if(!tipoGrafico){
        $('#myModal').modal('show');
    }else{
        var graficoSoftran = <?= json_encode($graficoEntregas)?>;
        if(graficoSoftran.length > 0 ){
            google.charts.load('current', {'packages':['corechart']});
            
            if(tipoGrafico == 'romaneio'){
                google.charts.setOnLoadCallback(drawChartRoman);
            }else{
               google.charts.setOnLoadCallback(drawChartMov); 
            }
                
        }else{
            $("#divGraficos").append("<h1>Não houve resultado para sua busca.</h1>");
        }        
    }
    
    function drawChartMov(){
        
        graficoSoftran.forEach(function(item){
        
            data = new google.visualization.DataTable();
            data.addColumn('string','Tipo de Entrega');
            data.addColumn('number','Quantidade');
            var siglaFilial = item.DSEMPRESA.slice(item.DSEMPRESA.indexOf('-')+1); 
            var codEmpresa  = item.CDEMPRESADESTINO; 
            var descFilial =  codEmpresa+"-"+siglaFilial;
            
            data.addRow(['Softran - '+item.QUANTIDADESOFTRAN,parseInt(item.QUANTIDADESOFTRAN)]);
            data.addRow(['Central - '+item.QUANTIDADECENTRAL ,parseInt(item.QUANTIDADECENTRAL)]);
            
            var options = {
                title:   descFilial,
                colors: ['#c0392b', '#2ecc71', '#f1c40f','#2980b9'],
                is3D: true
            };
            
            $("#divGraficos").append('<div id='+codEmpresa+' style=" display: inline-block width: 500px; height: 300px"><h1>teste<h1> </div>');
            chart = new google.visualization.PieChart(document.getElementById(codEmpresa));
            chart.draw(data,options);
            
        });
    }
    
    
 
    
    function drawChartRoman() {
       
       graficoSoftran.forEach(function(item){

            data = new google.visualization.DataTable();
            data.addColumn('string','Tipo de Entrega');
            data.addColumn('number','Quantidade');
            var siglaFilial = item.DSEMPRESA.slice(item.DSEMPRESA.indexOf('-')+1); 
            var codEmpresa  = item.CODIGOEMPRESA; 
            var descFilial =  codEmpresa+"-"+siglaFilial;
            
            
            data.addRow(['Softran - '+item.QUANTIDADEMOVIMENTOSOFTRAN,parseInt(item.QUANTIDADEMOVIMENTOSOFTRAN)]);
            data.addRow(['Central - '+item.QUANTIDADECENTRALCERTA ,parseInt(item.QUANTIDADECENTRALCERTA)]);
            data.addRow(['Erradas - '+item.QUANTIDADECENTRALERRADA,parseInt(item.QUANTIDADECENTRALERRADA)]);            
            data.addRow(['Não Entregue - ' + item.QUANTIDADEROMANABERTO +' ('+item.QUANTIDADEDIA+')'  , parseInt(item.QUANTIDADEROMANABERTO) ]);
            
            var options = {
                title:   descFilial,
                subtitle : 'teste3',
                colors: ['#c0392b', '#2ecc71', '#f1c40f','#2980b9'],
                is3D: true
            };

            $("#divGraficos").append('<div id='+codEmpresa+' style=" display: inline-block width: 500px; height: 300px"> </div>')
            chart = new google.visualization.PieChart(document.getElementById(codEmpresa));
            chart.draw(data,options);

        });
        
      }
</script>