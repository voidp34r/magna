<div class="panel panel-default">
    <div class="panel-heading">
        Pendencias
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
                        <th>ID</th>
                        <th>Motorista</th>
                        <th>CPF Motorista</th>
                        <th>Data Ocorrência</th>
                        <th>Usuário Portaria</th>
                        <th>Cod. Filial</th>
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
                                <?= $item->ID?>    
                            </td>
                            <td>
                                <?= $item->DSNOME?>
                            </td>
                            <td>
                                <?= $item->CPFMOTORISTA?>  
                            </td>
                            <td>
                                <?= data_oracle_para_web($item->DTCRIACAO)?>
                            </td>
                            <td>
                                <?= $item->NOMEPORT ?>
                            </td>                            
                            <td>
                                <?= $item->CDEMPRESA?> 
                            </td>
                            <td align="right">
                                <a href="frotas_portaria/resolver_ocorrencia/<?= $item->ID?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-check-circle-o"></i>
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
    </div>
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>