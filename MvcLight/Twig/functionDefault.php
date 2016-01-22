<?php

return array(
    'md5' => function ($string) {
        return md5($string);
    },
    'dump' => function ($variable) {
        var_dump($variable);
    }
);
