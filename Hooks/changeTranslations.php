<?php

// Execute for specific domain 
add_filter("gettext_{text-domian-goes-here}", 'makChangeText', 20, 2);
function makChangeText($translated_text, $untranslated_text)
{
    if ($untranslated_text == 'To test your WooCommerce installation, you can use the sandbox mode.') {
        return 'To test your CustomText installation, you can use the sandbox mode.';
    }
    return $translated_text;
}


// Execute for every translation string
add_filter('gettext', 'makChangeTextWithOutDomain', 10, 3);
function makChangeTextWithOutDomain($translated_text, $untranslated_text, $domain)
{
    if ('blah' !== $domain) {
        return $translated_text;
    }
}
