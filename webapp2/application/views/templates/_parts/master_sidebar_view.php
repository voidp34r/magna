<div class="col-sm-2 affix-sidebar">
    <div class="sidebar-nav">
        <div class="navbar navbar-default" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-navbar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <span class="visible-xs navbar-brand">
                    <img src="assets/img/brand.png">
                </span>
            </div>
            <div class="navbar-collapse collapse sidebar-navbar-collapse">
                <ul class="nav navbar-nav" id="sidenav">
                    <li class="active">
                        <a id="navbar-brand">
                            <img src="assets/img/brand.png" class="hidden-xs">
                        </a>                                    
                    </li>
                    <li class="navbar-separator">
                        Módulos
                    </li>
                    <?php
                    if (!empty($navbar['menu']))
                    {
                        foreach ($navbar['menu'] as $menu)
                        {
                            $selecionado = FALSE;
                            foreach ($navbar['submenu'][$menu->ID] as $submenu)
                            {
                                if ($modulo_selecionado == $submenu->PASTA)
                                {
                                    $selecionado = TRUE;
                                }
                            }
                            ?>
                            <li>
                                <a data-toggle="collapse" 
                                   data-target="#submenu_<?= $menu->ID; ?>" 
                                   data-parent="#sidenav" 
                                   <?= ($selecionado) ? '' : 'class="collapsed"'; ?>>
                                    <i class="<?= $menu->ICONE; ?>"></i>
                                    <?= $menu->NOME; ?>
                                </a>
                                <div id="submenu_<?= $menu->ID; ?>"
                                     class="collapse <?= ($selecionado) ? 'in' : ''; ?>">
                                    <ul class="nav nav-list">
                                        <?php
                                        foreach ($navbar['submenu'][$menu->ID] as $submenu)
                                        {
                                            ?>
                                            <li <?= ($modulo_selecionado == $submenu->PASTA) ? 'class="active"' : ''; ?>>
                                                <a href="<?= $submenu->PASTA; ?>">
                                                    <?= $submenu->NOME; ?>                                                            
                                                </a>                                                    
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </li>
                            <?php
                        }
                    }
                    ?>
                    <li class="navbar-separator">
                        <?= parte_do_texto($sessao['usuario_nome']); ?>
                    </li>
                    <li class="nav-list-item <?= ($modulo_selecionado == 'geral_anotacao') ? 'active' : ''; ?>">
                        <a href="geral_anotacao" class="">
                            <i class="fa fa-fw fa-sticky-note-o"></i>
                            Anotações 
                        </a>                                                    
                    </li>
                    <li class="nav-list-item <?= ($modulo_selecionado == 'geral_tarefa') ? 'active' : ''; ?>">
                        <a href="geral_tarefa">
                            <i class="fa fa-fw fa-thumb-tack"></i>
                            Tarefas
                            <?php
                            if (!empty($tarefas_qtd))
                            {
                                ?>
                                <span class="badge pull-right"><?= $tarefas_qtd; ?></span>
                                <?php
                            }
                            ?>
                        </a>                                                    
                    </li>
                    <li class="nav-list-item <?= ($modulo_selecionado == 'geral_historico') ? 'active' : ''; ?>">
                        <a href="geral_historico">
                            <i class="fa fa-fw fa-history"></i>
                            Histórico 
                        </a>                                                    
                    </li>
                    <li class="nav-list-item">
                        <a href="autenticacao/sair">
                            <i class="fa fa-fw fa-sign-out"></i>
                            Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>