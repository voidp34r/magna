<!DOCTYPE html>
<html>
    <?php
    $this->load->view('templates/_parts/master_header_view');
    ?>
    <style media="screen">
        .print-fundo {
            background-color: #fff;
        }
        .print-pagina {
            margin-top: 20px;
            margin: auto;
            width: 21cm;
            -webkit-touch-callout: none; /* iOS Safari */
            -webkit-user-select: none;   /* Chrome/Safari/Opera */
            -khtml-user-select: none;    /* Konqueror */
            -moz-user-select: none;      /* Firefox */
            -ms-user-select: none;       /* IE/Edge */
            user-select: none;           /* non-prefixed version, currently
                                            not supported by any browser */
        }
    </style>
    <style media="print">
        @page {
            size: A4;
        }
        .print-pagina {
            line-height: 0.8;
        }
    </style>
    <body>
        <?php
        $this->load->view('templates/_parts/master_js_view');
        ?>
        <div class="print-fundo">
            <div class="print-pagina">
                <?php
                echo $the_view_content;
                ?>
            </div>
        </div>
        <script>
            window.print();
//            setTimeout(window.close, 100);
        </script>
    </body>
</html>