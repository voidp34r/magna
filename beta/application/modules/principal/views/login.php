<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <base href="<?= base_url(); ?>">
        <meta charset="utf-8">
        <meta http-equiv="Content-Language" content="pt-br">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Transmagna - Login</title>

        <!-- Bootstrap core CSS -->
        <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="assets/css/style.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>

        <div class="container">

            <?php
            echo form_open('', 'class="form-signin"');
            ?>

            <div class="panel panel-default">
                <div class="panel-body">
                    <div align="center">
                        <img src="assets/img/logo.gif" class="img-responsive">
                        <br>
                    </div>
                    <label for="usuario" class="sr-only">Usuário</label>
                    <input 
                        type="text" 
                        id="usuario"
                        name="usuario" 
                        class="form-control" 
                        placeholder="Usuário" 
                        required 
                        autofocus>
                    <label for="senha" class="sr-only">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        class="form-control" 
                        placeholder="Senha" 
                        required>
                    <button class="btn btn-lg btn-primary btn-block" type="submit">Entrar</button>
                    <?php
                    $validation_errors = validation_errors();
                    if (!empty($validation_errors) || !empty($erro)) {
                        ?>
                        <br>
                        <div class="alert alert-danger">
                            <?php echo validation_errors(); ?>
                            <?php echo (!empty($erro)) ? $erro : ''; ?>
                            <br>
                            <small><?= date('d/m/Y H:i:s'); ?></small>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <?php
            echo form_close();
            ?>
        </div> <!-- /container -->

        

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="bower_components/jquery/dist/jquery.min.js"></script>
        <script src="bower_components/tether/dist/js/tether.min.js"></script>
        <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    </body>
</html>
