<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
        <a class="pull-right" data-toggle="collapse" href="#filtro">Filtro</a>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <form action="geral_consulta/listar_pessoa">
                <div class="row">
                    <div class="col-sm-3">
                        <label>Tipo</label>
                        <?= campo_filtro($filtro, 'TIPO', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>CPF/CNPJ</label>
                        <?= campo_filtro($filtro, 'CPFCNPJ', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Nome</label>
                        <?= campo_filtro($filtro, 'NOME', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Endereço</label>
                        <?= campo_filtro($filtro, 'ENDERECO', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Bairro</label>
                        <?= campo_filtro($filtro, 'BAIRRO', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Cidade</label>
                        <?= campo_filtro($filtro, 'CIDADE', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>UF</label>
                        <?= campo_filtro($filtro, 'UF', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>CEP</label>
                        <?= campo_filtro($filtro, 'CEP', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Telefone</label>
                        <?= campo_filtro($filtro, 'TELEFONE', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>E-mail</label>
                        <?= campo_filtro($filtro, 'EMAIL', 'like'); ?>
                    </div>
                </div>
                <br>
                <input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
            </form>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php
        if (!empty($lista))
        {
            ?>
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>CPF/CNPJ</th>
                        <th>Nome</th>
                        <th>Endereço</th>
                        <th>Bairro</th>
                        <th>Cidade</th>
                        <th>UF</th>
                        <th>CEP</th>
                        <th></th>
                        <th></th>
                        <th>Ativo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item)
                    {
                        ?>
                        <tr>
                            <td>
                                <?= $item->TIPO; ?>
                            </td>
                            <td>
                                <?= $item->CPFCNPJ; ?>
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td>
                                <?= $item->ENDERECO; ?>
                            </td>
                            <td>
                                <?= $item->BAIRRO; ?>
                            </td>
                            <td>
                                <?= $item->CIDADE; ?>
                            </td>
                            <td>
                                <?= $item->UF; ?>
                            </td>
                            <td>
                                <?= $item->CEP; ?>
                            </td>
                            <td>
                                <?php
                                if ($item->TELEFONE)
                                {
                                    ?>
                                    <i class="fa fa-fw fa-phone" title="<?= $item->TELEFONE; ?>" data-toggle="tooltip"></i>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($item->EMAIL)
                                {
                                    ?>
                                    <i class="fa fa-fw fa-at" title="<?= $item->EMAIL; ?>" data-toggle="tooltip"></i>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <?= tag_ativo($item->ATIVO); ?>
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
        <div class="pull-left">
            <?= anchor('geral_consulta/listar_pessoa/' . $anterior . '?' . $_SERVER['QUERY_STRING'], 'Anterior'); ?>
        </div> 
        <div class="pull-right">
            <?= anchor('geral_consulta/listar_pessoa/' . $proximo . '?' . $_SERVER['QUERY_STRING'], 'Próximo'); ?> 
        </div>
    </div>
</div>