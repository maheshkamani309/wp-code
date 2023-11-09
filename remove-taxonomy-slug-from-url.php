<?php

/**
 * To changed the query paramaters
 * 
 */
function mak_change_term_request($query)
{

    // Specify the taxonomy name here, You can also use 'category' or 'post_tag'
    $tax_name = 'product_cat';

    // Request for child terms differs, we should make an additional check
    if ($query['attachment']) :
        $include_children = true;
        $name = $query['attachment'];
    else :
        $include_children = false;
        $name = $query['name'];
    endif;


    // Check the the term is exists or not 
    $term = get_term_by('slug', $name, $tax_name);

    if (isset($name) && $term && !is_wp_error($term)) :
        if ($include_children) {
            unset($query['attachment']);
            $parent = $term->parent;
            while ($parent) {
                $parent_term = get_term($parent, $tax_name);
                $name = $parent_term->slug . '/' . $name;
                $parent = $parent_term->parent;
            }
        } else {
            unset($query['name']);
        }

        switch ($tax_name):
            case 'category': {
                    $query['category_name'] = $name; // for categories
                    break;
                }
            case 'post_tag': {
                    $query['tag'] = $name; // for post tags
                    break;
                }
            default: {
                    $query[$tax_name] = $name; // for another taxonomies
                    break;
                }
        endswitch;

    endif;

    return $query;
}
add_filter('request', 'mak_change_term_request', 1, 1);


/**
 * Changed the permalink url for the particular taxonomy
 * 
 * @param $url String URL without removing taxonomy slug
 * @param $term Object Object of the taxonomy
 * @param $taxonomy String Name of the taxonomy
 * 
 * @return $url Strin New url for the taxonomy
 */
function mak_change_term_permalink($url, $term, $taxonomy)
{

    // Changed your taxonomy name and slug here
    $taxonomy_name = 'product_cat';
    $taxonomy_slug = 'product_cat';

    // check if taxonomy slug is not in the URL then return request URL.
    if (strpos($url, $taxonomy_slug) === FALSE || $taxonomy != $taxonomy_name) return $url;

    $url = str_replace('/' . $taxonomy_slug, '', $url);
    return $url;
}
add_filter('term_link', 'mak_change_term_permalink', 10, 3);

/**
 * To redirect request to new url if someone run url with the taxonomy slug
 */
function mak_old_request_term_redirect()
{

    // Changed your taxonomy name and slug here
    $taxonomy_name = 'product_cat';
    $taxonomy_slug = 'product_cat';

    // exit the redirect function if taxonomy slug is not in URL
    if (strpos($_SERVER['REQUEST_URI'], $taxonomy_slug) === FALSE)
        return;

    if ((is_category() && $taxonomy_name == 'category') || (is_tag() && $taxonomy_name == 'post_tag') || is_tax($taxonomy_name)) :

        wp_redirect(site_url(str_replace($taxonomy_slug, '', $_SERVER['REQUEST_URI'])), 301);
        exit();
    endif;
}
add_action('template_redirect', 'mak_old_request_term_redirect');
