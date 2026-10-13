<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Renvoie 'active' si l'URL courante commence par l'un des préfixes donnés.
 *   <a class="nav-link <?= nav_active('candidats') ?>">
 * nav_active('concours/parametres') ou nav_active(['projets', 'evaluation'])
 */
if (!function_exists('nav_active')) {
    function nav_active($prefixes, $exact = false)
    {
        $uri = trim(get_instance()->uri->uri_string(), '/');

        foreach ((array) $prefixes as $prefix) {
            $prefix = trim($prefix, '/');
            if ($uri === $prefix || (!$exact && strpos($uri, $prefix . '/') === 0)) {
                return 'active';
            }
        }
        return '';
    }
}

/** Initiales pour l'avatar (« Jean Ndayishimiye » → « JN ») */
if (!function_exists('user_initials')) {
    function user_initials($prenom, $nom)
    {
        return mb_strtoupper(mb_substr((string) $prenom, 0, 1) . mb_substr((string) $nom, 0, 1));
    }
}
