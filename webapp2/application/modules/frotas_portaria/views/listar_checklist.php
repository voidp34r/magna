<style>
    .labelModal{margin : 5px}
    
    .labelDiv{margin : 20px;color : #c0392b}
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        Checklists -  <small>Total: <?= $total ?></small>   &nbsp&nbsp
        <small>
              <?= anchor(current_url(), 'Atualizar'); ?>         
              -&nbsp&nbsp<a id="aFiltro" data-toggle="collapse" href="#filtro">Filtrar</a>
        </small>
        <div class="collapse" id="filtro">
            
            <div class="panel-body">
                <div class="row">
                    <form>
                      <div class="row">    
                        <div class="col-md-2" id='plateFind'>
                            <label>Placa do Véículo</label>
                            <?= campo_filtro($filtro, 'NRPLACA', 'like'); ?>
                        </div>      

                        <div id="dateIni" class="col-sm-2">
                            <label>Data Início</label>
                                <?= campo_filtro($filtro, 'DTINI', 'date'); ?>
                        </div>
                        <div id="dateFim" class="col-sm-2">
                            <label>Data Fim</label>
                            <?= campo_filtro($filtro, 'DTFIM', 'date'); ?>
                        </div> 
                        
                        
                        <div class="col-sm-2">
                            <label>Hodômetro</label>
                            <select multiple name="int[TPACAO]" class="form-control input-small"  multiple="multiple" size="1">
                              <option value="1">NÃO</option>
                              <option value="2">SIM</option>
                            </select>                            
                        </div>
                          
                        <div class="col-sm-2">
                            <label>QrCode</label>
                            <select multiple name="int[INQRCODE]" class="form-control input-small" multiple="false">
                              <option value="0">NÃO</option>
                              <option value="1">SIM</option>
                            </select>                            
                        </div>                          
                          
                        <div class="col-sm-2">
                            <label>&nbsp</label>
                            <input type="submit" value="Filtrar" class="btn btn-sm btn-primary btn-block">
                        </div>
                      </div>  
                      <div class="row">
                        <?php if(isset($download)) { ?>
                            <div class="col-sm-2 pull-right">
                                <input type="button" class="btn btn-sm btn-success btn-block" value="Baixar Fotos" onclick="$('#modalMsg').modal('show')">
                            </div>
                        <?php } ?>
                        <?php if(isset($download_planilha_portaria)) { ?>
                            <div class="col-sm-2 pull-right">
                                <input
                                    class="btn btn-sm btn-default btn-block" 
                                    value="Baixar Planilha"
                                    onclick="downPlanilha()"
                            </div>
                        <?php } ?>

                      </div> 
                    </form>                    
                </div>
            </div>
            
            
        </div>    
    </div>
    <div class="panel-body table-responsive">
        <?php
        
        if (isset($lista))
        {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cód.</th>
                        <th>Placa do veiculo</th>
                        <th>Data Inclusão</th>
                        <th>Atualizou Hodômetro</th>
                        <th>Leu QrCode</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item)
                    {   
                        ?>
                        <tr>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                              <?= $item->NRPLACA; ?>
                            </td>

                            <td>
                                <?= data_oracle_para_web($item->DTINCLUSAO); ?>
                            </td>
                            <td>
                                <i class="fa <?= $item->TPACAO == 2 ? 'fa-check' : 'fa-exclamation-circle'; ?>" aria-hidden="true" 
                                   style="color : <?= $item->TPACAO == 2 ? '#27ae60' : '#c0392b'; ?>">  
                                </i>
                            </td>
                            <td> 
                                <i class="fa <?= $item->INQRCODE ? 'fa-check' : 'fa-exclamation-circle'; ?>" aria-hidden="true" 
                                   style="color : <?= $item->INQRCODE? '#27ae60' : '#c0392b'; ?>">   
                                </i>
                            </td>
                            <td align="right">
                                <a href="frotas_portaria/ver_checklist/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-folder-open"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table> 
            <?php
        }
        ?>
        <div class="modal" id="modalMsg">
            <div class="modal-dialog">
                <div class="modal-content">
                    
                    <div class="modal-header">
                        <h4 class="modal-title">Selecione a data para realizar o download</h4>
                    </div>
                    
                    <div class="modal-body">
                        <div  class="row" align="center">
                            
                            <form>
                                <div class="form-group">
                                    
                                    <label for="dtIniDown" class="labelModal">Inicio</label>
                                    <input type="date" id="dtIniDown" value="<?php echo date("Y-m-d");?>">
                                    
                                    <label class="labelDiv">Até</label>
                                    
                                    <label for="dtFimDown" class="labelModal">Data Final</label>
                                    <input type="date" id="dtFimDown" value="<?php echo date("Y-m-d");?>">
                                </div>
                            </form>
                            
                        </div>    
                        <div  class="row" align="center">
                            <input type="button" class="btn btn-danger" value="Cancelar" onclick="$('#modalMsg').modal('hide')">
                            <input type="button" class="btn btn-success" value="Confirmar" onclick="downloadImagens()">
                        </div>                            
                            
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>
    </div>
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>

<script>     
    $('document').ready(function(){
        $("#dateFim").find("input").mask("99/99/9999");
        $("#dateIni").find("input").mask("99/99/9999");
        $("#plateFind").find("input").mask("aaa-9999");
        
        $(document).bind("DOMSubtreeModified",function(){
            $('div.bs-searchbox').remove();
            $('button.btn.dropdown-toggle.btn-default').addClass('form-control input-sm');
        });
    });
    
    
    function downPlanilha(){
        var dtIni = $("#dateIni").find("input").val(); 
        var dtFim = $("#dateFim").find("input").val();
        
        if( dtIni && dtFim ){
            $.ajax({
                url: 'frotas_portaria/baixar_planilha',
                type: 'POST',
                data: { dtIni, dtFim },
                success: data => {
                    $(data).table2excel({name: "Download", filename: "Checklist" });
                }
            });        
        }
    }

    function downloadImagens(){
        
        var dtIni = $('#dtIniDown').val(); 
        var dtFim = $('#dtFimDown').val(); 
         
        if( dtIni && dtFim ){        
            $.ajax({
                url: 'frotas_portaria/download_zip_image/'+dtIni+'/'+dtFim,
                type: 'GET',
                success: () => {
                    window.location = 'frotas_portaria/download_zip_image/'+dtIni+'/'+dtFim;
                }
            });
        }
    }
</script>    