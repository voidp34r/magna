<!DOCTYPE html>
<html>
    <?php
    $this->load->view('templates/_parts/master_header_view');
    ?>
    <body>
        <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">
                        <img src="assets/img/brand.png">
                    </a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <?php
                        foreach ($navbar['menu'] as $menu) {
                            ?>
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" role="button">
                                    <?= $menu->NOME; ?> <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php
                                    foreach ($navbar['submenu'][$menu->ID] as $submenu) {
                                        ?>
                                        <li>
                                            <a href="<?= $submenu->PASTA; ?>">
                                                <?= $submenu->NOME; ?>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" role="button">
                                <?= parte_do_texto($sessao['usuario_nome']); ?> <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="principal/sair">Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <?php
        $this->load->view('templates/_parts/master_footer_view');
        ?>

        <div class="container">

            <?php
            $this->load->view('templates/_parts/master_menu_view');
            ?>

            <hr>

            <?php
            $this->load->view('templates/_parts/master_alert_view');
            ?>

            <?= $the_view_content; ?>
        </div>

    </body>
</html>