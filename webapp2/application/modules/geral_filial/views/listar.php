<a class="btn btn-success" href="geral_filial/adicionar">
    <i class="fa fa-fw fa-plus"></i>
    Novo filial
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Filiais
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
                        <th>Nome</th>
                        <th>Município</th>
                        <th>UF</th>
                        <th>Endereço</th>
                        <th>CEP</th>
                        <th>Telefone</th>
                        <th>IP</th>
                        <th>Ping</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item)
                    {
                        ?>
                        <tr <?= (!$item->ATIVO) ? 'class="text-line"' : ''; ?>>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td>
                                <?= $municipios[$item->GERAL_MUNICIPIO_ID]->NOME; ?>
                            </td>
                            <td>
                                <?= $municipios[$item->GERAL_MUNICIPIO_ID]->UF; ?>
                            </td>
                            <td>
                                <?= $item->ENDERECO; ?>
                            </td>
                            <td>
                                <?= $item->CEP; ?>
                            </td>
                            <td>
                                <?= $item->TELEFONE; ?>
                            </td>
                            <td>
                                <?= $item->IP; ?>
                            </td>
                            <td>
                                <?php
                                exec('ping -w 1 -n 1 ' . $item->IP, $saida, $retorno);
                                echo ($retorno == 0) ? 'OK' : 'Fora';
                                ?>
                            </td>
                            <td align="right">
                                <a href="geral_filial/editar/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>     
                                <a href="geral_filial/excluir/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-trash"></i>
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