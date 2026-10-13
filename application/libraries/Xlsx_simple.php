<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Générateur XLSX minimal, sans dépendance (ZipArchive uniquement).
 *
 * Produit un vrai classeur Office Open XML : une feuille, en-tête en gras sur fond
 * vert, première ligne figée, filtre automatique, largeurs de colonnes ajustées.
 * Les nombres restent des nombres (triables / sommables dans Excel), le texte est
 * écrit en « inline string » : aucune formule n'est jamais interprétée.
 *
 * Usage :
 *   $this->load->library('xlsx_simple');
 *   $chemin = $this->xlsx_simple->generer('Candidats', $entetes, $lignes);
 */
class Xlsx_simple
{
    /**
     * @param string $titre_feuille  Nom de l'onglet (31 caractères max)
     * @param array  $entetes        ['Libellé', ...]
     * @param array  $lignes         [[valeur, ...], ...]
     * @return string                Chemin du fichier .xlsx temporaire (à supprimer après envoi)
     */
    public function generer($titre_feuille, array $entetes, array $lignes)
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('L\'extension PHP « zip » est requise pour générer un fichier Excel.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', $this->_content_types());
        $zip->addFromString('_rels/.rels', $this->_rels());
        $zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
. '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
    <Application>SATRACO Concours</Application>
</Properties>');
$zip->addFromString('docProps/core.xml', $this->_core());
$zip->addFromString('xl/workbook.xml', $this->_workbook($titre_feuille));
$zip->addFromString('xl/_rels/workbook.xml.rels', $this->_workbook_rels());
$zip->addFromString('xl/styles.xml', $this->_styles());
$zip->addFromString('xl/worksheets/sheet1.xml', $this->_feuille($entetes, $lignes));
$zip->close();

return $tmp;
}

/* ---------------------------------------------------------------- */

private function _feuille(array $entetes, array $lignes)
{
$nb_col = max(1, count($entetes));
$derniere_col = $this->_colonne($nb_col - 1);
$nb_lignes = count($lignes) + 1;

// Largeur de colonne d'après le contenu le plus long (bornée)
$largeurs = array_map(function ($e) {
return mb_strlen((string) $e);
}, $entetes);
foreach (array_slice($lignes, 0, 500) as $l) {
foreach (array_values($l) as $i => $v) {
$longueur = max(array_map('mb_strlen', explode("\n", (string) $v)) ?: [0]);
$largeurs[$i] = max($largeurs[$i] ?? 0, $longueur);
}
}
$cols = '';
for ($i = 0; $i < $nb_col; $i++) { $w=min(60, max(8, ($largeurs[$i] ?? 8) + 2)); $cols .='<col min="' . ($i + 1)
    . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>' ; }
    $xml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
    . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . '<dimension ref="A1:' .
    $derniere_col . $nb_lignes . '"/>'
    . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
    . '<sheetFormatPr defaultRowHeight="15"/>' . '<cols>' . $cols . '</cols><sheetData>' ; $xml .=$this->_ligne(1,
    array_values($entetes), 1);
    foreach (array_values($lignes) as $n => $l) {
    $xml .= $this->_ligne($n + 2, array_values($l), 0);
    }

    $xml .= '</sheetData>'
    . '
    <autoFilter ref="A1:' . $derniere_col . $nb_lignes . '" />'
    . '
    <pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3" />'
    . '
    <pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0" />'
    . '</worksheet>';

    return $xml;
    }

    private function _ligne($num, array $valeurs, $style)
    {
    $xml = '<row r="' . $num . '">';
        foreach ($valeurs as $i => $v) {
        $ref = $this->_colonne($i) . $num;
        if ($v === NULL || $v === '') {
        continue;
        }
        if (!$style && (is_int($v) || is_float($v))) {
        $xml .= '<c r="' . $ref . '" s="' . (is_float($v) ? 3 : 0) . '">
            <v>' . $v . '</v>
        </c>';
        } else {
        $s = $style ? 1 : (strpos((string) $v, "\n") !== FALSE ? 2 : 0);
        $xml .= '<c r="' . $ref . '" t="inlineStr"' . ($s ? ' s="' . $s . '"' : '') . '>
            <is>
                <t xml:space="preserve">'
                    . $this->_xml((string) $v) . '</t>
            </is>
        </c>';
        }
        }
        return $xml . '</row>';
    }

    /** 0 → A, 25 → Z, 26 → AA */
    private function _colonne($i)
    {
    $s = '';
    for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
    $s = chr(65 + ($i - 1) % 26) . $s;
    }
    return $s;
    }

    /** Échappement XML + suppression des caractères de contrôle interdits */
    private function _xml($s)
    {
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s);
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function _content_types()
    {
    return '
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '
        <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml" />'
        . '
        <Default Extension="xml" ContentType="application/xml" />'
        . '
        <Override PartName="/xl/workbook.xml"
            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml" />'
        . '
        <Override PartName="/xl/worksheets/sheet1.xml"
            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml" />'
        . '
        <Override PartName="/xl/styles.xml"
            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml" />'
        . '
        <Override PartName="/docProps/core.xml"
            ContentType="application/vnd.openxmlformats-package.core-properties+xml" />'
        . '
        <Override PartName="/docProps/app.xml"
            ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml" />'
        . '
    </Types>';
    }

    private function _rels()
    {
    return '
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '
        <Relationship Id="rId1"
            Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
            Target="xl/workbook.xml" />'
        . '
        <Relationship Id="rId2"
            Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties"
            Target="docProps/core.xml" />'
        . '
        <Relationship Id="rId3"
            Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties"
            Target="docProps/app.xml" />'
        . '
    </Relationships>';
    }

    private function _core()
    {
    $d = gmdate('Y-m-d\TH:i:s\Z');
    return '
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . ' xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
        . '<dc:creator>SATRACO Concours</dc:creator>'
        . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $d . '</dcterms:created>'
        . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $d . '</dcterms:modified>'
        . '</cp:coreProperties>';
    }

    private function _workbook($titre)
    {
    // Caractères interdits dans un nom d'onglet : \ / ? * [ ] :
    $titre = mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $titre) ?: 'Export', 0, 31);
    return '
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets>
            <sheet name="' . $this->_xml($titre) . '" sheetId="1" r:id="rId1" />
        </sheets>'
        . '</workbook>';
    }

    private function _workbook_rels()
    {
    return '
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
            Target="worksheets/sheet1.xml" />'
        . '
        <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"
            Target="styles.xml" />'
        . '
    </Relationships>';
    }

    /** Styles : 0 normal · 1 en-tête (gras blanc sur vert SATRACO) · 2 texte multiligne · 3 décimal */
    private function _styles()
    {
    return '
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<numFmts count="1">
            <numFmt numFmtId="164" formatCode="#,##0.00" />
        </numFmts>'
        . '<fonts count="2">
            <font>
                <sz val="11" />
                <name val="Calibri" />
            </font>'
            . '<font><b />
                <sz val="11" />
                <color rgb="FFFFFFFF" />
                <name val="Calibri" />
            </font>
        </fonts>'
        . '<fills count="3">
            <fill>
                <patternFill patternType="none" />
            </fill>
            <fill>
                <patternFill patternType="gray125" />
            </fill>'
            . '<fill>
                <patternFill patternType="solid">
                    <fgColor rgb="FF1E7B3C" />
                    <bgColor indexed="64" />
                </patternFill>
            </fill>
        </fills>'
        . '<borders count="1">
            <border>
                <left />
                <right />
                <top />
                <bottom />
                <diagonal />
            </border>
        </borders>'
        . '<cellStyleXfs count="1">
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0" />
        </cellStyleXfs>'
        . '<cellXfs count="4">'
            . '
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" />'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"
                applyAlignment="1">
                <alignment vertical="center" />
            </xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">
                <alignment wrapText="1" vertical="top" />
            </xf>'
            . '
            <xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" />'
            . '
        </cellXfs>'
        . '<cellStyles count="1">
            <cellStyle name="Normal" xfId="0" builtinId="0" />
        </cellStyles>'
        . '</styleSheet>';
    }
    }