<!DOCTYPE html>
<html>
    <head>
        <title>MVC LIGHT ERROR!</title>
        <style>
            *{
                margin: 0;
                padding: 0;
            }
            body{
                background: #EEEEEE;
            }
            .wapper{
                margin: 30px;
            }
            .title-error{
                width: 1000px;
                margin: auto;
                padding: 10px 30px;
                background: #fff;
                border-radius: 8px;
                -moz-border-radius: 8px;
                -o-border-radius: 8px;
                -ms-border-radius: 8px;
                -webkit-border-radius: 8px;
                border: 1px solid #bcbcbc;
                margin-bottom: 5px;
                color: red;
                font-size: 25px;
            }
            .content-error{
                width: 1000px;
                margin: auto;
                padding: 10px 30px;
                background: #fff;
                border-radius: 8px;
                -moz-border-radius: 8px;
                -o-border-radius: 8px;
                -ms-border-radius: 8px;
                -webkit-border-radius: 8px;
                border: 1px solid #bcbcbc;
                font-size: 20px;
            }
        </style>
    </head>
    <body>
        <div class="wapper">
            <?php
            foreach ($error as $key => $value) {
                ?>
                <div class="title-error">
                    <h2><?php echo $value['name']; ?></h2>
                </div>
                <div class="content-error">
                    <p><?php echo $value['msg']; ?></p>
                </div>
                <?php
            }
            ?>
        </div>
    </body>
</html>