<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Aides d'affichage du front-office (dates en français, montants, fichiers publics).
 * Indépendant de l'extension intl : fonctionne sur tout hébergement mutualisé.
 */

if (!function_exists('date_fr')) {
    /**
     * « 2026-10-20 23:59:00 » → « 20 octobre 2026 à 23:59 »
     * @param string|null $datetime
     * @param bool        $heure    afficher l'heure (masquée si 00:00 et $heure = 'auto')
     * @param bool        $court    mois abrégé (« 20 oct. 2026 »)
     */
    function date_fr($datetime, $heure = 'auto', $court = false)
    {
        if (empty($datetime) || ($ts = strtotime($datetime)) === false) {
            return '';
        }

        $mois = $court
            ? ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.']
            : ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

        $jour  = (int) date('j', $ts);
        $texte = ($jour === 1 ? '1er' : $jour) . ' ' . $mois[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);

        $afficherHeure = $heure === 'auto' ? date('H:i', $ts) !== '00:00' : (bool) $heure;

        return $afficherHeure ? $texte . ' à ' . date('H:i', $ts) : $texte;
    }
}

if (!function_exists('montant_fr')) {
    /** 1000000 → « 1 000 000 BIF » (espace insécable comme séparateur de milliers) */
    function montant_fr($montant, $devise = 'BIF')
    {
        if ($montant === null || $montant === '') {
            return '';
        }
        $decimales = floor((float) $montant) == (float) $montant ? 0 : 2;
        return number_format((float) $montant, $decimales, ',', "\u{202F}") . "\u{00A0}" . $devise;
    }
}

if (!function_exists('fichier_public')) {
    /**
     * URL d'un fichier déposé via le back-office (uploads/…), ou null s'il
     * n'existe pas. Refuse tout chemin hors du dossier uploads/.
     */
    function fichier_public($chemin)
    {
        $chemin = ltrim((string) $chemin, '/');
        if ($chemin === '' || strpos($chemin, 'uploads/') !== 0 || strpos($chemin, '..') !== false) {
            return null;
        }
        return is_file(FCPATH . $chemin) ? base_url($chemin) : null;
    }
}

if (!function_exists('front_asset')) {
    /** URL d'un asset du front avec numéro de version (évite le cache après mise à jour) */
    function front_asset($chemin)
    {
        $fichier = FCPATH . 'assets/v2/front/' . ltrim($chemin, '/');
        $version = is_file($fichier) ? filemtime($fichier) : '1';
        return base_url('assets/v2/front/' . ltrim($chemin, '/')) . '?v=' . $version;
    }
}

if (!function_exists('lignes')) {
    /** Découpe un texte saisi « un élément par ligne » en tableau propre */
    function lignes($texte)
    {
        $items = preg_split('/\R/u', (string) $texte);
        $items = array_map(function ($l) {
            return trim(preg_replace('/^\s*[-•*–]\s*/u', '', $l));
        }, $items);
        return array_values(array_filter($items, 'strlen'));
    }
}

if (!function_exists('paragraphes')) {
    /** Texte libre → paragraphes HTML échappés */
    function paragraphes($texte)
    {
        $blocs = preg_split('/\R\s*\R/u', trim((string) $texte));
        $html  = '';
        foreach (array_filter($blocs, 'strlen') as $bloc) {
            $html .= '<p>' . nl2br(html_escape(trim($bloc))) . '</p>';
        }
        return $html;
    }
}

if (!function_exists('taille_fichier')) {
    /** Octets → « 2,4 Mo » */
    function taille_fichier($octets)
    {
        $octets = (float) $octets;
        if ($octets < 1024) {
            return (int) $octets . ' o';
        }
        $unites = ['Ko', 'Mo', 'Go'];
        $i = -1;
        do {
            $octets /= 1024;
            $i++;
        } while ($octets >= 1024 && $i < 2);
        return number_format($octets, $octets < 10 ? 1 : 0, ',', ' ') . ' ' . $unites[$i];
    }
}
