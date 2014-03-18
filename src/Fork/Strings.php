<?php
/**
 * This source is part of Fork Suite.
 *
 * A multi-platform / multi-vector pentesting framework for client side attacks.
 *
 * (c) BoxedResearch LLC <products@boxedresearch.com>
 */
namespace Fork;


class Strings {

    public static function mb_str_replace($needle, $replacement, $haystack)
    {
        $needle_len = mb_strlen($needle);
        $replacement_len = mb_strlen($replacement);
        $pos = mb_strpos($haystack, $needle);
        while ($pos !== false)
        {
            $haystack = mb_substr($haystack, 0, $pos) . $replacement
                . mb_substr($haystack, $pos + $needle_len);
            $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
        }
        return $haystack;
    }

} 