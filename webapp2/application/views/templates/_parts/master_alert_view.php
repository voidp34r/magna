<?php
if (!empty(validation_errors()) || !empty($erro) || !empty($sessao['erro']))
{
    ?>
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <?php echo validation_errors(); ?>
        <?php echo (!empty($erro)) ? $erro : ''; ?>
        <?php echo (!empty($sessao['erro'])) ? $sessao['erro'] : ''; ?>
        <br>
        <small><?php date_default_timezone_set('America/Sao_Paulo'); echo date('d/m/Y H:i:s'); ?></small>
    </div>
    <?php
}
if (!empty($sessao['sucesso']) || !empty($sucesso))
{
    ?>
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <?php echo (!empty($sessao['sucesso'])) ? $sessao['sucesso'] : ''; ?>
        <?php echo (!empty($sucesso)) ? $sucesso : ''; ?>
        <br>
        <small><?php date_default_timezone_set('America/Sao_Paulo'); echo date('d/m/Y H:i:s'); ?></small>
    </div>
    <?php
}