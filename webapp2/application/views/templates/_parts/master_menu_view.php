<?php
if (!empty($modulo_menu))
{
    ?>
    <ul id="topbar" class="nav nav-pills">
        <?php
        if (!empty($modulo_nome))
        {
            ?>
            <li class="disabled">
                <a href="<?= $modulo_url; ?>">
                    <?= $modulo_nome; ?>
                </a>
            </li>
            <?php
        }
        foreach ($modulo_menu as $menu_nome => $menu_url)
        {
            ?>
            <li>
                <a href="<?= $modulo_url . '/' . $menu_url; ?>" <?= ($menu_url == $menu_selecionado) ? 'class="active"' : ''; ?>>
                    <?= $menu_nome; ?>
                </a>
            </li>
            <?php
        }
        ?>
    </ul>
    <hr>
    <?php
}