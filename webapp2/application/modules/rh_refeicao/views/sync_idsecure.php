<!-- <a class="btn btn-primary" id="btnSync" href="rh_refeicao/sync_idsecure/1">
    <i class="fa fa-cloud-download" aria-hidden="true"></i>
     Sincronizar Id Secure
</a> -->
<script src='assets/js/rh_refeicao.js'></script>


<button class="btn btn-primary" id="btnSync" onclick="sync(true)" href="rh_refeicao/sync_idsecure/1">
    <i class="fa fa-refresh" aria-hidden="true"></i>
     Sincronizar Id Secure
</button>

<!-- <button class="btn btn-danger" id="btnRemove" onclick="remover(12892904481)" href="rh_refeicao/sync_idsecure/1">
    <i class="fa fa-cloud-download" aria-hidden="true"></i>
     Sincronizar Id Secure
</button> -->


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
                        <th>Nome</th>
                        <th>Data Refeição</th>
                        <th>Horário Refeição</th>
                        <th>Status Importação</th>
                        <th>Status Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item)
                    {   
                        ?>
                        <tr>
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
                            <td>                               
                               <!-- <button class="btn btn-danger">excluir </button> -->
                               <button class="btn btn-danger" id="btnRemove" onclick="remover(<?=$item->USER_ID_IDSECURE?>, <?=$item->HORARIO_REFEICAO?>)">
                                    remover
                                </button>
                                
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