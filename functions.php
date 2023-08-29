<?php
/**
 * OceanWP Child Theme Functions
 *
 * When running a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions will be used.
 *
 * Text Domain: oceanwp
 * @link http://codex.wordpress.org/Plugin_API
 *
 */

/**
 * Load the parent style.css file
 *
 * @link http://codex.wordpress.org/Child_Themes
 */
function oceanwp_child_enqueue_parent_style() {
	// Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update the theme).
	$theme   = wp_get_theme( 'OceanWP' );
	$version = $theme->get( 'Version' );

	// Load the stylesheet.
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'oceanwp-style' ), $version );
}

add_action( 'wp_enqueue_scripts', 'oceanwp_child_enqueue_parent_style' );

// Add custom font to font settings
function ocean_add_custom_fonts() {
	return array('Chilet');
}

// Shortcode pour afficher les produits d'un producteur.
function afficher_produits_avec_etiquette($atts) {
    ob_start(); // Démarre la mise en mémoire tampon de sortie

    // Récupérer les attributs passés au shortcode
    $atts = shortcode_atts(array(
        'etiquette' => ''
    ), $atts);

    // Vérifier si l'attribut 'etiquette' a été spécifié
    if (!empty($atts['etiquette'])) {
        // Récupérer les produits avec l'étiquette spécifiée
        $products = wc_get_products(array(
            'status'         => 'publish',
            'limit'          => -1,
            'tag'            => $atts['etiquette'], // Utilisation de l'attribut 'tag' pour la recherche par étiquette
            'return'         => 'objects',
        ));

        // Vérifier si des produits sont trouvés
        if ($products) {
            echo '<div class="products-wrapper">';
            foreach ($products as $product) {
                $product_id = $product->get_id();
                $product_name = $product->get_name();
                $product_permalink = $product->get_permalink();
                $product_image = $product->get_image('thumbnail');

                echo '<div class="product-item">';
                echo '  <div class="product-image">' . $product_image . '</div>';
                echo '  <div class="product-content">';
                echo '    <h3 class="product-title"><a href="' . $product_permalink . '">' . $product_name . '</a></h3>';
                echo '  </div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo 'Aucun produit disponible pour le producteur "' . $atts['etiquette'] . '".';
        }
    } else {
        echo 'Veuillez spécifier une étiquette pour afficher les produits correspondants.';
    }

    return ob_get_clean(); // Récupère le contenu de la mémoire tampon de sortie et l'efface
}
add_shortcode('afficher_produits_etiquette', 'afficher_produits_avec_etiquette');

// Gestions des breadcrumbs des pages paniers
function paniers_trail_items($items) {
    if (str_starts_with($_SERVER['REQUEST_URI'], '/paniers/index.php')) {
        if ($_GET["action"] == "ajoutercde") {
            return [$items[0], "Paniers", "Nouvelle commande"];
        } else if ($_GET["action"] == "voircde") {
            return [$items[0], "Paniers", "Commandes"];
        } else if ($_GET["action"] == "planning") {
            return [$items[0], "Paniers", "Permanences"];
        }
        else {
            return [$items[0], "Paniers"];
        }
    }
    else if (str_starts_with($_SERVER['REQUEST_URI'], '/paniers/nouveau.php')) {
        return [$items[0], "Bon de commande"];
    } else {
        return $items;
    }
}

function paniers_page_title($title) {
    if (str_starts_with($_SERVER['REQUEST_URI'], '/paniers/index.php')) {
        if ($_GET["action"] == "ajoutercde") {
            return "Nouvelle commande";
        } else if ($_GET["action"] == "voircde") {
            return "Commandes";
        } else if ($_GET["action"] == "planning") {
            return "Permanences";
        }
        else {
            return "Paniers";
        }
    }
    else if (str_starts_with($_SERVER['REQUEST_URI'], '/paniers/nouveau.php')) {
        return "Bon de commande";
    }
    else {
        return $title;
    }
}

// Configuration de woocommerce pour l'affichage des produits.
add_filter('ocean_title', 'paniers_page_title');
add_filter('oceanwp_breadcrumb_trail_items',  'paniers_trail_items');

add_filter('woocommerce_register_post_type_product','paniers_remove_shop_default_description');
function paniers_remove_shop_default_description($args){
	$args['description'] = '';
	return $args;
}

$false = function() { return false; };
add_filter('wc_product_sku_enabled', $false);
add_filter('wc_product_weight_enabled', $false);
add_filter('wc_product_dimensions_enabled', $false);

// Creation de modeles avec Gutemberg
add_theme_support( 'block-templates' );

// Login and logout redirect
add_filter( 'logout_redirect', function( $url, $query, $user ) {
	return home_url();
}, 10, 3 );


add_filter( 'login_redirect', function ( $redirect_to, $request, $user ) {
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'administrator', $user->roles ) ) {
			return $redirect_to;
		} else {
			return home_url();
		}
	} else {
		return $redirect_to;
	}
}, 10, 3 );
