<?php

/**
 * str_split function for multibyte strings.
 *
 * @param string $str
 * @param int    $split_length
 * @return array
 */
function mb_str_split($str, $split_length)
{
    $chars = array();
    $len = mb_strlen($str, 'UTF-8');
    for ($i = 0; $i < $len; $i += $split_length) {
        $chars[] = mb_substr($str, $i, $split_length, 'UTF-8'); // only one char to go to the array
    }
    return $chars;
}
