<?php

return array(
    'upperword' => function ($string) {
        for ($i = 0; $i < strlen($string); $i++) {
            if ($string[$i] == ' ' && ($i < strlen($string) - 1)) {
                $string[$i + 1] = strtoupper($string[$i + 1]);
            }
        }
        return $string;
    },
    'cut' => function ($string, $limit) {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }
        return rtrim(mb_substr($string, 0, $limit - 1)) . "...";
    }
);
