<div class="panel panel-default">
    <div class="panel-heading">
        Checklist cod. <?= $cadastro->ID; ?>
    </div>
    <div class="panel-body form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label">Data/Hora</label>
            <div class="col-sm-3 form-control-static">   
                <?= data_oracle_para_web($cadastro->DATAHORA); ?>
            </div>
            <label class="col-sm-2 control-label">Motorista</label>
            <div class="col-sm-5 form-control-static">   
                <?= $motorista_nome; ?>
            </div>
            <label class="col-sm-2 control-label">Filial</label>
            <div class="col-sm-3 form-control-static"> 
                <?= $motorista_cdempresa; ?>
            </div>
            <label class="col-sm-2 control-label">Usuário</label>
            <div class="col-sm-5 form-control-static"> 
                <?= $usuario_nome; ?>
            </div>
            <label class="col-sm-2 control-label">Observações</label>
            <div class="col-sm-10 form-control-static"> 
                <?= nl2br($cadastro->OBSERVACOES); ?>
            </div>
        </div>
    </div>
</div>
<?php
if (!empty($documentos_tipos))
{
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            Documentos
        </div>
        <div class="panel-body form-horizontal">
            <?php
            foreach ($documentos_tipos as $tipo)
            {
                ?>    
                <label class="col-sm-2 control-label capitalize"><?= $tipo; ?></label>
                <div class="col-sm-10 form-control-static"> 
                    <?php
                    foreach ($documentos as $documento)
                    {
                        if ($documento->TIPO == $tipo)
                        {
                            ?>
                            <div class="col-md-3">
                                <i class="fa fa-fw fa-<?= ($documento->DEFERIDO) ? 'check text-success' : 'close text-danger'; ?>"></i>
                                <?= $documento->NUMERO; ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}
if (!empty($fotos_tipos))
{
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            Fotos
        </div>
        <?php
        foreach ($fotos_tipos as $tipo)
        {
            ?>    
            <div class="panel-body form-horizontal">
                <label class="col-sm-2 control-label capitalize"><?= $tipo; ?></label>
                <div class="col-sm-10 form-control-static"> 
                    <?php
                    if (!empty($fotos[$tipo]))
                    {
                        foreach ($fotos[$tipo] as $upload)
                        {
                            ?>
                            <a href="geral_upload/abrir/<?= $upload->ID; ?>/<?= $upload->NOME; ?>" class="fancybox-button" rel="fotos">
                                <div class="thumb-center" style="background-image: url('geral_upload/abrir/<?= $upload->ID; ?>/<?= $upload->NOME; ?>');"></div>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>        
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}
?>