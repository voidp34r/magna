<a class="btn btn-success" href="rh_biometria/cadastro_usuario">
    <i class="fa fa-fw fa-plus"></i>
    Novo usuário
</a>

<div class="panel panel-default">
    <div class="panel-heading">
        Usuários
        <small>
        (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>  
        </small>
        <a class="pull-right" data-toggle="collapse" href="#filtro">Filtro</a>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <?= form_open('ti_biometria/listar_usuario', 'method="GET"'); ?>
            <div class="row">
                <div class="col-sm-3">
                    <label>Nome</label>
                    <?= campo_filtro($filtro, 'DSNOME', 'like'); ?>
                </div>
                <div class="col-sm-3">
                    <label>CPF</label>
                    <?= campo_filtro($filtro, 'CPF', 'like'); ?>
                </div>
            </div>
            <br>
            <input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
            <?= form_close(); ?>
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
                        <th>Cód.</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Tipo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $item){ ?>
                        <tr>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                                <?= $item->DSNOME; ?>
                            </td>
                            <td>
                                <?= substr($item->CPF,3,11) ; ?>
                            </td>
                            <td>
                                <?php
                                    switch($item->TIPO){
                                        case 1:
                                            echo "Funcionário";
                                        break;
                                        case 2:
                                            echo "Agregado";
                                        break;
                                        case 3:
                                            echo "Terceiro";
                                        break;
                                    }                            
                                ?>
                            </td>
                            <td align="right">
                                <a href="rh_biometria/editar_usuario/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                </tbody>
            </table> 
            <?php
        }
        ?>    

    </div>
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>