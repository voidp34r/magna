<!DOCTYPE html>
<html>
    <?php
    $this->load->view('templates/_parts/master_header_view');
    ?>
    <body>
        <div class="row affix-row" id="conteudo" style="display: none;">
            <?php
            $this->load->view('templates/_parts/master_sidebar_view');
            $this->load->view('templates/_parts/master_js_view');
            ?>
            <div class="col-sm-10 affix-content">
                <?php
                $this->load->view('templates/_parts/master_menu_view');
                ?>
                <div class="affix-content-inner">
                    <?php
                    $this->load->view('templates/_parts/master_alert_view');
                    echo $the_view_content;
                    ?>
                </div>
            </div>
        </div>

        <script src="assets/js/template.js"></script>
    </body>
</html>