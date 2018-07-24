<a class="btn btn-success" href="geral_pallets/adicionarRemessa">
    <i class="fa fa-fw fa-plus"></i>
    Nova Remessa
</a>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body table-responsive">
        <?php if (!empty($lista)){ ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                    	<th>ID</th>
                        <th>CLIENTECOLETA_ID</th>
                        <th>Tipo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $item){ ?>                    
                        <tr>
                        	<td><?= formata_cpf_cnpj($item->CNPJ);?></td>
                            <td><?= ucfirst($item->DSNOME);?></td>
                            <td><?= $item->TIPO;?></td>
                            <td align="right">
                                <a href="geral_pallets/editarCliente" 
                                   type="button" 
                                   class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table> 
        <?php } ?>
    </div>
</div>