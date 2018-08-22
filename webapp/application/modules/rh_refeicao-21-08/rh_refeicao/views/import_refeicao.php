<a class="btn btn-primary" id="btnSync">
    <i class="fa fa-cloud-download" aria-hidden="true"></i>
     Importar Usuários Refeitório
</a>

<a class="btn btn-success" id="btnSyncRelat">
    <i class="fa fa-fw fa-sticky-note-o" aria-hidden="true"></i>
     Relatório refeições de hoje
</a>

<div class="panel panel-default">
    <div class="panel-heading">
        Usuários
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>     
            -&nbsp&nbsp<a id="aFiltro" data-toggle="collapse" href="#filtro">Filtrar</a>       
        </small>
    
        <div class="collapse" id="filtro">

            <div class="panel-body">
                <div class="row">
                    <form>
                        <div class="row">  
                            <div class="col-md-2">
                                <label>Nome</label>
                                <?= campo_filtro($filtro, 'NOME', 'like'); ?>
                            </div>  
                            <div id="dateRefeicao" class="col-sm-2">
                                <label>Data Refeição</label>
                                <?= campo_filtro($filtro, 'DTREFEICAO', 'date'); ?>
                            </div> 
                            <div class="col-sm-3">
                                <label>Horário Refeição</label>
                                <select multiple name="int[HORARIO_REFEICAO]" class="form-control input-small"  multiple="multiple" size="1">
                                <option value="1">12H</option>
                                <option value="2">19H</option>
                                <option value="3">22H</option>
                                <option value="4">00H</option>
                                </select>                            
                            </div>
                            <div class="col-sm-3">
                                <label>Horário Refeição</label>
                                <select multiple name="int[ACESSOU]" class="form-control input-small"  multiple="multiple" size="1">
                                <option value="1">Acessou</option>
                                <option value="2">Não Acessou</option>
                                </select>                            
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
        
        if (isset($lista))
        {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Data Refeição</th>
                        <th>Horário Refeição</th>
                        <th>Status Importação</th>
                        <th>Status Acesso</th>
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
                              <?= $item->NOME; ?>
                            </td>

                            <td>
                                <?= $item->DATA; ?>
                            </td>
                            <td>
                                <?php 
                                    switch ($item->HORARIO_REFEICAO) {
                                        case '1':
                                            echo "12H";
                                            break;
                                        case '2':
                                            echo "19H";
                                            break;
                                        case '3':
                                            echo "22H";
                                            break;
                                        case '4':
                                            echo "00H";
                                            break;
                                    }
                                ?>
                            </td>
                            <td>
                                <?= $item->STATUS; ?>
                            </td>
                            <td>
                                <?php 
                                    switch ($item->ACESSOU) {
                                        case 2:
                                            echo "Não Acessou";
                                            break;
                                        case 1:
                                            echo "Acessou";
                                            break;
                                        case '3':
                                            echo "22H";
                                            break;
                                        default:
                                            echo " - ";
                                            break;
                                    }
                                ?>
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
    
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>

<div class="modal fade" id="modalSync" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" style="color:white;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Selecione o Horário da Refeição</h4>
      </div>
      <div class="modal-body">

        <center>
            <div class="row" id="btnRefeicao">
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(1)">Refeição - 12h</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(2)">Refeição - 19h</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(3)">Refeição - 22h</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(4)">Refeição - 00h</button>
                </div>                            
            </div>                
            <b>
                <p id="pWait">Aguarde..<p>
                <p id="pSubmitEnd"><p>
            </b>
        </center>

      </div>
      <div class="modal-footer">
        <button class="btn btn-success btn-block" id="btnConcluir" onclick="location.reload();">Concluir</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSyncRelat" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" style="color:white;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Selecione o Horário da Refeição</h4>
      </div>
      <div class="modal-body">

        <center>
            <div class="row" id="btnRefeicaoRelat">
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(1,1)">Refeição - 12h</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(2,1)">Refeição - 19h</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(3,1)">Refeição - 22h</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="startImport(4,1)">Refeição - 00h</button>
                </div>                            
            </div>                
            <b>
                <p id="pWaitRelat">Aguarde..<p>
                <p id="pSubmitEndRelat"><p>
            </b>
        </center>

      </div>
      <div class="modal-footer">
        <button class="btn btn-success btn-block" id="btnConcluirRelat" onclick="location.reload();">Concluir</button>
      </div>
    </div>
  </div>
</div>

<script> 

    $('document').ready(function(){
        $("#dateRefeicao").find("input").mask("99/99/9999");        
    });

    $('#btnSync').on('click',function(){
        $('#pSubmitEnd').hide();
        $('#pWait').hide();
        $('#btnConcluir').hide();
        $('#modalSync').modal('show')             
    });

    $('#btnSyncRelat').on('click',function(){
        $('#pSubmitEndRelat').hide();
        $('#pWaitRelat').hide();
        $('#btnConcluirRelat').hide();
        $('#modalSyncRelat').modal('show')             
    });

     function startImport(ref, relat = 0){
        setFormStyle(true);

        $.ajax({
            url : "rh_refeicao/importAfd/"+ref+"/"+relat
        }).done(function(data){
            console.log(data);
            data = JSON.parse(data);
            console.log(data);
            $('#pSubmitEnd').html(data.msg); 
            $('#pSubmitEndRelat').html(data.msg); 
            setFormStyle(false);                     
        }); 
    }
    function setFormStyle(isLoad){
        if(isLoad){            
            $('#btnRefeicao').hide();
            $('#pSubmitEnd').hide();
            $('#pWait').show();
            $('#btnConcluir').hide();

            $('#btnRefeicaoRelat').hide();
            $('#pSubmitEndRelat').hide();
            $('#pWaitRelat').show();
            $('#btnConcluirRelat').hide();
        }else{
            $('#pSubmitEnd').show();
            $('#pWait').hide();
            $('#btnConcluir').show();

            $('#pSubmitEndRelat').show();
            $('#pWaitRelat').hide();
            $('#btnConcluirRelat').show();
        }
    }   

    function closeModal(){
        $('#btnRefeicao').show();
        $('#pSubmitEnd').show();
        $('#pWait').hide();
        $('#btnConcluir').show();
        $('#modalSync').modal('hide')    
    }  
</script>