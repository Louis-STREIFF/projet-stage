<?php
/**
 * Plugin Name: Airtable Form
 * Description: Custom shortcodes, styles and scripts, native AJAX search, and dynamic artist profile pages.
 * Version: 1.0
 * Author: Louis Streiff
 */

require_once plugin_dir_path(__FILE__) . 'airtable.php';
function mon_plugin_enqueue_assets() {
    wp_enqueue_style(
        'mon-plugin-styles',
        plugins_url('Search/styles.css', __FILE__)
    );

    wp_enqueue_script(
        'mon-plugin-script',
        plugin_dir_url(__FILE__) . 'Search/script.js',
        [],
        null,
        true
    );

    wp_localize_script(
        'mon-plugin-script',
        'monPluginData',
        ['ajaxUrl' => admin_url('admin-ajax.php')]
    );
}
add_action('wp_enqueue_scripts', 'mon_plugin_enqueue_assets');

add_shortcode('search', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'Search/form.php';
    return ob_get_clean();
});

add_shortcode('search_results', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'Search/search.php';
    return ob_get_clean();
});

add_shortcode('add_artist_form', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'Add/add_artists.php';
    return ob_get_clean();
});

add_action('wp_ajax_search_artists', 'search_artists');
add_action('wp_ajax_nopriv_search_artists', 'search_artists');

function search_artists() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'Search/search.php';
    echo ob_get_clean();
    wp_die();
}

add_action('init', function () {
    add_rewrite_rule(
        '(?i)^artiste/([^/]+)/?$',
        'index.php?pagename=artiste&artist_slug=$matches[1]',
        'top'
    );
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'artist_slug';
    return $vars;
});

add_shortcode('artist_profile', 'display_artist_profile');

function display_artist_profile() {
    $slug = get_query_var('artist_slug');
    if (!$slug) {
        return '<p>Aucun artiste sélectionné.</p>';
    }

    $selected_artist = get_artist_by_slug($slug);
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/single-artist.php';
    return ob_get_clean();
}

add_action('template_redirect', function () {
    if (is_page('artiste') && get_query_var('artist_slug')) {
        $slug = get_query_var('artist_slug');

        $existing_page = get_page_by_path($slug, OBJECT, 'page');

        if ($existing_page) {
            wp_redirect(get_permalink($existing_page), 301);
            exit;
        }
    }
});
function get_artist_by_slug($slug) {
    $apiKey = get_option('mon_plugin_airtable_api_key');
    $baseID = get_option('mon_plugin_airtable_base_id');
    $tableName = get_option('mon_plugin_airtable_table_name');

    if (!$apiKey || !$baseID || !$tableName) {
        return null;
    }

    $filter = "AND({Status} = 'public', LOWER(SUBSTITUTE({Slug}, ' ', '-')) = '" . strtolower($slug) . "')";
    $results = getArtistsFromAirtable($apiKey, $baseID, $tableName, $filter);

    if (!empty($results)) {
        return $results[0]['fields'];
    }

    return null;
}


function mon_plugin_enqueue_artist_styles() {
    if (is_page('artiste') && get_query_var('artist_slug')) {
        wp_enqueue_style(
            'artist-profile-styles',
            plugins_url('Templates/style.css', __FILE__)
        );
    }
}
add_action('wp_enqueue_scripts', 'mon_plugin_enqueue_artist_styles');


add_action('admin_menu', 'mon_plugin_ajouter_menu');

function mon_plugin_ajouter_menu() {
    add_options_page(
        'Réglages API Mon Plugin',
        'API Mon Plugin',
        'manage_options',
        'mon_plugin_api_settings',
        'mon_plugin_afficher_page_reglages'
    );
}

add_action('admin_init', 'mon_plugin_enregistrer_reglages');

function mon_plugin_enregistrer_reglages() {
    register_setting('mon_plugin_api_settings_group', 'mon_plugin_airtable_api_key');
    register_setting('mon_plugin_api_settings_group', 'mon_plugin_airtable_base_id');
    register_setting('mon_plugin_api_settings_group', 'mon_plugin_airtable_table_name');
    register_setting('mon_plugin_api_settings_group', 'mon_plugin_map_api_key');

    add_settings_section(
        'mon_plugin_api_settings_section',
        'Clés API',
        null,
        'mon_plugin_api_settings'
    );

    add_settings_field(
        'mon_plugin_airtable_api_key',
        'Clé API Airtable',
        'mon_plugin_champ_airtable_api_key_html',
        'mon_plugin_api_settings',
        'mon_plugin_api_settings_section'
    );

    add_settings_field(
        'mon_plugin_airtable_base_id',
        'Base ID Airtable',
        'mon_plugin_champ_base_id_html',
        'mon_plugin_api_settings',
        'mon_plugin_api_settings_section'
    );

    add_settings_field(
        'mon_plugin_airtable_table_name',
        'Nom de la Table Airtable',
        'mon_plugin_champ_table_name_html',
        'mon_plugin_api_settings',
        'mon_plugin_api_settings_section'
    );

    add_settings_field(
        'mon_plugin_map_api_key',
        'Clé API Map',
        'mon_plugin_champ_map_api_key_html',
        'mon_plugin_api_settings',
        'mon_plugin_api_settings_section'
    );
}

function mon_plugin_champ_airtable_api_key_html() {
    $value = get_option('mon_plugin_airtable_api_key', '');
    echo '<input type="text" name="mon_plugin_airtable_api_key" value="' . esc_attr($value) . '" class="regular-text">';
}

function mon_plugin_champ_base_id_html() {
    $value = get_option('mon_plugin_airtable_base_id', '');
    echo '<input type="text" name="mon_plugin_airtable_base_id" value="' . esc_attr($value) . '" class="regular-text">';
}

function mon_plugin_champ_table_name_html() {
    $value = get_option('mon_plugin_airtable_table_name', '');
    echo '<input type="text" name="mon_plugin_airtable_table_name" value="' . esc_attr($value) . '" class="regular-text">';
}

function mon_plugin_champ_map_api_key_html() {
    $value = get_option('mon_plugin_map_api_key', '');
    echo '<input type="text" name="mon_plugin_map_api_key" value="' . esc_attr($value) . '" class="regular-text">';
}

function mon_plugin_afficher_page_reglages() {
    ?>
    <div class="wrap">
        <h1>Réglages API</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mon_plugin_api_settings_group');
            do_settings_sections('mon_plugin_api_settings');
            submit_button('Enregistrer');
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_notices', function () {
    if (current_user_can('manage_options') && !get_option('mon_plugin_api_key')) {
        echo '<div class="notice notice-warning"><p>⚠️ Votre plugin n’est pas encore configuré. Merci d’entrer votre clé API dans <a href="' . esc_url(admin_url('options-general.php?page=mon_plugin_api_settings')) . '">les réglages du plugin</a>.</p></div>';
    }
});

function afficher_artistes_par_format_shortcode($atts) {
    ob_start();

    $apiKey = get_option('mon_plugin_airtable_api_key');
    $baseID = get_option('mon_plugin_airtable_base_id');
    $tableName = get_option('mon_plugin_airtable_table_name');
    $atts = shortcode_atts([
        'slug' => '',
    ], $atts);
    $formatSlug = strtolower(sanitize_title($atts['slug']));
    $filter = "Status='public'";
    $allArtists = getArtistsFromAirtable($apiKey, $baseID, $tableName, $filter);

    $artists = [];
    foreach ($allArtists as $record) {
        $fields = $record['fields'] ?? [];

        if (!empty($fields['Services_Type']) && is_array($fields['Services_Type'])) {
            $formatNames = getProductServiceNames($apiKey, $baseID, $fields['Services_Type']);
            foreach ($formatNames as $name) {
                if (strtolower(sanitize_title($name)) === $formatSlug) {
                    $artists[] = $record;
                    break;
                }
            }
        }
    }
    include plugin_dir_path(__FILE__) . 'Templates/format-results.php';

    return ob_get_clean();
}

add_shortcode('artist_by_format', 'afficher_artistes_par_format_shortcode');

