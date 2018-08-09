<?php
/*
 * STATUS 1 = DEFERIDO
 * STATUS 3 = CANCELADO
 */
if ($cadastro->ABERTO && $cadastro->USUARIO_ID == $sessao['usuario_id'])
{
    if (empty($analises))
    {
        ?>
        <a class="btn btn-default btn-block-mobile" href="frotas_agregados/adicionar_veiculo/<?= $id; ?>">
            <i class="fa fa-fw fa-truck"></i>
            Adicionar veículo
        </a>
        <a class="btn btn-default btn-block-mobile" href="frotas_agregados/adicionar_motorista/<?= $id; ?>">
            <i class="fa fa-fw fa-user-plus"></i>
            Adicionar motorista
        </a>
        <a class="btn btn-default btn-block-mobile" href="frotas_agregados/adicionar_gerenciador_risco/<?= $id; ?>">
            <i class="fa fa-fw fa-map-marker"></i>
            Adicionar gerenciador de risco
        </a>
        <?php
    }
    if (!empty($veiculos) && !empty($motoristas) && !empty($gerenciadores_risco) && $cadastro->AGREGADO_STATUS_ID != 1)
    {
        ?>
        <a class="btn btn-success btn-block-mobile" href="frotas_agregados/enviar_solicitacao/<?= $id; ?>">
            <i class="fa fa-fw fa-check"></i>
            Enviar para análise
        </a>
        <?php
    }
}
if ($cadastro->AGREGADO_STATUS_ID == 1 && $cadastro->AGREGADO_STATUS_ID_JURIDICO != 1)
{
    ?>
    <a class="btn btn-success btn-block-mobile" href="frotas_agregados/parecer_juridico/<?= $id; ?>">
        <i class="fa fa-fw fa-balance-scale"></i>
        Dar parecer jurídico
    </a>
    <?php
}
if (empty($veiculos) && empty($motoristas) && empty($gerenciadores_risco))
{
    ?>
    <a class="btn btn-default btn-block-mobile" href="frotas_agregados/excluir_solicitacao/<?= $id; ?>">
        <i class="fa fa-fw fa-close"></i>
        Cancelar solicitação
    </a>
    <br>
    <br>
    <div class="alert alert-warning">
        <i class="fa fa-fw fa-info-circle"></i>
        Utilize os botões acima para preencher sua solicitação.
    </div>
    <?php
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        Solicitação cod. <?= $cadastro->ID; ?>
        <div class="pull-right">
            <?php
            if ($cadastro->ABERTO)
            {
                ?>
                <a href="frotas_agregados/editar_solicitacao/<?= $id; ?>" type="button" class="btn btn-xs btn-default">
                    <i class="fa fa-fw fa-pencil"></i>
                </a>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="panel-body form-horizontal">
        <div class="form-group">
            <label class="col-sm-1 control-label">Data</label>
            <div class="col-sm-2 form-control-static">   
                <?= data_oracle_para_web($cadastro->DATAHORA); ?>
            </div>
            <label class="col-sm-1 control-label">Filial</label>
            <div class="col-sm-2 form-control-static">   
                <?= $filial_nome; ?>
            </div>
            <label class="col-sm-2 control-label">Solicitante</label>
            <div class="col-sm-4 form-control-static"> 
                <?= $usuarios[$cadastro->USUARIO_ID]; ?>
            </div>
        </div>
    </div>
</div>
<?php
if (!empty($analises))
{
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            Análises
        </div>
        <div class="panel-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Observações</th>
                    </tr>
                </thead>
                <tbody><?php
                    foreach ($analises as $item){ ?>
                        <tr>
                            <td><?= data_oracle_para_web($item->DATAHORA); ?></td>
                            <td><?= $usuarios[$item->USUARIO_ID]; ?></td>
                            <td><?= $item->TIPO; ?></td>
                            <td><?= $status[$item->AGREGADO_STATUS_ID]; ?></td>
                            <td><?= nl2br($item->OBSERVACAO); ?></td>
                        </tr>
                        <?php
                    } ?>
                </tbody>
            </table> 
        </div>
    </div><?php
}

/**
 * =============================
 * PANEL PROPRIETÁRIO
 * =============================
 */
echo form_open(''); ?>
<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <a data-toggle="collapse" href="#collapseProprietario">
            <i class="fa fa-fw fa-building"></i> Proprietário 
            <small>(cód. <?= $proprietario->ID; ?>)</small>
        </a>
        <div class="pull-right"><?php
            $aberto = false;
            if ($proprietario->ABERTO == 1){
                $aberto = true;
            }
            
            if (!empty($proprietario_upload)){
                foreach ($proprietario_upload as $upload){
                    if ($proprietario->{'ABERTO_' . $upload->LABEL} == 1){
                        $aberto = true;
                    }
                }
            }
            
            if ($aberto){ ?>
                <div class="label label-success">
                    Cadastro aberto
                </div>
                <a href="frotas_agregados/editar_proprietario/<?= $id; ?>/<?= $proprietario->ID; ?>" 
                	type="button" 
                	class="btn btn-xs btn-default">
                    <i class="fa fa-fw fa-pencil"></i>
                </a>
                <?php
            }?>
        </div>
    </div>
    <div id="collapseProprietario" class="panel-collapse collapse">
        <div class="panel-body form-horizontal">

            <div class="form-group">
                <label class="col-sm-2 control-label">Nome</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($proprietario->NOME); ?>
                </div>
                <label class="col-sm-2 control-label">CPF/CNPJ</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->CPF_CNPJ; ?>
                </div>
                <label class="col-sm-2 control-label">RG</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->RG; ?>
                </div>
                <label class="col-sm-2 control-label">Inscrição Estadual</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->INSCRICAO_ESTADUAL; ?>
                </div>
                <label class="col-sm-2 control-label">Estado</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $municipios[$proprietario->GERAL_MUNICIPIO_ID]->UF; ?>
                </div>
                <label class="col-sm-2 control-label">Município</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($municipios[$proprietario->GERAL_MUNICIPIO_ID]->NOME); ?>
                </div>
                <label class="col-sm-2 control-label">Bairro</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($proprietario->BAIRRO); ?>
                </div>
                <label class="col-sm-2 control-label">Endereço</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($proprietario->ENDERECO); ?>
                </div>
                <label class="col-sm-2 control-label">Nº/Complemento</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($proprietario->NUMERO); ?>
                </div>
                <label class="col-sm-2 control-label">Telefone</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->TELEFONE; ?>
                </div>
                <label class="col-sm-2 control-label">Celular</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->CELULAR; ?>
                </div>
                <label class="col-sm-2 control-label">E-mail</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($proprietario->EMAIL); ?>
                </div>
                <label class="col-sm-2 control-label">ANTT</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->ANTT; ?>
                </div>
            </div><?php 
            if (!$gatilho_analise){ ?>
            	<hr><?php 
            }?>
            <h4 class="col-sm-offset-2">Dados bancários</h4>
            <div class="form-group">
                <label class="col-sm-2 control-label">Favorecido</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($proprietario->FAVORECIDO); ?>
                </div>
                <label class="col-sm-2 control-label">CPF/CNPJ</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->FAVORECIDO_INSCRICAO; ?>
                </div>
                <label class="col-sm-2 control-label">Banco</label>
                <div class="col-sm-4 form-control-static">   
                    <?= remove_acento($proprietario->BANCO); ?>
                </div>
                <label class="col-sm-2 control-label">Agência</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->AGENCIA; ?>
                </div>
                <label class="col-sm-2 control-label">Conta corrente</label>
                <div class="col-sm-4 form-control-static">   
                    <?= $proprietario->CONTA; ?>
                </div>
            </div>
            <div class="form-group">
	            <div class="col-sm-2 col-sm-offset-10"><?php
					//Mostra toggle checkbox do grupo
		            if ($gatilho_analise && $proprietario->ABERTO != 2){ ?>
		                <div class="checkbox">
		                	<input name="analise[proprietario][<?= $proprietario->ID; ?>][ABERTO]" 
		                		class="analise" 
		                		type="checkbox"
		                		data-on="Deferido" 
		                		data-off="Indeferido"
		                		data-toggle="toggle">
		            	</div><?php
		           	}?>
	           	</div>
	        </div>
	        <br><?php            
            if (!empty($proprietario_upload)){?>
                <hr>
                <h4 class="col-sm-12">Arquivos</h4>
                <div class="form-group"><?php
                	$iter = new ArrayIterator($proprietario_upload);
	                foreach ($iter as $upload){?>
                    	<div class="col-sm-6" >
                    		<div align="center">
		                    	<label><?= $pre['upload_proprietario'][$upload->LABEL]; ?></label>
		                        <div>
		                            <a href="geral_upload/abrir/<?= $upload->ID; ?>/<?= $upload->NOME; ?>" 
		                            		data-fancybox-type="iframe" 
		                            		class="fancybox-button" 
		                            		rel="proprietario">
		                                <?= $upload->NOME; ?>
		                            </a>
		                            &nbsp&nbsp
		                            <small>
		                                <?= data_oracle_para_web($upload->DATAHORA); ?>
		                            </small>
		                        </div><?php
		                        if ($gatilho_analise && $proprietario->{'ABERTO_' . $upload->LABEL} != 2){?>
		                        	<br>
		                            <input name="analise[proprietario][<?= $proprietario->ID; ?>][ABERTO_<?= $upload->LABEL; ?>]" 
		                            		class="analise" 
		                            		type="checkbox" 
		                            		data-toggle="toggle" 
		                            		data-on="Deferido" 
		                            		data-off="Indeferido">
		                           	<?php
		                           	//if (!$iter->hasNext()){?>
		                           		<br><br><hr><?php
		                           	//}
		                        } else if ($proprietario->{'ABERTO_' . $upload->LABEL} == 1){?>
	                            	<div class="label label-success">Em aberto</div><?php
	                            }?>
	                    	</div>
                    	</div><?php
	                }?>
	            </div><?php
            }?>
        </div>
    </div>
</div><?php

/**
 * =============================
 * PANEL VEICULOS
 * =============================
 */
if (!empty($veiculos)){
    foreach ($veiculos as $item){ ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <a data-toggle="collapse" href="#collapseVeiculo<?= $item->ID; ?>">
                    <i class="fa fa-fw fa-truck"></i> Veículo 
                    <small>(cód. <?= $item->ID; ?>)</small>
                </a>
                <div class="pull-right">
                    <?php
                    $aberto = false;
                    if ($item->ABERTO == 1)
                    {
                        $aberto = true;
                    }
                    if (!empty($veiculos_upload[$item->ID]))
                    {
                        foreach ($veiculos_upload[$item->ID] as $upload)
                        {
                            if ($item->{'ABERTO_' . $upload->LABEL} == 1)
                            {
                                $aberto = true;
                            }
                        }
                    }
                    if ($aberto)
                    {
                        ?>
                        <div class="label label-success">
                            Cadastro aberto
                        </div>
                        <a href="frotas_agregados/editar_veiculo/<?= $id; ?>/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                            <i class="fa fa-fw fa-pencil"></i>
                        </a>
                        <?php
                        if (empty($analises))
                        {
                            ?>
                            <a href="frotas_agregados/excluir_veiculo/<?= $id; ?>/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                <i class="fa fa-fw fa-trash"></i>
                            </a>
                            <?php
                        }
                    }
                    
					//Mostra toggle checkbox do grupo
		            if ($gatilho_analise && $item->ABERTO != 2){ ?>
		                <div class="checkbox">
		                	<input name="analise[veiculo][<?= $item->ID; ?>][ABERTO]" 
		                		class="analise" 
		                		type="checkbox"
		                		data-on="Deferido" 
		                		data-off="Indeferido"
		                		data-toggle="toggle">
		            	</div><?php
		           	} ?>
                </div>
            </div>
            <div id="collapseVeiculo<?= $item->ID; ?>" class="panel-collapse collapse">
                <div class="panel-body form-horizontal">
                	<div class="form-group">
                        <label class="col-sm-2 control-label">Placa</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->PLACA; ?>
                        </div>
                        <label class="col-sm-2 control-label">Nº do certificado</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CERTIFICADO; ?>
                        </div>
                        <label class="col-sm-2 control-label">Nº do RENAVAM</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->RENAVAM; ?>
                        </div>
                        <label class="col-sm-2 control-label">Nº do chassis</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CHASSIS; ?>
                        </div>
                        <label class="col-sm-2 control-label">Potência</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->POTENCIA); ?>
                        </div>
                        <label class="col-sm-2 control-label">Tipo do veículo</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->TIPO_VEICULO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Modelo</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->MODELO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Tara</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->TARA; ?>
                        </div>
                        <label class="col-sm-2 control-label">Cor predominante</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->COR_PREDOMINANTE; ?>
                        </div>
                        <label class="col-sm-2 control-label">Ano do modelo</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->ANO_MODELO; ?>
                        </div>
                        <label class="col-sm-2 control-label">Ano de fabricação</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->ANO_FABRICACAO; ?>
                        </div>
                        <label class="col-sm-2 control-label">Estado</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $municipios[$item->GERAL_MUNICIPIO_ID]->UF; ?>
                        </div>
                        <label class="col-sm-2 control-label">Município</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($municipios[$item->GERAL_MUNICIPIO_ID]->NOME); ?>
                        </div>
                        <label class="col-sm-2 control-label">Combustível</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->COMBUSTIVEL); ?>
                        </div>
                        <label class="col-sm-2 control-label">Categoria</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->CATEGORIA); ?>
                        </div>
                    </div>
                    <?php
                    if (!empty($veiculos_upload[$item->ID]))
                    {
                        ?>
                        <hr>
                        <h4 class="col-sm-offset-2">Arquivos</h4>
                        <?php
                        foreach ($veiculos_upload[$item->ID] as $upload)
                        {
                            ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?= $pre['upload_veiculo'][$upload->LABEL]; ?></label>
                                <div class="col-sm-6 form-control-static">   
                                    <a href="geral_upload/abrir/<?= $upload->ID; ?>/<?= $upload->NOME; ?>" data-fancybox-type="iframe" class="fancybox-button" rel="veiculo">
                                        <?= $upload->NOME; ?>
                                    </a>                                    
                                    <br>
                                    <small>
                                        <?= data_oracle_para_web($upload->DATAHORA); ?>
                                    </small>
                                </div>
                                <?php
                                if ($gatilho_analise && $item->{'ABERTO_' . $upload->LABEL} != 2)
                                {
                                    ?>
                                    <div class="col-sm-4">
                                        <input name="analise[veiculo][<?= $item->ID; ?>][ABERTO_<?= $upload->LABEL; ?>]" class="analise" type="checkbox" data-toggle="toggle" data-on="Deferido" data-off="Indeferido">
                                    </div>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <div class="col-sm-4">
                                        <?php
                                        if ($item->{'ABERTO_' . $upload->LABEL} == 1)
                                        {
                                            ?>
                                            <div class="label label-success">Em aberto</div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>       
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}
/**
 * =============================
 * PANEL MOTORISTAS
 * =============================
 */
if (!empty($motoristas))
{
    foreach ($motoristas as $item)
    {
        ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <a data-toggle="collapse" href="#collapseMotorista<?= $item->ID; ?>">
                    <i class="fa fa-fw fa-user"></i> Motorista 
                    <small>(cód. <?= $item->ID; ?>)</small>
                </a>
                <div class="pull-right">
                    <?php
                    $aberto = false;
                    if ($item->ABERTO == 1)
                    {
                        $aberto = true;
                    }
                    if (!empty($motoristas_upload[$item->ID]))
                    {
                        foreach ($motoristas_upload[$item->ID] as $upload)
                        {
                            if ($item->{'ABERTO_' . $upload->LABEL} == 1)
                            {
                                $aberto = true;
                            }
                        }
                    }
                    
                    if ($aberto){ ?>
                        <div class="label label-success">
                            Cadastro aberto
                        </div>
                        <a href="frotas_agregados/editar_motorista/<?= $id; ?>/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                            <i class="fa fa-fw fa-pencil"></i>
                        </a><?php
                        if (empty($analises)){ ?>
                            <a href="frotas_agregados/excluir_motorista/<?= $id; ?>/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                <i class="fa fa-fw fa-trash"></i>
                            </a><?php
                        }
                    }
                    
		            //Mostra toggle checkbox do grupo
		            if ($gatilho_analise && $item->ABERTO != 2){ ?>
		                <div class="checkbox">
		                	<input name="analise[motorista][<?= $item->ID; ?>][ABERTO]" 
		                		class="analise" 
		                		type="checkbox"
		                		data-on="Deferido" 
		                		data-off="Indeferido"
		                		data-toggle="toggle">
		            	</div><?php
		           	} ?>
                </div>
            </div>
            <div id="collapseMotorista<?= $item->ID; ?>" class="panel-collapse collapse">
                <div class="panel-body form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">CPF</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CPF; ?>
                        </div>
                        <label class="col-sm-2 control-label">Nome</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->NOME); ?>
                        </div>
                        <label class="col-sm-2 control-label">RG</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->RG; ?>
                        </div>
                        <label class="col-sm-2 control-label">Estado</label>
                        <div class="col-sm-4 form-control-static">  
                            <?= $municipios[$item->GERAL_MUNICIPIO_ID]->UF; ?>
                        </div>
                        <label class="col-sm-2 control-label">Município</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($municipios[$item->GERAL_MUNICIPIO_ID]->NOME); ?>
                        </div>
                        <label class="col-sm-2 control-label">Bairro</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->BAIRRO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Endereço</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->ENDERECO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Nº/Complemento</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->NUMERO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Telefone</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->TELEFONE; ?>
                        </div>
                        <label class="col-sm-2 control-label">Celular</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CELULAR; ?>
                        </div>
                        <label class="col-sm-2 control-label">E-mail</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->EMAIL; ?>
                        </div>
                        <label class="col-sm-2 control-label">Data de nascimento</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= data_oracle_para_web($item->DATA_NASCIMENTO); ?>
                        </div>
                        <label class="col-sm-2 control-label">PIS</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->PIS; ?>
                        </div>
                        <label class="col-sm-2 control-label">ANTT</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->ANTT; ?>
                        </div>
                        <label class="col-sm-2 control-label">Validade ANTT</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= data_oracle_para_web($item->ANTT_VALIDADE); ?>
                        </div>
                        <label class="col-sm-2 control-label">Nome do pai</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->FILIACAO_PAI); ?>
                        </div>
                        <label class="col-sm-2 control-label">Nome da mãe</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->FILIACAO_MAE); ?>
                        </div>
                    </div>
                    <hr>
                    <h4 class="col-sm-offset-2">CNH</h4>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Categoria</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CNH_CATEGORIA; ?>
                        </div>
                        <label class="col-sm-2 control-label">Número</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CNH_NUMERO; ?>
                        </div>
                        <label class="col-sm-2 control-label">Prontuário</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CNH_PRONTUARIO; ?>
                        </div>
                        <label class="col-sm-2 control-label">Emissão</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= data_oracle_para_web($item->CNH_EMISSAO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Data da 1ª hab.</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= data_oracle_para_web($item->CNH_1HABILITACAO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Vencimento</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= data_oracle_para_web($item->CNH_VENCIMENTO); ?>
                        </div>
                        <label class="col-sm-2 control-label">Estado</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $municipios[$item->CNH_GERAL_MUNICIPIO_ID]->UF; ?>
                        </div>
                        <label class="col-sm-2 control-label">Município</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($municipios[$item->CNH_GERAL_MUNICIPIO_ID]->NOME); ?>
                        </div>
                        <label class="col-sm-2 control-label">Orgão</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->CNH_ORGAO; ?>
                        </div>
                    </div>
                    <?php
                    if (!empty($motoristas_upload[$item->ID]))
                    {
                        ?>
                        <hr>
                        <h4 class="col-sm-offset-2">Arquivos</h4>
                        <?php
                        foreach ($motoristas_upload[$item->ID] as $upload)
                        {
                            ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?= $pre['upload_motorista'][$upload->LABEL]; ?></label>
                                <div class="col-sm-6 form-control-static">   
                                    <a href="geral_upload/abrir/<?= $upload->ID; ?>/<?= $upload->NOME; ?>" data-fancybox-type="iframe" class="fancybox-button" rel="motorista">
                                        <?= $upload->NOME; ?>
                                    </a>                             
                                    <br>
                                    <small>
                                        <?= data_oracle_para_web($upload->DATAHORA); ?>
                                    </small>
                                </div>
                                <?php
                                if ($gatilho_analise && $item->{'ABERTO_' . $upload->LABEL} != 2)
                                {
                                    ?>
                                    <div class="col-sm-4">
                                        <input name="analise[motorista][<?= $item->ID; ?>][ABERTO_<?= $upload->LABEL; ?>]" class="analise" type="checkbox" data-toggle="toggle" data-on="Deferido" data-off="Indeferido">
                                    </div>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <div class="col-sm-4">
                                        <?php
                                        if ($item->{'ABERTO_' . $upload->LABEL} == 1)
                                        {
                                            ?>
                                            <div class="label label-success">Em aberto</div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}

/**
 * =============================
 * PANEL GERENCIADORES DE RISCO
 * =============================
 */
if (!empty($gerenciadores_risco))
{
    foreach ($gerenciadores_risco as $item)
    {
        ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <a data-toggle="collapse" href="#collapseGerenciador<?= $item->ID; ?>">
                    <i class="fa fa-fw fa-map-marker"></i> Gerenciador de risco 
                    <small>(cód. <?= $item->ID; ?>)</small>
                </a>
                <div class="pull-right">                   
                    <?php
                    if ($item->ABERTO == 1)
                    {
                        ?>
                        <div class="label label-success">
                            Cadastro aberto
                        </div>
                        <a href="frotas_agregados/editar_gerenciador_risco/<?= $id; ?>/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                            <i class="fa fa-fw fa-pencil"></i>
                        </a>
                        <?php
                        if (empty($analises))
                        {
                            ?>
                            <a href="frotas_agregados/excluir_gerenciador_risco/<?= $id; ?>/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                <i class="fa fa-fw fa-trash"></i>
                            </a>
                            <?php
                        }
                    }

                   	//Mostra toggle checkbox do grupo
		            if ($gatilho_analise && $item->ABERTO != 2){ ?>
						<div class="checkbox">
							<input name="analise[gerenciador_risco][<?= $item->ID; ?>][ABERTO]" 
								class="analise" 
								type="checkbox"
								data-on="Deferido" 
								data-off="Indeferido"
								data-toggle="toggle">
						</div><?php
		           	} ?>
                </div>
            </div>
            <div id="collapseGerenciador<?= $item->ID; ?>" class="panel-collapse collapse">
                <div class="panel-body form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Gerenciador de risco</label>
                        <div class="col-sm-4 form-control-static">
                            <?= $gerenciadores_nome[$item->GERAL_GERENCIADOR_RISCO_ID]; ?>
                        </div>
                        <label class="col-sm-2 control-label">Motorista</label>
                        <div class="col-sm-4 form-control-static">
                            <?= remove_acento($motoristas_nome[$item->AGREGADO_MOTORISTA_ID]); ?>
                        </div>
                        <label class="col-sm-2 control-label">Data da consulta</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= data_oracle_para_web($item->DATA_CONSULTA); ?>
                        </div>
                        <label class="col-sm-2 control-label">Data de validade</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= data_oracle_para_web($item->DATA_VALIDADE); ?>
                        </div>
                        <label class="col-sm-2 control-label">Protocolo</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= $item->PROTOCOLO; ?>
                        </div>
                        <label class="col-sm-2 control-label">Operador</label>
                        <div class="col-sm-4 form-control-static">   
                            <?= remove_acento($item->OPERADOR); ?>
                        </div>
                        <label class="col-sm-2 control-label">Observações</label>
                        <div class="col-sm-10 form-control-static">   
                            <?= $item->OBSERVACAO; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

/**
 * =============================
 * PANEL ANÁLISE
 * =============================
 */
if ($gatilho_analise && $cadastro->AGREGADO_STATUS_ID != 1 && !$cadastro->ABERTO){
    require_once('adicionar_analise.php');
}

echo form_close();
