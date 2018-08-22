<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= !empty($item) ? 'Editar' : 'Novo'; ?> módulo
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Chave *</label>
            <div class="col-sm-4">
                <?= form_input('CHAVE', set_value('CHAVE'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Documento *</label>
            <div class="col-sm-10">
                <?= form_textarea('DOCUMENTO', set_value('DOCUMENTO'), 'class="summernote"'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Trocas possíveis</label>
            <div class="col-sm-10 form-control-static">
                <abbr title="CPF, NOME, RG, RG_UF, ENDERECO, NUMERO, ESTADO, MUNICIPIO, NACIONALIDADE, IDADE, ESTADO_CIVIL">PF</abbr>
                |
                <abbr title="CPF, NOME, RG, RG_UF, ENDERECO, NUMERO, ESTADO, MUNICIPIO, NACIONALIDADE, IDADE, ESTADO_CIVIL, DATA_CONTRATACAO">PF_DISTRATO</abbr>
                |
                <abbr title="CPF, NOME, RG, RG_UF, ENDERECO, NUMERO, ESTADO, MUNICIPIO, NACIONALIDADE, IDADE, ESTADO_CIVIL, NOMEADO_CPF, NOMEADO_NOME, NOMEADO_ESTADO_CIVIL, NOMEADO_PROFISSAO, NOMEADO_RG, NOMEADO_RG_UF, NOMEADO_ENDERECO, NOMEADO_NUMERO, NOMEADO_ESTADO, NOMEADO_MUNICIPIO, NOMEADO_CNH, VEICULO_MARCA, VEICULO_MODELO, VEICULO_PLACA, VEICULO_CHASSI">PF_NOMEACAO</abbr>
                |
                <abbr title="CNPJ, NOME, ESTADO, MUNICIPIO, ENDERECO, NUMERO, ESTADO_FILIAL, MUNICIPIO_FILIAL">PJ</abbr>
                |
                <abbr title="CNPJ, NOME, ESTADO, MUNICIPIO, ENDERECO, NUMERO, INSCRICAO_ESTADUAL, SOCIO_ADMINISTRADOR, SOCIO_CPF, SOCIO_RG, DATA_CONTRATACAO">PJ_DISTRATO</abbr>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<script>
    $(function () {
//        $('#summernote').summernote('disable');
//        $('#summernote').summernote('enable');

    });
</script>