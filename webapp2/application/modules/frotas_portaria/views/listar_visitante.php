<a class="btn btn-success" href="frotas_portaria/cadastro_visitante">
    <i class="fa fa-fw fa-plus"></i>
    Novo Visitante
</a>
<button class="btn btn-default" onclick="$('#modalPlanilha').modal('show')">
    <i class="fa fa-table"></i>
    Baixar Planilha
</button>
<div class="panel panel-default">
    <div class="panel-heading">
        Visitantes
        <small>
            (<?= $total; ?>)&nbsp - &nbsp<?= anchor(current_url(), 'Atualizar'); ?>&nbsp 
            - &nbsp<a id="aFiltro" data-toggle="collapse" href="#filtro">Filtrar</a> &nbsp -
            &nbsp
            <?php if(uri_string() != 'frotas_portaria/listar_visitante/1/true') { ?>
                <?= anchor("frotas_portaria/listar_visitante/1/true", 'Visitas encerradas'); ?>           
            <?php } else { ?>
                <?= anchor("frotas_portaria/listar_visitante", 'Visitas em andamento'); ?>
            <?php } ?>
        </small>
        <div class="collapse" id="filtro">
            <div class="panel-body">
                <div class="row">
                    <form>
                        <div class="row">       
                            <div id="dateIni" class="col-sm-2">
                                <label>Data Início</label>
                                <?= campo_filtro($filtro, 'DTINI', 'date'); ?>
                            </div>
                            <div id="dateFim" class="col-sm-2">
                                <label>Data Fim</label>
                                <?= campo_filtro($filtro, 'DTFIM', 'date'); ?>
                            </div>   

                            <div class="col-sm-2">
                                <label>Tipo Documento</label>
                                <?= form_dropdown('TPDOCTO', ["CPF" => "CPF","RG" => "RG"] , set_value('TPDOCTO'), 'required id="TPDOCTO"' ); ?>                           
                            </div>    

                            <div id="NRDOCUMENTO" class="col-sm-2">
                                <label>Documento</label>                                
                                <?= campo_filtro($filtro, 'NRDOCUMENTO', 'text'); ?>                     
                            </div>                   
                            
                            <div class="col-sm-2">
                                <label>&nbsp</label>
                                <input type="submit" value="Filtrar" class="btn btn-sm btn-primary btn-block">
                            </div>
                            
                        </div>  
                    </form>                    
                </div>
            </div>
            
            
        </div>    
    </div>
    <div class="panel-body table-responsive">
        <?php
            if (!empty($lista))
            {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo visita</th>
                        <th>Tipo do Documento</th>
                        <th>Documento</th>
                        <th>Empresa</th>
                        <th>Tel empresa</th>
                        <th>Tem Veículo</th>
                        <th>Solicitante</th>
                        <th>Hora Entrada</th>
                        <th>Hora Saída</th>
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
                                <?= $item->NOMEVISITA ?>
                            </td>
                            <td>
                                <?= $item->TPVISITA ?>
                            </td>
                            <td>
                                <?= $item->TPDOCTO ?>  
                            </td>
                            <td>
                                <?= $item->NRDOCUMENTO ?>  
                            </td>
                            <td>
                                <?= $item->DSEMPRESA ?>  
                            </td>
                            <td>
                                <?= $item->TELEFONEEMPRESA?>
                            </td>
                            <td>
                                <?php if($item->FGVEICULO == "SIM"){ ?>
                                    <button onclick="showInfo('<?= $item->TPVEICULO ?>','<?= $item->MODELO ?>','<?= $item->COR ?>','<?= $item->PLACA ?>')" class='btn btn-success'>
                                        SIM
                                    </button>
                                <?php }else{ ?>
                                      <?= $item->FGVEICULO ?>
                                <?php }?> 
                            </td>                        
                            <td>
                                <?= $item->SOLICITANTE?> 
                            </td>
                            <td>
                                <?= data_oracle_para_web($item->DTENTRADA)?> 
                            </td>
                            <td>
                                <?= isset($item->DTSAIDA) ? data_oracle_para_web($item->DTSAIDA) : '' ?> 
                            </td>
                            <?php if(!isset($item->DTSAIDA)){ ?>
                                <td align="right">
                                    <a href="frotas_portaria/ver_visitante/<?= $item->ID?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-folder-open"></i>
                                    </a>
                                    <button onclick="confirmMessage('<?= $item->ID ?>','<?= $item->NOMEVISITA  ?>')" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-check-circle-o"></i>
                                    </button>
                                </td>
                            <?php }else { ?>
                                <td align="right">
                                    <a href="frotas_portaria/ver_visitante/<?= $item->ID?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-folder-open"></i>
                                    </a>
                                </td>
                            <?php }?>

                        </tr>
                        <?php
                        }
                    ?>
                </tbody>
            </table> 
            <?php
        }
        ?>
    </div>
     
    <!-- Modal de Info -->
    <div class="modal modal-md" id="modalInfo">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" onclick="closeModal()" >&times;</button>
                    <h4 class="modal-title">Dados do Véiculo</h4>
                </div>
                <div class="modal-body">  
                    <div class="form-group">
                        <label for="tpVeiculo">Tipo do veículo</label>
                        <input type="text" disabled class="form-control" id="tpVeiculo">
                    </div>
                    <div class="form-group">
                        <label for="modeloVeiculo">Modelo</label>
                        <input type="text" disabled class="form-control" id="modeloVeiculo">
                    </div>
                    <div class="form-group">
                        <label for="corVeiculo">Cor</label>
                        <input type="text" disabled class="form-control" id="corVeiculo">
                    </div>
                    <div class="form-group">
                        <label for="placaVeiculo">Placa</label>
                        <input type="text" disabled class="form-control" id="placaVeiculo">
                    </div>
                </div>   
            </div>
        </div>
    </div>
    <!--// Modal de Info -->

    <div class="modal" id="modalPlanilha">
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
                        <input type="button" class="btn btn-danger" value="Cancelar" onclick="$('#modalPlanilha').modal('hide')">
                        <input type="button" class="btn btn-success" value="Confirmar" onclick="downPlanilha()">
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
        $('#NRDOCUMENTO').find("input").mask('999.999.999-99');
    });

    $(function () {
        $('#TPDOCTO').change(function(e){
            if(e.target.value == "CPF"){
                $('#NRDOCUMENTO').find("input").mask('999.999.999-99');
            }else{
                $('#NRDOCUMENTO').find("input").mask('999999999');
            }
        });
    });

    const showInfo = (tipo,modelo,cor,placa) => {
        $('#tpVeiculo').val(tipo);
        $('#modeloVeiculo').val(modelo);
        $('#corVeiculo').val(cor);
        $('#placaVeiculo').val(placa);
        $('#modalInfo').show();
    }
    const closeModal = () => $('#modalInfo').hide();

    const confirmMessage = (id, name) => {
        if (confirm(`Tem certeza que deseja encerrar a visita de ${name}?`)) {
            window.location.href = `frotas_portaria/finalizar_visita/${id}`;
        }
    }

    const downPlanilha = () => {
        var dtIni = $("#dtIniDown").val(); 
        var dtFim = $("#dtFimDown").val();
        
        if( dtIni && dtFim ){
            $.ajax({
                url: 'frotas_portaria/baixar_planilha_visitas',
                type: 'POST',
                data: { dtIni, dtFim },
                success: data => {
                    $(data).table2excel({name: "Download", filename: "Visitas.xls" });
                }
            });        
        }
    }

</script>