<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
        <small>
            (<?= $total; ?>) &nbsp - &nbsp <?= anchor(current_url(), 'Atualizar'); ?>
            &nbsp
            &nbsp
            <a id="aFiltro" data-toggle="collapse" href="#filtro">Filtrar</a>
        </small>
    </div>
    <div class="collapse" id="filtro">
        <div id="infoCampos" class="panel-heading">
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <label id="infowar">Info!</label>
            </div>
        </div>
        <div class="panel-body">
            <form action="cce_entregas0800/infoEntregas0800/1">
                <div class="row">
                    <div class="col-sm-2">
                        <label>Nome motorista</label>
                        <?= campo_filtro($filtro, 'DSNOMEMOTORISTA', 'like'); ?>
                    </div>
                    <div class="col-sm-2">
                        <label>Documento</label>
                        <?= campo_filtro($filtro, 'NRDOCTOFISCAL', 'like'); ?>
                    </div>
                    <div class="col-sm-2">
                        <label>Destinario</label>
                        <?= campo_filtro($filtro, 'DSDESTINARIO', 'like'); ?>
                    </div>
                    <div id="dateIni" class="col-sm-2">
                        <label>	Data Início</label>
                        <?= campo_filtro($filtro, 'DTINI', 'date'); ?>
                    </div>
                    <div id="dateFim" class="col-sm-2">
                        <label>Data Fim</label>
                        <?= campo_filtro($filtro, 'DTFIM', 'date'); ?>
                    </div>                    
                    <div class="col-sm-2">
                        <label>Tipo de Entrega</label>
                        <select multiple name="getFiltroDate[]" class="form-control input-small">
                          <option value="1">Inicio</option>
                          <option value="3">Normal</option>
                          <option value="5">Problema</option>
                        </select>
                    </div>
                    <div class="col-sm-2 ">
                        <label>Tempo de Entrega <small>Hora:Minuto</small></label>
                        <div class="input-group">
                            <input name="hrini" id="hrini" type="text" class="form-control input-sm"/>
                            <span class="input-group-addon input-sm">Até</span>
                            <input name="hrfim" id="hrfim" type="text" class="form-control input-sm"/>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>Filial Entregadora <small>(Código)</small> </label>
                        <input  class="form-control input-sm" type="number" name="cdFilial">
                    </div>
                    <div class="col-sm-2">
                        <label>Filial Entregadora <small>(Descrição)</small> </label>
                        <?= campo_filtro($filtro, 'DSFILIALENTREGA', 'like'); ?>
                    </div>
                    <div class="col-sm-2">
                        <label>Taxas da Entrega <small>(TDE/TDA)</small> </label> 
                        <select multiple id="filtroTeste" name="getFiltroTipo[]" class="selectpicker" data-live-search="false">
                          <option value="1">Com TDE</option>
                          <option value="2">Com TDA</option>
                          <option value="3">Sem TDE</option>
                          <option value="4">Sem TDA</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <br>
                    	<label>&nbsp</label>
                        <input type="submit" value="Filtrar" class="btn btn-sm btn-primary btn-block">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php if (!empty($lista)){ ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Filial Entregadora</th>
                    <th>Motorista</th>
                    <th>Filial Origem/Documento</th>
                    <th>Destinario</th>
                    <th>Data Início</th>
                    <th>Data Fim</th>
                    <th>Ultima Ocorrencia</th>
                    <th>Tempo de Entrega</th>
                    <th>TDE</th>
                    <th>TDA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $item){ ?> 
                    <tr>
                        <td><?php
                            $pos = strpos($item->DSFILIALENTREGA, '-');
                            $siglaEmp = substr($item->DSFILIALENTREGA, $pos+1);
                            $desc = "{$item->CDFILIALENTREGA} - {$siglaEmp}";
                            echo $desc; 
                            
                         ?>
                        </td>
                        <td><?= $item->DSNOMEMOTORISTA;?></td>
                        <td><?= $item->CDFILIALORIGEM;?>-<?= $item->NRDOCTOFISCAL;?> </td>
                        <td><?= $item->DSDESTINARIO;?></td>
                        <td><?= data_oracle_para_web($item->DTINI,8)?></td>
                        <td><?= data_oracle_para_web($item->DTFIM,8);?></td>
                        <td><?php 
                            //Caso não tenha nenhuma ocorrência a Entrega já foi iniciada, porem não foi finalizada
                            switch ($item->CDULTIMAOCORRENCIA){
                                 case 1 :
                                    echo "Entrega Iniciada";
                                    break;
                                case 3:
                                    echo "Entrega Realizada Normalmente";
                                    break;
                                case 5:
                                    echo "Problema na entrega";
                                    break;
                            }
                            ?>
                        </td>
                        <td><?php 
                                if(!is_null($item->DTFIM)){
                                  echo compareTwoDate(data_oracle_para_web($item->DTINI,8),data_oracle_para_web($item->DTFIM,8));   
                                }
                        ?></td>
                        <td><?php 
                            if($item->TDE == 0){
                                echo "NÃO";
                            }else{
                                echo "SIM";
                            }
                        
                        ?></td>
                        <td><?php 
                            if($item->TDA == 0){
                                echo "NÃO";
                            }else{
                                echo "SIM";
                            }
                        
                        ?></td>
                    </tr>
                 <?php } ?>
            </tbody>
        </table> 
        <?php } ?>
    </div>
</div>    
<nav>
<ul class="pagination">
    <?= $paginacao; ?>
</ul>
</nav>
<script> 
    var sqlPagination;    
    /*
     * Ao iniciar Realiza a adição das mascaras e seta os eventos para os componentes
     */
    $( document ).ready(function() {
        var sqlPagination =  "<?= $sqlPagination;?>";
        $("#dateFim").find("input").mask("99/99/9999");
        $("#dateIni").find("input").mask("99/99/9999");
        $("#hrini").mask("99:99");
        $("#hrfim").mask("99:99");
        
        $("#dateFim").find("input").on("focusout",verificaData);
        $("#dateIni").find("input").on("focusout",verificaData);

        $("#hrfim").on("focusout",verificaHora);
        $("#hrini").on("focusout",verificaHora);
        $("#infoCampos").toggle(false);
        
        
        $('ul.pagination').find('a').each(function(index,item){ 
            if(sqlPagination){
                var href =  $(this).attr('href')+'/?sqlPagination='+sqlPagination;
                $(this).attr('href',href);               
            }
        });
                
    });
    
    
    $(document).bind("DOMSubtreeModified",function(){
       $('div.bs-searchbox').remove();
       $('button.btn.dropdown-toggle.btn-default').addClass('form-control input-sm');
       
    });
    
    
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
    
    /*
     * Verifica se as Horas são validas, Verifica se a hora inicial é maior que a inicial
     */
    function verificaHora(){
        
        var hrini = $("#hrini").val();
        var hrfim = $("#hrfim").val();
        
        if(hrini != "" && hrfim != ""){
          
          var datIni = new Date();  
          var arr1 = hrini.split(":"); 
          datIni.setHours(arr1[0],arr1[1],0);
 
          var datFim = new Date();  
          var arr2 = hrfim.split(":"); 
          datFim.setHours(arr2[0],arr2[1],0);
          
          if(datIni > datFim){
              setDescWarning("A Hora inicial não pode ser maior que a hora final!");
              $("#infoCampos").toggle(true);
              $("#hrini").val("");
              $("#hrfim").val("");
              $("#hrini").focus();
              
          }else{
              $("#infoCampos").toggle(false);
          }
 
        }else{
            $("#infoCampos").toggle(false);
        }
        
    }
    
    function setDescWarning(info){
         $("#infowar").text(info);
    }
    
</script>