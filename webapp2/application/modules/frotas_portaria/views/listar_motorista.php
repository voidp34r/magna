<a class="btn btn-success" href="frotas_portaria/listar_motorista">
    <i class="fa fa-fw fa-refresh"></i>
    Atualizar
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Motoristas aguardando validação
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
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
                        <th>Data/Hora</th>
                        <th>Filial</th>
                        <th>CPF</th>
                        <th>Nome</th>
                        <th class="text-right">Ocultar</th>
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
                                <?= data_oracle_para_web($item->DATAHORA); ?>
                            </td>
                            <td>
                                <?= $item->CDEMPRESA; ?>
                            </td>
                            <td>
                                <?= $item->CPF; ?>
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td align="right">
                                <?php
                                if ($item->ATIVO)
                                {
                                    ?>
                                    <a href="frotas_portaria/ocultar_motorista/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default btn-confirm">
                                        <i class="fa fa-fw fa-eye-slash"></i>
                                    </a>
                                    <?php
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
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>