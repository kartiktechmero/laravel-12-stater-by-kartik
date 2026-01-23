<?php

/** Adminer - Compact database management
 * @link https://www.adminer.org/
 *
 * @author Jakub Vrana, https://www.vrana.cz/
 * @copyright 2007 Jakub Vrana
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 *
 * @version 5.4.1
 */

namespace Adminer;

const VERSION = '5.4.1';
error_reporting(24575);
set_error_handler(function ($rc, $tc) {
    return (bool) preg_match('~^Undefined (array key|offset|index)~', $tc);
}, E_WARNING | E_NOTICE);
$Nc = ! preg_match('~^(unsafe_raw)?$~', ini_get('filter.default'));
if ($Nc || ini_get('filter.default_flags')) {
    foreach (['_GET', '_POST', '_COOKIE', '_SERVER'] as $X) {
        $vi = filter_input_array(constant("INPUT$X"), FILTER_UNSAFE_RAW);
        if ($vi) {
            $$X = $vi;
        }
    }
}if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('8bit');
}function connection($g = null)
{
    return $g ?: Db::$instance;
}function adminer()
{
    return Adminer::$instance;
}function driver()
{
    return Driver::$instance;
}function connect()
{
    $wb = adminer()->credentials();
    $L = Driver::connect($wb[0], $wb[1], $wb[2]);

    return is_object($L) ? $L : null;
}function idf_unescape($v)
{
    if (! preg_match('~^[`\'"[]~', $v)) {
        return $v;
    }$le = substr($v, -1);

    return str_replace($le.$le, $le, substr($v, 1, -1));
}function q($zh)
{
    return connection()->quote($zh);
}function escape_string($X)
{
    return substr(q($X), 1, -1);
}function idx($ta, $z, $k = null)
{
    return $ta && array_key_exists($z, $ta) ? $ta[$z] : $k;
}function number($X)
{
    return preg_replace('~[^0-9]+~', '', $X);
}function number_type()
{
    return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';
}function remove_slashes(array $sg, $Nc = false)
{
    if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        while ([$z, $X] = each($sg)) {
            foreach ($X as $de => $W) {
                unset($sg[$z][$de]);
                if (is_array($W)) {
                    $sg[$z][stripslashes($de)] = $W;
                    $sg[] = &$sg[$z][stripslashes($de)];
                } else {
                    $sg[$z][stripslashes($de)] = ($Nc ? $W : stripslashes($W));
                }
            }
        }
    }
}function bracket_escape($v, $Aa = false)
{
    static $ii = [':' => ':1', ']' => ':2', '[' => ':3', '"' => ':4'];

    return strtr($v, ($Aa ? array_flip($ii) : $ii));
}function min_version($Li, $ze = '', $g = null)
{
    $g = connection($g);
    $eh = $g->server_info;
    if ($ze && preg_match('~([\d.]+)-MariaDB~', $eh, $C)) {
        $eh = $C[1];
        $Li = $ze;
    }

    return $Li && version_compare($eh, $Li) >= 0;
}function charset(Db $f)
{
    return min_version('5.5.3', 0, $f) ? 'utf8mb4' : 'utf8';
}function ini_bool($Nd)
{
    $X = ini_get($Nd);

    return preg_match('~^(on|true|yes)$~i', $X) || (int) $X;
}function ini_bytes($Nd)
{
    $X = ini_get($Nd);
    switch (strtolower(substr($X, -1))) {
        case 'g':$X = (int) $X * 1024;
        case 'm':$X = (int) $X * 1024;
        case 'k':$X = (int) $X * 1024;
    }

    return $X;
}function sid()
{
    static $L;
    if ($L === null) {
        $L = (SID && ! ($_COOKIE && ini_bool('session.use_cookies')));
    }

    return $L;
}function set_password($Ki, $P, $V, $H)
{
    $_SESSION['pwds'][$Ki][$P][$V] = ($_COOKIE['adminer_key'] && is_string($H) ? [encrypt_string($H, $_COOKIE['adminer_key'])] : $H);
}function get_password()
{
    $L = get_session('pwds');
    if (is_array($L)) {
        $L = ($_COOKIE['adminer_key'] ? decrypt_string($L[0], $_COOKIE['adminer_key']) : false);
    }

    return $L;
}function get_val($J, $m = 0, $mb = null)
{
    $mb = connection($mb);
    $K = $mb->query($J);
    if (! is_object($K)) {
        return false;
    }$M = $K->fetch_row();

    return $M ? $M[$m] : false;
}function get_vals($J, $c = 0)
{
    $L = [];
    $K = connection()->query($J);
    if (is_object($K)) {
        while ($M = $K->fetch_row()) {
            $L[] = $M[$c];
        }
    }

    return $L;
}function get_key_vals($J, $g = null, $hh = true)
{
    $g = connection($g);
    $L = [];
    $K = $g->query($J);
    if (is_object($K)) {
        while ($M = $K->fetch_row()) {
            if ($hh) {
                $L[$M[0]] = $M[1];
            } else {
                $L[] = $M[0];
            }
        }
    }

    return $L;
}function get_rows($J, $g = null, $l = "<p class='error'>")
{
    $mb = connection($g);
    $L = [];
    $K = $mb->query($J);
    if (is_object($K)) {
        while ($M = $K->fetch_assoc()) {
            $L[] = $M;
        }
    } elseif (! $K && ! $g && $l && (defined('Adminer\PAGE_HEADER') || $l == '-- ')) {
        echo $l.error()."\n";
    }

    return $L;
}function unique_array($M, array $x)
{
    foreach ($x as $w) {
        if (preg_match('~PRIMARY|UNIQUE~', $w['type'])) {
            $L = [];
            foreach ($w['columns'] as $z) {
                if (! isset($M[$z])) {
                    continue 2;
                }$L[$z] = $M[$z];
            }

            return $L;
        }
    }
}function escape_key($z)
{
    if (preg_match('(^([\w(]+)('.str_replace('_', '.*', preg_quote(idf_escape('_'))).')([ \w)]+)$)', $z, $C)) {
        return $C[1].idf_escape(idf_unescape($C[2])).$C[3];
    }

    return idf_escape($z);
}function where(array $Z, array $n = [])
{
    $L = [];
    foreach ((array) $Z['where'] as $z => $X) {
        $z = bracket_escape($z, true);
        $c = escape_key($z);
        $m = idx($n, $z, []);
        $Kc = $m['type'];
        $L[] = $c.(JUSH == 'sql' && $Kc == 'json' ? ' = CAST('.q($X).' AS JSON)' : (JUSH == 'pgsql' && preg_match('~^json~', $Kc) ? '::jsonb = '.q($X).'::jsonb' : (JUSH == 'sql' && is_numeric($X) && preg_match('~\.~', $X) ? ' LIKE '.q($X) : (JUSH == 'mssql' && strpos($Kc, 'datetime') === false ? ' LIKE '.q(preg_replace('~[_%[]~', '[\0]', $X)) : ' = '.unconvert_field($m, q($X))))));
        if (JUSH == 'sql' && preg_match('~char|text~', $Kc) && preg_match('~[^ -@]~', $X)) {
            $L[] = "$c = ".q($X).' COLLATE '.charset(connection()).'_bin';
        }
    }foreach ((array) $Z['null'] as $z) {
        $L[] = escape_key($z).' IS NULL';
    }

    return implode(' AND ', $L);
}function where_check($X, array $n = [])
{
    parse_str($X, $Sa);
    remove_slashes([&$Sa]);

    return where($Sa, $n);
}function where_link($t, $c, $Y, $tf = '=')
{
    return "&where%5B$t%5D%5Bcol%5D=".urlencode($c)."&where%5B$t%5D%5Bop%5D=".urlencode(($Y !== null ? $tf : 'IS NULL'))."&where%5B$t%5D%5Bval%5D=".urlencode($Y);
}function convert_fields(array $d, array $n, array $O = [])
{
    $L = '';
    foreach ($d as $z => $X) {
        if ($O && ! in_array(idf_escape($z), $O)) {
            continue;
        }$ua = convert_field($n[$z]);
        if ($ua) {
            $L
                .= ", $ua AS ".idf_escape($z);
        }
    }

    return $L;
}function cookie($E, $Y, $te = 2592000)
{
    header("Set-Cookie: $E=".urlencode($Y).($te ? '; expires='.gmdate('D, d M Y H:i:s', time() + $te).' GMT' : '').'; path='.preg_replace('~\?.*~', '', $_SERVER['REQUEST_URI']).(HTTPS ? '; secure' : '').'; HttpOnly; SameSite=lax', false);
}function get_settings($sb)
{
    parse_str($_COOKIE[$sb], $ih);

    return $ih;
}function get_setting($z, $sb = 'adminer_settings', $k = null)
{
    return idx(get_settings($sb), $z, $k);
}function save_settings(array $ih, $sb = 'adminer_settings')
{
    $Y = http_build_query($ih + get_settings($sb));
    cookie($sb, $Y);
    $_COOKIE[$sb] = $Y;
}function restart_session()
{
    if (! ini_bool('session.use_cookies') && (! function_exists('session_status') || session_status() == 1)) {
        session_start();
    }
}function stop_session($Sc = false)
{
    $Ei = ini_bool('session.use_cookies');
    if (! $Ei || $Sc) {
        session_write_close();
        if ($Ei && @ini_set('session.use_cookies', '0') === false) {
            session_start();
        }
    }
}function &get_session($z)
{
    return $_SESSION[$z][DRIVER][SERVER][$_GET['username']];
}function set_session($z, $X)
{
    $_SESSION[$z][DRIVER][SERVER][$_GET['username']] = $X;
}function auth_url($Ki, $P, $V, $j = null)
{
    $Ai = remove_from_uri(implode('|', array_keys(SqlDriver::$drivers)).'|username|ext|'.($j !== null ? 'db|' : '').($Ki == 'mssql' || $Ki == 'pgsql' ? '' : 'ns|').session_name());
    preg_match('~([^?]*)\??(.*)~', $Ai, $C);

    return "$C[1]?".(sid() ? SID.'&' : '').($Ki != 'server' || $P != '' ? urlencode($Ki).'='.urlencode($P).'&' : '').($_GET['ext'] ? 'ext='.urlencode($_GET['ext']).'&' : '').'username='.urlencode($V).($j != '' ? '&db='.urlencode($j) : '').($C[2] ? "&$C[2]" : '');
}function is_ajax()
{
    return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}function redirect($B, $D = null)
{
    if ($D !== null) {
        restart_session();
        $_SESSION['messages'][preg_replace('~^[^?]*~', '', ($B !== null ? $B : $_SERVER['REQUEST_URI']))][] = $D;
    }if ($B !== null) {
        if ($B == '') {
            $B = '.';
        }header("Location: $B");
        exit;
    }
}function query_redirect($J, $B, $D, $_g = true, $yc = true, $Gc = false, $Vh = '')
{
    if ($yc) {
        $vh = microtime(true);
        $Gc = ! connection()->query($J);
        $Vh = format_time($vh);
    }$qh = ($J ? adminer()->messageQuery($J, $Vh, $Gc) : '');
    if ($Gc) {
        adminer()->error
            .= error().$qh.script('messagesPrint();').'<br>';

        return false;
    }if ($_g) {
        redirect($B, $D.$qh);
    }

    return true;
} class Queries
{
    public static $queries = [];

    public static $start = 0;
}function queries($J)
{
    if (! Queries::$start) {
        Queries::$start = microtime(true);
    }Queries::$queries[] = (preg_match('~;$~', $J) ? "DELIMITER ;;\n$J;\nDELIMITER " : $J).';';

    return connection()->query($J);
}function apply_queries($J, array $T, $uc = 'Adminer\table')
{
    foreach ($T as $R) {
        if (! queries("$J ".$uc($R))) {
            return false;
        }
    }

    return true;
}function queries_redirect($B, $D, $_g)
{
    $vg = implode("\n", Queries::$queries);
    $Vh = format_time(Queries::$start);

    return query_redirect($vg, $B, $D, $_g, false, ! $_g, $Vh);
}function format_time($vh)
{
    return lang(0, max(0, microtime(true) - $vh));
}function relative_uri()
{
    return str_replace(':', '%3a', preg_replace('~^[^?]*/([^?]*)~', '\1', $_SERVER['REQUEST_URI']));
}function remove_from_uri($Mf = '')
{
    return substr(preg_replace("~(?<=[?&])($Mf".(SID ? '' : '|'.session_name()).')=[^&]*&~', '', relative_uri().'&'), 0, -1);
}function get_file($z, $Hb = false, $Nb = '')
{
    $Mc = $_FILES[$z];
    if (! $Mc) {
        return null;
    }foreach ($Mc as $z => $X) {
        $Mc[$z] = (array) $X;
    }$L = '';
    foreach ($Mc['error'] as $z => $l) {
        if ($l) {
            return $l;
        }$E = $Mc['name'][$z];
        $di = $Mc['tmp_name'][$z];
        $ob = file_get_contents($Hb && preg_match('~\.gz$~', $E) ? "compress.zlib://$di" : $di);
        if ($Hb) {
            $vh = substr($ob, 0, 3);
            if (function_exists('iconv') && preg_match("~^\xFE\xFF|^\xFF\xFE~", $vh)) {
                $ob = iconv('utf-16', 'utf-8', $ob);
            } elseif ($vh == "\xEF\xBB\xBF") {
                $ob = substr($ob, 3);
            }
        }$L
            .= $ob;
        if ($Nb) {
            $L
                .= (preg_match("($Nb\\s*\$)", $ob) ? '' : $Nb)."\n\n";
        }
    }

    return $L;
}function upload_error($l)
{
    $He = ($l == UPLOAD_ERR_INI_SIZE ? ini_get('upload_max_filesize') : 0);

    return $l ? lang(1).($He ? ' '.lang(2, $He) : '') : lang(3);
}function repeat_pattern($Zf, $re)
{
    return str_repeat("$Zf{0,65535}", $re / 65535)."$Zf{0,".($re % 65535).'}';
}function is_utf8($X)
{
    return preg_match('~~u', $X) && ! preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $X);
}function format_number($X)
{
    return strtr(number_format($X, 0, '.', lang(4)), preg_split('~~u', lang(5), -1, PREG_SPLIT_NO_EMPTY));
}function friendly_url($X)
{
    return preg_replace('~\W~i', '-', $X);
}function table_status1($R, $Hc = false)
{
    $L = table_status($R, $Hc);

    return $L ? reset($L) : ['Name' => $R];
}function column_foreign_keys($R)
{
    $L = [];
    foreach (adminer()->foreignKeys($R) as $p) {
        foreach ($p['source'] as $X) {
            $L[$X][] = $p;
        }
    }

    return $L;
}function fields_from_edit()
{
    $L = [];
    foreach ((array) $_POST['field_keys'] as $z => $X) {
        if ($X != '') {
            $X = bracket_escape($X);
            $_POST['function'][$X] = $_POST['field_funs'][$z];
            $_POST['fields'][$X] = $_POST['field_vals'][$z];
        }
    }foreach ((array) $_POST['fields'] as $z => $X) {
        $E = bracket_escape($z, true);
        $L[$E] = ['field' => $E, 'privileges' => ['insert' => 1, 'update' => 1, 'where' => 1, 'order' => 1], 'null' => 1, 'auto_increment' => ($z == driver()->primary)];
    }

    return $L;
}function dump_headers($zd, $Ve = false)
{
    $L = adminer()->dumpHeaders($zd, $Ve);
    $Jf = $_POST['output'];
    if ($Jf != 'text') {
        header('Content-Disposition: attachment; filename='.adminer()->dumpFilename($zd).".$L".($Jf != 'file' && preg_match('~^[0-9a-z]+$~', $Jf) ? ".$Jf" : ''));
    }session_write_close();
    if (! ob_get_level()) {
        ob_start(null, 4096);
    }ob_flush();
    flush();

    return $L;
}function dump_csv(array $M)
{
    foreach ($M as $z => $X) {
        if (preg_match('~["\n,;\t]|^0.|\.\d*0$~', $X) || $X === '') {
            $M[$z] = '"'.str_replace('"', '""', $X).'"';
        }
    }echo implode(($_POST['format'] == 'csv' ? ',' : ($_POST['format'] == 'tsv' ? "\t" : ';')), $M)."\r\n";
}function apply_sql_function($r, $c)
{
    return $r ? ($r == 'unixepoch' ? "DATETIME($c, '$r')" : ($r == 'count distinct' ? 'COUNT(DISTINCT ' : strtoupper("$r("))."$c)") : $c;
}function get_temp_dir()
{
    $L = ini_get('upload_tmp_dir');
    if (! $L) {
        if (function_exists('sys_get_temp_dir')) {
            $L = sys_get_temp_dir();
        } else {
            $o = @tempnam('', '');
            if (! $o) {
                return '';
            }$L = dirname($o);
            unlink($o);
        }
    }

    return $L;
}function file_open_lock($o)
{
    if (is_link($o)) {
        return;
    }$q = @fopen($o, 'c+');
    if (! $q) {
        return;
    }@chmod($o, 0660);
    if (! flock($q, LOCK_EX)) {
        fclose($q);

        return;
    }

    return $q;
}function file_write_unlock($q, $Bb)
{
    rewind($q);
    fwrite($q, $Bb);
    ftruncate($q, strlen($Bb));
    file_unlock($q);
}function file_unlock($q)
{
    flock($q, LOCK_UN);
    fclose($q);
}function first(array $ta)
{
    return reset($ta);
}function password_file($h)
{
    $o = get_temp_dir().'/adminer.key';
    if (! $h && ! file_exists($o)) {
        return '';
    }$q = file_open_lock($o);
    if (! $q) {
        return '';
    }$L = stream_get_contents($q);
    if (! $L) {
        $L = rand_string();
        file_write_unlock($q, $L);
    } else {
        file_unlock($q);
    }

    return $L;
}function rand_string()
{
    return md5(uniqid(strval(mt_rand()), true));
}function select_value($X, $A, array $m, $Uh)
{
    if (is_array($X)) {
        $L = '';
        foreach ($X as $de => $W) {
            $L
                .= '<tr>'.($X != array_values($X) ? '<th>'.h($de) : '').'<td>'.select_value($W, $A, $m, $Uh);
        }

        return "<table>$L</table>";
    }if (! $A) {
        $A = adminer()->selectLink($X, $m);
    }if ($A === null) {
        if (is_mail($X)) {
            $A = "mailto:$X";
        }if (is_url($X)) {
            $A = $X;
        }
    }$L = adminer()->editVal($X, $m);
    if ($L !== null) {
        if (! is_utf8($L)) {
            $L = "\0";
        } elseif ($Uh != '' && is_shortable($m)) {
            $L = shorten_utf8($L, max(0, +$Uh));
        } else {
            $L = h($L);
        }
    }

    return adminer()->selectVal($L, $A, $m, $X);
}function is_blob(array $m)
{
    return preg_match('~blob|bytea|raw|file~', $m['type']) && ! in_array($m['type'], idx(driver()->structuredTypes(), lang(6), []));
}function is_mail($hc)
{
    $va = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
    $Wb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    $Zf = "$va+(\\.$va+)*@($Wb?\\.)+$Wb";

    return is_string($hc) && preg_match("(^$Zf(,\\s*$Zf)*\$)i", $hc);
}function is_url($zh)
{
    $Wb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';

    return preg_match("~^(https?)://($Wb?\\.)+$Wb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $zh);
}function is_shortable(array $m)
{
    return preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea|hstore~', $m['type']);
}function host_port($P)
{
    return preg_match('~^(\[(.+)]|([^:]+)):([^:]+)$~', $P, $C) ? [$C[2].$C[3], $C[4]] : [$P, ''];
}function count_rows($R, array $Z, $Xd, array $s)
{
    $J = ' FROM '.table($R).($Z ? ' WHERE '.implode(' AND ', $Z) : '');

    return $Xd && (JUSH == 'sql' || count($s) == 1) ? 'SELECT COUNT(DISTINCT '.implode(', ', $s).")$J" : 'SELECT COUNT(*)'.($Xd ? " FROM (SELECT 1$J GROUP BY ".implode(', ', $s).') x' : $J);
}function slow_query($J)
{
    $j = adminer()->database();
    $Wh = adminer()->queryTimeout();
    $mh = driver()->slowQuery($J, $Wh);
    $g = null;
    if (! $mh && support('kill')) {
        $g = connect();
        if ($g && ($j == '' || $g->select_db($j))) {
            $fe = get_val(connection_id(), 0, $g);
            echo script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$fe&token=".get_token()."'); }, 1000 * $Wh);");
        }
    }ob_flush();
    flush();
    $L = @get_key_vals(($mh ?: $J), $g, false);
    if ($g) {
        echo script('clearTimeout(timeout);');
        ob_flush();
        flush();
    }

    return $L;
}function get_token()
{
    $yg = rand(1, 1e6);

    return ($yg ^ $_SESSION['token']).":$yg";
}function verify_token()
{
    [$ei, $yg] = explode(':', $_POST['token']);

    return ($yg ^ $_SESSION['token']) == $ei;
}function lzw_decompress($Ga)
{
    $Sb = 256;
    $Ha = 8;
    $ab = [];
    $Jg = 0;
    $Kg = 0;
    for ($t = 0; $t < strlen($Ga); $t++) {
        $Jg = ($Jg << 8) + ord($Ga[$t]);
        $Kg += 8;
        if ($Kg >= $Ha) {
            $Kg -= $Ha;
            $ab[] = $Jg >> $Kg;
            $Jg &= (1 << $Kg) - 1;
            $Sb++;
            if ($Sb >> $Ha) {
                $Ha++;
            }
        }
    }$Rb = range("\0", "\xFF");
    $L = '';
    $Ui = '';
    foreach ($ab as $t => $Za) {
        $gc = $Rb[$Za];
        if (! isset($gc)) {
            $gc = $Ui.$Ui[0];
        }$L
            .= $gc;
        if ($t) {
            $Rb[] = $Ui.$gc[0];
        }$Ui = $gc;
    }

    return $L;
}function script($oh, $hi = "\n")
{
    return '<script'.nonce().">$oh</script>$hi";
}function script_src($Bi, $Kb = false)
{
    return "<script src='".h($Bi)."'".nonce().($Kb ? ' defer' : '')."></script>\n";
}function nonce()
{
    return ' nonce="'.get_nonce().'"';
}function input_hidden($E, $Y = '')
{
    return "<input type='hidden' name='".h($E)."' value='".h($Y)."'>\n";
}function input_token()
{
    return input_hidden('token', get_token());
}function target_blank()
{
    return ' target="_blank" rel="noreferrer noopener"';
}function h($zh)
{
    return str_replace("\0", '&#0;', htmlspecialchars($zh, ENT_QUOTES, 'utf-8'));
}function nl_br($zh)
{
    return str_replace("\n", '<br>', $zh);
}function checkbox($E, $Y, $Ua, $he = '', $sf = '', $Ya = '', $je = '')
{
    $L = "<input type='checkbox' name='$E' value='".h($Y)."'".($Ua ? ' checked' : '').($je ? " aria-labelledby='$je'" : '').'>'.($sf ? script("qsl('input').onclick = function () { $sf };", '') : '');

    return $he != '' || $Ya ? '<label'.($Ya ? " class='$Ya'" : '').">$L".h($he).'</label>' : $L;
}function optionlist($wf, $Zg = null, $Fi = false)
{
    $L = '';
    foreach ($wf as $de => $W) {
        $xf = [$de => $W];
        if (is_array($W)) {
            $L
                .= '<optgroup label="'.h($de).'">';
            $xf = $W;
        }foreach ($xf as $z => $X) {
            $L
                .= '<option'.($Fi || is_string($z) ? ' value="'.h($z).'"' : '').($Zg !== null && ($Fi || is_string($z) ? (string) $z : $X) === $Zg ? ' selected' : '').'>'.h($X);
        }if (is_array($W)) {
            $L
                .= '</optgroup>';
        }
    }

    return $L;
}function html_select($E, array $wf, $Y = '', $rf = '', $je = '')
{
    static $he = 0;
    $ie = '';
    if (! $je && substr($wf[''], 0, 1) == '(') {
        $he++;
        $je = "label-$he";
        $ie = "<option value='' id='$je'>".h($wf['']);
        unset($wf['']);
    }

    return "<select name='".h($E)."'".($je ? " aria-labelledby='$je'" : '').'>'.$ie.optionlist($wf, $Y).'</select>'.($rf ? script("qsl('select').onchange = function () { $rf };", '') : '');
}function html_radios($E, array $wf, $Y = '', $dh = '')
{
    $L = '';
    foreach ($wf as $z => $X) {
        $L
            .= "<label><input type='radio' name='".h($E)."' value='".h($z)."'".($z == $Y ? ' checked' : '').'>'.h($X)."</label>$dh";
    }

    return $L;
}function confirm($D = '', $ah = "qsl('input')")
{
    return script("$ah.onclick = () => confirm('".($D ? js_escape($D) : lang(7))."');", '');
}function print_fieldset($u, $qe, $Oi = false)
{
    echo '<fieldset><legend>',"<a href='#fieldset-$u'>$qe</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$u');", ''),'</legend>',"<div id='fieldset-$u'".($Oi ? '' : " class='hidden'").">\n";
}function bold($Ja, $Ya = '')
{
    return $Ja ? " class='active $Ya'" : ($Ya ? " class='$Ya'" : '');
}function js_escape($zh)
{
    return addcslashes($zh, "\r\n'\\/");
}function pagination($G, $zb)
{
    return ' '.($G == $zb ? $G + 1 : '<a href="'.h(remove_from_uri('page').($G ? "&page=$G".($_GET['next'] ? '&next='.urlencode($_GET['next']) : '') : '')).'">'.($G + 1).'</a>');
}function hidden_fields(array $sg, array $Bd = [], $lg = '')
{
    $L = false;
    foreach ($sg as $z => $X) {
        if (! in_array($z, $Bd)) {
            if (is_array($X)) {
                hidden_fields($X, [], $z);
            } else {
                $L = true;
                echo input_hidden(($lg ? $lg."[$z]" : $z), $X);
            }
        }
    }

    return $L;
}function hidden_fields_get()
{
    echo (sid() ? input_hidden(session_name(), session_id()) : ''),(SERVER !== null ? input_hidden(DRIVER, SERVER) : ''),input_hidden('username', $_GET['username']);
}function file_input($Pd)
{
    $Ce = 'max_file_uploads';
    $De = ini_get($Ce);
    $zi = 'upload_max_filesize';
    $_i = ini_get($zi);

    return ini_bool('file_uploads') ? $Pd.script("qsl('input[type=\"file\"]').onchange = partialArg(fileChange, "."$De, '".lang(8, "$Ce = $De")."', ".ini_bytes('upload_max_filesize').", '".lang(8, "$zi = $_i")."')") : lang(9);
}function enum_input($U, $wa, array $m, $Y, $kc = '')
{
    preg_match_all("~'((?:[^']|'')*)'~", $m['length'], $Ae);
    $lg = ($m['type'] == 'enum' ? 'val-' : '');
    $Ua = (is_array($Y) ? in_array('null', $Y) : $Y === null);
    $L = ($m['null'] && $lg ? "<label><input type='$U'$wa value='null'".($Ua ? ' checked' : '')."><i>$kc</i></label>" : '');
    foreach ($Ae[1] as $X) {
        $X = stripcslashes(str_replace("''", "'", $X));
        $Ua = (is_array($Y) ? in_array($lg.$X, $Y) : $Y === $X);
        $L
            .= " <label><input type='$U'$wa value='".h($lg.$X)."'".($Ua ? ' checked' : '').'>'.h(adminer()->editVal($X, $m)).'</label>';
    }

    return $L;
}function input(array $m, $Y, $r, $_a = false)
{
    $E = h(bracket_escape($m['field']));
    echo "<td class='function'>";
    if (is_array($Y) && ! $r) {
        $Y = json_encode($Y, 128 | 64 | 256);
        $r = 'json';
    }$Ig = (JUSH == 'mssql' && $m['auto_increment']);
    if ($Ig && ! $_POST['save']) {
        $r = null;
    }$bd = (isset($_GET['select']) || $Ig ? ['orig' => lang(10)] : []) + adminer()->editFunctions($m);
    $qc = driver()->enumLength($m);
    if ($qc) {
        $m['type'] = 'enum';
        $m['length'] = $qc;
    }$Tb = stripos($m['default'], 'GENERATED ALWAYS AS ') === 0 ? " disabled=''" : '';
    $wa = " name='fields[$E]".($m['type'] == 'enum' || $m['type'] == 'set' ? '[]' : '')."'$Tb".($_a ? ' autofocus' : '');
    echo driver()->unconvertFunction($m).' ';
    $R = $_GET['edit'] ?: $_GET['select'];
    if ($m['type'] == 'enum') {
        echo h($bd['']).'<td>'.adminer()->editInput($R, $m, $wa, $Y);
    } else {
        $nd = (in_array($r, $bd) || isset($bd[$r]));
        echo (count($bd) > 1 ? "<select name='function[$E]'$Tb>".optionlist($bd, $r === null || $nd ? $r : '').'</select>'.on_help("event.target.value.replace(/^SQL\$/, '')", 1).script("qsl('select').onchange = functionChange;", '') : h(reset($bd))).'<td>';
        $Pd = adminer()->editInput($R, $m, $wa, $Y);
        if ($Pd != '') {
            echo $Pd;
        } elseif (preg_match('~bool~', $m['type'])) {
            echo "<input type='hidden'$wa value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i', $Y) ? " checked='checked'" : '')."$wa value='1'>";
        } elseif ($m['type'] == 'set') {
            echo enum_input('checkbox', $wa, $m, (is_string($Y) ? explode(',', $Y) : $Y));
        } elseif (is_blob($m) && ini_bool('file_uploads')) {
            echo "<input type='file' name='fields-$E'>";
        } elseif ($r == 'json' || preg_match('~^jsonb?$~', $m['type'])) {
            echo "<textarea$wa cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';
        } elseif (($Th = preg_match('~text|lob|memo~i', $m['type'])) || preg_match("~\n~", $Y)) {
            if ($Th && JUSH != 'sqlite') {
                $wa
                    .= " cols='50' rows='12'";
            } else {
                $N = min(12, substr_count($Y, "\n") + 1);
                $wa
                    .= " cols='30' rows='$N'";
            }echo "<textarea$wa>".h($Y).'</textarea>';
        } else {
            $qi = driver()->types();
            $Je = (! preg_match('~int~', $m['type']) && preg_match('~^(\d+)(,(\d+))?$~', $m['length'], $C) ? ((preg_match('~binary~', $m['type']) ? 2 : 1) * $C[1] + ($C[3] ? 1 : 0) + ($C[2] && ! $m['unsigned'] ? 1 : 0)) : ($qi[$m['type']] ? $qi[$m['type']] + ($m['unsigned'] ? 0 : 1) : 0));
            if (JUSH == 'sql' && min_version(5.6) && preg_match('~time~', $m['type'])) {
                $Je += 7;
            }echo '<input'.((! $nd || $r === '') && preg_match('~(?<!o)int(?!er)~', $m['type']) && ! preg_match('~\[\]~', $m['full_type']) ? " type='number'" : '')." value='".h($Y)."'".($Je ? " data-maxlength='$Je'" : '').(preg_match('~char|binary~', $m['type']) && $Je > 20 ? " size='".($Je > 99 ? 60 : 40)."'" : '')."$wa>";
        }echo adminer()->editHint($R, $m, $Y);
        $Oc = 0;
        foreach ($bd as $z => $X) {
            if ($z === '' || ! $X) {
                break;
            }$Oc++;
        }if ($Oc && count($bd) > 1) {
            echo script("qsl('td').oninput = partial(skipOriginal, $Oc);");
        }
    }
}function process_input(array $m)
{
    if (stripos($m['default'], 'GENERATED ALWAYS AS ') === 0) {
        return;
    }$v = bracket_escape($m['field']);
    $r = idx($_POST['function'], $v);
    $Y = idx($_POST['fields'], $v);
    if ($m['type'] == 'enum' || driver()->enumLength($m)) {
        $Y = $Y[0];
        if ($Y == 'orig') {
            return false;
        }if ($Y == 'null') {
            return 'NULL';
        }$Y = substr($Y, 4);
    }if ($m['auto_increment'] && $Y == '') {
        return null;
    }if ($r == 'orig') {
        return preg_match('~^CURRENT_TIMESTAMP~i', $m['on_update']) ? idf_escape($m['field']) : false;
    }if ($r == 'NULL') {
        return 'NULL';
    }if ($m['type'] == 'set') {
        $Y = implode(',', (array) $Y);
    }if ($r == 'json') {
        $r = '';
        $Y = json_decode($Y, true);
        if (! is_array($Y)) {
            return false;
        }

        return $Y;
    }if (is_blob($m) && ini_bool('file_uploads')) {
        $Mc = get_file("fields-$v");
        if (! is_string($Mc)) {
            return false;
        }

        return driver()->quoteBinary($Mc);
    }

    return adminer()->processInput($m, $Y, $r);
}function search_tables()
{
    $_GET['where'][0]['val'] = $_POST['query'];
    $ch = "<ul>\n";
    foreach (table_status('', true) as $R => $S) {
        $E = adminer()->tableName($S);
        if (isset($S['Engine']) && $E != '' && (! $_POST['tables'] || in_array($R, $_POST['tables']))) {
            $K = connection()->query('SELECT'.limit('1 FROM '.table($R), ' WHERE '.implode(' AND ', adminer()->selectSearchProcess(fields($R), [])), 1));
            if (! $K || $K->fetch_row()) {
                $og = "<a href='".h(ME.'select='.urlencode($R).'&where[0][op]='.urlencode($_GET['where'][0]['op']).'&where[0][val]='.urlencode($_GET['where'][0]['val']))."'>$E</a>";
                echo "$ch<li>".($K ? $og : "<p class='error'>$og: ".error())."\n";
                $ch = '';
            }
        }
    }echo ($ch ? "<p class='message'>".lang(11) : '</ul>')."\n";
}function on_help($fb, $kh = 0)
{
    return script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $fb, $kh) }, onmouseout: helpMouseout});", '');
}function edit_form($R, array $n, $M, $yi, $l = '')
{
    $Hh = adminer()->tableName(table_status1($R, true));
    page_header(($yi ? lang(12) : lang(13)), $l, ['select' => [$R, $Hh]], $Hh);
    adminer()->editRowPrint($R, $n, $M, $yi);
    if ($M === false) {
        echo "<p class='error'>".lang(14)."\n";

        return;
    }echo "<form action='' method='post' enctype='multipart/form-data' id='form'>\n";
    if (! $n) {
        echo "<p class='error'>".lang(15)."\n";
    } else {
        echo "<table class='layout'>".script("qsl('table').onkeydown = editingKeydown;");
        $_a = ! $_POST;
        foreach ($n as $E => $m) {
            echo '<tr><th>'.adminer()->fieldName($m);
            $k = idx($_GET['set'], bracket_escape($E));
            if ($k === null) {
                $k = $m['default'];
                if ($m['type'] == 'bit' && preg_match("~^b'([01]*)'\$~", $k, $Gg)) {
                    $k = $Gg[1];
                }if (JUSH == 'sql' && preg_match('~binary~', $m['type'])) {
                    $k = bin2hex($k);
                }
            }$Y = ($M !== null ? ($M[$E] != '' && JUSH == 'sql' && preg_match('~enum|set~', $m['type']) && is_array($M[$E]) ? implode(',', $M[$E]) : (is_bool($M[$E]) ? +$M[$E] : $M[$E])) : (! $yi && $m['auto_increment'] ? '' : (isset($_GET['select']) ? false : $k)));
            if (! $_POST['save'] && is_string($Y)) {
                $Y = adminer()->editVal($Y, $m);
            }$r = ($_POST['save'] ? idx($_POST['function'], $E, '') : ($yi && preg_match('~^CURRENT_TIMESTAMP~i', $m['on_update']) ? 'now' : ($Y === false ? null : ($Y !== null ? '' : 'NULL'))));
            if (! $_POST && ! $yi && $Y == $m['default'] && preg_match('~^[\w.]+\(~', $Y)) {
                $r = 'SQL';
            }if (preg_match('~time~', $m['type']) && preg_match('~^CURRENT_TIMESTAMP~i', $Y)) {
                $Y = '';
                $r = 'now';
            }if ($m['type'] == 'uuid' && $Y == 'uuid()') {
                $Y = '';
                $r = 'uuid';
            }if ($_a !== false) {
                $_a = ($m['auto_increment'] || $r == 'now' || $r == 'uuid' ? null : true);
            }input($m, $Y, $r, $_a);
            if ($_a) {
                $_a = false;
            }echo "\n";
        }if (! support('table') && ! fields($R)) {
            echo '<tr>'."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select('field_funs[]', adminer()->editFunctions(['null' => isset($_GET['select'])]))."<td><input name='field_vals[]'>"."\n";
        }echo "</table>\n";
    }echo "<p>\n";
    if ($n) {
        echo "<input type='submit' value='".lang(16)."'>\n";
        if (! isset($_GET['select'])) {
            echo "<input type='submit' name='insert' value='".($yi ? lang(17) : lang(18))."' title='Ctrl+Shift+Enter'>\n",($yi ? script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".lang(19)."â¦', this); };") : '');
        }
    }echo $yi ? "<input type='submit' name='delete' value='".lang(20)."'>".confirm()."\n" : '';
    if (isset($_GET['select'])) {
        hidden_fields(['check' => (array) $_POST['check'], 'clone' => $_POST['clone'], 'all' => $_POST['all']]);
    }echo input_hidden('referer', (isset($_POST['referer']) ? $_POST['referer'] : $_SERVER['HTTP_REFERER'])),input_hidden('save', 1),input_token(),"</form>\n";
}function shorten_utf8($zh, $re = 80, $Ch = '')
{
    if (! preg_match('(^('.repeat_pattern("[\t\r\n -\x{10FFFF}]", $re).')($)?)u', $zh, $C)) {
        preg_match('(^('.repeat_pattern("[\t\r\n -~]", $re).')($)?)', $zh, $C);
    }

    return h($C[1]).$Ch.(isset($C[2]) ? '' : '<i>â¦</i>');
}function icon($yd, $E, $xd, $Yh)
{
    return "<button type='submit' name='$E' title='".h($Yh)."' class='icon icon-$yd'><span>$xd</span></button>";
}if (isset($_GET['file'])) {
    if (substr(VERSION, -4) != '-dev') {
        if ($_SERVER['HTTP_IF_MODIFIED_SINCE']) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }header('Expires: '.gmdate('D, d M Y H:i:s', time() + 365 * 24 * 60 * 60).' GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: immutable');
    }@ini_set('zlib.output_compression', '1');
    if ($_GET['file'] == 'default.css') {
        header('Content-Type: text/css; charset=utf-8');
        echo lzw_decompress("h:M±h´ÄgÌÐ±ÜÍ\"PÑiÒmcQCa¤é	2Ã³éÞd<Ìfóa¼ä:;NBqR;1Lf³9ÈÞu7&)¤l;3ÍÑñÈÀJ/CQXÊr2MÆaäi0)°ìe:LuÃhæ-9ÕÍ23lÈÎi7³màZw4Ñ<-ÒÌ´¹!U,FÃ©vt2S,¬äa´ÒFêVXúaNqã)-ÖÎÇhê:n5û9ÈY¨;jµ-Þ÷_9krùÙ;.ÐtTqËo¦0³­Öò®{íóyùý\rçHnìGS Zh²;¼i^ÀuxøWÎC@Äö¤©kÒ=¡Ðb©Ëâì¼/AØà0¤+Â(ÚÁ°lÂÉÂ\\ê Ãxè:\rèÀb8\0æ0!\0FÆ\nBÍã(Ò3 \r\\ºÛêÈa¼'Iâ|ê(i\n\r©¸ú4Oüg@4ÁCî¼º@@!ÄQB°Ý	Â°¸c¤ÊÂ¯Äq,\r1EhèÈ&2PZ¦ðiGûH9G\"v§ê¢££¤4rÆñÍDÐR¤\npJë-A|/.¯cêDu·£¤ö:,Ê=°¢RÅ]U5¥mVÁkÍLLQ@-\\ª¦Ë@9Áã%ÚSrÁÎñMPDãÂIa\r(YY\\ã@XõpÃê:£p÷lLC Åñè¸ÍÊO,\rÆ2]7?m06ä»pÜTÑÍaÒ¥C;_ËÑyÈ´d>¨²bnð«n¼Ü£3÷X¾ö8\rí[Ë-)Ûi>V[Yãy&L3¯#ÌX|Õ	X \\Ã¹`ËC§çå#ÑÙHÉÌ2Ê2.# öZ`Â<¾ãs®·¹ªÃ£º\0uhÖ¾¥M²Í_\niZeO/CÓ_`3Ýòð1>=Ðk3£R/;ä/dÛÜ\0úãÞÚµmùúò¾¤7/«ÖAÎXÂÿ°Ãq.½sáL£ý :\$ÉF¢¸ª¾£w8óß¾~«HÔj­\"¨¼¹Ô³7gSõä±âFLéÎ¯çQò_¤O'WØö]c=ý5¾1X~7;iþ´\rí*\n¨JS1Z¦ø£ØÆßÍcåtüAÔVí86fÐdÃy;Y]©õzIÀp¡Ñû§ðc3®YË]}Â@¡\$.+1¶'>ZÃcpdàéÒGLæá#kô8PzYÒAuÏvÝ]s9ÑØ_AqÎÁ:ÆÅ\nKhB¼;­ÖXbAHq,âCIÉ`çj¹S[Ë¶1ÆVÓrñÔ;¶pÞBÃÛ)#é;4ÌHñÒ/*Õ<Â3L Á;lfª\n¶s\$K`Ð}ÆôÕ£¾7jx`d%j] ¸4Y¤HbY ØJ`¤GG .ÅÜKòfÊI©)2ÂMfÖ¸ÝXRC¸Ì±V,©ÛÑ~g\0èàg6Ý:õ[jí1H½:AlIq©u3\"êæq¤æ|8<9s'ãQ]JÊ|Ð\0Â`p ³î«jfOÆbÐÉú¬¨q¬¢\$é©²Ã1J¹>RH(Çq\n#rà@e(yóVJµ0¡QÒ£ò6Pæ[C:·Gä¼ Ý4©Ò^ÓðÃPZµ\\´è(\nÖ)~¦´°9R%×Sj·{7ä0Þ_Çs	z|8ÅHê	\"@Ü#9DVLÅ\$H5ÔWJ@z®a¿J Ä^	)®2\nQvÀÔ]ëÇÄÁj (A¸Ó°BB05´6bË°][èkªAwvkgôÆ´öºÕ+k[jmzc¶}èMyDZií\$5e«Ê·°º	A CY%.Wb*ë®¼.­Ùóq/%}BÌX­çZV337Ê»aºòÞwW[áLQÊÞ²ü_È2`Ç1IÑi,÷æ£Mf&(s-äëÂAÄ°Ø*DwØÄTNÀÉ»ÅjX\$éxª+;ÐðËFÚ93µJkÂS;·§ÁqR{>l;B1AÈIâb) (6±­r÷\rÝ\rÚÚìZR^SOy/ÞM#ÆÏ9{kàê¸v\"úKCâJ¨rEo\0øÌ\\,Ñ|faÍ³hI©/oÌ4Äk^pî1HÈ^ÍphÇ¡VÁvox@ø`íg&(ù­ü;~ÇzÌ6×8¯*°ÆÜ5®Ü±E ÁÂpéâîÓ¤´3öÅgrDÑLó)4g{»ä½å³©L&ú>è»¢ØÚZì7¡\0ú°Ì@×ÐÓÛffÅRVhÖ²çIÛ½âðrÓw) =x^,k2ôÒÝjàbël0uë\"¬fp¨¸1ñRI¿z[]¤wpN6dIªzëõån.7X{;ÁÈ3ØË-I	âûü7pjÃ¢R#ª,ù_-ÐüÂ[ó>3À\\æêÛWqÞqJÖuh£ÐFbLÁKÔåçyVÄ¾©¦ÃÞÑ®µªüVîÃf{K}S ÊÞMþ·Í¼¦.M¶\\ªix¸bÁ¡1+£Î±?<Å3ê~HýÓ\$÷\\Ð2Û\$î eØ6tÔOÌã\$s¼¼©xÄþxó§CánSkVÄÉ=z6½¡Ê'Ã¦äNa¢Ö¸hÜü¸º±ý¯R¤å£8g¢äÊw:_³î­íÿêÒIRKÃ¨.½nkVU+dwj§%³`#,{é³ËðÊYý×õ(oÕ¾Éð.¨c0gâDXOk7®èKäÎlÒÍhx;ÏØ ÝLû´\$09*9 ÜhNrüMÕ.>\0ØrP9ï\$Èg	\0\$\\Fó*²d'ÎõLå:búð42Àô¢ð9Àð@ÂHnbì-¤óE #ÄÉÃ êrPYê¨ tÍ Ø\nð5.©àÊâî\$op lX\n@`\r	à\rÐ Î ¦  	 ÊàêðÚ Î	@Ú@Ú\n  	\0j@Q@1\rÀ@ ¢	\$p	 V\0ò``\n\0¨\n Ð\n@¨' ìÀ¤\n\0`\rÀÚ ¬	à\rà¤ ´\0Ðr°æÀò	\0`	àî {	,\"¨È^P0¥\n¬4±\n0·¤.0ÃpËðÓ\rpÛ\rðãpëðópûñqñQ0ß%ÑÑ1Q8\n Ô\0ôkÊÈ¼\0^àÒ\0`àÚ@´àÈ>\nÑo1w±,Y	h*=¡P¦:ÑVïÐ¸.q£ÅÍ\rÕ\rpéÐñ1ÁÑQ	ÑÑ1× `Ññ/17±ëñò\r ^Àä\"y`\nÀ # \0ê	 p\nò\n` r Qð¦bç1Ò3\n°¯#°µ#ð¼1¥\$q«\$Ñ±%0å%q½%Ðù&Ç&qÍ &ñ'1Ú\rR}16	 ï@b\r`µ`Ü\rÀ	ÞÀÌdàª¨	j\n¯``À\n`dcÑP,ò1R×\$¿rIÒO 	Q	òY32b1É&Ï01ÓÑÙ Ó fÀÏ\0ª\0¤ Îf\0j\n f`â	 ®\n`´@\$n=`\0ÈÒv nIÐ\$ÿP(Âd'ËðôÄà·gÉ6--ÒC7Rçà 	4à ô-1Ë&±Ñ2t\rô\"\n 	H*@	`\n ¤ è	àòlÕ2¿,z\rì~È è\rFìthöØ ëmõäÄì´z~¡\0]GÌF\\¥×I\\¥£}ItC\nÁT}ªØ×IEJ\rx×ÉûÂ>ÙMpIHô~êäfhtë¯.bxYEìiK´ªoj\nðíÅLÀÞtr×.À~d»H2U4©Gà\\Aêç4þuPtÞÃÕ½è° òàÍL/¿P×	\"G!RîÎMtO-Ìµ<#õAPuIëRè\$c¹ÃDÆ §¢-ÃGâ´O`Pv§^W@tH;Q°µRÄÕ\$´©gKèF<\rR*\$4®' ó¨ÐÈÊ[í°ÛIªó­UmÑÆh:+þ¼5@/­l¾I¾ªí2¦^\0ODøª¬Ø\rR'Â\rèTÐ­[êÖ÷ÄÄª®«MCëMÃZ4æE B\"æ`ö´euNí,ä¬é]Ïðtú\rª`Ü@hö*\r¶.V%Ú!MBlPFÏ\"Øï&Õ/@îv\\CÞï©:mMgnò®öÊi8I2\rpívjí©Æ÷ï+Z mT©ueõÕfv>f´ÐÖ`DU[ZTÏVÐCàµTð\r¹Uvkõ^×¦øLëÙb/¾K¶Sev2÷ubvÇOVDðÖImÕ\$ò%ÖX?udç!W|,\rø+îµcnUe×ZÆÄÊþöë-~X¯ºûîÀêÔöBGd¶\$i¶çMv!t#Lì3o·UIOu?ZweRÏ ëcwª. `È¡iøñ\rb§%©bâ¦H®\"\"\"hí _\$b@ázªä\0f\"érW¨®*æB|\$\$¬BÖ× \"@r¯(\r`Ê îC÷¸Ç(0&.`ÒNk9B\n&#(Äêâ@ä¯Ú«dü^÷º®ü £@²`ÒI-{0£â\nB{4sG{§ø;z®©b÷{ Ñ{b×¯){BàÁxKÂÀÅ5=cÚª«yåî&ìJ£PrÅI/Ü \0ÚâV\r¥×íÈ=¸£N\\Ø¦=ÃKè}XVíx¹µØ¥Ëx²©døÕÛ*H'¦Î´¸»{XÆ=ØÊ=\0ï8¼\0¾¹å[É«JÚtÙùOØe¹ØÉèÞ\røý ÊDXý§ÅÄý}×z°¾ ù)y'Ù'ÃÑÙIÌ(ù[l(5`f\\Á`¿ùe.lY(¹=z×!Y%h¾O¹+ù`Ù\"e æçÄºKòù¥þ¿¯£¸ÿ ßÙ#S¹EIYû.HÖJtG·`¾H¼J5»Í5~ ¸6C¥hø§ùXDz\nx¡yshFK¡c¡zj¢ZY8(¹þ%Ù|yI«£ßØÚée¡úY¡X»¡u¢Ú ´Úi]¦Úc¡ÚM¥ú;È§ùò>Ç¡Q T©øüú¨ [~Wé~ÙcÝz©úµz¥º½¢ú\r¬:  \0èrYû¢x)Ê!ªúÉ¡¹K¦ú+§z!£ÓC+°´Ù®âÃ¯:Ý§ª¤ú©¢Zgû~z4f¥¯	¥:÷£sºÓªê+õxÊÂ%»=³GÛIf3?úãø¿µ+Y´úq¶@àûGúáy¶»oµÙÑ´Ûp\rª~Á{W¶[·¹é®yè:\0Æ\\»·;e¹Û¡¶YI\"·¸zdÂk©Zö|[uuÏ+×¹9q¼¹nR Ë®¥B»Ø×z|\rá¤ýk¤^»îª[1ªÛ%.pA­2<Û=¼Ø¡è\$é;Ö5)³m¸!»ÑXXýºYÃx¨5vT\\®QÀ%:À¢>ÀàÉÛ;¸e|/·yÁÅ§ÅW§x× |g®ÓÄCÝÆ\\ü¼<¼9z\\®#ð.FV;8¡èNÍX7ø×ÊÎ\"8&d5¬P4Gj?Ê\0Ü?\"=­ùHER");
    } elseif ($_GET['file'] == 'dark.css') {
        header('Content-Type: text/css; charset=utf-8');
        echo lzw_decompress("h:M±h´ÄgÆÈh0ÁLÐàd91¢S!¤Û	F!°æ\"-6NÄbdGgÓ°Â:;Nr£)öc7\rç(HØb81s9¼¤Ük\rçc)Êm8OVA¡Âc1c34Of*ª- P¨1©r41Ùî6Ìd2Ö®Ûo½ÜÌ#3BÇf#	Ög9Î¦êØfc\rÇIÐÂb6EC&¬Ð,buÄêm7aVãÂÁs²#m!ôèhµårùÞv\\3\rL:SAÂdk5ÝnÇ·×ìýÊaF¸3éÒe6fS¦ëy¾óør!ÇLú -ÎK,Ì3Lâ@ºJ¶Ë²¢*J äìµ£¤»	¸ð¹Áb©cèà9­ê9¹¤æ@ÏÔè¿ÃHÜ8£ \\·Ãê6>«`ðÅ¸Þ;Aà<T'¨p&q´qEê4Å\rl­ÃhÂ<5#pÏÈR Ñ#IÝ%êfBIØÞÜ²¨>Ê«29<«åCîj2¯î»¦¶7j¬8jÒìc(nÔÄç?(a\0Å@5*3:Î´æ6£æ0ã-àAÀlLPÆ4@ÊÉ°ê\$¡H¥4 n31¶æ1Ítò0®áÍ9éWO!¨r¼ÚÔØÜÛÕèHÈ£Ã9Q°Â96èF±¬«<ø7°\r-xC\n Üã®@ÒøÜÔ:\$iÜØ¶m«ªË4íKid¬²{\n6\rxhËâ#^'4Vø@aÍÇ<´#h0¦Sæ-c¸Ö9+p«a2Ôcyh®BO\$Áç9öwiXÉùVY9*r÷Htm	@bÖÑ|@ü/l\$z¦­ +Ô%p2lÉ.õØúÕÛìÄ7ï;Ç&{ÀËmX¨C<l9ðí6x9ïmìò¤¯À­7RüÀ0\\ê4Î÷PÈ)AÈoÀxÄÚqÍO#¸¥Èf[;»ª6~PÛ\ra¸ÊTGT0èìu¸Þ¾³Þ\n3ð\\ \\ÊJ©udªCGÀ§©PZ÷>³Áûd8ÖÒ¨èéñ½ïåôC?V·dLðÅL.(ti­>«,ôÖÃR+9iÞC\$äØ#\"ÎAChVb\nÐÊ6ðT2ewá\nf¡À6m	!1'cÁä;Ø*eLRn\rì¾G\$ô2S\$áØ0Àêa'«l6&ø~Ad\$ëJ\$s ¦ÈB4òÉéjª.ÁRCÌQj\"7\nãXs!²6=ÎBÈ}");
    } elseif ($_GET['file'] == 'functions.js') {
        header('Content-Type: text/javascript; charset=utf-8');
        echo lzw_decompress("':Ì¢Ðäi1ã³1ÔÝ	4ÍÀ£ÌQ6a&ó°Ç:OAIìäe:NFáD|Ý!Cyêm2ËÅ\"ãÔÊr<Ì±ÙÊ/C#Ùö:DbqSeJË¦CÜº\n\n¡Ç±S\rZH\$RAÜS+XKvtdÜg:£í6EvXÅ³jÉmÒ©ej×2M§©äúB«Ç&Ê®L§C°3åQ0ÕLÆé-xè\nÓìDÈÂyNaäPn:ç¼äèsÍ( cLÅÜ/õ£(Æ5{ÞôQy4øg-ý¢êi4ÚfÐÎ(ÕëbUýÏk·îo7Ü&ãºÃ¤ô*ACb¾¢Ø`.­Û\rÎÐÜü»ÏÄú¼Í\n ©ChÒ<\r)`èØ¥`æ7¥CÊÈâZùµãXÊ<QÅ1X÷¼@·0dp9EQüf¾°ÓFØ\rä!æ(hô£)Ã\np'#Ä¤£HÌ(i*r¸æ&<#¢æ7KÈÈ~# ÈA:N6ã°Ê©lÕ,§\rôJPÎ3£!@Ò2>Cr¾¡¬h°Ná]¦(a0M3Í2×6ÔUæãE2'!<·Â#3R<ðÛãXÒæÔCHÎ7#nä+±a\$!èÜ2àP0¤.°wd¡r:Yö¨éE²æ!]<¹jâ¥ó@ß\\×pl§_\rÁZ¸Ò¬TÍ©ZÉsò3\"²~9À©³jãPØ)QYbÝDëYc¿`zácµÑ¨ÌÛ'ë#tBOh¢*2ÿ<ÅOêfg-Z£Õ# è8aÐ^ú+r2bø\\á~0©áþ¥ùàW©¸ÁÞnÙp!#`åëZö¸6¶12×Ã@é²kyÈÆ9\rìäB3çpÞî6°è<£!pïG¯9àno6s¿ð#FØ3íÙàbA¨Ê6ñ9¦ýÀZ£#ÂÞ6ûÊ%?s¨È\"ÏÉ|Ø§)þbJc\r»½NÞsÉÛih8Ï¹æÝè:;èúHåÞõuI5û@è1îªAèPaH^\$H×vãÖ@ÃL~¨ùb9'§ø¿±S?PÐ-¯ò0Cð\nRòmÌ4ÞÓÈ:ÀõÜÔ¸ï2òÌ4µh(k\njIÈ6\"EY#¹Wrª\rG8£@tÐáXÔâÌBS\nc0ÉkC I\rÊ°<u`A!ó)ÐÔ2ÖC¢\0=¾ æáäP1Ó¢K!¹!åpÄIsÑ,6âdÃéÉi1+°ÈâÔkê<¸^	á\nÉ20´FÔ_\$ë)f\0 ¤C8E^¬Ä/3W!×)u*äÔè&\$ê2Y\n©]EkñDV¨\$ïJ²xTse!RY» R`=Lò¸ãàÞ«\nl_.!²V!Â\r\nHÐk²\$×`{1	|± °i<jRrPTG|w©4b´\r¡Ç4d¤,§E¡È6©äÏ<Ãh[Nq@Oi×>'Ñ©\r¥ó;¦]#æ}Ð0»ASIJdÑA/QÁ´â¸µÂ@t\r¥UGÄ_G<éÍ<y-IÉzò¤Ð\" PÂàB\0ýíÀÈÁq`ïvAaÌ¡Jå RäÊ®)JB.¦TÜñL¡îy¢÷ Cpp\0(7cYYa¨Mé1em4Óc¢¸r£«S)oñÍàpæC!I¼¾SÂb0mìñ(dEHø¸ß³Xª£/¬P©èøyÆXé85ÈÒ\$+Ö»²gdèöÎÎyÝÜÏ³J×Øë ¢lE¢urÌ,dCX}e¬ìÅ¥õ«m]Ð2 Ì½È(-z¦Zåú;Iöî¼\\) ,\n¤>ò)·¤æ\rVS\njx*w`â´·SFiÌÓd¯¼,»áÐZÂJFM}Ð À\\Z¾PìÝ`¹zØZûE]íd¤ÉOëcmÔ]À ¬Á%þ\"w4¥\n\$øÉzV¢SQDÛ:Ý6«äGwMÔîS0B-sÆê)ã¾Zí¤c|Ë^RïEè8kMïÑÌsd¹ka)h%\"Pà0nn÷/Á#;Ög\rdÈ¸8ÞF<3\$©,åP);<4`Î¢<2\nÊõé@w-®áÍAÏ0¹ºª¹LrîYhìXCàa>ºætºLõì2yto;2ÝQª±tîÊfrmè:§Aíù¡÷ANºÝ\\\"kº5oVëÉ=îÀt7r1ÝpäAv\\+9ªâ{°ç^(if¬=·rÒºuÚÊûtØ]yÓÞÐùCö¶ºÁ³ÒõÝÜgi¥vfÝù+¥Ã|Êì;¸Âà]~ÓÊ|\re÷¥ì¿ÝÚ'íû²¦ä¯²°	½\0+Wcoµw6wd Su¼j¨3@ò0!ã÷\n .wm[8x<²ËcM¬\n9ý²ý'aùÞ1>È£[¶ïµúdïÞux¯à<\"Yc¸ÞB!i¹¥êwÀ}ô5U¹kººÜØ]­¶¸ÔÒÀ{óI×R¥=f W~æ]É(bea®'ubïm>)\$°P÷á-6þR*IGu#ÆUKµAXtÑ(Ó`_Âà\" ¾£p¸ &UËËÙIíÉ]ýÁYG6P]Ar!b¡ *ÐJoµÓ¯åÿóïÁòvý½*À Ø!é~_ªÀÙ4B³_~RBiKùþ`ç&JÛ\0­ô®N\0Ð\$àÌþåCÂK SÐòâjZ¤Ð Ìû0pvMJ bN`Lÿæ­eº/`RO.0Pä82`ê	åüÆ¸d ÂGxÇbP-(@É¸Ó@æ4¨H%<&ÀÌZàÀèp¬°%\0®pÐÐøêã	¯	àÈ/\"ö¢J³¢\ns_ÀÌ\ràg`!käpX	èÐ:Ävíç6p\$ú'ðÇ¥RUeZÿ¨d\$ì\nLáBºâó.Þdnî¤Òtm>vjäí)	Mº\r\0Â.àÊHÑ\"5*!eºZJºèëãf(dc±¼(xÜÑjg\0\\õÂõÀ¶ Z@ºàê|`^r)<(È)ÌëªóÊÐì@YkÂmÌíl3QyÑ@ÉÑfÎìPnç¼¨ÐT ò¯N·mRÕq³íâVmvúNÖ|úÐ¨Z²ÈÚ(Ypø\"4Ç¨æàò&î%lÒP`Ä£Xx bbdÐr0Fr5°<»Cæ²z¨¯6ähe!¤\rdzàØK;Ät³²\nÙÍ HÆQ\$QEnn¢n\rÀ©#T\$°²Ë(ÈÑ©|c¤,¼-ú#èÚ\r ÜáJµ{dÑE\n\$²ÆBriTÔò+Å2PEDBe}&%Rf²¥\nü^ôCàÈZàZ RVÅA,Ñ;«ç<ÂÄì\0O1éÔêc^\r%\r ìë`Òn\0y1èÔ.Âð\r´ÄK1æM3H®\r\"û0\0NkXPr¸¯{3 ì}	\nSÈdÚx.ZñRTñwS;53 .¢s4sO3FºÙ2S~YFpZs¡'Î@ÙOqR4\n­6q6@DhÙ6ÍÕ7vE¢l\"Å^;-å(Â&Ïb*²*ò.! ä\r!#çx'G\"ÍwÁ\"úÕ È2!\"R(vÀXæ|\"DÌvÀ¦)@á,¸zmòAÍwT@ÀÔ  Ð\nÖÓðºÐ«hÐ´IDÔP\$m>æ\r&`>´4ÈÒA#*ë#<w\$T{\$´4@dÓ´Rem6¯-#Dd¾%E¥DT\\ \$)@Ü´WC¬(t®\"MàÜ#@úTF\r,g¦\rP8Ã~´Ö£Jü°c öàÄ¹Æê Ê\"LªZÔä\r+P4ý=¥¤SâTõA)0\"¦CDhÇM\n%FÔpÖÓü|fLNlFtDmH¯ªþ°5å=HÍ\nÄ¼4ü³õ\$à¾Kñ6\rbZà¨\r\"pEQ%¤wJ´ÿV0ÔM%ål\"hPFïA¬áAã®ò/G6 h6]5¥\$fS÷CLiRT?R¨þCñõ£HU§Z¤æYbFþ/æ.êZÜ\"\"^Îy´6RG ²ÌnâúÜ\$ªÑå\\&OÖ(v^ ÏKUºÑ®ÎÒam³(\rïº¯¾ü\$_ªæ%ñ+KTtØö.Ù36\nëcµ:´@6 újPÃAQõF/S®k\"<4AgAÐaU\$'ëÓáfàûQO\"×k~²S;ÅÀ½ó.ïË: k¼9­ü²óe]`nú¼Ò-7¨;îß+VËâ8WÀ©2H¢U®YlBívÞöâ¯ÖÔ´°¶ö	§ýâîp®ÖÉl¾m\0ñ4Bò)¥XÁ\0ÊÂQßqFSq4ÿnFx+pÔò¦EÆSovúGW7o×w×KRW×\r4`|cqîe7,×19·u Ïu÷cqä\"LC tÀhâ)§\ràJÀ\\øW@à	ç|D#S\r%5læ!%++å^k^Ê`/7¸(z*ñð´EÝ{¦S(Wà×-XÄ0V£0Ë¥îÈ=îÍa	~ëfBëË2Q­êÂru mCÂìë£tr(\0Q!K;xNýWÀúÿ§øÈ?b< @Å`ÖX,º`0eºÆN'²Â¤&~øtÓu\"| ¬i ñBå  7¾Rø ¸lSu°8AûdF%(Ôú äúïó?3@A-oQÅº@|~©KÀÊ^@xób~D¦@Ø³¸TNÅZC	WÒÂix<\0P|Äæ\n\0\n`¨¥ ¹\"&?st|Ã¯wî%àèmdêuÀN£^8À[t©9ªB\$àð§©ð¦'\">U~ÿ98 éòÃFÄf °¹uÈ°/)9À\0áëAùz\"FWAx¤\$'©jG´(\"Ù ±s%THîßÀe,	M7ïb¼ ÇØa ËÆ·&wYÔÏ3°Øø /\rÏù¯Ù{\"ùÝp{%4bó`í¤Ôõ~nåE3	Î °9å3XÖdäÕZÅ9ï'@¨l»f¯õØQbP¤*GoåÅ`8¨¯ùAæB|Àz	@¦	àb¡Zn_Íhº'Ñ¢F\$f¬§`öóºHdDdH%4\rsÎAjLRÈ'ÞùfÚ9g IÏØ,R\\·øÊ>\nH[´\"°Àî©ª\rÓÂLÌ,%ëFLl8gzLç<0ko\$Çk­á`ÒÃKPÔvå@dÏ'V:VØMü%±èÕ@ø6Ç<\ràùT«®LE´NÔS#ö.¶[x4¾açÌ­´LL® ª\n@£\0Û«tÙ²å\n^F­º¥º5`Í R7ÈlL uµ(dº¡¹ Ô\räBf/uCf×4ÿcÒ Bïì_´nLÔ\0© \$»îaYÆ¦¶¸~ÀUkïv¥eôË¥¦Ë²\0ZaZXØ£¦|Cq¨/<}Ø³¡ÅÃº²º¶ Zº*­w\nOãÅz`¼5®18¶cøû®¯­®æÚIÀQ2YsÇKæ\n£\\\"­ Ã°cò*õB¶îÌ.éR1<3+õÅµ*ØSé[õ4Ómì­:RhITdevÎIµHäèÒ-Zw\\Æ%nè56\nÌWÓi\$ÕÅow¬+© ºùËrÉ¶&Jq+û}ÒDàø¼Ój«dÅÎ?æU%BBeÇ/M¶Nm=ÏóU·Âb\$HRfªwb|²x dû2æNiSàóØgÉ@îq@ß>ÎSv §|ïkrx½\0{ÔR=FÿÏÎÎâ®Ï#r½8	ðZàvÈ8*Ê³£{2SÝ+;S¦Ó¨Æ+yL\$\"_Ûë©Bç8¬Ý\"E¸%ºàº\nøÐÂp¾p''«pówUÒª\"8Ð±I\\ @ Ê¾ Lnðæ Rß#MäDµþqLNÆî\n\\Ì\$`~@`\0uç~^@àÕl-{5ñ,@bruÁo[Á²¾¨Õ}é/ñy.×é {é6q°RpàÐ\$¸+13ÛúÚú+¨O!D)® à\nu<¯,«áñß=JdÆ+}µd#©0ÉcÓ3U3»EY¹û¢\rû¦tj5Ò¥7»e©w×Ç¡úµ¢^qß¿9Æ<\$}kíÍòRI-ø°¸+'_Ne?SÛRíhd*X4é®üc}¬è\"@vi>;5>Dn \räë)bNéuP@YäG<ñ¨6iõ#PB2A½-í0d0+ðügKûø¿í?¨néãüddøOÀ¯åácüi<ú0\0\\ùëÑgî¦ùæê¡NTi'  ·ô;iômjáÜÅ÷»¸uÎJ+ªV~À²ù 'ol`ù³¿ó\",üÌ£×ÓFÀå	ýâ{C©¸¤þT aÏNEÛQÆp´ p+?ø\nÆ>'l½¤* tÉKÎ¬p°(YC\n-qÌ0å\"*ÉÁ,#üâ÷7º\"%¨+qÄ¸êB±°=åi.@x7:Å%GcYIÐ0*îÃkÀÛ\\·¯ðQ_{¤ ÅÇ#Áý\rç{H³[p¨ >7ÓchënÎÂÔ.µ£¦S|&JòMÇ¾8´ÀmOhþÄí	ÕÑqJ&aÝ¢¨'.bçOpØì\$ö­ÜD@°CHB	È&âÝ¡|\$Ô¬-6°²+Ì+Â Âàpºà¬¡AC\rÉì/Î0´ñÂî¢MÃiZnEÍ¢j*>û!Ò¢u%¤©gØ0£à@ä¿5}rÉ+3%Â-m¢G<ã¥T;0°¯¨DV£dÀgÛ9'lM¶ýH£ F@äPunütFB%´MÄt'äGÔ2ÅÀ@2¢<«e;¢`õ=LXÄ2àÏäX»}oc.L+âxÓ&D¨a¡É«ÁF2\ngLE°.\\xSLýx­;lwÑD=0_QV,a 5+Léó+Û|\$Åi­jZ\nêDÖEÎ,B¾t\\Ï'H0Á±R~(\\\"¢Ö:Ðn*ûÕ(¡×o®1wãÕQí×röÒÃEteÓF\$èSÑ]Ð\rLäyF\\BiÀhhdáÿ&áh;fo¾B-y`ÅÔð0JlPéxao·\$Xq¼,(Ö¡C*	Îë:¤/öé®HG\"ðcC¢¡Q¸\nFÁÔÒ#ð¶8í¢F:Ð£\0Ok¾âDüÆ])ÏtT8Láð¨æn©`ÕÎ±|ªHJ³Ö  \"Ò6ø{­Á?=I<HGc Å¤FÒ@,C ¼@jì\$L·â(nEÊP¢æjb¿nãÎ«¶äWá \rÀLqéèÏÐsPHêz\\V\$kÄÒtr5,¤lÈØè<ñ'\0^S02¸0f -5\"ac¼\"3Up£æ\"Ü©%®\0'Zt\"96Ì9_ @Z{0Iç¬DÀZE@ôÎNÃh`¡\"½` \0µàÐÉ¹(GÃHâÄCh¥ I¼òf`@ZD¹\$)âKá;ZÚø\0ä/éCT>r_R@Oå`1rTÒ¨Ib\0ç*¹8 ÄÇËh\$é_pùRÄ\$®¥Ni^ÊªP/O)¸Â.Å¹T6Ü\\Ù@T¾ÑrÄ`)øöÀT=ân\02e«+9Ê¢\\®@¥äú>ÉPH1	äy#Êô¥rú<°a¸eÜKÛ/cM@_.\09Ë¨ÔÐ¬B®ÔÁÙ0iaó\nðdea´%|S2ô¿å#¸n»D\$/¹+EÎdøÖ_2PË\$s,ok¡#ü<	²AÂÄr{BÙA-Q4Ò¤Ù\nª\ryù!Æbä±«ñáOÚö@É¬Ák¤¼ ê±\"§rà*¤ÝYÒ/ðÈ a0ñÙ%.gE~ºù&© 89áÃ#@M_ Àý7Kä¸J`òX)²B\$¯(	:gn*ù|M6PZªHtêJtqCx[Ú¼äál=\n®ÅU3Êf\\ÌJîP	,:É}TA»SYH(\n¢¸ØI¶Ù²Ä!t(2U\"Ë\\çX­^sÌ	Æa!®\nPr`ÉX3fnb¥©àèJ÷¬Ü&¸zåzQSf £üät¡!T?à9%(QBø}6B°kP\0ó>õg&~fhUðr§,¢ p5HiÆp¢qÉügöVçVüÏOgWEJ8â0GìÔak°Õ@N NMÄä°UÐUxÈª­ßS¦x	Áà	ðK@c 1yê±VlÏ ¦ÂCð2Q^rP6|ýI^Mª,¦j%dÝ`Ü«àüF§Ï\\#%³|ÄC¿­¡7ì¢ÔGÚTNãùi«HÎQ­O¦ÏÁCÌyBÑ\$±%T°*á>z\rMM KpÓ J7OÛ·é4å%ò\$¤pàé4°Í£¯EÒª\"Tõ\0O\0Õ@>	rO¨]¡¢xÒ}^¥IÚÖ@Ê ÅºqnçÝ0©Bb¡ÈµIÉ(¤M/ý;é¦Ê}RN\n¡C£<b­PÔµu?Â=Pe¹CL^'ìSÔÎ?}4)ÓS-ÕÃð1\r5S«OEóSFÓ©AOR+ÓÞ+v§å5Â&C)Ù®KSDBß³N|E\rcÚUôYÊ¾Àê£Väø?H)å®+sFäákºLPW-ø,üU:&ãt{®Vo¤·Jl'¨ðWÈe74Xn GFª'®Þ`æÉCcö±%Ilñju6£ßÈÂvÂU³ðZë\0*¨NÔ#ö¤(¼¨n¥-;|4«]XÇîÁy' °;ÝZÅñ) s9ÈÀ%R+\$À°	¿QÞà(\"¡_kX°¦\nM#¦\"!p~:è*úÀ°\$µ3O¸ÄÆª6½+à\nB{1ðà|H·K<[`3ð#å®F@èÍÇ! |©Ø\0àð>®[nrMMý+á®mO_2¹ÑÈÅ\0«e^	Ì7Z¸&êµBÅJè¤h7QO%rfÆp ÎâÖ¥mØ¨â¾ÃÂ4Eàl«úü+àäV®£iñN SZàWté2WÅ[;ªÀv\"%Å\$^Ö-(I\$ÊÈS@R-&³Tãz¬k(²	ä%R8ìuY\0[9-¢ÈÎ(õ)E¹è8¡=^¹¡ÁG5#Á¼¾)1V¦Éb\r]Ne;&ÌY`r¬êI§ØPÝ±ÜËÁÖ²ª \0Å@Pç7°·â0Hª¨ÃØR­x¾\0000C|än=¨`ÐáTT¿Ø\rEhONÈ´Á' Ò&Ütc©K ÜU5þÖßÂÎÃõP3\\îà2\"\0yó5¢V]¼©6>ÐU!¡@ËhuÌÚ(¼\"E%07B½6¼dáHN±¢µìij';@ÕeËMzlSfjKYÖó­®-uhóH¯smL@éÐ\"r×jÊºéj'l7	ò(uuÑEåÂ·e¥a@ñ+K:ÓÂ%n«z Vñ·Ñ;ä[î_Vz_­Eàãâ8<Sb¨ÜÍÖ6gÀ¼:cÍþÀ7\nµ¨­ì%Q K¡7óÜ®BÛëÚñw¨u¹5©ì0»ÖãÊ¹yÃncnKúæ¦T8åÊ÷s±ºW=+=K\n_[p¢G¿Ä·C5¢ÁÖÃ'ÛD\"ÝM<\":|Mq4¹¹ÎfsÁx	qlÍ°QPÓ²aOY×E=ûõî6nTëBthÄC\0pÿ×@n£ÎD(aÜP°\"ï'ZNäÛ¬¢®\rüLNXg±<!w¶¸Ú[ûB)´§)~½×ãcÂxàviÂ¦ÿqÉø¶a¤@KÕð7s§EQdÃ½ïkô÷Ä?\"Ú3-\"UÆ|½ýíÂï|21D>ß³â]Â­&\\hèTÆ³5\0`Tz¢ás -¼N£¹ÉÙ\"f¸NåLU¹]n(D©(ê&%\"e\\¬OãÉNæInÛ¿¤\0ÒÐìÆ±Ø÷@ÁÑïVä|RMYCÛTßÁûÿbÔUHðp)ÀÈSÕsÀ qÓi±`Z5vtå¸*áOO\nñ(£ÝÖëFà¦Ø58Ã!ax@{^P¾Õ½¸?«°Àeh}\\³j^2òL½,6Á.ØN	K%±ßuipÈÈ!?²l -5íw½K\"VÈØ\\ÃIs¢Ï2!ßð\$4º5v\nàèògrÃòNÖå}÷£;Ýý­ÂúæW%D(pWaë\0¡v'à±6ú®Vê«ÔÆ¿0WÀñE4ÒEUlÂ8ÇLDî¶EÂ<kOñHÉßDUÚ	`vS·¬LÃ!DTMbnWVÁCd)Zeè¸ö:¾2Çd8¦KåÞþ4®-GübÍ¾wQWæ30\rüf\0Ê,µ`Qhl±ÖÙ0ËPõà0h@\\Ôr·8×ÇTðâÂ1ð`¤&ÿÌwXï>ÈF?|P*ñM¤qZÑ¯¬}Ë0k`#ÀÕ«cò'[ÇÖ±Ë|sÉIJî\rÞã¬û¿<OaÆ¼@ÔW¬u°TÆÆ:ÑóE^ª²¾²!kÐÿÎa\$È>5òu_äâKcCQ¿r-Ñä'\rÈiCì§Ù@8ÎSPSÁ_XglÒ%£	Án1r.<w_aÉºÄ³èGhÒ4\næW×ZïaBn,\\\0¬±DU\nbbZ'Òá72ºÍrÛÂ¢®}¿Y>/Àw\\YÐ`^7J«jS¢¯ð¨S.Ào%æJg\0GD,¼Æé>7 ¹Rî¹0á¹¯Æø3¼ß6ø%i\0Sª^L·AÔØ\riòäO<ºÀa phv[¯{¥\0éE«^xóÜ¼gYzWÎyGa»ç:(>C½öÖe\0ãÖÚ])ô3yts_a7ç+áæBúCeT·ÞfoÅPÛ¤Õ2E·C¾ÚvÇ>ÙwölzÛ*pêY²ýö±q°öØQâp\nv[|qõÒ¨E[ÑXió¢ì®=²z(	ÈMÛn]7F\r§©Cs4|-} Ä¿(NU£?,À¥Úý°âØºq	¸âpq~ü¬ÿ ¦ê©FÂ% 88·×é¦¢\$×Þ°[¼±µrÄo!3ãý(°gÆô×¥pJ!éÁ´qÚZ°v?Ñøc­ýÑL£7£Ð6èü\$möÖq§í8l!Ãù5­C;Q,ÔdÞsFõ-O§fÃø\$äð6Í%U¨C¸´f\"çe(jº\rMtÇFèëR÷x;n¦B\$÷¹SSôx'¢õGöþéMÓ	Ë4Í¬'k¿~±×#9e´³Yº¢Ö~¢ìë­;fÞ+Îj¼K9p¨ÉÔM'X/rt²\0Õ\\ÍJ%Q¨Ýè·R\rÐ²O3¤|å¯ù×ÂÏ±³4ÝxF×ðµs5EÈÔ;ÔWRÒJXÊ¶Jì\$þÁwzOöÏ&ÇµÁÄzkS×\n\nNUPâ°.ö»0ÀbdkPåÌÚ	G6Ö+BÜz1ÎhQ>sHv³ÃÂÄQÙ EØpÝMä)Ø\n\\ÑÜPzÄèí.sÛÍÂ gÅá)a~ÖÆÈ¥Ý!(!Gìhr[²*ª£ªîÕ¢`~Í\"!âO¿5¹G3Å*qkgB,\$öãÛ**1c.»n	8¨¥\$d ´±VSneMiZ¶íÅ7Å¾g¶Aù5Ü½Ú\nú`¶,2ºÇa¦Ò¯ÿömMkÊ»´ßÉ¯ð²/-Ý6µ@?#`Ø)ãÔha©Âñá)VcÆ]Ò_= Rz\\ïVR§µ=¾Ø·³(-ãotõ\$Ü¥È\n÷¢dSm³yµÚfÓ©ÙN\rùm(t;DÍÁÿp¸2¤Ý¶²ÃZRl)Ð9MÌÀ,/YixªÑkÑ).¤2@S^úöuÚåd6¤!Ë>VBà x<¸Kt06ò@È\nGAáP°(ûªNbDÐK\n\"µäcN¬´\rÄ.põ¤'2Ldê²µÑß\\Ly§A=	õÄDm3%Ä@±Ù¡¥Á8åqbSP\"âÞ¢Æ®/ÏDzëC&»OûÇ\0007fÂD^1ÅXº/ã,\n÷vçWx%f)Î' àDdQ@I(Ò7Y¾Â|ÉÝºAÿQ±¸D«Ú e 8×7k)_ ñ@\"\"½¼%à}¸	¡(Ìë11Ø§\rõ¡Êãeòá?-ÉµH&ëÍäõé\rLÛêâ'»eÛ®0ÔT×]ÍÔC!ÀemNzì	UzöñÀÉ¢SÜaf¶7Mê^CD£õÂ(_ïìÃãâ#\"ídr5¦9±Ùõ81Öhf¨È­áa_ÃtZX\0èU¼­{2nn]¾ ;FRû²!}>séHiÎy#³´?\"Å¤¥çíÀ>{°®Î/?7îF®òY¯°úª?AjÁ.U!5`ÂHÀæ\$r\0î'\n¾\":.ûdÔÙÆªíqÙRÕ­ohõÝ>êÌ{ç×1Ý+ä>èËÉ·tÍkð%-Dì=9Ê}ÄC@ã8cmHr°ï ÁWÀnÊ \0Ä<(ÂRR«8¾ú´YVàÅ`ëppÜ.Ue_`®°¹^¦õìµn^ç_ÅR|ßrÎp7/!M5±ìÅ|×À\nû&¢Fù±VVzO­AÖ~Ñ|Æ¶Ð4NÈ¿¬Õò¸ðg¿yh-¿\nN\"r\"³ôÕGcôsª©D' XoÙ§¥øO{¥{Y{¯ÆEø=TeìZ¸ºúî{\";HÛÑXz¤t±ðwê*-ºÕÞõU¨çè§wú-þ¤\"¦<A^¿OºÍT ¶]D?:þùåû©åíæ<pqõ[¿È,)©&`Û{xKIÂI`º`Îcþ°0±ùªDÇy8öÉqC­YëCFõçJÍÙnkã[¹8÷É¢ñ:\n^ÛÖ«ÄTØ!X*Mú<5`\0¯É6Aò2oÐP.µé£aøAH¨¶#x[·âïË 'o@¿æO0^äê¨óh|ÞP=+Í)ºd[©ÇÈøX-ôWÂ!ÓèÃ/:\"0k#XÇ<ôâ°ôhCGÝ @F(ékö¹l¢&H½F0OSzÅwæQý3ÝÅÙz|+\r9b½TÅ}'Ü¬wA´\r°nFù©Ñ!Èg0lplÑ1û+ø|¤hkzÔi&÷ªuëD±{KÖî\\¾ ¢\$t(¶;èÒäÃ¬þªHýr|Bw§D3[Mâ!:(Ý{Z®å(|-ÓHy0ê^'×½}ï*£üÒöNK¯«5KU²ájMå\"Âwá]%üû{1qÙÈz )]ÑÅ®[k\0O4ßýÒìûUFÀ\0ócâmZEGtsDQZã)n;7<qhlXx§IÆÂ^ÌVîå&Í·ÑC`,É%£¡1\"@1Ç|Í)R¥kßþVÏê}S,Ä#!ÉÍGµôá]ý¤ExåÝýYTüý<%ÿQÑ¿Û@Úíömô¤¶JcææB£B iâGñÇf2 ¨cDÇänÕ§§=JüI_¶ûðî'ÌïóiA &,Ð{ËùcÃÚ4ºÇoV%d¡2ýxe»#s_UÓHåÕW!  =Û·ÏOú<(y\0.ÀG¹'Ï\rð57äpVòº¶(æ¿Ã¾:îç}ôRRHHy[Òÿ	´²¿ý 1åÂøO\")ññL¦lÀñ1ÂÿûíÇÞ«û¡+<~	\0¿Âçsø¯ë?ÐB@¯ôdÿãýäÍ?nÿ~Á&LÐ­ ?ð«ÿ@:@;ýÈy¾òðQèº>ÈãÓfü«ù:\0¼tæ+jþszéK,b^áp·ÀýHXÅ?PÀ\\Dè?v\"£îËü\"¢&° ?­÷¯»tþ`áV?«\0úäJwC1Oð#êÆ*	ûþ@Ì¿é\0ÃþÁÆû¡/#8\"¢OÅ\"¥\0ã¡ø6NcìÃ¤[ýp@Cóh\0{\0	¾pDOþÀFt£ÈH/!h@æÿL°;À@ÿì¦wÁôIÔ~CëËÂ¸)îE¡©4+¼¯°)§áEbç?]«d¤í\$ä<¤éÌ`o¸¾Ò£îï?}°8Æb¾Ø¸/°Jª§Ùo#ò¼ÚIV,Ac¤´3íXa äÈoîªxiËõ£ð\"æ¤CUÁªD°kYÈé}©\n\r\0,GÆ\0Ê|q»¯ .ÅÆÀNqÄpNÐjBO\$|Cõp}ÆÂ4`±ðÂÀ\\*4ÖÐbA¤àó+æD_ôòÀÄX¡\$·@¢6\n\0\$~Ë£æ\0À®JbÝ¡Â UpXõiD\"üÛç lgÑt'£þ ç+xÂ<¨ÓNÞ51eàÂ0`ò¿ñB8qÞ\"O-â	C!¦ÒØmÉµÞÚÞ*¸¸f@#6ZÐ9 ¤ZRàÇ°ê¸ÅÀã	HZL eò½¢÷î9Â9À T nÎ?xX\$î0´%\0002\nÁy!eà:\$ÈQssAµnxKÂçl1' Nz!p¥À¬.á¹êcép¾¤1@)mÍ:@PÂ\0á1\nä(CRä5D(¼PÌ1#	Ýd7+\n£BuøhaM	aî\0>¸1W¨ý¡\0að¾4 sÒ-×'jp«å\nJmQ¨þÈ) ");
    } elseif ($_GET['file'] == 'jush.js') {
        header('Content-Type: text/javascript; charset=utf-8');
        echo lzw_decompress("v0F£©ÌÐ==ÎFS	ÐÊ_6MÆ³èèr:ECI´Êo:CXc\ræØJ(:=E¦a28¡xð¸?Ä'i°SANNùðxsNBáÌVl0çS	ËUl(D|ÒçÊP¦À>Eã©¶yHchäÂ-3Ebå ¸b½ßpEÁpÿ9.Ì~\n?Kb±iw|È`Ç÷d.¼x8EN¦ã!Í23©á\rÑYÌèy6GFmY8o7\n\r³0²<d4E'¸\n#\ròñ¸è.C!Ä^tè(õÍbqHïÔ.¢sÿ2NqÙ¤Ì9î¦÷À#{cëÞåµÁì3nÓ¸2»Ár¼:<+Ì9CÈ¨®Ã\n<ô\r`Èö/bè\\ È!HØ2SÚF#8ÐÇI78ÃK«*Úº!ÃÀèéæ+¨¾:+¯ù&2|¢:ã¢9ÊÁÚ:­ÐA,IñÌv4Ç¢ûê£P-«\nÒ¸¯¨ØË%>(à¬c(P¸74c8XÐï`Xâó:\r£ä¨3 ÙKIAHHÈsë\"NÒ8RÅ0HY5GD¹W(®ã¹3¬¯Ut¢ê  PÞ9MÂùVdû?4\rCPªbØ¼2*bà3®T`Üön¨VMsb 0]pGµ%n\\£EÏ]ð¢8ßíhÆ7µ¡E`Ö@PIí¥jV½öTöíz\rC+¸R8\ró\0aRØ¾7Ã0æý¸½÷l_¶2dYAxPZAÛ°@y°AðRôT Èo¨ä^CK~cóôéâ°{}c¸øèãZ.~!Ð`ö¿Á@C«.Þ.¹ô¨é¹¤ýyô¡\nòlöé9wt\\C\$pÕ¨pÉÙ8æ/Áåª¤eyn_³§æãàHç!fwZôõ%hö°c5~[ÃH{\$»î\nµ»\r!öô4¡nÄÿìn6ÍcHºéçÛJ.6|`Ó÷;.Þ°[ãùpÊà¡ÀWÝªÝô>ùý\\÷ÖîhWªôZ¾³­ÌOÔÊ7PúöÌxA¾pUWñ)«µç¹!/pÒiÓ[ÁÀ´³~Xà\nRàôù³â\$Á8?BEÕy!côPÚCá¢5.\nH±]=«y*\$Âés©ÐÀt`¡«5¦¼7a¬\r\0è5ÇjÌÜ-gÌÀûºÞ\0õÍ¤#êoAÐÃÐî\"p´;£\nH<¹ºðÑm!¡² ¼dÃK´>+dî ø=¡p)ªpP	#À|«<)70Û¬-»ãÀá(ekþ9HéÊEè9¶Ìô. N¬äÄJ hL>e<Û¿¼C`K´éxVA¿ öaPÐA9WIy4WjçpW«¥ÕÁd²ERÐ2Ëip#)ÑÌÂØÚÝCD?rºu°ªxs³|Ï¸AX+?§þlÂ<H &ÐÖðîñÐT#¤|Ð Q£b ¶-\$°}Ah:t0íPöD¨9!9SmÂHûi\ro}¿ÆªP_EÑa¿æx­f¸u{Ó²vàâ<)Â/#ÑQC*Üª\0ºrNirêÒtÚGNo¤w>ØÄÀµMÔÓ¼ò DJ¹Cv`ò`N÷a@]¸(U ó¦ýS5{È=ïØ·9N´Óç8z3^<»	ë¤Ñ	 ðX¢c¥\n=@ü¿s3&ê d¥ù»Aj%\rÀy\\{<#á	UgÏR`¤^ÁK4lå¿!÷t°¼´{\0ÜW«&|-àé¢U¬À/7yU°ÊCº¿ÎÐXªÏR¡6uåH¤åVu|I V§Õ\nq<é¼*p÷)ó¾Óüÿ©&Nøq¡¼/RÙ\nV	¸8©²úÃáÀ3<;©ÔÁÄø}_¬ph\rø ¥ Ópt¢9#%<¨¾2iàd3æRs¹\náÛøkOfÈÇÓä«9pA\nÊä¸9 ·ú¼ IùYÌÈòþC¬c,U²2æ^Ì\0í0\$öNÀ®qsJÎ+d¶*@1:uÂ¶ë´ÏôúíkÎ©!Ó4;é@zZÇ&¤Ìd\n3\$ ÅßÝ C¨]¦¤ú£QÊÝBVwpª.KÌ\\Î¬Ô\$9Ài<2Zp:a`UÔ´ÁïÖS¨3¤Ý|T!¼&PéÊö,c=´Ä0Ó=ÇËNÖÛdë­6nÏZyiTTJ¶¨wÚûeSîuÈ'nÅmí¸I¨n\r;Ý³ÂÝ*)Aãi1yQí\rÛ_8?âÕ¾®7¥6ðÚÁËl1øÇ½þùß{ò½¿±°àc²­vrãû»{\\ð®Î.,Û¼»ßeêvùkàÛeó~L÷^ç7À®\n@.síÿçÝ8t}É8¦C-äÑ»ô-ý¸4ßIédO{sÕ»8ää[Ëµòf·;}QÄÁ³¹s^Ý¹×QÚ2[ª(@Ä\nL\n)À(Aòað\" ç	Á&PøÂ@O\nå¸«0(M&è}'! 0{6ñÞ}ûºk÷Ê@;ðpx6ázgÖ|+ÌòDòîâ¾+øÏ¤ÊyJßL#}Îó¬~ûü*/}ñïÍÈ4·Áä|Awýûó<ÀèwOñ¬èäX\0ÂÄâÎÕç~ü®ðþ\rÞÚæÞÝ­ÜþÎZåì¨Ä*¢Ù\nðÏ§\0vä0 ïèäïþ*Íòù/hDâ?Oú\rnêéðBïPFøoêíÏñ0\\ÿ`ç0fú°kïï°rùOïHðpÿðhýîxïpqÏÒÖPáT b´ ¶åOPÄ¯õ8æ¢æÍÑP ÚOýoÆ.Îí0§Î\0Æ\rÀÍ	°ªúîêPE°Kì®Í\rP)\röâoÄðTþèv ê\rDÜ¯ý°oâÿíðüMöA(XhCL&º\"h\r,ÒNÂ^qKkb ¶Ø\"	ðÇ}qy\"ïÀRÍ`ú°Ä\0°¶Ð²ºn+´®\rn¡ò³qH«HLñ®µ\0V%F: Ø½\$\rñ¬fé¬¶ÑjBçm©Qm£G\\è±¦nk«%\"V½±d¬Âk @ä òªç!2+6·Ò% §~ÈÍÄ%ë r.ÌR[È 2?\"Ì¹#\0¶Ôw\$ÂU%±#!%²)\$ò	\$LÈmA-W¬È{@Ü·¬ß#Ò_&ìÔxÒÜò]\$S'\0ä\rò½ãgÉ@mè¹0¡`dÍfº`G&L\0È':xjxç*Ð¾ìDÈLÇä²êè´Ä¶±ºÅÞ(±ÀqÅ°Â,&ÔÜïlÀNt* \n ¤	 %f(£¼ÏÐ¾µkZ	¶%i®n\".ëÄ»Çæ°®Æ~\0æU@Ä¤d¾4Äö'r¤\rn#`äì2HÁ ¶Íg6ë&£v ¶º×'¢\rrS^\$å@ÀÌXf>Îk6Ãr7`\\	5V'W5à¶\rdTb@Eî£2`P( B'ã¶º0 ¶/àôwâsÚ³Þã&r.SVsÑ9ÉJJòx&8³´»ÓvÀÔ!`z4\$k´\0ãÐx7pIó¤ Ó©Aé9µ;´ªÎ\rÅ~¯è¯4¯ó>~'\nPs0PâíQA+/7`WOåéG1Fpæ´\n|í\0P¹GGtI\"TíiG O@°½FÔV~Gè2Ø\$»éª%¸«96´,7LÐÖæÑLSoLhÍóP5Ê¼æÐ£\0¤ Î£PÀÔ\râ\$=Ð%nUjXUÄÜÈkÜÏàN\0æ«ç\rÀ¾)F*h@ök B³Ú5\$«56Lbs|Mo8+8\"õ:ÍóG4³ONS5Î#j²\"ó³Nn§®cçJtå½T¢%(DUSÕ]MÕj\$TK`5öo@úè²ÈÌÍ§rYSNR1ERÖ\rÀ¶³¾E²ðXrôNJ7ÕbgTUx®M5«*î0rÕ:3¦³	ô	2i1Qøµk¼Få¼Ð0YZstÍe¼½¦c\n:oHÊFE£ xu¢Í#ú4ãS#	 	\$¨t?õ¦E(på(êR\"|eB X¦ê8	4Å>\r/´<í\0E,^çD.ËE{5 ÛaµÜ*äÐ\rÑàZ»gç|ÔÖ~Ö\r:mocÔÑ9õ¨ÍJøv*ôÃB´Ò7rTÕ&Ð­nlH¶ÆPVÊ6ÔÇmDwÈ)m ö\rµñCV¨wãú \$ùuSô°ÓwS`ADèLS6qk³)Jkl'L£hB9h Jimn<\0Ð  <æ·\0¾[¼:\0ìK(¬~ªÏs\0÷KÌö¯Y'ÊgÙaçÄO¦¸´Ø(¶]v:¦&!`íPäàxV^w²¶ nºÄ¹àø7\0¾&g|B\0(ÒÂÓì*,Á×Ä¾×Â²dº7â¬tÇözw¥z\n»E\",\0Ô\"fb¤\$Bã(óh(Í4Õª5b?ÃÎw¦Áq|@Æ+ëØÞ¶×ô¸&ÉÛ~Nâ´âÌ×øN6<u¦FxWQµÀ^À^¦¿§;P.#/­ç|WÈ8k.ÕÅ/7K/wÈQlÁ8~QÏ³\\1Ã\\Ì&\"Ø¦WRïË/)|¾A5r§µeE@¾Ákµ\0OàÍwK&×fØÓ\"'Lm¸Üðl@ùøÛPZ³ùã÷7ªÈðÄ\r#oØx`]ÄbÌNzZ@¾0NRè,éx[P¤¹øc²8zXÈ\r?óÇÌ?9÷2Ãx}LÁÌF'LPðyzÃ°\\ÆÇTÃÌ Å¤¼¬iNÇæÇÂÇ×ÒTx%xaucw¸#l,\"àP£Ûb*¶g#ZudÍè,5\$¢D¤ä3]Ø?h~«0\n½yæN7Æb´Íùþz\0Þa5qÌk·pÃ÷v±Qµù,D[¹A\\EyKyP#U¡¹Zk¹ó&)E9q¸ÜèÀî¿\"ª7¹³!£Ú[ÍQÐMdÛuQJ#\$o¹]¥jÛ¥¹gÀO¦\n¦XDèÍ6Éê£¢Øe¹·§¶XÇZ£ø§¤:¢åE©:O©U©ÙbÂz]7sú«ØDÃìc£0¹`Â?¢\\ÖS{Ýyõ¯Èé¯SihÑzÅEiçij&®×«e'¼kº­Xy f6V-ZëWewÅ;G\$á´×å{S´¸KÎÊ7	³1nº>@ÌizúÃzÿw«9 ú{ x;º\0é¸Ú\nIû¹íyk[¥©7{·Þ»8-~ÄÓwñ,[lÈÄ@Ï· VÔ+á¹ÓØ¿û½j½Ûc¸Ø¤Íø©\\qÇû¹Y¾¼¾ì'¸Æ¤z½£Y»´Ý»«»Ë?aA:QÙ­ãûæ(¥â} ó\n¡yî#Sóy\0Ï[ì?àÎÏÈ/¡¡Ù]¼ªÁÕ«M£y£{Ë£Ü9¬¼=PÚÏ«¹OLs\\sWDÁÀØ»¾Ë±|7ñjN-E Ë+`uÆ¼¡\rM}×å~¿»ØIøÕ~i¦Ú´±|çlvÃù}ÏYÄL1l>\r¹ÅÅúñ±9à,o¢YÐ9£}¢ÇÕSgg¿¬»é¼ÊèóË:Ëu)ËÜÀE¼ÅÌCÌÀøR%»·ë~|Ù~ÌwÍë³Î0]Î|êÇ\\îÃyÏÃy\\öÂØ¬7Ð¹ÐìeÑ,mÌu¥Ò7Öý(T],wñÎ¸fU=¯TRW6<ÖëÒKÖ½¸¾gÚ;ô³Ë¦||1Æ\0Qy®\"9ùvb\$5·mwö²Îoè\r\0xbkHé|µÉ ÀZ\rëh»ÀWÊ\\¦«Ô±±Ôö.ó3Uö\rË½Ø\r½Çá>?2)á©â/â=âÞ5Ëþ0@ÆH×~<Ð½âx·Þ_þ/Ë¾3æ~I+~l~HYäÉ{åýáÞYâ^]À^aãeè^hë^r+>C÷¹ÕbB¯,¶û´«2/LÊè²Á ¼Rý#mµRKIK'íEöW­1ï]Fµz´_]óTÞÑ%4Ì\0ÚV=í4á;\$T Çæ{¯?æ ÷ï¬¼Ôó3Àùn\r¦z ¼ûX?c§p\n?ú#ØÐaîd¡î¤ºµÓX´\nÕÇ:zàÌ-^Xì! Ò`ø:\0äÇöy,DlãÕJ`û¢A)hÕUõéµúµõÿê+ç¼çñËè5+êüÃæç~_Éñãþ¿¨¹+<¹b]<m5~'óôÿ]¹ù')ôÞ¬Üº/ú½P¾rè4Óoõ{ô_ng¿ HFÈpBsÜHû1)îbóÞbñ§Ê?í¼\"[ÖC<ýU~<0¶Úyã:õG @}è¬zØïÞºòw)}¡ú[êôçì<8&·X\"`ÝB­Ww­µ{ÅkùU²¿½¢.ûãä§E;À=ÏpQÉ¢³óR)t\0;ø¡Ô¼ÒÎ*­JC^ ¤dë,ý+d-¨~¸*¿ðxpn@û¥Añ?ÎQh{ä³'A5öP{dX¼`ßH+êsSªÅkX/E(3=¨!004¦\rjÅÐZaÛôÁÁ>mú­Ý4¡»¾À?og3xÆúJW\$°EQÀè^&ìÉ\nQE©ßh¯ÒjèÃqCNüÆ ,yáêHÌÎ²\$'@\nð¶ú;\0\\]÷ÏÐ²(é\n6arÇ©Àu¡Pä/ò;P¼#q1ÀõË\n£PB.à6©õ°ð`\n×FÙ°ÍW»»ËÂþ¦ 3dbZUºÄÖï±=¨ø×ðxØaö@=®fËÀÍZ¦³;Bkè¬ÀïÅëmJîNgÌ^¢öÝpér²¹äÙ²¯(Ilc¢úø¯p*öAÒOá«UÂ7\\D<Tö§Ôf+ THÄËÏ `RÇôÁZq[`of\\Â\"ÏxÒ|EÑf¢Æ²áºÅ°P/S\"²_Î8Å-CöFô]\"jãh®ØFù29Àé!EùÓåìb[âóÑøEö*ÐêMìxÂ\0`9DU_»t½£¹Ñq¼^ÄÍ(ÏÅÕñj!ÍÆtX®'Eì_Ø»¥MÆQd^bÁ³|ò,è{4\\Mò°X°Ffù-¬kN`7,¦ùàBJG5À&ã*1LÌ4	#£-®üÏÇ`'\n£L?\0)Å|Àr	X|çe\nJ9@Ê¬¶È¥À6qÄX\"ÉqE¦	PmÑÂ¢N»Ò7¤}	ø¡<I\nªAÍj¢£uø÷ÈL+FöÜ'£CZÈd&RncIÉÅlò\$ð»\")|7Ë4hCvcsÉÅ}ÂsªG0~#fÀèeBð°¥í.ràO!<]/dñ[A\$ ©)JP±¾\0Y%F`&B÷´ÂvMII P*7ÀäÖ2Ô&lüXo.\0ªKZBq&<Jáp	eÿi;\r¡0PBÅÙHÒM²ÀLüÄ°=ÉTÞòXc1&y-I¨6fN|¨¤¯&yRÉn0r¨	ä%VÈÀêÊRKRdÐHà ´A ü¤Y\nÜè<JÄºL±úù'~V \"¥l!dÊè'`qå´ù«>Iit3:LÉ²\\s%ÐÍª¡E@HC¶¡ì\nf\"¤@ 1Ý1 l¨nÍúª¼îç/X\\DK à^-²nÏ|À\"\n8@{à)P æ(P(äòs f y0óÀM @°\0&bÊQX¦]3	8®<Ãæ#11<Ì.bÓÔÃf*p'<ó4ÅÏ)1 \0®À)ÚnÀ~cÈT S ætI11(\0P,d\"=üÐ@½6¹²\0Àw\\ÕfzY LÖn(ÝO}5	ºÍàW=ÒæÝ2YÍe@OlÜ¹7I»NòmX\0ùN:nãïBò\0¢kÀ|¨æ,p>Nxnxh¦í5éÎ	ÀGd'Å3éMØS\$HÀ©1iNÔ0ó¸Ý8¹MvÓÄ\0P\\©ÐNHó\0|9ç¦@\0!dH§NÉ¥L\nSØÔØ*MQu@&£7i8òÀ¦)1\0#Ljró3\\àç9HKÎÞdÓ?hg:	îOzvsàø§ÈÉóO¢|\0F4ßçÕ>ùöÏ¾pSÕ|õç³<*LBw)ä <è¨?9ìÐ@	3ç¥è+7Ï²esîÏ\0@ÓÐy\$\n(#BÐ'ÐRÓ« Åè5CiðÐ 4: çCÚ Ð¾}4D¡ã(i<jÌPôQüÓ\0ADñÏf´%¡ý¨>¹»LõÓ4T@I°OáúX¹ÀX ×(&l')}\$eI±fÆN_% Ð4àÆi²\\À±UhðCÒ=D§uóàË'@àv¢Ò8dBÐ-%(T%Ò7´óã¨Ôf\nX\0m@CÎÐ0ÑòI´±\rÉ½w<úQ£õhS09@òÚI,t´')Ë¦\0J7°\rË\0!Æ·W1\0åôù~¶_ÔÆ\rÝ2\nfÜû§@QKÐ9\r\rXi{/¹~ª£§¥Ý2Z_òôÑúùÂ2'*o¬	UØ³©ê\0¡{Óe(\$§¸iáM£4T4Ì4í§}6)âºËômV}Aê3Q\0ÓìlÓ/=@QZ:kµNÀÀ|Q­&¢Õ4J³»R*iSPÔ¨5â\n®t@æÔ_õ)QIMXoªÞ äk19B7à=ÐÈäÇ\0É·Ìl|Ø¦¨[aaÓ.§Ô¨°\n\0½49§Îv@G ´PO'ZHÃX'VZ@T²nÜÙgß7ð>âl3cDÙæÓXZÄÏfjY«í_ËmX)Ê¢zG¦¡ÅÀà\"P2|\0NàjÏXì{º\0ç0dä¢Tl´ \nq;Ùß:bS¡äÊhfy¨Õø)Q+jSCQàä²ySÉÕ¸§0ôHqà`	Ú`ÒF¹l®pT+ yçÒrºjZÕKªc«¡é¹WmAÖ:¹Çyè5ß\0P&úúÂzWÇÉZ)D¢	TùvD«V¸Õ3VºõÔF§È­°RjÖ­û¨p³v®5)Ñ'X&@.°åC@ç`pTª°lSw_ªâ	¢Á#ßí:!/Ô5¢rrâÐr¿ð;ºF»&«M@À\\C\0\"\$Øÿ(TÛX+þÈ\$t+Ór¬84Xf¸ÖIìdë#&¨cIPëÓZÒõÓälÁÌ±(l¬ÁZùÖÈÌ6^¦è3æ¾|¯sÅ\\Ô=EàrçÁ¿3­¯©w+¬(±, Ðc§ÄÀÐ^ª|Ú:`h[ÛUah÷t¥¤ZÔÀËÔ¶ËO;¡Þqyvì\\êùA^°¥ñx!ýj2VÕ¤Õ´E¢ªd´0Ø±õÖ°4H«²±°YHz ¶0+Ø¿Rjô´ôf_k¢µ¥AJÁjÌà[´©,U\\jXXó=´©°ZDw5uË¤ÓÕnù	%'£}&¾p&´ )¾Ò¬q´XÔÖ\0+_9ÝC)õIÛ)Rý¬ì§`Äµÿ¦@ê/!+UAfâöáÃ\0RÐ=ÓAó´%àr3{\0`%z0æ®\$ê>Ñ¸=¦h¬]/û6§4\0i_2¶U¶«eªè¦;:J±NuV|ë@ø	¨üGºhU§=Qh'(T>,þn?#ÊÝts¥ýfç©=cÐVvu`¡U'X)ÖMÒé÷QºpÕp7×¤!a´J¨lÙ0@ZFçE¨=ClJd­áóÓíuAJtÈªp0¦W¼UwØëÆñ÷Fa\niÝ»X¢J*»àÙo*6ÚèkÕ8ýN®÷[* /Ñu¯MCUMaJÞ²¶V!¶½ìU!+ÛÅ¬´pxhæà<@BíâÀ½] ;ë  íu®­Ð_2RñL¸ÅÌ:Çß	«4½.f1ë@b¬%\0ÇÃä!{ø=MÛ¿°|¢`x	\nÑoú!p)_ýtãÈ¾ûÝ÷#pa¥¿ý±i\\ï3D¸À.¶ñ¶Y÷2ÀxÅFgÑë¹8'(Ñ0BJ¼É@b£Z£n	p\"Ee9 »ÁJç0X3ô«b¸\r; ÅS¢1[yÈ=(73À	Ã2¸*ÀÈl0!V¥lrZ@<´T¸ÙKmáXiF\nUÚ?fT\$i8GS)L\$¬8B±iD!\\B#<4aT·»+@®-ü7\\¨Ðx6Âp°¡¼?\r N/é»°¨%L+`¸hÀtÂÌ<W>á{¢Í~(@ìüüáØRä06ÇP+¾{EsÃ¶\$ñ*Ù¼b	&¦#ð¥Ì[XÌ¯Áþ&ØÞbùïnÎñSÌU¯¸læ,0G~ç}àcUf'dCs<m\r;Ø<Æî*4ÎÜÇ¬Ç~±Çoam4¸]/î0ÄÜ2cÈFxw¦H;Ràâ»qïµ¾&	kXã?AIÆ Æ\">´¬xÐ?°÷,PÄôbäiÅ«ñ)c<\\+Ù+ ^n3ÅÔÒä|N'!+PGN5ìT°ÙÁþBK §!ù1\":¦2bP¤,ä Fy*ÒöNÃ<a[&Â3ÉÀ²té7ù\$\\Çqß 2ecInÞTãy2c_	@\nuþp Áüxÿ+çXUq·<®A.ØÂKÊÿÊ!2¥?¿8frË8\r8(íôp^±!ëöÿ×!YÊ=q>´\ràv-ÏÙ°¯	Ë1âÆgþf,ïõ[ã«,e'ZX:2\\H¡ó øy<1)[Î±Ò;àD|#ð©H@ÁòLSÑ3¨>;ô]2X¬vjï.GEßBi+d®%ÞÂ,Qr%Ð¦Â¶*ýIÔèà5`¦tÑ-Ésbª8EÍÛ¾e\0=ç´2î¿/è¼ùYq9-eZ®¼1\\ÊçÒ^öU½`&g WJËY×hK]8W@;Ðpô# âªè#BynqÄ\$uä¦Y¯ç!á\$§ö)(rX@/+L8ÉO^Êp6,¼åÚÑ°wÂ<%MS©S=Z%´WèÌ\r\nHy/¢2+eÚ1¦EýÉ£\\ÊUw	(p\n-°ÃØI¶¨SîEñZiI@1	ô¥`ãÆ\$ñ44¥´8íÓ>\0Âäi·MùÓ4æQ jºY½©yÑp#éxú`Óî¦m'é¥ZÚ6¨zaé»S iÑ&´í¨ÊRü>z\nöôÃ{TiÿP:ÔöjÝZjTÁt¦Rïù¨@:à¨Þ­5«hj{\râfÏÚrñ½Ð\"x |¦cxÍ?§ró²àkú¨pÕ.²rÓ>tqÐC¡ªê	k5h¯­a\nó­U:yòÐó¥ã®xW8èk·â×)3Ú!Òkó^ÔtÒ}ôÒ-x5ï^¸²B(q@±×Qd]Æ´CrØ\"kw[&ÏÊuísÖW:ÉêN@îÀ×Ód±ô¸¹=°³+Z9©¤NÄôµ±°³@ã¾móÏë{-%>çHÃ¦·R0*7K/<~á,jsÖÒön§P\09.ÖÍµµþSj\nØË74Ý±,í\$;EÚÎÕ-¶Çmé\0*È»vÔü7µc;u&vÝÖ²¬37íØ¡»y(·tõn;JßÛàA¶ïÚG4¢hfáñù¹R@5)V{[þYÍÅmàb£²©è6û¸1pÛJÝ6ã¸ÍÀî;[.ÐÅ[r¯Üb9¤V¹÷0­Ëî´\rwÝ÷C·Ïw×à×VT¤ &=×,âhªzHä)êè 8¼õEsIt<@e+0yÃé¬njçT¤¤ÞÆ®w©~dÁJÿØÏù«@û)c±+hñª,íûêØ«8pµíL KÏÃ:QîA­ñogõ×1Äoç»?IÊZ.Æ?Á=~ß¬¥n¹°¼©kF¬!n%/éEt0'ÌP<ÆµGÂqPä´F¦ÎxAøq¿µêÄ×â«vn`,ùºcWÀ{á9Kúß{|±+s£<é£÷4Z+×¦¹6ÁPéPL¿ÈÙÇÀ(L=¼Õ®¡jf¾hÛ>)½Aïí ýqÿpKÌ¼ÕåÒ ü~À6d0¥ÔY½#y¿}ütOÆî°RýæCSÆ_²çßðÈ|bHwë¯sO%UÐðwâpÙÜNò¤ºY]éÆíÞÓU\"rMît¦ù»\0jxoW¦DÁË[[ÌM± Øy·ÄTÀò8üÃ@·9àhÖâ!ùÌr`ïà\\/®4Áu{dÖ8SÇ¡Ásb¹\"ò ¤Á¬ié;úji¨Ç¿¬kýj}v£iÖ74ß½­JÃä9=Õ54ð0'ù?íÕ(Þ7öqgûøà t	ô_¶âÝü[§úízñÓ\\wÌ_>sÇÁ_ÿÞÒg\0¹ç·ú©V|\$äp¸-½ÞBsðXÜÀ.ÇÙÈ;ÿ¾3¤g²ûPCD¹êGy1j\0y=MË;FÐm(ÂoD7y³kÇ÷ÁÌbåo=ç!:.Ó%C%í¸tß¿¹²Xm\$½Ì6&öPÉbjÀëTÞuÑ*ÀTx\nÀd5¾¼õìÎt^d³(S|²ô×-qËøãÑï«\0©¨Åú(tXYQ!HFî´k³÷à·0t«æ4H|³oNoûÈN%°\\Çw\"0½ÎBqµ\$[çùÂf|qÎü7~EyÖíî¥Xº¡çqø×¨>|ë Ob*Ñ\nÒÅèImßcËEÐ®ôºeÈÐ6e¦üvËLÀÉÕnÉ©äKxx~aúÇÀf)9Ë]F¦!¤sòIiNÄh~áÓ©×R£úÒÞ.ì÷µì¯GF½ú÷«8¢ï/zdCfð6-#g|ûÎï½tÛÂÐ;¿ÞÖ4TVô)·kVÞßÓñ/yÀC ×ÀÐÉ9òÊ07h@ëÜò).HqãEÝîñN}üK¯+ØYr¹\nb3@ØK1 Ö)lAË§Þ=#«HiL®ýÍÊ5o¾AïãÍB>Y@\n1Hºà·!+â×È£s¼0èGH~^7ÀÙ ÐÃÉQrIô8²Íð\0ÃÐ`¤\nw¦=0Ay¨[QÚ8HÊã¢O¡üg m¼ï#Ê®ukHB§ÿ°#°ouf oÝêk íãñ^!ÿñp{À}»Ø½4Iv½ºíÅû?x{¨äCY¬-åIC×Ðõó»È>0¤ûl\r¥Ñ\0°Ø|Q×1åÏ5Lö/±öîj¿ù3;Lï´·^ï{ÆUÞn(}íºÿîÌb½ÍWÈÙ¡Üä+>æï'¸·ðÑ{WsC~qM;PäéR¿vÌ¢×Æº:púàóQïÕGÀà 7a§;Àéá_ÏzÜæ)|¿£Á:ðg\0Y*Æ/kÄ\nÊ>UòÀ0xH@ë-=\"0H^U°E+Òx+ÿû#Æ;èáª1¯kÅyú´£ÍThü:GÛ&ª-!qs3^|úÛàxWë-lëý!×¸íF°÷Xôt]ÇîBXY;QLÎÊ½êé0cIÄojèéAøQºýÆàLþùGGãâ%\$(wÒ¹ÐEhÈXKða¹·ïçÑoúºb¿5ËÎøúÄsAÕðât/\rÝ`­wÕ7<MP´*yY¿h>PîrÌ=zjW01ÿgùdlþiD/â}^VÉ\"b·À>ÐáàX¼RnÏÝâr.0õëüÿôÌ9@ÌÙÐæ ¥ÜÿÛ®È·Ö;å&³^û2hYXh£(´ÿ¡b \0¦ØÁ/Ü\0ÊlÆ:0ä÷ÜÅ?óÃt%¥> ÀCG4@Öí­@ËE¯<ã Àh	Oê0Kä\0@rà[ì\"±¾À)AÎoX4§z¼ è¹NRºÖÌ«`ö¼jäk¬ÈÀ¦P £]OlÀ÷ë2\nì³ï*»b½5DnÛöïã2ò(þ\$ÁÓ<)»Hac:¶Ï/Ë8Ài:ùn6:à0;Î<1úLP\$ Ø£âYÂþ\$»¡³®Ñ:0´¢¨µðjIP¾\nrL!w¢ûN\0>~/`4É+\0æ¤Á<^RX°U6¦É:\0öbNÂè*.éN¯Ìpxp_¶Ã 8\0XoÂKbè|Él\0ÆÜÂö)\0°PÁª:<pl¥\n@»A½SPP°º¬Æ\\»Ò AÔ×03\0006 ºø(à.ÇØÓpv´}Ø9©z«ýäµ¦À@N\$Å?5§ã¼i+Av8`»y¨ ¨¦\n;Ô ¹ óêV¤pßú\"Ïïj¤íE=ÙxÁ0d\$§PèÐV	xßXñ ëg\\?\0ePaAJ/`ÓpS¤ÁLÐÁ	¨(PYBqÁÎÐAï!.bÂVsª¡\$ß	|Pf%gzTã£A¥¤ÐkÁ½Ô0l%.¢l¥5I¦É+8I+¶BH¸*©pÂQ\nG^B«	rLPUBµq hB¼ÈénBÂ,bè¤Ù4ºÁÍY×`	|#`.BæÔ.­ÎBïÌ\$ð6!ì*Ðs\$â#<B%¡QÌ*óe	NÒÂrLÐÚ\0ÎN1!i+\0·ãÑ¤÷\"60bCgaNÞ\rPUCqä/PÑ\$BNIBÁµ,%#£-÷\r´+e³h&pÂÂº/d+ðÄÁ·P²C-ÉBÕD;DC}<BB:0¨Ã¸\rPîCPèÐôBiÌ1ÀÁr£Î	à'Àìc[Á\r?P*?ÐæÂ³ü+pñA·,1Q\0ÃL@°qDh.Ð÷½YPùÂÃ¨û¤´`0¢@ç¤6Q·b\n\rÞ¬0Ã\r\$1Á­@²ÃØ=ÄºBÖ¨ -têX°ìBùC1®,©+BIìÏ´%ù 	ààÖ³(ÙÐíKT\0ÞÐF@¬/¢7Xá\nDÒØ``ã[Îñ¶pDÇ¬LÇúDÐ¼Q\0\0îN`3^ \n@°%È	9Áü§ø\0Øó[ð þ	³LÝÄÏÔMAë¯¤Q2Q8)úHWñGDíb%\np	ØSª (à#¶tÁÚDòHQq[Éf]\\'(B@^á(CCvÄúV±[Å`(ñ^E¡üZc!7ºÃEÌ*Y1mEuìYñ_E¥¤\\`ÿEÊc,[1eÅ×ô]Ñ`Eá°\"ä¬86 ×ñzÅq¼]±hÅxí@ÌçOE®\$O6¥}ÀQq=«!\nÅ<bÑ:µQÜc¢ùOÆ'bïÒ\nÜTàÅ(|QqFÅR`&E*1ÔRã!L^ÑfÅÙì`Ñ`Åñc\0^H!ÆygÑ|F\rñbF¦l<ÆÌcqeFäiÑpF¡hÏ\0ËÌ]Q¦ÆôhqbÆ¨4O #\$=\$g±®FÏkñ¢Fp.ª<\0åk`©Æün´Æ¦§äO(J ³ä[q¿F»j1»FÖ4¤\\(¼¤3\\TgÿD-TÑCA´+ \rà7ÎíMàx¾Á	\0Z	R\0005p\r1ÕE\nìVIÆ(;R~[>`36¤rp	Ô% ÀÚ-°ÁÐ\0ù	dCÃ±ü(9¦ÑøÆAx@2Áþ¤!±Ý*`\0002Ç²~8Sñåã	P¡AÚ/ Æ#æ©-§81¤nÁÚt*\0#O±ø0=0	'\0d 	À( xàGù  \$Ó\0 Hôà(¦2\n²¦3øfïînðâ 7â\nÌ`7GþF@>H.á5 >?ÿèB>Ç<ãzò\$`¡À>0ÐR¨útY´ã¶°+ ÂF®àQÎ äl@>\0ÓÌ1·0«\$VòÆ´Kò&\0½¤@0µ ûHà>4úäo\0006Æôqî¶8»1ÃP=9Æ\n®ð1°7ê²\0¦D*Ò<ñ#H\"1|ò#£é!K3Ò=~=nmòHî,ñ#ô,{I#ÚtÃÉ~) Ö\r b6òIð!1gàE¢/ìr\$ääÅ)\0ôóøÈÒ6Ì'ß0v3g@É\\ú@\0Â!¹ 3!4ä©HdLùfÃ_è°9r,[xëebÐf ÿHHhpf1~ÈD%,Áa)0 Ö¥ã%<N´©øc>½& K!K0Â-Ø;øÍH0ª¢&Üà¬.Á|#´°.È@3Éá!Kå\0002\0ó!07Ê¿eJ9h¿²\\=dðTò\\\0ßQDI«d°\r(II 8DrZ~(;bÝJ8ËÀC¶U!£RH¼\r» 3Iô¦Ä¡_ãýAÉN#32¨æ1ø@äú{¬£@ÿJ+#¨=R}ú2à-~H.Ê!`:àÉTñ^!Kû )JÐ»(\\-\"#¥ÊL0	tÊ)d/ï!r]8¸Ñô.SKÒ\0H\\wGà:Ã)G÷·Ê¼®¯Û5+ÈN	æNà\rÊJÌËÒÿò¾t¡0&(\náaÍ!Isá!!d1¼ð0²ÊDè	2:Ë5¨¬á3)°WîµJ%½òÁ:0âÅÒË²#Z*Hë-kç×´æ\rÈR²Ú<-|ZÖ2§LµÀÖ\0¬ødxæKAty2v®L­A8Ê\0ÉK²²3+\\¬¼ÿ{eïö¼ë¼  äì!Ö²ï¼ÿ\$¬gãIª.Ã¦ÉO#«Êòµ)Ø>°\$g!PØ6KFè#Òq\nÖWØP2NzS{)|/'¿²û±ë(kòá¥øfQ*ÒìJdJ´»>­©øÁ!02ò\0Ñ0RbrÊÚ4ÁyJª \r <Ø^H@Ì¸S©°dÁÁWlTÅóÌ\\¦T¼£è¦TÃF;q!àMCÈx!\$ºÖ9²_ñAy?ù²z¸2|½I|Ë.«ì2²)*c´BR+ÜÀË2Ô­`K2û\\RåË+ÀBó0ËL©4ÌÍ3dÀ )ÌÈß(­ËG,ø\"´Ìô¯Ò3x¿S?LÕ3<ÎSAM.tÏÒù2³.Á !+·%8!ÁJH@µK%û±ÌÊÍ4Ãÿ³AÌÙ3±³NÍ*dÔ,ÊM4ä/Å¿£Zc¦ÍO1¸\r\0\0ß(¤¢Á§M</ÔÖÓQÌå5Ì­38J75äÌS_=5Ü©A-0:)~HRàQt¬Í\0\rsdGsdLº.Ìó´[6@Öâ5é6È2¥MÌ -/ÍÆ	\0cóLÍÎ¦ùÌÀäMÈûÿ³eÝ7x³q\0003b½4ÑÙ4`1.jä­@H46\$Wà6\0\rª²iÎ\n©ôº¡y\0±8h;Éí>ÌÑÛQ_<40?RH10ÛG (LüfQ5/°xXøÝs­¤bL§`<Ë8{£r§´ûSìáÉ­9@Bçß½8x6à¯'´N\nÎRÙQf`üÝ8|è¡\n'´ØÔï»6T(2^A*/'0E²tÊ*¼xáÉ#dËgàHØÒë²RHÙ(Bó°¶U#`\n \$ÎÌc±â\0(#é9ì§2\r>¤óêÜ\nÌ`CrýKòHP ©£ìÁÛeP7­\$ð8ÒÉ6½ñèðY°6¨|¶T»ØhàÂº RÐ,4s³Ç*¬ èKª)DsJVÙÔõ5¦\nMH?í/|õ\0006@.LÀhùË¨À>óÆKv§L\"!K(\nËI´£¤÷¡Ov¤ó)I øÌÏv6Ã3ã¨é>dò¥!÷àO¦¸f/ÞKºÐ¥ÎLv0Â	T\nØ,á,ËÇãÈÜ¹Ð6ÈM³|ºOdð»!'U=ÃC/øJd*¬Ç­F<ÿÓ¸¥±!H; Ú;ÑA~^<aSe¸2Ö¡=Oõ1 9L{9è ,:­9Ð ,Ð7øµ¸-\0`¶\0Ö`¸ Ìu:1| e1ÌVáL#AÆ	ÏAp\rªú`À©Ì7)Æ@à,dÄ¡ëÐ{+¯óÜÊÇ5\0bìÍ`°¤Lh¦U\n¥Ð¦e\nSïÎ}ä\0ÖÐJAñø!2\0çA,u\0006PKQ|3ðO	PS68\0%@0É¦\nL­ò5.É ¤\0',Ô<ä)@¢í?(P5óJ¨ÇgIª\rd9\0îùTÌ´)ÐBòLA\$c»b)ü¦\rÈXÿèC´O³(ì\$ÂNÑJ qHV `\"M8¾tñìÆ\r;0»3|QC7ÅXLu1}3X:übO4]©ëUçMaEÈ?T=å%0¤âC!ÃT®ÂLHøÇ`ÕÑ*P8K/\\ÙËK6{ïSY+·Íª´àäKQ6@6ôVÍ<eIP¯6CSÔzOÙ\0ÕËPf±áÈ>¡80àÂÇôÈaÏ\"gI,gmFû¢çár ®Id|FR\$ áÈHñ!2FÜ¦P HR¸	î²¸	±8²aÈ5õIh.QLÉ(@;Ó\r³ZÑ3%\0=)@ØBà*ÇíHø?T£\0#ô¢ÇíI\rÐJpõôLÉJU%ªeR_Iå&@:R±%Õ&ÔªRq+*øR¸Y°?R\"J*R¨3ËJ°iÔ¬\"ý,ô¸ÒÁF5&jäÌ6ì.¢'KE+Ô£Ë&u)T¶RML)T°£LD~Ô¹R¶EÂSK½	Ô¤ÓKÕ1!ÒÍLª 7ÆÅDÀcKê-3ò4ÓD÷ÓA/x%T@SVÁI±ûS^= À`\rËê\n5#Û0	R2Q8ªòTI#¨%S·Âwº´Þ|RÏ7ÓÁ/EÓÊ\0¸¿´ë~0A/\$ÅÚÓ¿@82L!d¦ÒÐtY°Oô|7<a(¼Ê®6­4d@\rO¸;tÔAÅ?Q¶iIá¾|&,<wÑñÀ-¦ä¢ÁCÂ¤ñ\$0Ûë+¬åðB1ô­Pñ\" ¬Ò*¦è#\0ãHþ§­E£JOÊú¬5Æ\0\r¢ñ]OÑì²²^\rxzò:^	òÁ_(´Á6M'%[É-i]Fà#ÒåNü¿Ë÷\0ÄÁLNÅ4²fÕ#i:SXÂ@4®÷%ù[´Ê Í=ã&#eªÊHcõ\0Â8øf  Æp4\0öag®àÌ/eD,AÚ@àbE	\$PõÄJjÔ2\0*:ÑÃ .Ãç24à£¨-P@uÈ=ô )Ò\n=x )\0ò =è\n`+§ÃH()\0#x÷& =:kiÓ&¼)H+>UéßÕChi\0OZ~@+Âx	\"À.F?B	ôBµmAüCÉ[¢íDèÕmQuFjµR/4ÁÃT¤uK%TÂMDcòW!B\0EÉÁÐ`É=èªÒ&L=ôcÏB(\nÌ¨[ÛÐ0âU\n ÉÕO¸Ï1EUTu]\rUÝ\\Dé[¸\rGËí`1ðÖ'XµdUÁTæ?C£¼HµsV3WEcuuÕ!Xðëu\$çà,Bâ}e5~ÄUµPÄ1Ö.'A:>p¾ Ã¨iÑ\n¤ùúSÝSµ¥E\n1Ð\"±T@µ@#Á³lNà1[(Ã±:³Á¤PQËUÍl4DÕ[%lÑÐ%[Xðu·Dú5ºb¢	ÊkHb×DµpqW]p@9\0[R8¡õÅÚ(-RÇN©ñ.£Â.UpTW%\\á*UÌ¦)\\á@ÉC­tUÍÞj\"\0<WRc}uuÅu]v3¼QsõÕ×!]}§WX]­w·×.¦muê<I\\ 5×¹ÍrÕÙ dæh×^usMó¸+ 9ÏEuà\njÉÉW¼½yã^èÆUòW[_E{õÒãHÐ>µöWÈAõ~Uáï_¨: è®k]xÙµþ\0b(ä\\B×\$õ89\\dàñX4UÙaèH8Wì#esï^éA?ø+=uóÿ\0×`ÄµêÊÕ|\0äz)ýuïpµÓÓãQ½uàâ)`£é]xOu@6¾ewàðØ`qäÅÈ9Æ?½ ÅW@hñÊv#	Ë@m\$©X+ <×~(ãf\"j§D¢¶×eÀ1XÁ_=rA=¿W9\\ 5ØËc=E@Èl 1X´Ì: ±^éÆ\0À\$=bMsûØ®¢FV¯b\$©Y	b8WöCTÝs6%Y @Ù(-Ö)ÙTÚb±/­DØd­¶HØ®LÇ`À@d	VGÙ=eUüÐÑùøvX\neÖZWF{­MMY9eMÑ²u 2GN(-(Ù+bµÖ	K_fX#Öf»9dåÖcØvnRb%4'-\"Ý\n9\$(J×W%fÕ6j@7gvr4ÿd/1ÓÉÏgÕ×Û[e}Óc%àÉÏc¨âNJ\$éøaaÙ¬N	Y2dS¸Úb­¡Q`\0Å ¯WÊ\rÂrÚ,Hü®\réhÅrQùæ\"°àºi\nÌFÚKK;ö·iP+Vªiu¤pÚc3ð]ÖZ`8j\0Ø-¹×ÚEiØ¥ÑØ+d]\"Ùh	VLÏ¼¦ÅÕïÙf}¢1ÙÙj¡6¦Æ7dMö\"XYjÀW@Ù¡ A\re%¬\0¢ÚÅjªbtY§:¡öªZµÛá§ÚÒe­v¹¦(Õ®6¬dÈA¡Zûk\r¬iE¢§K6ÁÚÕl*Ñ<J]hu®ö¼ÚÿÚ ¨®¨¢6[#lbò;ØóbÕ³v¶dttà7ØJt°A§¼Ù¸8ØòÐÞA\nlxÒ¹Zþ&°!~©Ò¥´[OmH¶Õ´~µÊ<\0ém«öÚZm»ä,×[Gd¶uéi-DqZL¼î66[nE¸ ÛeÌj1Â[l¥6ÞJ}s/[»me¸Í#èmÆÚJ¤¹vòF7n?Jt[Ró\\6ÐYoÖL±c`à5Úíoý¯ÖòëüvmXç-ý6µa côöZsl%¡C@£[Wa¬ ÔÖí[õ¼W[Mo¶Åg\\±¬/\0ÅoØ \"µsbM 9«dÍÄ£×Àãä5Ç8<vÖ#4ÒmÇ,}Ùç§V|Ml¥S\\Îöim;ÿçá»Ûâ+YQrEÈ·%ÚÉrjÆ7Û¯a}¡À¨ZúMrW\"ÜsÊ3ÿØ(êB4ÜÁqà76q¾Co*µÙ+q­ÆñÜÛhsv<oÕW-ÛÉrí7?Ü§t\r· Øµt->tHá)­rJ7\"@w]Å¬7IYpmÒ·7XYt\nG¬Ê]\rp\rÑQ\n8\nÃ2Ñ°TezPÝ\0î=ÕvÝNEÉ#ÁÜ½uÌEÝiu-NÖËsíÔ^Ý.é}È÷CÜdË2Á]1f½¬.gÝr0é4ÖÀÚx°õ>WvEÚ7.]jÝÛUÇ\\k4qö!ÜõtØwIÝÁF]pwq5w)\0ýÝÑqµÒp\0\"\0_az]^Û7v]Å[-Ü<ÝåvÞL7®c]a7?\$¯-Ü7;^w5àvµ]=va¡gÚví¼¯	k\$w±ÊÜ;Z!µ©Véql@;Vð0íl÷\\[ôv-óÝý`X ·xWõ_½Ò\"øÜnð à<^uwíÙÄ^ew=Ó·]ÕÄru¹pñá*²urÐZ[]þÁGÄ>Ã©5CÇÌ»òÉ(0*ÉµV0WÅ	  ¦ (\nÕ¨[zÈò`)^¸ZkÉ±'ä#Åî\n\n^ä()·BiW¼ Ê\n°\nÝ¦ ò7¶ÞÞ<é¥'Ð@	 °^Æ:À3\0*«@ºíì\nÐJãÓ&h\nÀ|ï Ì¡zõì\0)_\n=ì  _D=h\n5_Z`*\0²ºÈ3Ò.Àº(\nÀ'¦Îã!h'¼(õ`Â«>< -\0{zi /®L8ò`/¦:øIUv@Z]<ì×À_{À0£ËÖ~=õ`ßm{,7ÔE{=í+£c~òl·ßÇ}5ìWó_²X×É&\$Jk×õ1mü7÷ßO{%üà«8hx`\"¦Àá­û·ý'{}î7°^è¢î÷Ðtò@\$¢ÕîúzÐ\n Vißß±{øi&JÅÿWïÞß|Ø\n	¥UZÍþwÏ_NWþß(»ÿé:_RR½ß3}æþ`&\n²iAh0Æ\0¸	¡|EñSÞßà¤_%òÃÊ_2Ö ö7Îß¶8ÍýØßÉ|{8%_Y}pcß\0}ök¦_kðò©_s}Ý÷ «_~Î?`	×»à{µìùàLpf8;\0¡^8\0¥_¸E` \"b<úwéóß~@	÷å_-ùØJß£Ìº_­-:fW·_Ã~Mùiôa1~~wéá=&í_?Zo÷·ÕuUàò7º|XJ³aUrbõdßðpZ	ÞÕ¨<ÖwêÞÐ0f#ÔU{~@&_æá	*¾£Ô¯{zwØ_3` #ÞÀ\$(0¸gU¥PX?ÎRk`>+\$.8gßÃþxàâlIßaãÝü8[Õ{ÕX8aßæðóIÒá÷\n¸\ràc~ÞaÍµWxà?\rÿ8&°L>!Â^µ|÷Út=åa×`=Øß« %ëé  âd	ÔâM}x\nW´+|-ì áUãÓx	iaCòÕWa-òsß¯ØòÉî'f(ZÙ'}ªèã×¦\"^'éb\nxkbª=5ýÉßhÒdCÝz=6\$CØ&+¸¦­~%8µ\0W`ô´â'òË`v&õYàµûø\n&0<âcÍ¨Ô<¸	 !MØóÏ\0¡þ,8bÉ\$Ä?EíXÉc\nµþªÝU­&Ahaí10ØÃã8ø¶âV»F2Ø¹âeNIbqÞ3·À>øÖ\0V..¸ØWÉ¦b°#aª=BìêÏ¨[P*=	Ð*àÊè¸`7ö¸q+Hº 	`\$âUØ5k^º=ÐZ	äb=íí²ã»}÷Xóc½.(¸õ¡«dÃ_3)ø_§3ôCÒbÜ\nF1¸±O,õÕQÅûycõö.¸#¦cØó	¿ãé.A©¼`>í5§LF9¦:<Æ+uàß\"ªäV[(=>X%Ò=H\nS9aÌxãIvE «a[ &2£dh=PZVU]U#Äd^\n¶ã7FG\"&½èT£&ÞàâåF?¸ä\0&Hy)bïîK9)ãw;P\nù\$d£æ#øÿbË&J³§gvK#Ê=nLXÓãH*Ø§^Ûýð *'Kán¹,¦`®:Ù:\0Vá 	)ï&!|ÎPØ²¦ÁVOå¦-ÃÔ§}{¸·b	ÖPØEe#æOcgþPÀ+ÕZrtù(O{Øôr®âÎByLNî2ø2å(&U	ß\0Xêãªìk¤MÀf5]ÖVXUU\\<ÚpíaHÒ#\0=öZàXvW	ßá¤,Rû`Ø)fß6Ucá0Øë,bÐÕ¨f'`%áÎ©eÝÞ^8\0þ^ú§Ò.\nX__­¦P\0)Áé.B@«dva9)'¹þN÷×åþO÷ñ+~¾+À«+¦XA¦-	õj3¨	®MþaXÕ]\"f)Ùª>y:æLà\n÷Å¡¸ò4V½Xø_péà0=-ý@Â§zªíUYbØÃ\0¨¸µÈ¸x\n&ñjXà&¨*É_r{R7KÖi+q3kw³±8f9Ï{6]éa ê´¤Vlùb>jy®fïÞy´¬<Åó)Ö(\nkfñjÔ«@­X \0Q~¸÷Ø@åF8CßÖL0ô@*\0¾`òÙÎ^Õ~rx®gDº6tTß|äx£á# >uÙÒâ	e)YÏªÇ®uñúJkyÕç2èÃÊ&±eUø{§M;ªÐâiU·âL=êu#×g£X*tÉßä+0×¾È 	 'µ¥úîUY^)5VÕ®*X-UÝa\"ªÇ¦ÊÒ8`­}aå×ÕUÙ{gæ­Îùâd¡n-8»\0æt9¦<æLµd«h÷ ,¾=I0Úh8òú¦\"ò´:¾8	Ô£cØ3h1\0i§§zð8Ö6cd·¶è8­Á1ÎhiÖÉß'õXË9|«£Ì_aèK ²´)­ß¯Xn9µ¨Z0ò5\0Æxá\0<Ðàã¸ àèÈ\nX½èÀ.Uà¦WicÉà:»:«´§ºø	½þQ&§µ \nZ\r<öYÇèñ£Ö£Íèÿ£ê´9VgµRè¸¶®¿ªbÊ¨Z«bùúä*åIGþàk¢©¢Êc.ª½rmP\0³3Y\0\"'¾Âª2ÞïUjIùiI¢º´9È ]W`+èèL>Ò-cÈ< ø|\0¿Zç¥æ\0¸èe¶{©óN£H´¶Ú=h+¤&ú@á1¦¦dºS^¿hôy=fWØUa3~úXNè;B¼+FOÚva\"MøÚwgícÈÒíG'Á ðòºGiá¢b)Þçí@öXC¸f<ö\0¸ÈôØ``¯Ú´:cð<Èp6<ÆcÑaïX^Ycò=-axCRK¨Z<ÆÊÒcf%¾?ø©n,Ùgç¨®>h/x÷ÚHç­VyeèÆYWVZ§}l©\0±-ó¹ûjO8²_[0\nY\\§Qª~#¿nZ¤¿>wyìà§ªØõY_[¥èöX\rê¶Þ«¸|egUj·:«Õ£sË¢aÀ\nZ&È­\r`Ò.«îIÔ`{¦.¹nå­«ø\næqÆWÉd.´)Òæa®`>gÔÎXä}8ò¸²»(ö\nÒ.Ív{¸T7,3£Îd«£g»~0óØá¤ô	<{Î<}°3§\$ÍÏ)±8@R34¨:í­Úäµ¥]¼à~6U!<@íjZ ÖºSØ^ÀÓ´B~ 5ôãÉ³,S¨,(Mó©ëÕ9p 3ìÎX©/ZæÛu'ø:·FÏp\\è)|Æ£¯X\rÀ<Y8Xtè!`6 4Æó°.¸à6ìÙÐ+ZÿPÛ°n¡ñÉ<­0Ûñ&bw®0>ÁSr<§@6«d\nzãlO°úFRlO±;H5°Ú*ºL^x0¶>g6s /ãqYe\0@<§z=\$CØ¦ø\n`+'S£9ëäXõ8«_/¾iñjö	·Ìh7²5Y\0&c©êwøÍäaÖ¡ØEìÉ­XyfuÎb{5ìÍ³vGµjgãnÎXdìè\n²{ÔlÇ«Í§³~­ìV¡³è	?¦3´Ì÷·éW}æÇR	Ch0æ>R½=÷ 7Æ¬à9µxÝ´>S\"Ó4zöÓúðÒ´0f\"`ð1½·:æ¸x:ò³x;ZÊdÆÖíÉeµðK;Ðð@Òg¶TML´¡7N3R¾k¾N(iÔF;)Orn:Ó¶°\"â>^#ëe;nÝ!Q\0 /ÔfU\0\"ý@Rp6ò ¨Bóm®0ó	·!ÛYír/TÊt1VÉeOQzBrÑ9,Æ@9mÓ!Ýt¼11;\"cmMu,Sæå<Å®W\"ó¶yv¡K)Và»mV,ÄÓb¨úkà\"à1¯µhxÅðÈF&àÈ9¹tÒ?àÜIq¶ ¤ï\0gø5»MK!,ò?Áå!PSQGÿe@Èa:Àb¾Hd@(: ÝîDdÔFJ1HÌ 8Èw&þã@â'i¹VmL E/å¡[RRË»ZõnÅfÕ§ÞÙ´_¹Gâhl¤bÅ»´ôlÉ.¦ïÁ3%IÎï[c6ð 3ká,IDíçX%¿JSoxêvKøBûq[iDìÊanýO@¼Û±mÚÕ/è|ÌiK¥2RjfVðr±ïZEJ³ng\0_ºHê;PHDÓnã-òÍiïQº°aÁ))¯PgÛáëL6»±¾%l»±kÕ:÷s¨kÚ1ÞúS©mÓNF½ëo¢(óSJIe:øÔºJ¯¾=l»ñïFæòÁ)ï²\$xàüÍ£¼>óûÃ9¼Xúo¼<û'ºÊmHf!ñï£D;\0áfÐSAër/ã;Vl<üeðµ±¯»Ø»2JÎA\$ü£kUÀ¢çÏÄY¯Ú ³	dù\0×»|pP!fÅÒðZ\r@âÀ¦ãOð'_x6;\\4§%´Z6[6tÊKÅ°#Óu1|î2XOo&Þ6~¥±DqàÖO<Ý<:Ó¶|­p%%ÌÖ³RÐ&\rÀ*oÊ¡x\0C[Ê¸#¸çõ:lpwªÍ\$KLÈ;sh`äaRnzà;Ïí;·±|8LªÏÜØ=OEÇ¥H* )-Tª/â²_íH._%öãÒHê¯Á7THÙSD5>ô°SË_µcr~yÍEì\0Ò*ä^¶ùÿ,ÍüFS¸è=\0é#¨>¦å@c·Eé¢ÅäMA7oØ\r R·ñq½{S©ÜóþÕ´1Ý'ø®á'm¹³À{p0f2/¼Ì»<m:-HÆ¸Â%N'[P°wdfÍâ\nÀ5<t¶	<n5ðF3ñ¹+¨b¡ð*ÏqÆedÆ«0µ@S;¸éd°ìÀ:;=>Ø=ÐÖ#¼ÅóÈàNÙR2Ï`ÿFÆd\"wQÝµUN«±¦ÀRæ¯utvÓ¡\r§'øK#¶Îþ²O\0É°ìÏír×'rHË.| í;O°K.8Èê¿L²ÜHTÀ|srÌIMr¤ú/4<ä¦Þ<rª®Æ0ì5nöÆÛÈln,Ûl?ËNÀà¢aËf¸Ü òá!fÆ¹òåo~À|ºòñ°°@2/¦À<¹òûËÐ%|³òÉ1~òiL¾	_1 5ì'Ë}½üÅrÜ!ï1´6òÿEo2¼ºsÌÏ.àóÌ \r¼ÅrûËý7<ÌóÌß2<Çsn;r5*OÔûlLD°F@Øl[Í«;ì]Í¦ÆHaGw7¼5¶YÆÌ®°5HµÕ\$ÅA·ª\\<t|µARQD¤ã!D\\îãI¹qvî@à}§ùÎØ_þÄlh(ñ¦ÅáSKiÏ)|iwT÷ÂÆÆÎ¼òO>æ,aÃñô>TÎá4|sÖd`#y\nuÃ,Üò\rÎ·@AsØ8=ÜütdñÜYËw&		NßBñhRãì2ªt %»q,Týï¡wMñ[!O=ýë²xÝNHJ@C6ßFÝ¶~øa\n.ÏG»q\"'(ôqà\r8CvhJ`9\0ð/ãÀ´|t&w@á&ÏÒ+ë\0¼°]ó¹Ëì<btÐ×?DqO:ÌÝØ</aÊX!8ÀYEÑUEñ½Òl¼Ý7G=ÓÑaLÊtñÓÿ@1^óÛÏÿ><ôÐQõX°½âÜ	\0Ç}?CwÔ©]=ÑEW¥\ríçÑíàäéNR+½NÔÍ#ß]?õ1ÇoG¬öô}.wG³ïÁ¤}%×6QÑèÀ#ô²@.åÝh;NÙ <PåÕä 1Ü8tÏÎâ¡¼þuÏI¢ÅMÐá!]tUÖ¨DÁ7q&0ÜTôz¦þ×\0ÆqHØtI¬|`6lIe°R¢Ø¦M¥°^ .õ5Äuóe^MÝö×_\"½õó7_½'o+Àè0ÑÓs×çQÝ?FØº«H¡ÝuîXR+ÂJÌ¢O¤\\Â\rÏ7`N¿ÿ|EtÏÐHû½w¼Yã_Ý»Oa#b@²ìGÒkÕØNÿÀ0ô S½¶UõÈ¸Þµ×h7=\$_Ú0cáxõ7e2qtDå¡pñ0½uómÏbÆëØ±*]^è%À7NÝ?öl/PüZôêwO=·ÔgnVÜôóÛÅ¡ vçeÕ©wn1að^]Áö]<p\\úÞÛàÂöÓÏ&AzX4#§Ý¯7Ø¯_=Ñk`Ð}!EØÁ²õóÁ^ßËKØ·t2_÷CØ·\\IX3Ø·ý°Ñ:%Õw/PQÐ.Ä£GÝ¬A?[ÓÝ'Ýwd\n9p)ÁÉr7vj[àóýÞ@!õìw^ÙR1ÛÚ¤7[ÖÝÝÇe;qïm[«wkØ´µ°¯/5Ù¢rö, ?b']­Úr§ò[\r/5ÚÝ°ª×¯·rÀ.îñ%ÄUáZv-ßl6~ËÍBõCóã­ÿ×ÈÖýGhø½³S ÄougM¶Ñïp,×O?Ë÷ÙßDd½mwà?]²u!Ã R¾4âV×_!§xIwö×^øH9_=>÷}á0ÅìÞ½)Üå=ÔHÉÛO_=ÝødØ¡øíØ ¥Ýd 3÷Þ]¬¹ÝþØÏ_6:xDx7B³Ó¼\0Ô]ð/È=ÖvIµJ<AºÝ×XÎ,M×Ëeô;GÂ\0002x|&\nÒtS½×ÎÜc4uÿã}Âøpv]\"9x_ObÜ'S÷K¥½(áKØaÞDøðX·ÌSmÝÇçáì3çä§_<;ÏT½þM¥äMù\rHo¾*Ëã_ò¹Ñèö-ÞRÔÍ'NàU:ô×¬ñÒHÚª%\0¾5¿g-t\riÅk5ÃÂÇ\n¯ù`åHsù¡Í¯isû~qOoí\r^m>\réO÷/fÕ&?wæÔõ	ÝùrÔ3*}îtóµÝ<çwOý÷¸\\eÉV&d%}!.ßÞå{Jªöf×&Fx°8+Rªï×:¢ùÓ,xàú\rØ^üø2uÞÔV]òqOs]øx=#C÷I}¬ýeÝîÅ1bÒõoÒtàJIæwP½\$vñ±´î<Fp<¦òyN¤\\·\0Ê=ÖÄâÜ+5ÃÂPlõ\nS»È³ýÿëÓ¾ç©\\6í¿vû»iõÃ9q4Þg#åïª|ùÕÔ¤vìz¨KäÞ½»µtÅ/Ý/ë¯Ü5éÒÊj_IÂöo%!7I¸t±¯QÄãk\nÈMÖwLMë­àépu!0K\n/W¯þº¬]Ú´æ[åï®´jzý·¶)Ô}\\Xí)ÓOþ¿ö>}ÂÌSìÙ8Á@\0Ö1Gþ{?Þ÷yU÷ÊaíGÐ/¶/´Kð~¨Kßí-O_7µ}*«Oa¾Ó{z%áó³Ï/¸Á,Ôµí\0#ÝSûí=¢`7í¨aZLí°ý»{H^Þ>ÐpµÂ7EOîð\"àüù©/¸Þé{ÓÈà=TNë½]¦ÖG.Ô ²øW·¦Ü×ûãé?]Oß¿vLVFÊû{åïíÝÁJÈÙïÏTmioÿì÷¨Ô@éxO¦?zÆ/0OÝYõ|.Pb|+Û»Æ\nðJaW½/Ä+!¯óì?ÎïÅ\\V_ìWªô|±Õ=¸ÇðO²ýÊ\\úFhabÒúoC¸Ç@ìÉOqç|û\nzÀ§OÂ2ùñj=EøQòI÷éñìþ¹v074ÍÍ®7³=­zÝß¿2ØMÈ×Í=)ú²M7[!öåÝßj·J	§Ë}¼^æwÍÿõ­ómáhzÇx9à{îÝ¶M½¹×AÛ|ëìÌèö÷ô³>o)ô0Öý¹¸\"ßBx}40ßKì(Ü\rmÝ¹ykß§Ð1|ÓOnPtÓè¬{û¼åÜCqÍÛÖ\\dû1ÒÓa.uô·]t¿7cnãÈYöDEVÑÛdÇÎ5(}	g×½u\ró×ÙåþÙ<T¿\\ý×\$·cÔ«ó*TûûðþÖ;[·«q{ûÞ×]U}Õ¶	Î_tÉÇã¢5udÀkmq_Þ¾7µKðÝ>ý+y{Ý&Ãa?Ô>N,¼Â5ËQö¦^±ý}ÿÑ¡.}aEr>ç+åÌ§@ÔÍê×ÏÝníãì?ce|X}'oìücñ«e_voìï{Ê÷}-÷x¿~Så_ÆÊ=ùwn\0;Ø=¸Á|âË'ì8öâä']³ÿ{CÃèýnü?æÏ[´uIÿÖ±4ü)~ëÒ=uMáßë=½À1ì]>F\$ðsRüª¯òOäã\ròåMw{°/I»â]íì?²5íú¼ÒõNµûèûíØ6uÃàR¿üyJ³ tî×Z\"ÈÙpñtÓÚÁîÝÙÿV_È©¡¿fþsÛoÏzÖ×óÓfÉ¹·óÁ7Ì«¿ûðfVúAY\$ª«ç|óëzÕí[n¦P;¥ü[ý\0Ý[Éú/÷eCÒ¬O)wßòUÈBV¶Wï\\±üçàpÔT]åØá_ëô¨	¯Gáú#Ó_v}Û5^éÐMµ/óõòõÞ\nD²¨ÜdÙîK­\0B³Ò\$ûóQz¨¥j ³MD)5ÄÀ4;");
    } elseif ($_GET['file'] == 'logo.png') {
        header('Content-Type: image/png');
        echo "PNG\r\n\n\0\0\0\rIHDR\0\0\09\0\0\09\0\0\0~6¶\0\0\0000PLTE\0\0\0­+NvYts£®¾´¾ÌÈÒÚüüsuüIJ÷ÓÔü/.üü¯±úüúC¥×\0\0\0tRNS\0@æØf\0\0\0	pHYs\0\0\0\0\0\0\0´IDAT8ÕÍNÂ@ÇûEáìlÏ¶õ¤p6G.\$=£¥Ç>á	w5r}z7²>På#\$³K¡j«7üÝ¶¿ÌÎÌ?4mÑ÷t&î~À3!00^½Af0Þ\"å½í,Êð* ç4¼âo¥Eè³è×X(*YÓó¼¸	6	ïPcOW¢ÉÎÜm¬r0Ã~/ áL¨\rXj#ÖmÊÁújÀC]G¦mæ\0¶}ÞË¬ßu¼A9ÀX£\nÔØ8¼V±YÄ+ÇD#¨iqÞnKQ8Jà1Q6²æY0§`P³bQ\\h~>ó:pSÉ£¦¼¢ØóGEõQ=îIÏ{*3ë2£7÷\neÊLèB~Ð/R(\$°)Êç ÁHQni6J¶	<×-.wÇÉªjêVm«êüm¿?SÞH vÃÌûñÆ©§Ý\0àÖ^Õq«¶)ªÛ]÷U¹92Ñ,;ÿÇî'pøµ£!XËäÚÜÿLñD.»tÃ¦ý/wÃÓäìR÷	w­dÓÖr2ïÆ¤ª4[=½E5÷S+ñc\0\0\0\0IEND®B`";
    }exit;
}if ($_GET['script'] == 'version') {
    $o = get_temp_dir().'/adminer.version';
    @unlink($o);
    $q = file_open_lock($o);
    if ($q) {
        file_write_unlock($q, serialize(['signature' => $_POST['signature'], 'version' => $_POST['version']]));
    }exit;
}if (! $_SERVER['REQUEST_URI']) {
    $_SERVER['REQUEST_URI'] = $_SERVER['ORIG_PATH_INFO'];
}if (! strpos($_SERVER['REQUEST_URI'], '?') && $_SERVER['QUERY_STRING'] != '') {
    $_SERVER['REQUEST_URI'] .= "?$_SERVER[QUERY_STRING]";
}if ($_SERVER['HTTP_X_FORWARDED_PREFIX']) {
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_FORWARDED_PREFIX'].$_SERVER['REQUEST_URI'];
}define('Adminer\HTTPS', ($_SERVER['HTTPS'] && strcasecmp($_SERVER['HTTPS'], 'off')) || ini_bool('session.cookie_secure'));
@ini_set('session.use_trans_sid', '0');
if (! defined('SID')) {
    session_cache_limiter('');
    session_name('adminer_sid');
    session_set_cookie_params(0, preg_replace('~\?.*~', '', $_SERVER['REQUEST_URI']), '', HTTPS, true);
    session_start();
}remove_slashes([&$_GET, &$_POST, &$_COOKIE], $Nc);
if (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime()) {
    set_magic_quotes_runtime(false);
}@set_time_limit(0);
@ini_set('precision', '15');
function lang($v, $F = null)
{
    if (is_string($v)) {
        $gg = array_search($v, get_translations('en'));
        if ($gg !== false) {
            $v = $gg;
        }
    }$sa = func_get_args();
    $sa[0] = Lang::$translations[$v] ?: $v;

    return call_user_func_array('Adminer\lang_format', $sa);
}function lang_format($ji, $F = null)
{
    if (is_array($ji)) {
        $gg = ($F == 1 ? 0 : (LANG == 'cs' || LANG == 'sk' ? ($F && $F < 5 ? 1 : 2) : (LANG == 'fr' ? (! $F ? 0 : 1) : (LANG == 'pl' ? ($F % 10 > 1 && $F % 10 < 5 && $F / 10 % 10 != 1 ? 1 : 2) : (LANG == 'sl' ? ($F % 100 == 1 ? 0 : ($F % 100 == 2 ? 1 : ($F % 100 == 3 || $F % 100 == 4 ? 2 : 3))) : (LANG == 'lt' ? ($F % 10 == 1 && $F % 100 != 11 ? 0 : ($F % 10 > 1 && $F / 10 % 10 != 1 ? 1 : 2)) : (LANG == 'lv' ? ($F % 10 == 1 && $F % 100 != 11 ? 0 : ($F ? 1 : 2)) : (in_array(LANG, ['bs', 'ru', 'sr', 'uk']) ? ($F % 10 == 1 && $F % 100 != 11 ? 0 : ($F % 10 > 1 && $F % 10 < 5 && $F / 10 % 10 != 1 ? 1 : 2)) : 1))))))));
        $ji = $ji[$gg];
    }$ji = str_replace("'", 'â', $ji);
    $sa = func_get_args();
    array_shift($sa);
    $Wc = str_replace('%d', '%s', $ji);
    if ($Wc != $ji) {
        $sa[0] = format_number($F);
    }

    return vsprintf($Wc, $sa);
}function langs()
{
    return ['en' => 'English', 'ar' => 'Ø§ÙØ¹Ø±Ø¨ÙØ©', 'bg' => 'ÐÑÐ»Ð³Ð°ÑÑÐºÐ¸', 'bn' => 'à¦¬à¦¾à¦à¦²à¦¾', 'bs' => 'Bosanski', 'ca' => 'CatalÃ ', 'cs' => 'ÄeÅ¡tina', 'da' => 'Dansk', 'de' => 'Deutsch', 'el' => 'ÎÎ»Î»Î·Î½Î¹ÎºÎ¬', 'es' => 'EspaÃ±ol', 'et' => 'Eesti', 'fa' => 'ÙØ§Ø±Ø³Û', 'fi' => 'Suomi', 'fr' => 'FranÃ§ais', 'gl' => 'Galego', 'he' => '×¢××¨××ª', 'hi' => 'à¤¹à¤¿à¤¨à¥à¤¦à¥', 'hu' => 'Magyar', 'id' => 'Bahasa Indonesia', 'it' => 'Italiano', 'ja' => 'æ¥æ¬èª', 'ka' => 'á¥áá áá£áá', 'ko' => 'íêµ­ì´', 'lt' => 'LietuviÅ³', 'lv' => 'LatvieÅ¡u', 'ms' => 'Bahasa Melayu', 'nl' => 'Nederlands', 'no' => 'Norsk', 'pl' => 'Polski', 'pt' => 'PortuguÃªs', 'pt-br' => 'PortuguÃªs (Brazil)', 'ro' => 'Limba RomÃ¢nÄ', 'ru' => 'Ð ÑÑÑÐºÐ¸Ð¹', 'sk' => 'SlovenÄina', 'sl' => 'Slovenski', 'sr' => 'Ð¡ÑÐ¿ÑÐºÐ¸', 'sv' => 'Svenska', 'ta' => 'à®¤âà®®à®¿à®´à¯', 'th' => 'à¸ à¸²à¸©à¸²à¹à¸à¸¢', 'tr' => 'TÃ¼rkÃ§e', 'uk' => 'Ð£ÐºÑÐ°ÑÐ½ÑÑÐºÐ°', 'uz' => 'OÊ»zbekcha', 'vi' => 'Tiáº¿ng Viá»t', 'zh' => 'ç®ä½ä¸­æ', 'zh-tw' => 'ç¹é«ä¸­æ'];
}function switch_lang()
{
    echo "<form action='' method='post'>\n<div id='lang'>",'<label>'.lang(21).': '.html_select('lang', langs(), LANG, 'this.form.submit();').'</label>'," <input type='submit' value='".lang(22)."' class='hidden'>\n",input_token(),"</div>\n</form>\n";
}if (isset($_POST['lang']) && verify_token()) {
    cookie('adminer_lang', $_POST['lang']);
    $_SESSION['lang'] = $_POST['lang'];
    redirect(remove_from_uri());
}$ba = 'en';
if (idx(langs(), $_COOKIE['adminer_lang'])) {
    cookie('adminer_lang', $_COOKIE['adminer_lang']);
    $ba = $_COOKIE['adminer_lang'];
} elseif (idx(langs(), $_SESSION['lang'])) {
    $ba = $_SESSION['lang'];
} else {
    $ga = [];
    preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~', str_replace('_', '-', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])), $Ae, PREG_SET_ORDER);
    foreach ($Ae as $C) {
        $ga[$C[1]] = (isset($C[3]) ? $C[3] : 1);
    }arsort($ga);
    foreach ($ga as $z => $I) {
        if (idx(langs(), $z)) {
            $ba = $z;
            break;
        }$z = preg_replace('~-.*~', '', $z);
        if (! isset($ga[$z]) && idx(langs(), $z)) {
            $ba = $z;
            break;
        }
    }
}define('Adminer\LANG', $ba);
class Lang
{
    public static $translations;
}Lang::$translations = (array) $_SESSION['translations'];
if ($_SESSION['translations_version'] != LANG.
    3675122236) {
    Lang::$translations = [];
    $_SESSION['translations_version'] = LANG.
        3675122236;
}if (! Lang::$translations) {
    Lang::$translations = get_translations(LANG);
    $_SESSION['translations'] = Lang::$translations;
}function get_translations($ke)
{
    switch ($ke) {
        case 'en':$e = "%ÌÂ(ªn0QÐÞ :\ró	@a0±p(a<M§Sl\\Ù;bÑ¨\\ÒzNb)Ì#FáCyfn7Y	ÌéÌh5\rÇ1ÌÊrNàQå<Î°C­|~\n\$uó\rZhsN¢(¡fa¯(L,É7&sL Ø\n'CÎÙôt{:Z\rÕcG 9Î÷\0QfÄ 4NÐÊ\0á;Nóèl>\"d0!CDÊôFPVëG7EfóqÓ\nuJ9ô0ÃÁar#u¢ÂÁDC,/d\n&sÌçS®«¼èsuå9nO4c)·{WÑ¼ç(A·x(-£îÀÛb­­à7°p(*ãk2¸¡'«£ ÚÂªëÒz¨+ à80*Â1iðæì¢ëÔ1A!\0îåLÓ@0ä2\0x\r\nøÌC@è:tã¼\"°Ûªø°#8_\nãsáj\r¬\nb7Ì\$ã8à^0ÍËº2j`Å·±¨îë8¬­à66JÃOÔ:Ô£»Dº-ì+¯s²¨°Ä<¶@M*Rí\n!ãbI\nÓyP\r¯ó®¨p@´­o¬(È»0Â:Ð\0ì0ÔMà§7Î P:­SC&®ã:¾3¶#Kû¯Ð9ãÈî4*õôJôRà»/DåB£­B;h®Î6ta¤í½n{z(T¹ð(Þ­ÁÃv®ÐÊÄç*Ø%z[!­ú05v)µ\rR¸)FÐâ_.BÔ®­#lHÊB(ñ²8ÊØ¶lc¶!J²`¦¡F£GÃ¾¯+Úç¢ïxÂ<ÍSb;ËõÈÁÏº|·º/jjåç °*±¦{.ëB[Î	É Ä« ¢&pà\"Î3üÞÏ4Ä£ ¢\n¼ P2ïôóÂÐÓ<±¯`Þ3Ãd8Ác<mrè|\$7('=Å#æ3W´éG¼Z/f0ºêhJUhÊ\"§;¡Z½çÒE]@ÙÕA£Z9t/(ÃØö4ßkOsdl²E\rpÜ5¦s*°:ÅcpÎ0Ô89G®&4ÛªÄ~ÐHR\$\$IRd;Ê4¦J²¸ÜÉáXÉt@øÒntË¸¡³0ÒWÑá¦!hÅ»aHé>a-:dtE pä¨4WàREHé%%¤Ô_;üIY,6ÇÖqê_e¤IYV´×R5o.x0²bPØrsêÎ6ãÚïÑ%hO¡76Ìcúå[]vDdFÞV/xÿ`Â[¿z®á<GbÍ¡\rlÿ/¨ ¬ÃJ¼n>Dãq62(3BTH\n4ÙCs&A5-4ç+²6nyÄ:«µñÓLÇHY«\"ÌÐb9I]¹cÎ,y\rqì­¢]È\"ZE.ÊAµÐà«QÄ@ÈÚ­&`Ò26qmz¾o+ãì) kb0nªüèKØÈëÞ\\Ú#Ä@@rdHr4wK´\rE\$ªH&[*±F-|´2ÂXËÙä\n7jLÊû%'Å3JùRg´12Üm\r²Z5oaÝ#\"xS\n¸ÅÂP¤JÜË'h6Yò[(¼[h/¨0ä³bñ{+ë´Ñ×¾a\"dUÑçÆ×Á.F¤`©\"×éÑeÑHåAù'	ÁØ|ÀQÃ\$ôáð¨P*PY\0D¡0\"ÖÙ2¨ií?§ü¼kÙà]+¬'A_-:Äx7ò#c\rù<=*¦G4%u\$a=»1r¬LÍ	¿nhP½G*Õû?NH½°ö#Jìñÿ3.¹b±x*¶¥	ùÀé®C2C8h*F6ØMOD]%û)\n\nøsZj±¬eÊc/Tî&_ûR\rèYD®AÒgªÅ¶£S\"GÔ²¼DYD( ¬TMßPIl è÷hq[¡à®'öapY\\\ne9\"¿Ã)ÁÅ]º¸êðep¡\rl7¹k»cXr%Ý0é\rÅÆ¬ý\nrä:AèE	¡W,öëz­¼«	3,ÅY³wÇê¶@Â@ ¡FÖ9Îd*%a÷ÒãÛÜ*à)³2ÔÑÌä(S¶\"Á<%£ÜÌf»½æÒ®èJ&q½ :glÔf¾zN2¬Ë¯·?<UvnØ©¼Á4ÎãKfì\0LôrÐ,Ç`Í2BÍk¹T\"ê°ª¹? (+PÅ¬é,!@),JBÛâÿ¥§´«ë lÐt9PLsÈaðLýúª¯8IQÆ¶Ì«H·Nô«ÛG×n;F{M{8;6´C¹-¤:E^ô½¹vñí­Å©½÷~ù\$ÊUïõçÀw<vÅ<{ØÒÜ(VêæVëiyg@TåÀÌuÆÎ5AåãÀËnUë,8:Ð¾¯8&ëåaà*R¶ÁÖ\rÚî*2\nBßë	Õúñ®½glÞøá;íïÖÁkwÞ'´¯×¸³ÒQÛ¼&}o\\ÖHgR´ÁlÅ¶,£!:½+©ÔNJ´1Ï3g²þY­C²Ò/^ßs¹Ï=×EÅñ¡ì[ë~J¿Wêè «ýñ¥°_â<\"ò+u).êÃ\n±sè¦Ñ-\r* Í¶ëp)hqTçÜ Q6rg>}*MTRÙdäYúrQ6[òÌ¸]ÿïÀÄ?Cï«Í¸À	ßÅð>ÊGoúû½2ÓÅÃ÷:ßmë²¯\$±+úÌYQBü~6,²gæû\$1ßÇÍþ¸²Ãb°¯âÅï\0EfoHf#þþ­ê(¼¼N'Ãð7ò8ÎÖþnp.ïoºÿ08íNÂý.úµïþ`¢2XºAÐ\0àÞE ÈO°bGPX¥Ýb4ð^¥¾ºæ?\\TìJ\ro6üaðÿ,ìÐû¯ü#°ZÅ`ÖÅ£Æþ°Æd;ð0«pH¢M\nPo¬^?OÚ6p´?@¨\$CxþÅV\n£)\nð60å\ràìpº?oû`	Ú[£ªpÊnUjNÀÞÌÊkfEO6\rhÀÏ7Kh5RàQ\$ïâ:b'¥6v\"1c	hÁ¥Ü>ÑMêÍ1R\r®Z´NÚîí¤ò\rV¢ ÒÄÏr#*6g<\râöÇB9bâ%¯fGd\n ¨ÀZÌ8bö|M*ÐïÑ/lªc+­\"á\"DÐìlâZUÆ|½t^M	±\r1ºàØ¬\n9C#â2,0°UÄÃgB£Wz*L\"ò`ëÛc´@Í+n\näj%«²W\"àÂÎO0Tó¬\"í¹KH²Î·?ïÔ_ì\0ßËÁ\$®ÒG#Ñ2á%þoKö7&4aÒ,Ê£ ¿¦Ffì*¹OKÁD7\0°e¦-,)Èà@Â¿Í·N¢H­ë\\ba	á\n`0Àó+ªNKHÅ²6;ñØ7å\\(,\n³NÀ²Æ\n±¢®ìWÆ5-\"0T!°Ü";
            break;
        case 'ar':$e = "%ÌÂ)²l*ÂÁ°±CÛ(X²l¡\"qd+aN.6­d^\"§Åå(<e°£l VÊ&,l¢S\nAÆ#RÆÂêNd¥|X\nFC1 Ôl7`ÈjRæ[¬á-sa_N±ÌvfÂ|I7ÎFS	ÌË;9ÏÖ18­Á+[è´x]°´Å¡'ò\$¾g)EA²ªxª¬³Dt\nú\"3?C,è¨ÌJÙ·díj=Ïèv=I ,Î¢Aí7Ä¤ìi6LæSéÊ:¥üèh4õNF~­Â.5Ò/LZuJÙÍ-xkª­¥Åè¿bÄ*ûxÌB4Ã:°¤I(FÁSRÇ2Pª7\rnHî7(ä9\rã&­ÅÂBÁÉrÊÙ¬ñcY¤Ì3,2Ðlj\$¯±Ì ÉfÕGæÁP£¥sfU\$(RÄµ(ºÉËñ))rª\"ÒK-3æÒ7«@2\r°DD{¨9#¢ì,ä0c¨9Á8@0ÍãÈ¾¯ã¸Òï¸S»¾c¼J2\0y@\rØÌC@è:tã½D3DÕD£8^2ÁxáFQÃÈIà|6Ä«ü3D£k¸4ãpx!ò~&ÁmIhDÄÊZe@HS¡D%(ÚMZ34rÑ	n>Òõ¥dÉ	rÝªBI!x+#Ý> ¡(ÈÛIBhÝhÄyFÊä\\ÜÂcnJâÜ#R1°S àP2Ãê6NC°Â6£,(²³Ötú,ò2±¤¨ÂII°U Ä\nJJç&ÛNK%J-ÛfY@[ÜRÜZÅ¬©KwÙ-j#ccc¬ò:( Æ0Ð¤qu³i0¦(ªÆV¤/Cõr^úbÄÉM&hHNÙoJÑòÊ[»\\Ý¶M¢á½ï°\\s¾è²)#®éf¨vìú5èî¡Új%!ä»­£+ÆÜ*üJåÄæÆ18\"vµy;üÇÀ\ru1bÂ¸ö7x\r£¨ç9Ru²ö:·´ÿ^£Â<Øv*Aâað`ÃHÏä¾|ÀC\0°¿÷ñ,B7|ÿÕß£Ãº7OôÒDaÍ!MBqüFÆ-BÍi1>L´36êÅB\$à·BÄÃt\r(à5ê_ÃxfÁ±5*Vùô,©\0 ¨ÎR¾\rÁäWèSÊ{ÌlÀÞÐ@sR!Ð9C@ÂÃâ|Kéà êwA@s&¡¥Ä©\nR¼+3ëðÝf+I\0T8¨pê«Àå\rSâ		4äD¤Cö\r!5©3¥ÂSyP* î©4nUÉU*À^_`tX\n´é¯ÐKÞ'îÌÏsÉ)ã`FâQ)e±nØOÛÉLÒúwc:¯j5G¢ðI{¹I)Hð¦TÚSêQªXÚr©Ujµø¿7ë#°I\r¡ÀèÕXóàÊ!}wCZºNAÁC)®±ØT£udQu¥ãv@\\¸±\$øÂúxK(`ØÕÊ3MeöC4°NpæYÃ¶­¢ PÇ|ëOf° /A:éÇßº+,iÐ²cXK`mýËD`´Xh\0ÀRé¤]f¸âIÊJC\$¬\r	®£pcQªoÃt9iØêàÓ¼ÿ\ráÞ÷ ³ÒziÓõÜ»R¹#3Ý+£ª ¯OOõtOQrÍX¯°îxh¡M\nEKû[xAÁádS\nA²Â#Z	t`§N¡Õ§=bÁ.6hY-6äRK¦B\$d+å4^)L4U \$l¡0&DÐ*z¬\"Ô¥cè`çd|eÜ#WNEø7MÓÀuKØqd/dHc\\MuÙ8|_¨­VUçNÖÖÀía\n<)B,\\ÜÉqÆKÈ<¸\$hR\"HÙm½ôòòrE®\0[MKOpMe=|MD´ë	@\$sÚÚ ;0ùR§ûöc	v¨ 0T\n-}¦\nr»Á¾ðàäqÑ20<£ÙHIKsBgÑk:µ²xNT(@.XËA\"Àò5Jì¦f\0émf.i¤;<ÉiâØ Gßê6(µÛã-{ÄI[ÐXWL²ãFè5·¸Ì,ÜÖs²µ\0JÕ0#n2©JãÛ£FM­l Ünr.dl;7tªÝI5}Ï\$SÅ0ÓjÌâµ¡#JùMâÄY[¿O\"ßù1?»RS)ñ)h8+yRK1´d;µàÌÙîVh½2.\$\nCÓ\"zóN'² ¦NJ£¤0z¥ªSP:Ñ\nèÚ¬a¢¬\0©],KßHçPîºéÞØ¼O#-ßi¦8R;Lò´MKm<³kLá§M&sFÒX³ï¹^|%ø¤GúH²y»Y (!ÏÆÈ(C/grÄôàÖæ#¤G=çÔÖ]iw\rÜCÕ ÄIy[N'nì×\nÌH¬sDÐ!%{NIíüÿµ\"à*1Y|*ÚDA¥7Áû¤Ãu5t´´\$xD\\ £qXu+Ú3ëhDLàDÃ,X¶Æã7p!`¼òl\"Húkn´{}]ÜÜûZÖmã«¨T2¤£	qõXjWG¯>Çjû2v¾ÒT]11º½É Ù·ÑöÙR*ÃéYnÄ*|¿£Áñò;R%rIQCÉR~:Ôv(B6wâTÿ^GLÞï\"\r\"¤ÎV´¸\"Xú>wo0÷AF=apc\"rpçjÃdoÃx\"H\0pÂÄmêæDG¥ÆûÐ*ÔbÍeØ!ïæð¶Ðr\"@'hÖ¢èÏ!YÆOTfPl\\n4ðæ0z>ÂLPvÖÇk\n2LÝ	ÆRt|\$e®ål0ÍO@èÌ@(\"bÄH¦bxì&DhÂHõ¶ÊÞF2æÇG\0ã*ÀGFÃ0È>äd3ÍÈ[­Ì³nZ2ÑFN\\ð`ØäJPàÐ\r®\ræ@\r§èÎìúpû+ªPé	mDÔ\$n-d¬Ôiâ¥çJBT)@ÒÄJíxÕP®pK-bÕ\r3£	Ý\r/MfÆ¼¤e¤jPúu\$®:Å\"×Kpt¨NÛGO|úïÒlFÂ1\r¾÷Ê\"p¥1Ì0±¸Ê²Qzï§1ÐéGÅ'çò1Ñæç`)çdub4P-¬udz4Ïøý#f+¥\"(ò\"	>>ò&ÚÈ&òÉFæófrÐò	\"RçH'QkË8Ú±m#%¯ø3ÒC#êR¨g½±äûn3P¾æd²çäªè.¼qÌØ¨O¥Ê´#*Yd]+Am'hïÃé,	'ÒÉ,UwgÑ+NrÄÖòÚ.é)ÈS-ÇSÉ/Ö[CI­jÐi-x?ÔöÚ¨*@I)¡qOâ]ÃîÒá*íÏ3Y3P/Ñ÷¢èM3ÅÒôÜæ4ò6³D¥Â\\ÊsE6s[\r#jÐq,¢[n®8ú°ÀÊUÙ\r©ÈnêÓ:ª9>)838³¡8ëÊêc»9cÖ°3êó(à³,2®ÀF7é=\"Í.±¨%ÓÜ)³V\"3æïÇsîfÞsë3/M<Ë6ð\r=p¦æôF3á,,o\"è¨2§ `<6øÆlFþ2¤#2onØS\\ØBÐ;Í¶5ÃB¶á\\ô¥÷he¢ºoôPÏ®@4·+æê]°D÷ÀGòF'Eô¨È\rV¼ÀÓ8iøP<d¼ ÞÒÈhPâ@Ìjx+¸°çÄ\n ¨ÀZÖ4¨A0vö'XF®\"0ìj+Z_å¤!Ô~Õ¬?Gâ]\n£Z	´§J®Ðú¢bNa.0fúÙ3£í`@PxÜÀDÑÉ,ÔaHÊ§¢@áZWöP#ÆàîLä2â(­G4µGXBÜgã@´ævÿ0ïDÆÞo½)K\nhÐlÕorÙÂÉWQ\nÔ	µmXp \nÎ:£8ó9\0Þ	M-ó9hpPh¬Íg6YBB[BGTe[MêÎÎoDx\$,ë+!dÅ£]²dôòX·ëMTñºH¬O\0ê±0lñBÃ\$flæf>O¶×ÕhÑUw0¢4·±×mRÁ«T\"MWHX^\rìîNdBTÔÉÚ,Æjfð\"-ã-Åä	\0@	 t\n`¦";
            break;
        case 'bg':$e = "%ÌÂ) h-Z(6 ¿´Q\rëA| ´P\rÃAtÐX4Pí)	EVL¹h.ÅÐdäu\r4eÜ/-è¨ÖO!AH#8´Æ:Ê¥4©l¾cZ§2Í ¤«.Ú(¦\n§YØÚ(Ë\$É\$1`(`1ÆQ°Üp9\$§+Jl³YhmrßF® ÊÊÎ@®#eºµ&ãÈÊa9kG:ò~ÈdrUIÑåí¬Âz¾¹aðëy2ÆµÑ¢«êòû^Ð¦GeS2u¢¨Jíû\\nE¢Wü&ÖoI\\qöØÕ=räBz½~Ì²7FÂp0Õî·bv¤%Ê6Ú°ÈÃ©¬k¸;\r£l©»JK¸§=/\0X+ÄºL=\$\n\r\r6°â3L[Ê;ìqÃlq*oÔYÅÏÖhA9ðs±Òr] ÑËÆ¹Ä\0*ÃXÜ7ãp@2CÞ9+Ì¤:Ç»,ÔRúO±1ãîÆ1Ìû¨hL\n6à'¥H¨*Q¡¢¬Ëhªii±\rªvÛ¾óä hÒ7J\0È7£A' ê8½*0c\"9Ñc8@0ÑÃè0MCT;# Ð7´Ø@85ï+`@TCL3¡Ð:æáxïeÃ\rEÊt®3ã(ÜÖm\\á¶\r²»UEÒ¸ÛS\r#xÜã|Ö©­C0êîO(ä½3./;/Ìj^>²¤íÍô-Íïk±NÈë¦'¯ÃªÂ«\nrA¢(+#ÝP ¡(È1­êÊäÙB^í ÄÒß¨ïb;?§®Ú4pì­ïJFå¥\n\$I5é}©©´¬ÌCr8£Õ#±Ðo28§«ë*¼ë\$h/rJéH¦Ï Å¾9O¸-ÎÂnE^å«&Ä+Õ¤øù?:ëZ,<oÃ½Jzoº¢\\ê;ÁMr{¤'3ÃÀ8<%±Ð¶`)\"b0çoÊk{k±ßÔÄC¸baóµíÈPDñÄøXã*tû¤Ø÷8=é3¼_©9zþêÝMß«	#\\õÐëÍ§¿ýõ61(v+{¿_:Ñþº,¼àié=]\"°FðÐH.]«@ÿÒÚw«Ôª:³ºëI\0\n	Ü/êÓÊØ \r¡Ô9©Ux·Í0t¬Q¥&By]¸ÂpæêÁ¤3Â°ËÁñ«\n,Õ0C¨\rª¥t¦¢TLRQ:Pð² ]F°\ntjH3BèÁ¦<AÉÉÄ.Ê\"\"ÜYLa.G£<Á \$Ê{;Plô¢°Ø6%tÃâ\\FR+ÉÅµQ÷iAÃrrL¹\"ÄBNÉ>µtSe/3ä`c:Æ¹Zdå@ét\$KìrÄñb0Nä¬¾6ÒdÄIÃ3'EH\"R'éO'ÓLª@2´J÷î-%¿*2ØðÀSd^LÃ7a¤6Dñ3\n 0¤Õ\$¹C (£ºql4E¯¾«a,E²RÌYÓÝh­5ªµÔ|P¥k }DW@nñ,ÔTvç\$iK}8¹´¼§\$¯Ý RPY%9<HJJ©`E:fFp:6åv¯UúÁXkc¬ÖjÏH9-E¬µb¼Y¢uo£cHQ÷1ñ!o3¦[é*= &4L<3Æ%âÔTÆ¼pÙÝSCQmD±~m+R-\rx¡V\"QçPh5Jr® @«`lÕ©<hedá3EâÃªSá:Ù ØÃ<DU¸*ãUm 4¶kQ0Â\"á>üûÐzS9oD ØÙ\nYeexkE×Ãã\0@P	Bâ»»bÝ=llzBtÎóG\"ÄÎåè¶¡½Y ÒLKöY©¥%oSve(Z#øSÐuC°53ø#æi{3dR¢ãRJ9«E=Ò¶ÄÁX«5j­Ã'á 40Ñ¡âÃ³f«0Ã	.(Ø\$Á³ÂXDÏH§fÐµ»2b4Dq\"¨ÏÒOº)Äp£­È³\\OkJ,B<ãÉâßLoY4&ÄàòxYE;7E8Ñ4ÌY%=6<^ä²kJzº.&¦SóZïò¹|¤/Î;EB:áD¡]Ô¬1bÞzÕ\$©)ôyI¦E?°Ló¨B¥Îoe4¹1ÉÂÑ	HËÓs0\nl±\$ ¥æUñ¯gÕ\\&ÏoìÇgéWK«­ùlø8èÆ½îCÑFÐ¯gq¡_5ÖÒQò¾;°aä¶G?ÀØ;\"ªoJ¹\"Ò`#K¸G,IKY¸Xóúe{·P¸oÄ1#õ1IÂdÚmîÙO9â¾]6\n;G°N¨7¹ qÀ§Kºí+OR1[ú32NÎx¶ææÈL/\n§ìy6hà¹½¶÷%;-¥¿3óÄB\$)õ²w/»äyY¯pW£æÛýWl¸uQäI÷ÍÚ¼×OêiÛ_¶¶ÚàÍo><Õ>gï_\\mEÇ¸]Ý¶bO~|=áËÙÁû9ÏÏ¨Bp`E:àê²ì¢¥.ÕÜkw~ä²VÛ®Ò!'6¤ÏÓP»ò¦c¤;1¯EüBØSóµêrÁ#6ñ8Ù#ÇÒ[O	½g_Àà­±\rÄ9Û¸²«Ã¢5Þ\"t%8ø¬Pò{wY¨Ó£²WØ:Nô¬`)0C2<Öù·)þh¢ÁMâÞ9ËÔ6ÇöþÇf§2A&yîè­ÂÙD\0öÀ²@Æ\rb\ncLTÃTÅÀË¦ê9â¬._D	¸ôÃ*\"\$ýD¨U«ø@ò®ã®oÖ>1p -ïl\"GBâ,D\$\"ÀËþ(ÅÎÜ{*FÃâèÁ¢õ\n ¨\n`\0â°ZR%F4ÌR«PS4ÀÊhÓ&6\"0IÒbfZà@ñÆNDÆY¤Îvá DÜKûípøo!`WFZDÊBÑë\0ü6öz¤¯¬ÞÐÄ,Î±\n\$LrbÐ!ÄB)Êêãæ?Äìà¬ÌþMày1\0ÉÃq\"&fZy0ûèÒ#16Ë!ðÛ,&äÐñ\\±Hìñqæòñ¡ brå 7Q6cá-»	<;Cx\$ÖË --øL+¨ñ\$ÀKì|Üñ´cVÔ±-q`îÌ7ÇDF½	ªÜÂ,±ÎÉt^h' Ðdâ#\r*à ÁJ§df¢Îi¤¶ÓN)p<Ç\rq¯\rlÈi®I`czÍêÀ(âHø,¢¼H'ï#Ês\$\"×b|ilêH:gñî¼qjz`\0#ñF+Ç*÷0ÙòXÎÏ®ÉÒ|8¦uÒA rð¦ó(Ñ%r°Dïö®Ê¼âÑ Ò22Ð¤ºzæjGú&¬Íò±ÐÚ/b¬V42\$|ÏöÙè%G N²ò~ds+Æ-Òþ|Û,â|s¦ÖG1Tþ\$oëðna²=¼0FnâïôÚQ<@\rÎJåZ\rà/ \r¯¦ Ë¦)Êø÷Î:pKÚZ¢æN­í¦\\âìG«(ä.ël§3pMtïâÞØcâßá ë1hÍó(r°î+mI9³:ñs¤ïèõR¸ð:ö3±8±êã-¯¼~\nò!Ðð<z±GÏÄé=\r<bÛÈásÜkSà\$ä¸§\$q\r³î(æ5°pÑ.Ós\$Sh|;t LðsBr*ó¼¬ÓÁ:ï	ô%Dí;±¼\"M:ÓgSÔtó qCpi«ï­á=ÑÐôÃè°îØz\$ì¥æ2¨ÆpØsÄîÏK\nMÛQb\"Nÿg.âî\nfe)ç>FÞmÎq.¸ÂîC±ó'ïÎE.ÂÛ©FðyçÀæÊA|ª)²ùÏÈrD;ªÝ;ñ_&4ôü4SDCIHT÷IÔTôX6²¶K2²îTêÿ5ü(!\nG>ÿô+%µ(ÕÌ'®	£S©Ç8-OôEP5HíòÞÑJÎ75Rµ\$ÕÔ6xtáõ!SõVâ@û{Tëz¢F6£~f#Ô\$÷r6DæÓè1À&l9Ï=ôOOµòÕì³[´~T??w2²¤}õ¿@ÃSí	VÇ²Þá°NÈFläRµòÿ&ðÉ]H!X1­Õø\$æÐEV	n`u½`´\n|z(L´gÈp:\rj5fZÅ.k<Ù<\$³D·ëMÖ9¶?e`ËdKwVM3ÈþQ°9Á nÍ¹,»UUA\\E°?h0UPµÙ2z9R}\\SEvRvb#jJÃaè%Z²¿k~%7Ø=Õ|tÁ¢®\rÅ^w(³{Q%à¥ã\nC :öáD5Ü\"ëmbõ6õhë:´g¼².)^(8â\r:þöo`äî)tð\$GpâÚBÈ!Ñþ~\rRÍ3%MwD'zöT¬k·6dÂòBFv\0oâp	f259&«OÑ@(Óð)	qw|zE'ì5G\0ØbÖ=B>çÐØñQ´ncBM1Gã´­Y2­¾+ÂÀÝâ°°ÃàÞ-\0ª\n pÈÆ³vOjÔ6÷IèÞ×d9>tùuRpØ\"w2*w±éÉâ&grlVòÀ÷\$/k.UZLäÌÿ1@r(m2ø§r'§xeIVøHELàN­7ËWgÈí£C'ªLêøypw#i£í¤f1x{\"3ÔTÔ°gP`1r9TÖ3(gò÷.·28¤G\n;L±*þß2oyÕBèoºìÔÐíGxÏéQg*tÓÓ!7X{çuøÇíÐÉ£ÐqÄm§Ä?ïÍÕÿK¥Q£mfìºñR÷Å	ÖÑÉ~ïdÕîäÏ{`NîÂÒoyÖ´Ø3ÿT2dMògñRûA#öN4'Úê9oNXÑ\nåuÔàðEf½o6ÔÔmîT¿Z¿lÇ;{GÒ<®ëêÎ°.\"4aN(\ràìJîýÊbèÌ`Fl\$sªÕÒ1nY2l^ÌäNà";
            break;
        case 'bn':$e = "%ÌÂ)À¦UÁ×Ðt<d ¡ ê¨sN¨b\nd¬a\n® êè²6­«#k:jKMÅñµD)À¥RAÒ%4}O&S+&Êe<JÆÐ°yª#FÊj4I©¡jhjVë©Á\0æBÎ`õULªÏcqØ½2`©ÜþS4C- ¡dOTSÑTôÕLZ(§©èJyBH§WÎ²t|¦,­G©8úrÑg°uê\$¦)¦ºk¦­¯2íÅè~\n\$g#)æe²ÅÓ«f\n½×ìVUÎßN·¼Ý(]>uL¼Úêë]	q:ÇöôjtZut©*#w=v½¯ì¨pß=áLË¨\r¶ª?JÒtH;:ºÄâ÷ºBÒ6ÊcöÁ äzù°*\nâüº(Ì:O¼-*¶X#psô¿Å{ÈØBÍPB/¥Åj{þ«B±Zºþ-IÚ¼NÒìÅJGED!´Q¤Y\$IMV§.ÐË<SPw@H<ÙÈÛx­ºmë¼^&HÛ¼­ÉÏôÒÈÅ4Ä6Ø´¯º| /¤½\"AjU<#²¼'Ëë*Io>¯)ê2ñÕ,­pë§,6IÒQIó4¼»Ï»A§QÄU8\$äXGKpþÈMêE>é]FDëÍGV9ÄJW´Oª<Û«uú&¡§-[X1aB*¨ÏYPju4îtã!E*lS¾Ýp«x2\r£HÜ2SÝ ÚQj6FSûªòÆVË¿XØ·mÃÖép»Å{Ö Wjîÿ\$ºsBR²}N@¥ðX@480z\r è8aÐ^ù\\0Þè\rãÎ£p^88ÃïxD³È£=G5ÍxÃ¤×ÞT:ÖJû«mådiÕØPK*:K(w{-´ïÌø¸ÏËÎ­¯ÑPzN¸7³ÄÓî1fç%Ôf¾É¨ÛîëÚrÝw@If­FÒ£'\r­àO+ËÆJíl2ò!s+Ê¢J-j¡ñK9'Å¼×(JOTÕ}òïdÄ¼µEý°¸«4úustÙ´ü¹JT4ìe\"7XÍsÐ³ôf*ìW2u´AÑ8Axãá²ñh>1XRïò·dE{(â©eghvm}«ýâlÅ9Ö8UAù»v¡y1éÌ¼¥RÞ\\9Bh±:°åBÂL©Y¾µÈ;Ü*I¥S'%^R£¿FL É6ôwÛoìi&7eÈPI§°½¸@\$ôI>}ÐÝ+C¦__úW]NF!B¦PájÇpà¸ÁÓÐ^a¦Tmµ æN¡VÆÙº,èjT	ÔÒºrºeJéMxp\$SÆÅLyRr|!§ô®cÌ>!ò7LK0Õæ°Xzi9qyE\r¯9@æ]Ðð¦7L5\r²caq\nR7XCn¾±ÃAZS0rx9´\"lÊÉ§]À\$ÙbIç\$Áf²ÌX.)®^¶TpÆäÁKicJêOÉ³z)àFÉî!£C\nå\"=ÉI!úÁ7¢Za%´»WÑßvÎqN²h.*&ÉÚÇR¾Xê¡ÓÇ´ r5Ë=ù0ð\\ÁMc½M¡ep¸P¦XÅ¾4£ä¬¸Ui¡Ërs¹JFèÂízG¥(åºö#ZcîmÕÆBâÿ|¦£/-Òâ»L\$\"0«yD\"ÔPM«®Ñ·øüÍ<ë½x¯5ëIR[Uk./ÔSá5QÊ¬ÏäÄÇÙ#d¬²¶ZËÙwflÖ«3tÏðd\rá¸0@Ó^Ú4¶@ÁH¢y~x_;ou¯0I8ª2huºìÑ¦#rnºÊêcôìÒR»#Í?Jâ³W¨a[Z6~RÁû(FÌ¹]³Í¡Ñog§ +µH«ÊÆzØãåjèÉÙyek dL2²fPÊc.fÉUVnÎYÛ=gà:3Ðç_ÙóG±Ýg¢ÊEe£yöj¥òºÆÍ[³m/1ZLù{{³öy79Èê¸Ê%ñL¥º¶VìÚð}²6êû©jæ¤,5¸Ç)M©«²ò1¹ÚW@b²: ð\"a)O5ä&7-¥fÌj]H\nö1OqE6/3aá\$\$\n0)oEmi.ÃF=]ªâ`*~\"êÿF2=á´VP`Æ\"ÔteÊ*ÆÝÙ«*r÷O.%~òocV¹Lÿ/w°ÉP<ÚZì\\Â¥=´´8m7â{*ägT4FØ¬hÖäIà!0¤â.Ä}Ñé¯§]0fÈª*AÍ²38;³{%ÉZÚY/I4dïJB4¡#¬iæñÿpr\\3Í5Ú ¤ØÐ@¸|}ÊMõ2gµþ{jµÍF57kêUµWjëwUâv9¶QÔB5+3i·Ïr+V»Ù{;©5¼OÑ²f²MÑ8Þâ:%Im\0 ÂT#l ½Z¡[Ìñý\$|»,x@_U.»ÓØíé<|b\nè¶w;Änd{Êg!Ý*d9A¥ík¨ÛÔG&x¨ 3W9Ø%FN#UwNnµ«²LîKËT aäfÓÊîõ¸Èõ_@¤#Ô]Ö¬n\$³Ý\"Y#ß'µ¨BÑ1§\rìr0øUÊ\0â¿KK6ÊR»¥Ó!#'F\"\$¹ÃU9½Q/\nFhêõq,1n]ëe/¤¡÷A:£tæäÿãøk79JS¨rÕ¸õdõLHÅ7«êQ~~Cã½xÚÚ©Ku+9£\nÜQA±ÑÑ[Ì]æíeõ¾2ûE¼óO÷±ä+ìÅÙE¼ï¬?/1»f\",îFKÞÓt7ÒQ/LÌØqOlrfîÏ¤X'ä,Àt9*fìèÎÂìÇI4O.ûÎ¬-Üq%.¾£@4âÂÃëLÅØ¼2#Ô¿gHSâ¤ÕnhmI\nÌ¦ïPÃ^ù¯Ü7ª#zØÆº/TS¯*%¾ëLÖË¯ÅÄ´é¤IæÀ(²ÉÏ·,ròì&_*äTël¾4áTFðvm¢tí#j6/§ZDÃRd(\\\r<à§l+nNv¥Çí¯NÇFµÑïO¸3èx7¦úu©V¨d\"Á,¦äõ1(Ææ9fï+õ¥\n nòn©§Ä¬\nC¢VOòákÿèvÉt4à°´¶	ÈÍl±nAã\rè\r+ÊrÀÄ0ºø\r\$pH*ÝJ½¥jô*lòQ8þn:TîUPûC¸þçü/b ¨\n`6¤üªÈ,|ÂøÎ4W\rð?Î5GÐcîêð<°NrÀ^(rÇÔ '5!ErN0~|l­ÈÀÅæÊª:´BË!ÐVoqJ.Ì\"ô³Ò*üÄ#Ö#*åªoLÅË3#pM%Jg\$úéìº4²Kå'%2Óñ&¢Äw\$òZl®m§úç#\0[¬hêh7dsxÇüÃ¯¦ö¤¿&O8ÒoÎêH3*ñöW1úm1#½+ÏÔÌË-,Bó,o(iÇ&m:lÑV²\$K]&*Èïä!0D\nÂ2X|ìÍ/¤âtÊ§ò>òBr\$ê©þ!âÍ¬9çåðV¤z1ïÄÃs(·-Ú×tdt1LÞ(q\0Fsr-jB*,.læÒ'BºÙìd¶S !\0bx>ê@ÏÅ²þLkY/ù\$Ç°VÓ4õbûÐü<`Ë%IÇ;ù;füIÂ¯32ÍsÇ/q<Ì¬+pâ+êäqRûs<RöË£ìË,SSÁ\n*>4ÚômmñC*f¨t@4t!4%ò.ÄrPª4CI2ºóM8ÁHÈCN¢yG¾¬ vÁTR/´/ìÊ7«ø¬²é#ërð ¦#Ntì+nF%ø¯PWq÷©S¤ëÍ|LáCGÏz®5CJuP\"ÊR\$tïGE'wCÏ=ïWà¯`ÉKÐÎ:Ãè¥@òù[BòA²ÏO±OÏ·Dão?j ´,ÉPEPñ½Q,gOô:ÿ1ODR²úèXþÑ@±t))ÄLµ?#´ÂôhÀcóRuUÀ>²rC3bòÊKÚvÒ~×ôCUÏ%YV&éVrX²ÌõréÎÆ 5}K1Xû\$XWYéÆîìS5\nV´C3JîÀ.BULUPDÕ<q\\#\\QÍSU5ë>DWhôÉlN>éßS Ú¨Ä<0¯6	¬Cu.ZtôoÄdQ.ºâd´v/6-öR%VÏ|ÑlYLAM\nHm\"[£]VW0ÔðvÏK­~G¯n\nÇnÁ.í¦¿ðáHð´[§ù3\$?HÈ³¦\\tÞFA]\"ööÈ¶ æóT¯U=3ÿR(²£S½A|ü\"Ý]olFñ_qÑSÖIT	Çlv·SvÞ¨gEJÇTcPcÐt¶Á[Åpì¾R1(Ô»:µý[µìÐïomÔ?BÖÕPµÕp×%at¶Ö³¨¨NSoHáf'ø+t6|ß'øj[#Å7'Â)øFNN¸\rÃQOZNQ^q1:ÔzUÉoõm5ÿoéÇ7uâøq/pwUåAy±inõiy5Õeç lµák±-11ÈÙz5SõÞ 7Áió{sr}_³yjj¬·ÊI7ÎÿVít÷©Q~§¤aºQbÞ¢¶+rÏÈÃWH1\\#}hz6Ô¸6Âxw÷¶à4åyEq8\"5å?öòÇ1uôûöÏ<7¥`{3Ý)W¿¡K÷1L1s}Xfñ×M~8qnEql¼æDg¶s³Í/÷õ©ôÏsKâWÀñÄÏ­1vý	óAW/D3jËiÕ|uw.ó§JóæÙÐø¸éø¡S¸qØa(2`æayâ\\rÚb­ò'mvB«o¦{\$cÄÿâwZ¶F5±\$Ãf¨A¶Ö-2ÓZUJ9 \$²ÇE¹ï¶û6ãN³m¾²Â¤VÁ#õuuù-&&ÃO>´ÑÅd9'Õ·1õdÄzé\$ñEx:&`¡ Øl\0x5MÒyÓ?Z@ª\n pÄãf|i7¥	\\VYÛS#ÏÄ{WMd¿¢6í¬Ç`yS{9¸;³;ìz¯'V2 ýxAôK R	Gz.ºGóV¹Ö½øO(¯Rù#2jc¨ûbø6ON£ÄfPæ±s²4c°Z4á2;'sSñÙ¸è&ç<`_\rY¹\nd\rC@¥ÂSo\"Ü0á%ØË8HúFÑèE©¶x:!\$Ãò&\$e5G;úD¼v,ø(<i­/¡¸=öå»«Ö:.<#béúÜÒW§¬Ä??6Ë\\¸Ã\\÷µPâ©~ádpËµbé(]X-¬¯~'ï¯Ãñed¸#vé#iLÀ÷Æ¬iðHÎ\" ç½x*TÂòET[b+KN58(>uð²Íé`84ä/;fASPÞ{Gx¤ë`2w?0Ý\"ÂèGMò\0006;s¿¤1£)Ñ\$ÌØJ~5¤=Æ\0+zÄ,O¦9ÒþÏj\\²àøV½­·ú\\köóÚ÷:y½çI¯3/éÕJTñHÍÖP7_~ä	7,¿ïË	|";
            break;
        case 'bs':$e = "%ÌÂ(¦l0FQÂt7¦¸a¸ÓNg)°Þ.&£±0ÃMç£±¼Ù7Jd¦ÃKiÃañ20%9¤IÜH×)7Có@ÔiCÈf4ãÈ(o9Nqi±¬Ò :igcH* A\"PCIêrÁDqäe0á	>m7Ý¤æSq¼A9Â!PÈtBaX.³	°B2­w1{=bøËiTÚe:E³úüÈo;iË&ó¨Ða1¢Øl2Ì§;F8êpÊÃÅÈÌ3c®äÕí£{²1cMàYîòd2àîw¼±T/cgÈêÌd9À¶\rÃ;P1,&)B¶MÒ5«ÌÒÖÃ[;Á\0Ê9K²±\"Cj\r¤£°¬i©5±ÃK^	Zp£³ãô©l¨Ã\nÔà P¨9\$iËÇP25JHä9n\"9êXä:Æ1¹cp3Ê©ÐèÒ0(`î4ºmä©0#î°`@%#Bþ3¡Ð:æáxï?ÃBárÀ3ì`^8M3Xä2á1ûÊ7Ë\0Ú#xÜã|	1J°¸{*QÌ0¥óð:C¨Ö:.ã¬X®ãL5:Èít ×\rTî@È·	KÐ;F¸Â¼Kh(J2F:6½³m6í±m\rHrE`ê6¦1òÕµãt¿#ª Èr\r/»â Ê3#¨Ù*77SýR´ºÚ#°ÆÕ¼[¡/|rØH<z1\r(ð@3²Àö\$`PßF!ÃPêÅB<qè:Ö,âÐ½0×Çh0Ö¿¢Ùz1\r£ØºXK\n\"`A¢!(vL:%«°+m_W¶=´=7±ªÇºÞºò@Ã(Û]ÔÕ}´;UtbRS¯©UVÇZ[¥Wµ#Óp¶ï¸ÑH¤mjZ=5zB0¸¯Mu-ÂÂá:²mjlÞ0Òkýp¼J°­§ 5DNOË«××Á>wC±4Gz,²bë°ngÏ£Â\$7KT÷|ÏÃ7êu¾c´uµ?Â¦p:Eãü!3Àß£{_ZHV7mí¦\rã0Ì),.C\"{¦Ãpy#/@:¥t²p1\$à9¦åi\0wño\$ìA@s\$*°ÊÊªGfäC~NbIdi-PºnoH4´\ròsN©Ý<§´úÃºPi)C%¢¹1-*yHè§¸\$YX\nrÒ!!5Üád!	TÒZHi\"I-F4Ô°x\nø2@\\èáÊÿiá='Äü 0Q#åócÑÊH9àÑNBñáá¾°Ðªk Fñm*Àå\0`t¿Cb:d,èÔ9 f\$.¢ZLa°6-áQ¯[3F´¡£t¤x3»ÄÆtËD¶ ×ýei3¯LÐ#nN?D%â-X¬HHi\r\0( J§)\0#VJiÏ!\$ÙâËJ)ãêªRÌ:¦53ËÀÞã© 	ÁÜ«øeñ.²ÕÅ³³bXRóF-àà½\"Üê9Öj:wÔk0D¨¢°Çð³0¦3OUQÝÂhBÃ\$b¦àÇLêHºm(T)°ÖØl\"Ëeê¢XKêU,R;Ò\\É%\$ëmäªBMá\r©9* Ü¶Õ/áÄ:´¨ÈmÑ\rÒD*Èôd¯'1Fª _É,á@'0¨ªgeÌH\0SËbKá¬òÖ+` {GèÆº\$P¦²@aêxÊGEnN	OÆ¡V&Ô]cp)´Ð@í^l%!*Nö¢yZ)`JãØd	ÙV¥§öV#h¡m\ni\0;«pÅOðO	À*\0B E\0¡!Fñ%äR¯P &[ã|ïª}Uqà_ÛþHÕ¸¨K©vì!b@}i\0·3Ed»Bxpgù¾²	QæÁ'-¡AõµPNC;#U¸´âòRmÚXUªx¶Ñ2Äåo+wFHÑ½Z3Ny)@ò¤	hÙ¨sµ)¯¶ÆÜ×aÈ­G0æÎ\"K}VØV}¸?ÁM/«¬sG`éá³IÍìíó|x´ïs*D-É§ß\n\0¿0}YkÉy¯áï\n+5¸SüìÚ,ÊH¾uKÐ1mEª\n´`ë3^Wò&K¾ÓæÑ4T¶PïÖLuGÍ)ÉlXC¤)°%î»³´±[zÀ'î«°z¾;íc2\\Jy\nk/,ñu`å°cªû|¿ÊY­¸P`O,Ä88ô°~ë<;_Æ%ÆQò¿'\$²6XSmä?æ³4(DY_\0¨BHñ\$×ç]pJDJ;2Òÿ+I½9é]-à^\0RÞ6ËÚÇUÃÊ£ªÍAV9¥B0F£¤JÖâw0[b6pÜO4ºßó¡BpÜ+Úª}leü¶C/3'çþpjùÞMê\\ÿªrÓnnsÿEÚ¦4MÚ\0*L°%=G#Ã&H÷#æÄ3¯ôî¡ÏjHï<×«ú<ìf3­ø4ÈJzg9ñÿÅöÇ#Ïµ*ºd;Rzñ<Fjë¢dQH)sù~ÕPÅéPº=ÅüÖz®Õ6KúßpÏÇÅ\n¬UÅ°,¾bÿÀU!¤vø~íAæ úû]A½Þ~#'åòcvÚf§3O?ª9AFU3O¤Í)Ïÿqß¯?ä&ÿØ¯¢DdÂD¾þ#üÛ Þ;ìXlª¿µjµ¢Ò-Ä2z/P5kL|ð6!fìü\nÇ¶(-¢øÙ´É\nú£&\"ÈJ-E/Ê\rbØ\0ÐCÇfÂR<3öEiú©)rF)b´FÈÿ/ºþÎÚÈk0oo )BþECY¯¿	ÉoþýÐ	,¶üLøÆg-Ì¿¦6§&.Ê6ÎÒë)ö,X%ìÏåj  Ã\rî\\ÉÔmðeýâ1#9Ï\rpþeâ>û\r´»­º°þ®Ë¸Û¾ÿ0È1µñ0&Ìþkr\"À)#æaâ_ÅÒ]qLR¢F7¦8-#Ê)£-Õi¬ÚBseØ%1\\[)U­ëCæê1J%0\n4Ç@§ÑKD\"1RÊ,ÙË¼ÛÍ£1×6ÿCX-ÍÞÂqXWLX×.üq7±ÎYQ&Áª 11Ý®\0Lqä×G;gX¶ÉÑ((cÉñ× G±Bý±êWf¸Ú¥6òd¢EâU\"p\râl6n+Å~ Ãr,À÷1VERîO\$GQÞûÃO%¦m ÏË\0002Y\r°×N~4í¨Ù+a¦v\"åE8 ÞKV®ÊãÈ(Rò}(ÒW,f{B«bÒ\r¾Ý|0)|1â8ã­À­Å+ã4²Ä3¨Ö¢2»-H7òÖ²ÄéJã `k\n¥ÒäÀ\råÿ	±*ÝHj<15&³01%9!ÆÎÞ*#TXíã0í½ÃVEqöqfJ+îAr^dF,N1/÷1î4ÓE1Ñ*	Ï&EÚoª¾&\0)pÎ-¥½*æâÌ\rÅìðIÌÌCð¥l'ìñ³(óëÓ^ÑffÙ VÃ8e®æf8;å¼YðÅpåÀn±!ñ\0£sÈû\"ÎèäòÀVØh»4M÷ð(îPÆÌêà&:!BJJ¤`ª\n pxÈf©\$ïª în¿JvèîXêÌ;­\0004*æ.ÑB42òÊ'bì'Ls¦ÔråtT Ú&Ó-°^ÌâÛ48,íø7©cF\"`7£q3ð)ÒÚ÷i¨1àÞ@Ð¢ÈÎgHdxCÑ®þÀ)cófzgÒå¦À`nþ;<Ñfr¥c,£KfÂ/ôVñ-w\0Ô.]BtÆÒ]  ÿ´ÝLÇÒ5PÎ!,¢níM´º>bVtÅõ\$Ñ¢ï.{ðÃ¦jHPÂfP'-g\"#ÀÄUC	D`\nT8G¨¨`ê]ssO§\n°C ìÆ¦´&-e:ÌYíür ô6J¢5d#2ÆÔñ`Ë4Ëz!o¦5Ãf¦LD îØü\nCJMt\n®ä2£G¹2!Àà";
            break;
        case 'ca':$e = "%ÌÂ(m8Îg3IØeL£©¸èa9¦ÁÒt<NBàQ0Â 6Lsk\r@x4dç	´Ês#qØü2ÃTÄ¡\0æBcé@n7Æ¦3¡ÒxCÈf4ãÈ(¨i8hTC`ÔuADZ¤ºs2Î§!üÜc9L7)ÎI&ZMQ)ÌB>¡MÎêÜÂc:NØÉ!¼äi3MÆ`(Q4D9ÂpEÎ¦Ã\r\$É0ß¯¾QªÖ5Û©Mç]Yí¨bsçcL<Ï7ØÔN§	]Wc©¡EáÆY!,\nóN«Åê¬xmâñoF[×ë7nýñ¨çµ^¯ ¦ç4C8)»lúlÞ-¸ÞB«26#ãÓr*ÃZ ;ÈÐä93É ¦4ª, )¤N ÈÏ7Ãj·k>cz,90pòÜQË*¡¢Iºü4cJNÚ ºap<4ÉJj0Ðè#\"S*£1[°¨#¶S.1ÉãpÎÈ|;£ãC31,æ;³Ã X8ÐÀÁèD4 à9Ax^;Ñr%-¤As<3ð^8NÓÀä2á\r¬ò ÑÌòdÌÆÁà^0Ñ62r,lÈ((À@¶²CêÌ/mÍnÛ£Íuv¶M a6,º£rÓ¹zlú\nèÀÝ ¡(ÈCÊªÛ×Å!F£`ê6´k2ÐµF|¬¸¯ÌM+\"#¨é¥ÌÈÆúÃg0³wPË'!õÓYB2+Y?2ÀÎâ²ò!¤Mp+Ø¼Ãz86\r}þ'Óprs@Ü>aV¥2¸4# ëo§Hâª<Jâ&a­|0¼h#\\7Y@ÈÀ¢&CxÄÀYe7Ø%pGÊ\"WYzå¯Wa:oÄÍÈÀ¤èª49íéº±¶>ÀÂlU¨(:nªóBôÑHQ@âÿeBþ4ÈB+ ¨<È®cÇ/·n+d`VP!N,I\\ÈJ2PµL3í>ÀçC;*\"0øÂ<ÕUbI×aôj7#ËÓÜÌ+ðÂ¼±&øÌò4Ñé=WÕºFÌ4[«ÈB Â¾óqH2ª\n&B*\n!4W 2ÞUÌ\rÈå9Â 7Ã4(O)7hÙ*ryQ&A¸<¹ãò¯ÓAÆÍ]óB~SÚ¹¡3Dñ×*[_A0S\0Ylz¬Xáã T5d¡*R2Z( K*@9'¸~IQNO¦­@(%¡BQÝG%§HÔ\rÀ½5QÁ>¯}¼CIËÁê\0Ò¤3Æ6\$-J°8Ú¬³UHÊ!jioÆXÎC .Oú\"¨5\n¡ÔJQª>'© ä¥³Õ*±)Õ>åz×ã`èúû%5ª4ÂMJÄ!@@Û/FªTÑPPijGñìbà82EÀD%D6&Ë|0hðJlAôá'Îù'Øá`fM#Ú¦ÂI=v,oYÀ5¦½ó\"õ¤¡ù@\$ÄæÙ` 4HBVÙã¨ÌCD	Ö\$ÏB\nxoLÌ7y+&òH³üb\\d¿\$J¡F0<Ta¸´Êu2f!©a 	1æÉÂ`EâùJBS\nAÑòU  C²\$ä\0B0ê¦6eÅsª@6½ÂPK.(¸`@KùD£@@ÃÉËË}0\$^wèmP¢]â|ÙC¡I?DÁ×0ça± ÂT 1\"IØT¬ÁJ@S÷;ù*&{V¶ômáy3hþ¼Rm~õÉ¥¹ààÉHlF¨µ2xó*p ÁR{%E¾L\\¹ëL!¿\$äsÉ46ê%D\0ÈD}¶Á&ð¨P*VwÂ E	ò¢ÙÚkYë]¦±µ0tk AÒVÓC ¡3öNÏò\0>Ýü¤êÙphbÁá b{ÀR=oBÆÐðÝG ¡N«;DcB	Z´J0£ü:«K\$ø³aÆüÛÌtoxÖÐ¤'ÐéÛÍdÉ(\0 ¬\\^[ZI\\+\$,Ì(:xÁ®¶ËDØ£/ÝHºa»G)eÛ9s nAö&íÎp¨è\\HiLFÂUW ¼(ÍÒ\nß¶áÖVZ`^ºrJá5ï\$3ß:;älY	¹­\0ÏÐêúÀPÃ¬½ê«fÌ³áBfz\ndlÏI­®±¬	¾:°\n¨£Úæ\0c\rdhÓ°CZX ¦m,N¾¹nSÍ\$Y¦ôvz\\ÅÌ72@Â0Å:R7YPÒTnuvãÖÝÎ·\"Þ½/ T!\$Jû<gÎÃú¢)¹z.\\ÐûÎ@-ê£ênW(/\0)rºcW\"ßáÎ£b=S	c_F2¡üó²{á®N× ..ÿ>ª¬8Ù+ã¶§nEÄ¹'äÚÕïÎ)Ùv.13)Ü¿{¢¹x/*\\Àó(Ç 4å]#?S±Ñ9ØéüÓ©9nBf)L(;à2&E_r1änUv`F'¥9Õ3ÛÜÍ]{¢ÔÂÜÃXv51«­SÛ\"D[rOW\ncè{ÅPéÆ²3æågÜ0Oa©Èì¥|Hpq:\nÅMªLÊ´HÀ4UïåoZEª©Á4Fï0ûeîE­§Y·¥ý¡ç\$|rk[ÌtL_KÞltÈ\nT+ïÀävK³·À¦aD@l\n1}Çbû¯æ<kÙoäc÷/¬[	ôíïâ\nøK*äã\\L\"6ÕAZÕb]âÖíV'L( Øã<N Ú^%Ô]\nd¤Ô#ævE¬¼>OÞõïPøTÆfÂÆÊCIÖÃä\08«®Æ\\V¯£úLwpRøNvlðoPdo°RmíÏIºÜðpWÃ24nn&jÇ¾åPx0oP}°	ã±\n%¡Ðjõ.Äà¦×¤¨ùÎQxº£G¥ìö¤×c\rÈê	´¾%Ø±Oc®C£TlC\$Í¤¢iv)ÎìTãö3\"Òd÷pô*ÊÔZB24PãT´;	ÀÊÀ£qåj9¤;W¢pËÍ0ªl]cF»É°Úösè[åÛÕäZ\"/0I\r¡æõ*0Ç1y0OW&å00ämQ¯¢ÊÑºÖ¥¬FÚC ËåâZíÀ7&iÜ1¼íêàk°¶àÊèÑ§ð°pðß0BO1ÿ\n±¾I¥ÕghmìDÐ6é5Ñ ÍÃ¤Èr-¢Ï±Dû¬F9¢@Êàf\ràà&%`\rrTî4ðßí&²o%ìú-ÿ	¦rklúõm¨ÙC m2ÚË£ Ñ-²¢Ù¢I\"%*Í*R0n'g+­,ÄLPâçPyVÝã?±´ù`F@ÓÒ«.*¨@0iÏg\r	crí|Jë8d)KÇ¾X\$Â\\¬o#\nJvo1-\"P4áúðª\$4X2\nS\$?~å \0{â¯o;äer\rV°Mä`§NkÈâd¢HîG\r§X§>\n ¨ÀZcnMPj¹\nö®F8ÇQ1Ñ\r:Ë:Nq:{G,!Bi\"ðÆ/Þ©ïû2'\n#f*þI\0007\"BohÊÍº6ÀEóÞ»ÃB2*ö`I)­\0[åBjºì\0Þ\"D\noãh]¢VãÇÆ\r=\0±Då®{QÐe£_¯;&ï\"n¶í¾ðåCÌà{SLhÍDãÃE4BïUC´^6t@ÓÆ(CX9òpC²`3\"RÒ\"n<BßFÊ]2VòàkACíLûJg8g!üfð6gT°m0z¢æe1ä(/f8Èî4]lHhÈÍYÄ°4d(-=kî7\"è8qÚ>´É>ÔM6ÈÙ'Eu0P{´GPFÜó4Zj« í:`à3Íá#ÅBBVd®	\0@	 t\n`¦";
            break;
        case 'cs':$e = "%ÌÂ(e8Ì*dÒl7Á¢qÐra¨NCyÔÄo9DÓ	àÒm\rÌ5hv7²µìe6Mfólç¢TLJs!Ht	PÊeON´Y0cA¨Øn8¬Uñ¤ìa:Nf¶¤@t< ¢yÀäa;£ðQhìybÆ¨Ç9:-P£2¹lþ= b«éØÉq¾a27äGÀÉ1W±ý¶Þa1M³Ì«v¼N¢´BÉ²ÐèÔ:[t7I¬Âe!¾í;¼¡É²ËZ-çS¤Dé¨ÕÎºíµfUîì©®îÄFôcga;da1l^ßíôBÍeÖ64Ê\$\nchÂ=-\0P#[h<K»fI£cD 0©CÊz¶° Ê9&ãÄÛ¬ëHØ	(æB\"ÐÔ##ÐÊ\nhÒ4@æñq¸©0ÜÎ!Ñâ2À¦oè2\r£HÝ©ÈHÈ¨J*\\ßªà\roÌf1F£H@1£Ð@4EÃ`ÂãÈ£0z\r è8aÐ^ô\\0ÊF\$8^1axà0cîxD£ÌQZ1¢rÌ@ÃXã|\nËæé!nºú7Â0<:(CXè:¨QÀÞ=\r®XÜñ:íKV:×Uå|ñ!1ã2Ðîch0ViBÐ£\$Î0C lÛvèóoÛC¸:20\" :(Æ\n%ãjf:IdÈ º7c{¶{×idÖ35uS>A\"0ê7\rtåö3àC;áÕ6UZ\rc Ê§#´DÐÄ/9DØ&\$íÀ¶µo\"Ò<]}÷ÆâäÈáÕ9#4öY\n\"d¶L¥ô\rëHí.6mPÞÛ¶bw^¹[#Óå­XÚê}ÄR:&ÖÎ4m0&ÐTÀÓÆë«ä×Ïî|7ÓÐä<¼a\0ÍçÖà¸ª2%)àèù	#l°Ù£Ç/íºØ.Í\"@Rÿ6j8@6£9S²ïN#vxËRTÉ`9Ì2v7ÃHÏ\\*kOpSØ£¤BÐÃh¬O9©Èû*ËúV¨ôþ¨lõR­æF!Eöe[¼+ÑH4\$ÀÊ:9cÖ,)Í(¨Ú¹R2\0G«Õù ÒÃb´ADôðÌÊS\$A<2\$¤|nËL¤äÀº³2	;AÌÈVÑêL1À=B7¤uA2Å\r<´CqÔ;`&È8MCl!¾ÂXB ´*%²8a´4<C[ä?Á¾W[¡<I/~&:Ø^DbRÎ­)H'aLc2ÙX´xDOa	7Æìí&f+Md1<\$:µáÂ#©Á9&¶ÂzOù@(%	\"I\nQj42ò*ääòÉ 40àrÖë·T¯ fLQJEÈþObâÏãnN)¤IrÕuÂµöÆDpt3\$õ5¦ãK\"êl}2Ø»\$ÄÜséÝ<§´úÔwPj)Éä¢`/*¤7K@Ü¥ÃÉ{åo*^;«~d(¡tàj\$ó-<¡ñBL¥\r2Ö:5ÀÜJËs¬i;S,LRh©\$A&pêÈ¦	9ÄÎA.¥1Ë04AöËÈâ÷È-!¨Ô£Sq:*b&jNBÃPi\$FÒQ\0@öãÞ4ÊIÖXi%E4É©+%£MPÈ \n (TuwRDÍ0ÌttBCRÖ2»bC=W`Ô´3Úã3hQ©q4íSõ¸f:ÕIöÓ\"Yd¡Ñ8YDÏb!t±5¦Ò&½±w84\ne0bß	 aL)i±)&OIO'Y\nDÌ131D ò[#Z§¢Ô bLpÉS\n%ôb[E\0­øÀHé0ßkäkGim¨[×¼C,¼24ªOq9 D(bîºaVðÀ)a-5\rE¢njÐP	áL*7d8ÔõII¯½XÐ_ÌÒ}¨7~ð^çµ44°%lY#ä\nÄà¶Ð@ÚI1áÉÕØñ|üÈ\"äf×\0Â0T«§¹sÔÂáH(E CÓ\n?ä²â\0ª­3ÌIoÈêÐã2 s@Ù%kÐÞb/#¡ÁeöÂcÊÔ+ÙZ:6h´M¡1EÎZØ¡Êu9±2:Fñ Xegã¨~Ié¶\nÅ	¢ãbI¦L?&6ðíû!WÔ0²ÐÜkP~ÒIÎ6×?¨Û	òW5Ò9	Ó¡ÔAvMÃ6z`ééÜ©VêíHÏOv°BM/\rÅçýy±Âxb·p`bhb²è­^`Õ<FNIÙ=@\$ôÃ®lÂ~¬¸Ò\niÅ~Ì>4ÓÌ:t¤\\ÕFÈ8rErÄË\$EtÇ¶ÛFZAh2¾sO_ì<gF¶±ÈÚRâÏ-PÔ>/´:#¬î|rI-Éb´ô/9<­	Þ.u·&^µCbÄHo3¢<éÉ´ç¾ðþ­g@4æ\n/Rð\nÊ#g©\\ñâ¥n½P*\nuÞ#¦U+íRÏD22\\ÄÛDpô±\0/*Ë=² Ö¹}5Á.{ =!QÚàG<oä^¸\rOF¼¹«h.i§x¯Ý#ÓLG9Ç	åö![^]ýEçc§hóÐxÿF=,hÊÇ0®YÌC7Fñ¥´ÞÄKû¿ÊWH.ñª¥óþ;ÁC¿uú^ÏÓ{_«æ¾¿@ðØøÈðe~ú8Z¦(Tî&`Hß}ddÃü¼TPV ZÈH¬BÅmz î¢>ýÚÈoúÜhÌHDÇ'£bv¬¨C\$ Êà´ß%d¢Àê×P(ÎdX£ªÆ8#Ñp5âîÃÄjhÆWE <J<´@ãC\\já|Z/\$<Vî\0JÕÆµçU\"l;ªH\$@¨ÛuÍ6V:WÐm	nlªWÐ¥	P~>ÎfÔÐ ÌÀh£².jN<,\"Êà¬`Æå&Âà.ÈÝÃÄ(âø\nEváâr¢ÀÈ&Pär6.lÌFÃØ!Q\0ç\$Xa¢.n&*ô¯N÷m?	Bâ4* Ø®(\$]LÆbÇ &kLÎdÆ´JXbÔÒÌÏ\nÐ.í=ÇÖl\ruÄ Ô°Ò¦ïð/îhØ±iÑo°®9²/¢ð&\$oçW¸MÉ2ðj÷/*÷ÄjO5P¬A¬ZäÞòQ#õHc¬ÖõÅ«\"ïqG¢+!E|÷¯WËìÉº#ÐòCp ÓÒ\rÎW ¯KC!rÉ Ñ\0Üy ÈãÅ-ÃÈ§ ý]lÈ³çZaBP¢|¦kU¥ÄÂàà\"¦Ò^¦ìÄ]âurN%«\$#fDx+v1q,\rÎ(p®@êä&¬¢F*@=R\$åR®A&Í±JÁ.0Ùì:Rr]¥ÞkF= ÏQ\".à²\r0£-É-R\"ç<&eQî²¢Îqç\"!erô ç%q%Ó\"ùä©PâæÌ\n?/cfmmLëG!0MÄyÍÊ0D^¦]à!|r\rtÑ~ÝfAE/ÔpÃrúÝÑcÉ,â*±3a6ð­1qy®H#gbâän0ÕÓbX).çq9®jäXÌvBÑ%âÞ^S<æ©6rsºçq\"våÉí<.Ó8Swó¼çHîzÅ-^ç>²ÍP?d©.3ØØ³Ý1î>:MO@óê|¡~rDNëÐ'×/ÒÛ#4&,Ô*¸7C\\E;\"Ô.\r®AS5ñäZF´2ãpyÂØl\$·Ë³R«RùÑâEÏ5FÇ	G	<ÝqÙ:\\ôl\"49Tt3(×HÍ`\$@\"vA%Àh`Ö8CýHa7l8£î,Ñ·¦ÃÀHÀd­=~t~·â\"2ÔÕo-Ïäh\rV?âte§¤¥\"vNlDcLC'h\"o(6B²x¢r~îØi:Û(\n ¨ÀZ{,z®ªÕN´ÊÍtÞûµ>C«RÔÙÔN,rÕARÑùµGëF!B!ÑòNUgA	ó8chC®C@]R7\0QOv-âÚ&aª#£n2óµ#\n-ÊK²H§¸ÀÏé[¥ð&¤Ö«ìÎEã4\0'L1N à¯ç\nË	¨ßC>õ0´hÑÓ-|bh­_µïb'`à%Ö1mA^Í»_ñ_`¶\08Ç&@ÂsT)Fæ@Ö'aêJcÎdcÅXFnèt,\0\$2APÐ#PÔ'u0f>Ûöf.Ò\nÂb¨ÒR' Þ~#gpIK¢z7ëbð~=Bû>qi\"¯McDoP\"AíC>ä¬øÐÒ\"*¨Æ¾ÒdlÙ\r\n3#©i£aV'PGì6Ä¹§Þ ¦É8s¶\0";
            break;
        case 'da':$e = "%ÌÂ(u7¢I¬×:\ró	f4À¢iÖs4N¦ÑÒ2l\"ñÑ¸9¦Ã,Êr	Nd(Ù2e7±óL¶o7C±±\0(`1ÆQ°Üp9GS<Üèy8MÁDYÁëÃÎCðQ\$Ücf³ö2 Äâ´)ÁÌØÃRN1ÈÃ7&sI¸Âl¶«´¡Å36Mãe#)b·l51Ó#´£lg6rYÄÈé&33´1°@aÁé\rÆI-	åræÌÉº6G2A]	!¼ÏÄ4z]Nw?Étú\"´3ÛÁ´o´Ûb)ætÅ3Ë­Y­ÁESq¬Ü7ê\nnû5 P¦<¨Î¦Ë´®&ìpì7 Ãz,ÄPà2ËÊ	+	»b\rÌ&ÆBÈ6±ª@È7£@ÿ°c\"8;-øÆ1Æ©\$ß¸ÐàÅ¸Ò:\rxêÁ!\0à¹c¼0ä¢4&C0z\r\r¸à9Ax^;Ír+!Mk3ê\0_)r¬0IÐ|6±([31-L7Áà^0ÈØ¬Ò\r¸ÉB²ØÖ:£aÎå&tÝ:â!c°Ò2àP<°n\nãä71£:\n\0ÅV Mq]W(7¢n@*,ý÷(ªeM2xÞ-Ë£fÃC(Ì0£c;1ã¬?EÔh#!¸ÓB>\r7[c¥hði{ô4ÌHæ7­Òhù»+¶68è´@Év:¯å3%	\rÈ6V·=»\\Ô`Z9m(h×~Dñ6B²ð£©QSÈ]åCÙeH!í'¾¬hè0Ôy®z(1U32Æ,BÓPX×É­4\$8H²hÐ\r±àäSB*?­4ÙÃl²Õ!R*(	[,3ÓmL¨-~#Í\rD#rÔô!ÐÀÏL»Ø|º<Ë£\\¢²ÌÏÄ¨LpÅÇmØ2	V£uê(Ø½ôÔ6:Zo*Mtª®àê54!1­%4è_[ÔÐ³'nèXÞ3Ø¢Ä.¸ë}§+Æ¢:ÇòÍn[9Ë¯0ãÄ×ód2Sâ<£\n¹Ê:#b ÐÙ#îÂp:ÈN¼]6&òÀÆ¡	'	h%Å´ÓtLi3¦ÖÓroN!¸£3 à \"ÐXà+WFÐZus\0¾&KÌ¹ â Å±Dd0É²:ë¨a_ÅÄ)ÍÂ¥Ø\"fMÝ5\"ø!sDB'ôZÉ=è¤¸g\"ÛL:~Î!@9/8¤XÁäÖl H.p#´zÐL|.vD,¿to_ºGÐa\$utBãte=êåõ5ÈêôS«Ó7ÏX ¤¤Ids]Ä-krLcË©§S5bk(©\"Ï:Ò^mÍØ \n (ÙHe1HèÂS{Xq],8É2CtI¢òº®[6RhàßÄ\0gHØUáå÷ªä\"H!yÔ\nü9FÉD9¥4âRb~\rÁÁ'¥\$¨¹á 40ÐÛLZaòbN-Âk6fÜÝæ!0¤k¤!åæh¹Eâ@H!²2¦ø35äP¾A¡É (Ø!ZöÅ6òtFÂI.ðÏ+¨Mg\nLRdÈ8®&ÞaWà m2ÆDËBFÉL®Ñ×êxS\nÊÂPßJéi»~\0ÎdNiÂçßÊ¤8PaH¦R¸&®PCIt+ÀÅ©:»r,á­»)*á!-rMÒ0o2QÁP(C®H[é­v Â`B12YõÎm\0RûkdÝð¨P*VokÂ E	Ú¡U)B\n\nÊMµÄ\$ej`ÁXÇ«)¹½Fç¬.pØ«'Ö»)`ÔOÝ	3Ê9Ðv|Ì*6'L2 Ôr|SðR*2ò^>ØÓ/C²¢ô_-Õ¹)Æ*¦º6¬p8#,¥¤¶2t±]S}Üè²#a¤*0³´ÀOú\n¸´ÐÒûk/HÜÓL°pÃTÎ¥ ØW½©ZÅ\09¤ÓLGZài1ø¿ªePÚr~+9fDÝ£´a±Ò4,,±)ÆZy±,A,°``^Tb^²ÁÑvÓãtÈüÅ.\$¨OàË9óLÛÉ.ÙRÖ¥r®ÀR%æÉf½SÀQGKxà.u§]=¿1Åm Aa T[X]ê7\rèæ;Z°ÈOC(j7Õæ2£Pá¦§Wà¼¦«óT)R¾W;Öc®^>n&'´±±YJFS\"ü\0HKÙði5°Aµv¾ËÛ%#_]»·éÁ÷1\$iÑS_ØF	¹#VÔ	`»e\rý´&åàDl%Æ¦öäëEW9 yMpÊ+±ÅßðÒ`aÊtÂÑ­rd6\rd Ârð0	!«*Êwæ©,a´*Òû|½ÞyG¾],9rûÑñÅ6ã|zÑËëÉ*KåÄ\$)<Ûv.8\nO¦S¬°²3Åtòè[.[IqÑ¢EdI Ábð>!Î>â¹'Ê§SYo1+;Ô©¶Þ9¬To:IIÀcþAXkCá:àÌUg Åç¿}ªõÜA:EòÔ) @Uda#ñé»w×^_i8»oIö^óãù¸&ih¢ï­§ºMNëÛ{¸4íîZTÉøÿY­ýZ¾ÞïùV9ÜéØú?_ýv9¯oáæ;/ã?¶Òoíä¾LÈ`ÊcÊ­!J\rF·â|~d Ü=ê¸8KfòÈöäFao¶m¬Ð0\rÄ°<«ô K¢&DlºP(BuÉýFýä>Ìðc&d&LØ\r¤ÎP^REPé¯ðe«åÀï	¼TðèÏ\0'<»U&¨ì¬*øðíÐ¶8pº@BpùËå)s«\n6ÍC\r«¦ng#¸jîTðäÇãL	\nVR,zNßf®åÀSOÂF0Õï/¨ì3o¦ÿðèíÐÜ\"åÒö1.ü3§	fÆzcúö-G,Ð|30ko<lî\ro|FÃ.éâÂÖéñbèH1æ¸rÏñ`~qd¨HZÃ@ÌüÐ\0¨ÑK>ë¯r~1 &PÕÑ¨1m	\rP\rz91«ÿ-C0ÑDoq\nÃðB­	.Ì#qÓ1»ä7CMÄ>àÒÉ©òM5bJ#\$u~>±:`Ì/Äì!KþjJÛ!Ñ¼	emf0Öcóò4(Ný/ºÞ\nUÍØÛJù@R Ø`Öb`Ý¨¿Lò(Æï8Ê8\\â~(0c&Ã¯ ª\n\n\" Î?¤,V¢6Û\rÛB³Pûò 5K*VÿMÄ¯EÊ+¦ÈìBí2&jþM ¥Ã¿£¯-Ì§Ê7ãÿ&\\(,\"ólFÖRBÄcà>#Ø	f!p\\[¬0Nß,,«ØöçÔdkVìî¶B//2¦tx%XíÎÐÅ¢2lgFF%3S%4°s\"í3&)<0n\$Ãbb¤ù@¦hSbÅ«,Ð¤ ¥ê´Ð*!Ç6B8ÖcöOR­d>òú&´*ò¿M2Çê%àìGCôgpÉf¤Xë¾Ó\01Ë¯<c8Je#Ó04ÀºãºF%»CLXsT,J¾&àîÊÍ\n	Ú%0Ñ³Jtñ¾\$¥";
            break;
        case 'de':$e = "%ÌÂ(o1\r!Ü ;áäC	ÐÊi°£9ç	ÇMÂàQ4Âx4L&Áå:¢Â¤XÒg90ÖÌ4ù@i9S\nI5ËeLºn4ÂNA\0(`1ÆQ°Üp9¡ÇS¡ê]\r3jéñPòpv£ ç>9ÔMáø(n1¦ú\$\$NóÒÄbqX¼8@a1Gcæ\\Z¦\n'¦ö©X(7[sSa²\$±NF(¼XÜ\n\"ÚÌ5äM¨R\rÇ6Êe]ÄÍ¤<×Àµ#(°@d¦áDM^¶|z:åÍgC¬®×Ü®©vÜ§ëûDSuÔïµ6-¡§lõ\"ïä¾¨Üâ¡*,Ô7mêâ÷À+ÛÜ\rÃ¢5Áãä0¾ P:c».\"¨ÜÚ\rcÝ\n¿\"26×J;)ÅCZì<ÁCjäÉrÂ±¸ã(0:NÅB`Þ3­R;Èè#á	6âÈ6­OËèJx²¨Hj-©ÀÒ3 cêºTj\rãCRHÆ\n¤ëÁÁèD4 à9Ax^;Òr.513èh^8Jl`ÈJ |6£c@¿ÌbTÇ\"Hpx!ôdÛ2ñDRDpè9¯ëSEK2n¸»£¼8¯«Ú\nÊhïdT	Ã(êü¸C*=B»d7>ðJºCÊ,ÜaÊ ¯{:2KpàÓ\rãmI\n¶PòÈ7«øÖÁ^ò@­Kµo¼ø(-s|áD\"@Pú<cM[,éÀÊ3¢¸³R÷¯oc)£nÐC&>3UúÇøÐÜð\rÃ=â2 Ùd£°ö+àmlÅh»íN64Þ×®üØ­c\\¹\n\"`Z%Þ©Sê7å/²LÉctmd¡¶¦ø¥V¤÷m];Í¼å³3c*T:N¦ø7¬ëÜ26¾¼Û¬n¨OWÅ±¯G<rr]b;­r[0¢Û¬!Xrâ¢l@9\rDÔå\r8m~ÞcZ:æÕõIÕaö°7-#=~2÷!òÔK\$°x¨íÉ.Êo:m/çòè{ì'ÜKîÿ%×-Ò\rþ¯-5ÎÆò\r¯êM¨ø½0ËD®\"bÿKS8e^¯ÜÑ/\0Ø®ËÒ//¼7¦ÀØI AJ6&gæx^Ã%Ê¤6pØÎÒNJ¸fæÈKØ.]Gø¡ ÞÉËÑ'ÄxõÒKQd'Á¸ZZPlÀ%4ÐB\"Õ	a9ñOõü]2%® µ2ë´&è`¬:\rø:âJ2~ÇX2\$X´¡ù\$E=#kÝJ\r\nC(£rêI-¥Õ,ÂI%@Ê!áÜ4ÐÖÖHp!x§\$ÿ3@Îãª×XdÖ&â4³&§µk\$U22u ZSÙBf^V	_\n\nªC¨£TzRr8)u2A{86ÅæK*zãÍsrO-=Ç¦_¸h|§Tù¾Øh\\Z!Åð:T8^É\nDÉ1ÉLFMâ¦\n (Ð¦G¦áÃt®ë°)j(ïH ³ù6YJ}în%G\$ëc4jiÏ%§3Xsi¸±ÕZ&àæGPl3æ.¼~ã52Å¨ ¸Sý5z©}¹DÇLµ ´ÝðS\n\n )'å#9&¨>ÆÌº4éê¨tcf=Z*¢~Ql\"H9¼ëÆ Ê¿^ª¬|WP	e02åÈûí7,ÔÕ©Äôlt0]éTÃ=ËÃ¬\rdébHÂRÇl7ôÍkñNéÌPu!l¥¹³RY&!*6¬ÏbQkbn&Dø ³7ÒKª.§æaÆfÍø\$p®*ºB\\	Ôø\$\\=§¤÷ÕÅÈm£V¢dlR Íâ\\° ¦Ã,öÎÉ> ï\0Â -a,ºÏRpÉä*:Q¬==ðO99'dõê{\$0ö@sË]Äøé\"ëÕwï_×¹HO¬Ø¶Eh.à{ì]®l1Y@ ráéLÏ¸ºÇTVÄmX^Ä¹W\$I3\0mÇ¡­¯f'\0ª A\n*@.[=K½Hµ35®\rªIøYâð	áÁV2°Z\r7Á:Âòã#%DqU²¶î\r©&êÌ6[Lº¿ÇsÏBý­Q\n#5ÍÙ²':ÔPA¼³'¨a²\r&cM èEQÌnlyã§§I*P-©§D=N©T¶\r1ã³ãÈyËÙÊ\$/:¯2\\p:¾À)À×lú¥á[¢,´´6ðâ|bi§ÈÀØS:@SWjôò@Sqz`K;g,f¦_Ðþuzãêu/;·6oe\\/Î#¡!{ÉY0Þdóy¼7Á½»RÉ6Óîx*&2¹i7#ª4rô\\x.|Ô1Ç*àfLµ³q\$(bqJ½\$ã	fTÜ¯Vå ¨BHu¡ñÁ±Ýæ}Óò£ÄÉ}Ì!ïT)ã%×YêéåUt­¶ºªÙ8§uËÈO©u07ÈëwTf¶)ú6cßßôÇ/OÞË7W~0ôÓBÖ²*uV«Ë¨Õ¼^q&71¢õäf_1±\rtößQß½Q±½û\ncìö×¿.=èãyhÌ6&Ù#íQâ>»Âä5±õñH#»árÄFr\$X¸Î@|þ¯ÁáF]¯2òþÌg>	é¼¥~ÍøùD;Ä­ ÒB|#¦vÃ6ÒÏ[oþe0¦Ð)Í8e¶t#HÊò@Î3æ¢Xð&!¢ZÐÛ\r08#°ø2sP6õfèâHÏHiåMåàBØÀf¤ºY6&fgF6tgPÒÄ/¬Ø1Ìêz\0êGP?Æ<ÏìÓð6PcÅº-áiÎg\0Z^D<£?ÀÐGEÜüÃnþe¢g°\\^\0\"C°Ù­/0:Ü\$\0ÃlÑÐD'Ð9¤TB°ìêôÑ¤çÐ¸ZC>CëÆMââ\nG\0001ÄïÏö/&²/*îÏ66M^1\$p1+\0|ö@èð®òòï'Ñ<Ôq@,oL\$:0¢Y°=,ãqlÉÑq«Ùºøf\r±j!Qn5ÂH\n%®5 ZÇàî\"ÌKc\"^«Ø5ÇêòÃlÞ P:ÈhtÄôìcòÇ`µÅ9\rK/âp}éÌP²Á¢Ø2eh\r±µ¢{1{9XâXÕíü^.ã0Ò&njfnBAÃnY­¹Qm#?#PñEYÑs¥#²Hêâq&¾qrE%(5¯§ò]²L5£?&Ru&Jqs%ÒTWP¨B@¥eàÐ|z¤>o~¬Ìÿ}\$\r*\$G·Ò7Ò¾¦²yc>bfÌÎ®YmDÔVýbF)-HmF,ÈÖêrzÕ¥äiçP\nGhvÆ\n-Nä¤mÉXMFdK?1k£>åk\$²Ñ1îS-¥¦æ.^TÒ¹âI3g21]4rÎs°X^3N?2ç4Có.çèQRêèòZ( ó3ñy7\$4o7Î¥2ÓT3à0ö³,­¢â\nàÒm%xà]2ÞÔªþ#Àè\rààÎh.³®¡(3¹;ÃLæV]*x3LÌIü£þ]'\\1Ã%^óòev\rV	h2£À}£bÿ'ÀíhhÃÅØ·¯´¡qÒ\n ¨ÀZ\0@ç¸¢P\$pÂRÈÐïÀÿ1,0!ô:ù°½*`î¸ð¥B2Eã4\$râÎ0áÆø¬ê./ð?§øHÃFÌvT0tVÁ@ó\$ÐtB!íË\rbcðC\\Là%´\r¤ÀvîI7\0\0K\nOPÞc\0002S4-2&æ!,ü4Á£;ÉML4Kí*\0PFÖß­AOLw;K¼mböotæ@ A#ÅÚ|´À@j×Pp5'Ö!ÂpÚB{àñSì&^f1åÔrâõS£DmR`ë	c@ÎRÛ-Diâ¸¢VG\r²:E8ÞÇóL£-LêrtÔàÏØm#Ó´Âëté\"â«u9¥è\r²¹ÔÞb/Zf6äÐüb:víÌLçä¤Òês(C©²¦";
            break;
        case 'el':$e = "%ÌÂ)g-èVrõ±g/Êøx\"ÎZ³Ðözg cLôK=Î[³ÐQeDÙËøXº¤Å¢JÖrÍ¹F§1z#@ÑøºÖCÏf+ªY.S¢D,ZµO.DS\nlÎ/êò*ÌÊÕ	¯Dº+9YX®fÓaÄd3\rFÃqÀænÏFÝWóûBÎWPckx2V'\\äñIõs4AÁDqäe0Ì¶3/¼ÕèÔètfÅOåê¥j,·Q#rØôDI¥½jI\rQeÒ^DÅAüJ¾­uC¢ª\"\nÎºÓÔM¼s7ÊÑäñ>|®íw2ò¾U:¤©RÎJ.(´¬¨Eª,Z7O\" ï(¹b<K¤¦42·LN£pR8ì:°´8¹<,ärªÑZì\$ì¢²39qÂÍ!j|¼¢ªRbõ¶ÊZ÷¥¤\rCMäròGnS1Ëú>Ì¼éj® ÄdÚ¨Qüo(ÆÒé Ð!r§¬{ÈL¦qvgÊæ%ì|<B¨Ü5Ãxî7(ä9\rãÎQ±Áo	N-ÓÇ\"J¡22q0¡ÌZ%	ÎÚ(Q±4çÁt¥H1\$\"Ð\"TÆrx^¹åê\rJª|Îq8¦!Ó%+Ø28# Ú4Ôp@2\rã(æQ#¢:#q#ÇovÀÎ6ØÂ:#RÕãHè4\rã­Ð\rHæ;Ò# XÐÓÁèD4 à9Ax^;ãpÃk[p]HáxÊ7øçà¡xD²Ãm\"ÕÛ5\"6ÞcHÞ7xÂ<ë¬æ]×Æq}Áix×ïP3æqehv^Ô,<0Å7(¢S®j3Ã:AÚ²«\rk)D­®éº{¨Ö¦q; Õ\nr|ú©3»ð+#Ýw ¡(ÈCÈè2;÷Á[ÖA=PE+²TT£0è'®¬%V­i©gÊÈ6HÙúb*(s¡YÓÁô(Ý3û ÄÜ´³Ïj(ÔD3y¦ÓÔÂÌ[Ð®¨Ü1	¼èõÎvÃÐY%4¥¯³ÓýFÇòÈ19)ª©\\tÝu*©\nø¡ÐÚÿiEÈ©)Kø®éK®gÊyYØ?BÚÒðS\n!0?µJnCJ%©±ð%U)ûXJ-i³ån3¡g\r]²A6Ìþù0F*ÿ¶Ô¥Ôëæ1äø!2ÛÌr&Â¤X/TÊ²N\0GC(aQkîñ'¸Æ¥Ú&µ<·\$N­x)q»G^Ð¡õ®ºw,]ÑE'\0 1v.1Ù§Èhuk²ÃN«z^\n1¼ ÂYË;&ÑÔ9àÍÃ0iñä2È|kØ5Ø\"æ½Ca«êEFé1&üá<80Ü»Y¹­s%9¹ô8+È0)­5gøZEqOØ	¢bE´&nÔË©ôC		V/ðFDÓ0JA2 (6@ä­yÞ\$éPÌ\"\"¬Î©ßSë,t¬ÃdkId8×`\r©ºä]ò»ÂXSãQG:ç4ý¶®§Ê3û7¦ê{È\\'kò`åÀÅGZ]æ1(»£3Üdj\$b0\0^ògÄò7dJziàéIØ-§xþ¹þ]hD ³®r­6Ì ¨[G¡Ä^4J&þ¨²\n£\r±XZÂ!Ô§&ÔO¦\n_v4¤ØBÒl@aPë} Ap Z«]l&åHiiXkb,M±v2Æë#d-É<³\$@ú¿3PÜäqgé¥´i²IäóIä§d°ÄuHç\",zè¥G Â!Æ)tEz¯12±ðe^fZ³\$0¦ÃbYv4Ç++L²)JÈåEe]4Ô6bÓ¬D29%r¢UZ¼I9ª*ÿÈl¸.Óâ¨,ÇÈLË;£WÇ¥,¡Y~q=Jç¨PÐ]Q¿.gbu*¹]J¤\$*V'Ã ªÁ«]6òc5aÁGUÐÚ[øaÎ¯0êº`f¸D6ðÏ\$ÊûìÕÉ|.\r6dv\0000ÉT3¥ñ\"RÞby8sÇ\$_@²>ü¿VX-dÁ¼|ÍòDÑ+\n (\0PYKE6y3Ã¶wR\$î´[iÈc°ö\0Hv\r2d3á\0¹Öü\\½taêwÄA >UPåS¢)T¿5UÃBf}x6ºäÅ8/æ\0À roáÜ4Æ#bKé`ce,îùY1xñKT0!0¤ÀÎR'»<çê´)*ÏÒ{D3)u}M; ÝfOô%+´¡¬£|ã¤\\ÖºÑº»%÷ÖëeÓáW!®éQ|nBH04[gDÅ½ªÔ\$l²;ÿ¬WGYIÃÂ%D´ø¢D½´û\"0XÞ ãfb¡ °\0 ÂTÙ/|ç?vwzINdeÉ¦+ÜÔXß¨+\"Bpo\$KW<Y:Ël&ÎÑ+Ý4*§]¡ú|ÒUFR'Ïî\$ÊVN`L89¨\02êX¾7}ÜfGþÉò ¢YFp¼ÎØáòTã'Ë/wH,V\0ªÑ«7¯	,N%\"Ä!Û;b¿!WËëpxi ­èUcáæaÄvÛ°Õì.(ÛÙ:¹¢Sà4UüÄ¤Uê&§#Ú4ô÷A{Ý!Mµö¶rÎW?©^5\"~zú¥?ëºº]kyÂ2.ç#ð#}eú£¡ÎÚDâì7,â},ÿ®þK.ç|RérÞof'áôS+îí|*Ly¯æão¸.vlü\$ÒÛl#oòn.Ã0þã(£~¡¬K¶÷Å>/ºÇäÒì´ËXqKXä¤F*ÙâJ%bÈ¨È}#N	ò\rÇDÌ¥6'òJOÛrøÍÀ9Ax¤HÑÈr\$ìëCð	Þclü\0Ø¬Nàlb¹ø¨Ø7\$æ¾æüÆÒäãðipúþ}Æ¾Sd4`ÜÓ,ð¸R\0ÚS\"¾È fÇ£¸¡\$hzXF7h¬Ùeh&«l¯ÊtH\"ùÀJãÔ#¤kð|ËÄãDæmÚ@Ó Æ\rb\ncN^cVÕàË§Ð#\$èH8qTðzFQ\\,+B8QEôÏÇ{ËÌu£@\"Å¢©´|)Ò*°D£1Æo&DwÐþWNWQèT%PUGÉ  ¨\n`\0â¥\r%¼^NÐeÄÃÅÊ\\íqG|ìf¢\"Þ6ÉvðåNGàA#Fþ?¢\0'#îB?ëÖTBÒKâDO²0H¬¬ÙRÄt\"ÏøJ,&*zðä¨,rHKP£¢Ð(r-%dn'ù&ÖÉ²fÒk&âë'\"'cw\0²|¨2C\$ä@¢è \"ð+Â.+Á&'Ä½Ò^&*3\rÚÀò\"¢](ËîBg&1\$qÙ'%&²'ã-îb+.rÎ)Òc)­)Ìpð²*lFÇÜçN¶>B\"°?ì\rÎi£ðÐG­ì¶úDÖÈð0ðzÜ-*s@'*0ïþFSNö Ä>333îÎø\r Sd6jEh8kÃ+°L-b(2\"q2mÒ]2oÞ!Â(x£ylÍ2opDÈ\\ÑKÆÑ±ªiA3-Kþ¶PHJOxx0¶¶p»ä'ÏîâOmä,ÈnpÇÓ^nM\0dt±SæN¦ëÈuIg%d¥\01?O?Â9Ê0?Ã2\"`*Ú Á>Ýñ\nGÊLp?2«@ô)BÄT©ï¶(:C£@³÷CèÆðh±èw\"¤F4%E/bí©Eq Ç@&\n)f'Mñf ¢6u°F Xç³\0¾±13ÇõI¤s0Ô÷(b\$°K«Ïs¾0x\"\$;íËä£Dôg\0¨4që[TÈU±ö\rÎR%ô\r	 ê\r°Ü\"ð}jð)¦ÍàÈ_K>H,È0Ù45APC¡j±MÿÉfwd\"R©@Õ#Q(A^cE©Aü÷è%DÄsMt<óo-%±h\"ãÑ\nÌë0®J (Ò¥Ó¢òAÒ£Õ`jõdå²èA2í&UpûµwS\$°.G\r\$Lù5(0ôLqjñb'>ãQõ5Z²+ZòÿbÞñGØA^BÔIÄ;TUW%\$Êi5Î£#u]T\\ÑëT5UStÂàds-'æè­ÞRãî¼D~âC%brû+Ö6kÚ²a,QRÎäct-BF1\$4²Î@6\$îhÄÉgêÄ½ì~VÀ'îaÕîMK¿dÒ¾H.ó?%®ÀåFÑÏ´\rUí[uÒ\"pBôE\n7b¦Ú!àÔc¬ãÉeÍ*´¦ÛVÎ1fÔ}õØÝMU!?!kÐý[ÐëUÃ>VÎÛ_hSFï^\0¶çTXV»nÞmµå\nc:!³)4´7\\ip4:s)TÔJºSl§-÷ëgnV7oW#m§lT ä{r·:Õo36öUEpI\n«H<C=dlvÄcvO&¦!k®º²Â\noçì´4¿Çâ?¶ÝXVw\rQÖÙF0l1-7yÂçyXÕ=_ézµFlTÐ÷@JñlÜP4·]·>ü³^¶,}EtÐ!|äRdã'Ð{4Ç}ÓÚ#¢gÖþ#çÏÐ9=w Ö­ÃRÂFF[tÂÈÍÑyê²[àÄ¸&Æ¬9!ø¸!<QcX/Nl8Æ3rd\rDxÚ?{òÛ7ahÅLõOrerXsn;71~cÛ79Ø~&WØ¾B[(}\n·å_·S@1(JTALÈtfÛB¥RPE-C%5yõÉ\rûelw½UxÛÌ?uç`_4£È¾¶ÉSB={#Å	¯x%£O·Y.\0¼o2I3\0UoWÎÿ=wÞ!òö¾y+YIá*óKXwÎÓàLqPt5¢ÀHr¯ÔyÙ);³ÍzlÎ©W.¨FÿA3ðnÒõHîµZNrî3 ÀØ`Æ\r]ãú8+ºÉü\"ÍÀH®è7v/}W¨Pnõ`å7KßcB½HU?ÉpV}fÎ\n ¨ÀZl+.RKBOB\n£¹[(NîÔ6\$KrÙs	Ä²!K®ÏCGËs·¦Ù%~È^éÈ(åDÊ¨±Ë«Ïòæîê ÉÙrÝbDXuE4B{TÓ.èbëÎÂÎÍ¤\$gîGª\"Ò%.~¿úÇ­8¤!rXrÐ&NHPÒé¥G)aîêÒ1Ìh5\\xOø,Z4Z¾edVuYïöF½D#Ê8/Å#UG®úÝ¯(É®	ï:ç×M)¯!°Øk[ûD¯m¹0Y<ZìqHa9q	¯IìîB,ùVý,N27ó:¤ð%~X63ÚãiCÊúbSGç¿Oñ¾õw¶W	>/¸\nMâ2q!8\"Ã--.¾qÔz\rLþÇ¹YlRÑ³r)e´4>±IiçôODÞÇòV-¤wú1Úå6ë·Ï¨ëQØ¢¹¯;kJ\"Ð)3.²wÛòñ§±nÀ\ràìQÀï`Ëjf²ÊI¹=\r36Þ+±»q©h¥\$Oq|J'WF° ";
            break;
        case 'es':$e = "%ÌÂ(oNb¼æi1¢ägBM±Ði;ÅÀ¢,lèa6XkAµ¡<M°\$N;ÂabS\nFE9ÍQé Ý2ÌNgC,@\nFC1 Ôl7AECL653MÆ\$:o9FSÖ,i7KúÒ_2Î§#xüI7ÎFS\rA<M°ÓÆia¬ÍÂ	¬r8³MNfDÒl4ÉÌ Òg±MjE*Äp²2i¼Èi°ÅN@¢	ÝÁá:ã.O~i¥ßr2­,ÊdQÄCO&p9H3÷,á0ÇgKvõIúyÓfG·´{¿[æ <Å\ræî¡»â¶é8Ü²½ïî¬J¡ëÓªþ P¦0ËÎ4kRÝ-¸ÞNj,KâÒÍoØÞÇ¬­ Þ:-¢&10¨*ÑÆ(¦2PªÊÝ¹¯àoØè9F©XÂQ¬dÊ1È&\rí@Ý¾ÈÌRþI\nÞÐÝ:(@æ7±PÂ1hR2:ÏhÃFòÌ²cÚ;¡`@<#C3¡Ð:æáxïGÃÂ±GrÐ3î(^ïcî´xDÂ´È¤ãb\$9xÂ\$b³ò/:Ó-µë\"âÚ¹/C:¼kÜÚ¶íÊ2Ø½ UÜ\r-ÐA\n©rX:©È¤î9+2OkÇ° PJèCÊÜaÊ¦ ²(Ø:³¶®®2(Úâ¤îÊâÀ0C-îþ!ë%Å©B Ê3`C`égµV¥Hñ81ïÚÞ7ËÍ\$HÛnÇ²2ºË¢j'¾N,G8­½ñC?mCÈè2ª`ñ7c ÉX½Tõ¢9J++ÞÃ\nb3®Ëv½K-Ýc-2léNÈ.·f×Ã©lË³ÉjÍ<,¬çl ìÂ¨Qnñ[_v1ÚÓp³æ\0±SLF\"sw´°M%B ãÛ(*xéÁèß?5#SÓ82ÍøØ7ÕµzG@T(Ü°¯R@åØÌ:â÷É9Ín ìóøÆyZÏ©-z9h1]^îV2£Ïò4-XIeÒ ¢7!ã,>2Èæ[%áÒè:OKÃà4ÜÑãº/=¬¥øYb5èå'¦´ÚÁdMà.ÄÀÈq#GpÉÂO2<)l1RLQ#\np7²lBÀm¦\$xÙ&¥¥@ÂÔ*Q*-F¨ðî¤TcJX©ÜÙ:Q.<è¢C;¼D)¡§\$°!~1y\n#`á-¤Q=·C¢µ		#\nO*\0@JOI|8aÀ4¤Zé!ò*!E(Å¤6j^')~q^TT4/òïôw~ØôZ<uä1ÆeOý8ù¡D,ö²\$HQ#	4#Ä×ËÄ¡©î08DpZ}æ:ñIþZ¤¡£'-c¹È%\$-~Wj¥É¬åèÉºA²pn\$NlNPÝ.XP	@&Ðh( ð¢ì(L¡È!¥¤7lRb\n#1jÒÌvÓzpå\$ÎdÃ¥4%Ù¢Ú[È@yä!=!HÝQ,H¥½ÂNHÂ@a?.°ö2BLe#jr:\$ý(Ü!ª¡=¦CÚEÄA¼5¼\0ÂF;óAÂ²Ô\rT*F]y_l(ÈÃ\0äC©í_Dü<&ÓnhÁGigPÑ\nLIJO\"¦.ÑªéA\0PF,0%BA¥2Ô3+W9i¨éÐövYËL5R¥ÀcàZ\$00¥cÜxS\n@õY·DÈA¾iÄÔ®¨z\"¥¨VB\nðµÛ7Wª[w	AÛsJaÐU¤tÞdÚLÉI£nÓ` ÁRz øÃ£3V< ¤|Z(4mwò=Ô®R{njW7h 0¨oØIA)Ë´xR\nXÖÐÄ`ëÐo]ëÅi!ü£r%ëÝ#©3u('2È)½7èÚ­×[X.3¡5JÍRLIÉÖR²Ñq©~:é=ju%[\"Mý½²*ímMw·V@Ím;\rù3P¬[Ïc.¬|*`å<Rdð±Zª*feqr>wÅÔ<§¡<JiUj1óôLó^z+Õ+TF»¥ü\n±p5jW´Ê)JRã2A»\n½+MÂ,F( \nköH>\0é_9á%%0ìèSÎhõBÑ+z0¿àÐ\r//´1Öklk9ùlÈg8¬\$ô%vmaºc­[ÆÓ«CÖí½9Dµ¶Ö¡qð#ö7ò'Üø´k]nvnê\$7_¯)Zs3BZXbÑª÷aHL\$@Èg}i}Ã@¨/èH±©Ç7ªQ*1¨Sb¯¬[XrK] ¼§®9WAÐäïiÛrÕÇ\$øOÜlaÒ8©sÀ'ð§÷19a0ü«®oÎh¡#	ÈYë§?läéà!=cDµÄt\n\nàìõ>;1éf@£´¸_]æªZxûL'ýQ|¾d4ÃËàwqr³½ÊèÝ.QRGà;Ê§|I;¡ðÅ¬2z\"ô±üe­1He;ól\$>s\"vXF_\\ì\" '*×5ddZ½çDÍb¶OoîVËA(ðÑ-fl½ªbP«FG} ±Ìù÷åàÜ.ê;DÍÔfSTZ(QØ¨³åj_¾/ØeÅ22c<±ý¯SëaµülçËî¼Á]|ØÊx¥hÕè2^n2­dd\" ØãLÐ×Ü^	ÞG^Ävþ­ dJ6?lÊ\nÆÈïÊ=ÌÈÆ0bÁÈýªµÌú£ðLxÚ/ª	Ú%'&?n\\çî¾4nfèfæÀÂçàE®y|ëÎV,Ðé\nm¬¬Íòd250c\nÊLÇÂÏ÷¢+°¯#\"Õ)v%ò2£LÙC&?#4?¢hGAJÈ æ&n\räü&©V8gÎ¢Ö2pØ*pHéØ+ï	#pîÙÃð-P£\nG¾¶eª)q·©`ÀÜ¾0¾«êøënD(æ'FÝ?L&^Ed´pjúÝ._dã1|pÖîZN\$Fq'plöñ `Ô0nØb3¦ÛÂÓö,f2h¢ÄHD76E#\"N<Ã^G,/Üp­QQäoä51v÷Ïo±çq­BÒÆ0ò!ò6ò®	!±½!ý!Äh¹\0â&xïøIàì«QÛ@ÊÛ/\$L¯i`çDÜÝò\\|l²\")&\rÄÝf>³%ÍÆ-ÊF1û¯'é='re/lÓ(Òð¦;²Ýv+ü!±p*<HB'¯nàÅõO øÒÀ.øÊb3op`cRá\nÝoãv],¶ðòW0.òË¤e/«äF]'FRÙß.c Ò©,`0ø®w0I*p\"\np\n¤\rVOE¯®§F!#ò@	d½1#nü'#¨:À@tpøE1dÄî\n\n p\$nihô&N*\$NË\n(ï>z£ü\"\"\$/ÄêÀÍ­hkmæZëð¡\"(dF\n£¨²¨(#cÞÄoëò´>mHBVâP\nJ\$ùÏìJ\$=nÏ,%ëL×¡taÂd.%¯¢ÊÏàá<'.!hX tbÀâÈ¶ùÄ*GöÎB@/¤RóÑ£­i4éVxÐ:4C'àæmäACi°?1\\ -Ê*pi	jboºÃætÄPfÊærÄ&x\"qM¢I(\nn4.÷dT¥àÄáDM\rc/hð¨AJ4(ãvõK\"Æ\$È*]PZÊ4½t(7`Þ[ îÞ`ªr#bg1j¦ØÌÈtÄkð\\¢¤ÌÄ´:SãäÀ";
            break;
        case 'et':$e = "%ÌÂ(a4\r\"ðØe9&!¤Úi7D|<@va­bÆQ¬\\\n&Mg92 3B!G3©Ôäu9§2	apóIÐêdCÈf4ãÈ(a&£	Ò\r1LjÀ:e2\rq!Ø?MÆ3±Ï¦V(Ô6ÅbòóyºÔe·WhòsyÈÒgDÍ¢¡Ån¡ZhB\n%( ¢¤ç­i4ÚsYÝm´4'S´RNY7D	Ú4n7ÆñÇhIÏ8'Sé:4Ü´>NSozÁ³µZW<,5!ÓZ 6ÍN~Þ³¨0ø~3?«Èr3àÌ¾î!¸Î«'\n3R%±®¬´b¨Ü5¸»2C-xÚ2HX6ã{94}@ãª´®\rÈ°Ò\n\$P¨ÉÃ*À&\rìÕ¿bSÈð0kL.·-±fB\0È7¹\0è9àå'#Æ%ð@ø¹Ì²îê(ÐÜÊÚ°9ìhÈãâ4.£0z\r è8aÐ^óè\\?C\\ÆáxÊ7ãÏ4C ^'aô8ºÀÌÆªèÒÂxÂ\$â²&ð5Ñ2	Ï\"ø»i¸Ö£&ÑXÐ¾*Ó. ¥]X1Íø¶¨ Ë'°\"áLnÓ>+Xä5?b¼]¿È(J´Ã*(kÈéÌCðÊ+åàà«\rãl.7+Qtc]5N6Ié¢mC£°\"©ëz#é êým£F#°XèC±JÆ&\r*+ô	ø\\VÚ6³´kEmkx¶·¤YM¨2¥vú64cZpÊ8×Îg\rc¨1#ÃÆ×4º0ÿÕSÀ1e)Æ@o{¶ø¦*\0ÜÑÖuü·CÕeZW5Úk^Ç\rZ:Õûc®F©b5±ha¬~±´lk%\" @4ã¹¢P¦ÂÛ¿\"Â(*\nvP6ÊH¨\"¥|ÌW²	ªs[-×ØfM¨½Ú0Ò+ª90Ít·#Í;O¤óu\"!°£6ìº÷aòt£:N\"Yâð±¡êùë0åãúCÃ7aâN'£n 4ë\næ<ËäÃç(e»ö íïÂ#wã-ÒÄWCKF½¸7H¸ðÌQ\$èáH\náC§ ×B÷ªVKÄrFlWPX0raJÜ0h2¸@R¨O\$\$|ýÁjÉÛ'*22VL8hà ¤r2`c0¤°¶½tÜnÓsN©Ý<§´úÓüFB\nü(eÚºß0ª<F4)×K\nlÂgjqIGM(60<d;¡`oÑ3NIÉIÛI**?(Ã4i.¥!×Åû²xOIñ?( \$^PÏiC½Õ¤\\¸p!ë¬:<W°Îo-'*\n7*ÃS\$evF¨ýq.ÆÔ´u2gª3Ðñ¥°Ç2ag+tKä¥Ã-!3÷®M Ô~è6AùLÅj(\\BÐ¡Öù\$d\"2gáPá\0¢äÎ#Ä5:br-!@\$	âg5Âðð!ÀeñVqN9±cÏ ¤ä¶ÎCxw-±³]U{åkjÏÃÔ F¬QI]Jc£Lq²\n-5&¤ÃAè1éÖfN4ÏÚq×\råL`@ÂRÑÍb8ý\nÔyÊb=Ó 	R2D¾C\"ÚíÃ±!Ôa[L2ö.¦¾¼òNHy7)(±\n<iÍ|á:eÕÃP°ÌlDO^`Ç7M='*(ãp ÂTªT¥3Q#i\nÌ¹êÝUHùäc5Ò<ÙÃ3jIP^ö±ê\0Ði§XAÐÛ×¶~adÝpGÄ°p@Ú08¤}ÿ\0(2¥R®aJòab\"\"`(+C=É	ë-h ÎayYExR1HdÂxNT(@+¾A\"À~.ââ7H¤¢Ä\\Rý¯È©³f¨)GYmDñCðÄØv<øÆ9C:&>\$aâæÃ*\rÊ0fb©Z4CHQ¶5ézç¡­`Ê©w	!µÍµÁ7EZê6o(xÓËzÏdkè:ÂÆ×iFZÄå°Ã\n:l5¨\"xPJ%RÿÌìÕGâ57LÍd*¸|X-UVæPËæüiCÑûxb\"1ËB\0Ç¦,#cLÜLøºÉÔÆ	A]d@C¾@+¬«>2äÆLsºë¤¸öYñ¢¦¥´ªpÎY64©Ö']c£e°»äZ­úÆC+¡å×MN]Ñaä<ÑPÿC9ÇK)ùá´&ÔpaP)Â(RrÁ»T[ÆÎÑ²í»ÃV. ÊaQ¶ºYNY8-EcAîC	\0Âã+òJÁ)ó>Ñp³GàDA\$eÃhÖà/¬´ìYÆrZ\$4FBÐHwÎ> @\\ÖgÂ^Ó\rdlÙ\"N·6ÿKMó\$ðÖª&>1klØ´cü9\$¤*¼ÎE ié;\0¤ô\$È)?WpÀDTFg´ø=½ìÏvÊ·U<\rTÉìgvzIggJÁM«¯¦ûMi¦üJæ¨°:Æ§¯ËQ!f­è¸kÊáÎZ\$ùÖyà\r¹g*Ò)h:ÈÛÑ:YüÊ«xÝÔ[G©Ç\nCÍKæ7öÖubóç®ôýIMÜÓK}¾À¹\r¯f?<ì!p8ûå­pxåÁ¥2NûÌY·ïº·ÿ!!xpn¦Ýõn´%?\${¯PÀ	¯ÇåËùOõÊ\\ínãLFäxê\r­\$,  Á \"©ßÌd,¯ö,v@,DÇäeg Ç,Iâ|DÊ3ð6É0:÷oRøÉéÌj+læÒV*º®.ï2nâë.¶Eô|¦Æ9%T-¦\\þàèMÚæ2(îh,Nl'C>»¡jØkÂ!p*÷g	p¢»Áj¼\r\\þÏIãÒ÷Ì\nP©°­Ïy	T9àZ}bXN£|B#zpM^4Æ>1É~<EâéÆÄf¸+Áx!©Mêî?|gPôBÌcã	!H>îf-^Ád´8ÄæpÂPö3BWÅÄ\$ha\npºØ×`è«Þ×ì&ï\$ ?åDXGXÅ/_Ï{*ÎX¥Y1bÕJT##Q±è<øèÈÎX14Fn\nïekÁxq«#qÄÿí§ñÁ1\rÃ>k-dq¾=1ÒkåsáGr'&YMn@-\$)b5 Äo!J=ÜÂ£Á Ð£âÿÌomp±~ÉföiPØõ0ËN/ò8zñÖIñèÀü¦ÖJ-#rHlü&¥)pàô²rY'løÎ[Øê\$çoIO²#>m7bÊ2ò¢êj_*²¯)ò´±ÍâÄ(^¨Ú12Ü%¾çr_m\$-ÀÚ÷2<õ/ò®~Î«'.rÛ.ïC°[&F´è-§-²êTÑúà2û-mÄ\rÈÜÍÐÝDb}ñßÈA\$ð/./D¤OÊY0È4/~ç	43HõF¢ÇàÚc¤\0P	°c ÈYRElmf®ÅTäòXÓWPJ¡nÀÂ3Òß8Ê	o\rÅÐÁÎnEîk8¥ZÄæèa-oÞ²0Ê=nüàW@Ø`Ö\$°b-Â\r|¹qôIã.¦«æ\n pzBæF®ë¨Q¢!e¸äÀÂöÅnúl¦ºóPf`@@ÌÞ ¦GÊRR4mîú¦÷³Ð5rÌlEDCØÞª¾È§Rb4K´X\"£üFàÊc.!Ãÿ	f?lðë,üÚ£_ìRþ¦vÏÐÅ\r¡/QÈ)à½êû-ëDÄ1ø45jL&Aô¡	¥I2?3ÏyÂr7}*\ràà[â.Ó£®WFÐW°!BÒ<EâÆ(bÂÔG3ü.'0iÐ+¯ÁE¦`Çã¦_lC.h&¬\n\nPÈ\0ÇR)M¢-%Ð]\$×Ö\$Ê%ÄT¦TÅADãïÌm§>b4¬4`M<\räFíR?o%Pömià+`Â½Æà2`	\0t	 @¦\n`";
            break;
        case 'fa':$e = "%ÌÂ)²l)Û\nöÂÄ@ØT6PðõD&Ú,\"ËÚ0@Ù@Âc­\$}\rl,Û\n©B¼\\\n	Nd(z¶	m*[\n¸l=NÙCMáK(~B§¡%ò	2ID6¾MBÂåâ\0Sm`Û,k6ÚÑ¶µm­kvÚá¶¹BhH²äA9Ä!°d+anÙ¾©¨ô<¦W-l'ÁDqäe0Ì³¾õ\nXÆ¬ÄvºC©-*Ue¡KY\$vâ¬Õ5±ë¥N«Wf+PdFØZ\\aÆT·ç¶·Jµ±Ä\\VLù°®Ã£#u\rõ#´´HÐÐý¿e¦â)÷¹nZ4®ÐÄ®>ÿN©ÖÜà(µNì£Íºïª ¸j(l4{\\)#°Ò7ëlX\$dË¨Ô)SÌCB¨Ü5Ãxî7(ä9\rã,²;Pá\nã!«¡b()ù\n»§MÒ*Rr?HFÈ1|¦³IüÜ¶1læ7«n¬0Q2\r°|TxÊ9(è£áÌãÆ1Ìc3À0ÄÈ²c¸Ò:\rxë6\"9ñhÈäì41ã0z\r è8aÐ^ôè\\0Ëô]áxÊ7ô0çDQAxDÃlZÉÁã4Z6ÏHÞ7xÂ5NqTë¢ÅDCñÃ¨ï8zT;0Þ?®ûÈØ	\$\$Í¤	5ÿ.%PU0¼íàP®0Ctæ£\$f@MãyÜq¥ÉlÖR¢¸%k¢Ôí°ÒÙ®æ/ôJ\r|ÿ(ÊÉm(6°÷bÁò¤ê`\rQ3\nÌ§MN¾+6¼:Ë¢[\$#\\ê&Ï2«|¯Ï:>¾ÜìýgNÒUCILñáºEÌó¸L6>\nb¸¢ªhØèÏ£´X¾OæÆß¼ÌïIª*±dìÊÎg\"Ö7\r%rºZ	^îÃo¶{µIÌd>K(h¶+4¨¸µ	ÉÊÌD=Éeë£t«kï6\rïÎÃÒâ·ûÒ¬©[Ð!c-&A\0Ú:s=X1ã ëvÎ±EØ9#Í{_¥ýØæuØÌ4ýøËãÌ A²x4sÐØÉö±lR7{ßÉñv£(ð:TóvÊé°M3®Ñ]\n\"úèÎ!hÀO±s­%«9Ãs3;ç83ä l:K,\"@I\ni0xËÚ\\]1µJ­ª¹ÃOÒÙ\$1B,,©7#ËÜØ\"F rç3D9B,ÚD2ß´¥8K\0¡;6P¬ABÕ­ákÊÃxrGaÙ\\øDÌ»Cdqö6VEaëÝ@ÐQdVÁÈ;æÚ]\rÈ©F7ÞC\"_QÀHU\$¥²SJqO*ôr¤TÊ¡0¾@è®@\"ÒU\\¡¨RÊÿ5}µ¸Ã)\nä0¦Òò£]F§Ø¨¹øÔÜN5/°ÀÆ¼Ú	¦m¹Ò	kµnJ5G©&¥TºSjt;©õCÕrTªS>·Û&r°mN1(·ÇòG^¢<+(~-Q±ËKl8	Îº\nZKä°í5Ò\nWD­|#²ÅM!?\$°ìÜdÓtW`?ÀØª9ÐÊ¼f}Ð9TÞC0u¢á°7w°\0 PMîÑà@c¨ú§á6?	ã{9Èé#³\"öD¢ÃP'GåÅäHYÁs%4\0%!À;(`ÏYDÁA21¨jD,GL £ØcèBõ\nJ{á¨TØâe\ré¶\"j>jª,8 ÈQ\r%F\$¾÷®°sPÉÁë¢ggCPªD%äÃ@ia¡Û=+FaÜR^ÂRÕÔ5ªr¯dò?:Æ K£,\"F%BäÏ4øÀÏIÁ:'ña¿\$<QJQªQ½KÈ»KµZ%Ä~V¡×­<ýPmÀCÁð@ÃÊLÁ&s\$°&<8TÆÃ2,\r±âk¥úf,2TÊÔ¦E\r_±6*u5(NÂò³Í8È\\2WqFÆ\\­¼¢µ\nÂù-@n211·2JÇbQ`B#[h½iF07öÐàã©@ÖBd1±Å:¦ãr,ÁR¬X£4ÂI´Z^BH8J¦A¢³­o?²;ÒCõQb¬È@¨à('à@BD!P\"çLì(L¹ñ8¦è¾Ù´(GnÝTEÚ9i«Fr:wz\\¢@è\$×&2AÍ)mÓú.;ÈB(V¢>Ò!æÐ®c¹³í¢¬J?.¹U3½­g.![Zñ@ÅÆsi«-³ZbD@êVFËÖz÷`7	(ÊaÄ\\Z¸³h`H!|âT++µWÜ³öÄç¨§GqÃ2ÚHHÂä¹ËRºÉ\\ó9\$ýs¬gÉ-¼&Ì	³¤\";=þ;&9ÛBÀÞ¨+èp\rÖâ\néÃíP!Ñefb}Ùgu¡´÷Z!Xn\r©?Ç,Þ6à¯/;·Óôè{Bâ*\\æãÔ÷ó*Q³ß1âfðÎÃk ¡Ç§'jÃ/adIÀn¬Îííóºðûb-4âTb-öYC7ì.6Õu·@u]~³ØT!\$ûÏu²É4&¤ÙaüÈ=DÜ\$2¢)²Ë´^@¼®CCCW´TõèßÓ\rP±°ÉÑ®âúâüO[öà'®Ë@ÖT}ÁzèûÅÖcmàay:+ßÚûzógw¼i¾éÉ³Å±ÌÐªnC·\\4g2Ëé<â§fír)N\$²Åºf¶ÜQG),xÙC¸G2£°ôBüÌ¬w\0mzsH®IK¢Fj_®¨nb\$H¥Â\\lFÖ\rûIßJGâèË§\0ê¼7iÛfÂÐã	r<¦ðÅº¼C-r;ÎnphæÎ4ÅuP\\\ráF¨/¨îÕÀpmàY­².X(î;þÆïÀBè8ð®«P´\\íÄÑDøÔ<4.éBtè«¨\\jÚè¼.­®ò\0ØäZOàÐ\r y®F\r®L.\"þÒJ0W`ÈOé1\rÚ.7-\nGM¤Úpíl¯ô¬j7meMÂ©o-£qGmª?c{0â7a°¢!ä.tBâ\rS	å sÐMÂböO~4F\"Ë§ rv\$'NÝ\rü«±÷Ì hfü!QlÒÅMë¨9-%ÐÂòÉppÿûQÀëqÆ Pºðwc¨\"£r@fmòCøs\njÈ9\"F,F­mHARëGVë¤Ó°l)î¢B¯ÌæÆ`Ê ë\r6P(æ-ñÌY±U#]Ò:R9ñSÒHºâæåÃ)q2AÌ¼]\r£ÏRbmãÂ;PR4ùr9&ì*ñRw(æá°H_	Mé­D­/ä&¤:p FÔ#N%rXßÃM%òmÏç'¬w&Å'/ß/µ&8pNZ`âÄlðDÑG\$íó-ÆBÚÝ.oF²ìÙä~¡ò­¨4%ÿ.À\ri2+`·ÀÉ\nj6o1sL2·Êx¤o@ëîÓ1ß3\0Ê·ÊpìÓ)JF¦óím¦À-5±Ë'ÆÓc/W-rÉSnr3~î®d3\0&ß ó\0^%ïÆÇ7¦)0ÐG²þuÍô0GÅ¢q)!CI\00Cît\$2^ðÛhÿÓµ*¥\rMT!ñør\n\$¥îãE»=£ÄÛ¨iEÈÑqÏ÷b°A>A1dGë·:H\0bÅ£¦àè@ØlµS¹Éñ\0©u+õ*c.Å@ª\n pÍDq¥:/¥fÌTkP8sîÓÎ,ÙPðMÑ.6?çN\"¢þ×Ô6f(s6lwBM-ô0b,<\\´EB\"6ÊãØùþih«Mj³]+OÌ!°H()Zq!DXéò*å¤o0x<ñôùBZmÐåRâ­O+QLHÕîoDÞ] táÝ5õ1Q5âcÅPÆÁEXP&sCï%(3IXdãlÆF§*&¯ÐcQõuè3Úñ\rp-*Ó¯fÜF×Jií.f¸©\r\\mÕKY+\$`dTîi%´oÍù*Böö¥¤Ä±C}Q­S;li1YoÁ8Ç\rµR«\\\$2\ràìE@îîàÊBÅéÈÓ§ôÓÑ¨¹]I«Öß¤";
            break;
        case 'fi':$e = "%ÌÂ(¨i2\rç3¡¼Â 2Dcy¤É6bçHyÀÂl;MlØegS©ÈÒnGägC¡Ô@tB¡ó\\ðÞ 7Ì§2¦	ÃañR,#!Ðj6 ¢[\rHyWUéÐòy8N¢|é=NFÓüI7ÎFS	ÌÊ ¡Ñ§4y¨Ë0Ç&Ù~AH¤ký!2Í2ù¬êp2«ØÓp(MSQRM:Ï\rf(i9×«ÚhºCcRJJrýTf!7ÉèYë4èÎÖ£¦éI7¯uzú^º\r2Ã¶¥O»Ä Öú6øy·bkÙâ÷Îù²Oæúd{%zçM ìñ¼£s2Ú4¢*6¢Z«òÀÝ¨Ü:«¢:cÐBR¼90ÌÀØ6·b>¤m*º¯¬%#7\$JT¯£Ä!kgÁ£LH0£ ê	øë1ãÈæ<¿Ã\\Ö¹ð°ÊÃ¶¢ÈÆ@Ã«øàëIïòpÌ§Ó\r1<9ÇÄ0f:iú899£l¸xÐ½ÁèD4&æáxïGÈÔ¸ÐÃxä3ì8^cïKxD¡ÎÒ¼6;Aà^0ÉH¬­Kð@¶¢t\$íó S`ç¡îä_%à@7+ÃXó?¥ *£QÌ U^9\"©¨Ö¿-a(È#°êÛ×ÄÚkÂæ Ë8à´«|üÃ§XçÞM¢hÒÜ\$­¬Í¢¹\"µa<W¢4,5§}ì3ëØÏ}\r8t0×­)#ï 0N¢E^£ì8Ë¡°¥<c-¶&Jkh ÈªJ7-#%z%§×U_ªòÒ5ÂP¼l)\"`Zæèhp¶R8û<-­cLñôÕ¨êè{ã¶¢cc;@O{4Èûj'¹vtµNxÒ­IV\\Ôîêò#Ô@ë\r Ík!Å=HZ%Pf¿J@÷!\0ÃR/i¨äI<ÕUbS@Ô3\r#:Z2ô!ó\09	^ÁO½[e²ÿrHãchª[w,ChÃ<Ö_p\"L'oí,(:WûÔ.f¬Ð¾HÔ âÛ3\r°ºÉtÒã-äÌ¥Í­P9-r3ÑhÊ§Í:wrCH¤ØÕ³lÜÒ`m(+2¦1HPQ¡BÇ=c-#øY&¦1'âÌîÅ\"u'§56òXÎbL\\©qb»#<jÉ¢KÀ\$ÛÊ\\Î	n°n)R\róqÚDð¨NaIB[ð±¹_!Ï|¡Î!¤HúÊæ¤ âÔBá@x(LR@@.+ÉÔ§,ePªD¨µ£ÃºKf49)U.¦Cp/!GÈD©C(hø¾ZªÛ1Çj:ÒR`'è7!ô/aSHDö¨aÍy}CÈ§üYÈ9Fë6l_S.ñæ=¨è¢bR\nJBHe0¦(xC\råÈåHqÎ#µ×ÒJCC!/Å!pËMIáÙd­)?(HjÚ9ÀäÄcÎ±ö+ÅòA\r@é¼Ç\0ØsL<i.Xb¥RU.¼ÅSìíÞb;(X7I¥EåjE	¬uúñÏ\nÑ>4µDÀÀ° \n (Ò2È)JÛ¦  ¡¾rKükCÄé©ÙöÊ¡å·jENiÏ|aÔ4d±4\"5XÙJiÄÌÕ\\ù&ñº.Ñ#æ`Ã¡Â,ë ëÇRq(¶¨i8þnxýaõ|Ä -5gCÉ\\û/ÀÂF¬ Tø¦L+È}\\>Rèsi:áº2¯DÎL+Ô&Æ,Ãp³þ\$Íñ°@ËêXÌ;4Br_°l³o²°)bM(]ÜÆù]²uf¨Ã\\{´)©¼êdÂTªÒØ\nCDpi<'ùZ,²Ùº!öð¹\"zCª[øµ2vÿÕrÙ¤êÒ:²`ÒRg'Hl`¨ÔVËôÔò.íC\"f(¬ÉUPw±@'­ì 3&ZÉí=lT¶à@BD!P\"âÐ@(L¸Ñ«ªL¡H©\0Í÷Ô±Ñ®CÐ ­Ê<f`<:åø¶RTÜÃçÍÜS²ÒqÈh,úv´ùÍ\rxïffÑ##	ÌMW0udÖ[Clyá@ ¶óz?t­bÖSÖËv9- Þ]3¦´ÝäQÙ5H\nõ34B\rCÔì­ÎÒ3+ÚCLâø|ÍÛ<ÅL4ï³òÖÏ	é¶/«±W3Ý±¶!ÌéoVÉyeé+·ÐÅië40Øý­=]RÌyë%e¡GËÂÎÀ3-Ú]Ãs:§E­®»Ã:)À6Öm/\$¯ZîQ§LÐÜJöM(CCám ^öP {ì5ÀpÒGÆ'ÞK5~·>´.ÙÙhao óÒpy«ü6!%'Ö#\rà(\"¶ÂØê¡7xÌ*[ï<×`ÉÌp²N\$AçÂjìRç%úÃTe¹Ax r+|ßÉÓIÓÕ<êÃFU\"\\f¨2_Â_T=Z1õÃo]nvÞ'ndCOMÓRÅ©ñµÄJW*ÊèögÞ{þï¤Ö2ªZ«+ë¦Þ[Î/ö1)ØXdÚ¢Ö{÷ËaÒLCãihëGX=ðQóV\\7ùÔ[êIÛ¯ðÝÍ¬í¤vììhv´ã´ÑþÄÈr´%rÖs<aæK^µl\r´ln#Æ³ûëÿ[ÏIÔ{§Úí0¹%ýIÇÛ<ÿ8Õç5iÝÎ'º©Cµó«aÁ×û³üÉ(aÂ%Í4&¤àB(-gj3Ö..ÀÑÂ\$·.--p\"¬È@,/\rÄ/Îp2Ïè¨bjíÎÜeÜ^\0\rÎöÐÛçL5`Úo fBX  É'LÜ+ú(/¸Ï\"2ÏÝO,ûoþâÌ´#(koò60ÍÐ	G&X¯à	cºonFèNÐºÌëeÊé®¾&âý\$iDñÜëNÐërü¶Ä-êv vPû0AîÐ±¯á\ríj_°¨ÿÖÞ§®'­ß\0åÖt,£TJêî!äà¢bnlâÌ-ÒpâÔ>\"ZGd&¢N2ª@%ðB\"CÒ½¤ê{íøMdê;BÀ,ñ:¬ðód\\\ríÞXEX?Ñ,]l¢°IOìø¢:ðúýñ«ñ¯\noö»FÆÚ\rªYÆø&ü.ÛÑ±ÎúQ¦þ°ª&úp°ÿPÜ¢9fþj'\\ô.­  õ¦  M\"T4kPÄC>\rnÖlåP\"é±©#0Á\r£-ÑÜ ÌafÐ1í\0!+ºú±û\$¨ÕúVHÂZFÄT#\$ª!VFÍô­ø\rìMVe°]â%2( Ñ(rc£C)-üàv1ÍÙ@È%0Znàáðøf1Ô»N{. XÑßÑ²ÅrD5å#Îá\$6ÆÆ\$n-_ '´;ñþA­@£C¯°LíhË0dd±üS£3:ñí1.d>'#äl	«<ÍRAëèí{%fô_²\r-ÿ4b¤#NÎÊ|¢j.ò{'ºA\rt4ègäðèk\$Â\rììó|\\³Jï|íä\rV<EËçÃÚÐ£hIÜúÌ2Ñ}q:Åà¨²@pÃb'Iépê:dÀaÓéóÖ\r\$Í\n(ýoä)ê&¢â&f­.o©L =Ï£0O:ô>h¦Â^{£ý£;CP;#ü¾´äÒèÊEgBÍd½£J8eÄq¦æ\$£H^\"áÈ\r>ÃX1È,TçÎ\"Ìù¥x3Oúã²¶´f9ìâó*r=1³ÑÖÚèÜ2ÏZ'GÂ¸ÊÔPÃÓEøæ1C#gÃV× \rðînHäÆbÉb	ÐªôÀ`ÌÙ-\nOÀ7l¾\"Ö\"£¾Ôä #Äè´ägùt`®®ÌÌArÏ¶GTþË-Rõ´zH[\"81ÈTâO/ÄîÚï¸,Êè{´Gp­\nN|L*üG\$æÀ";
            break;
        case 'fr':$e = "%ÌÂ(m8Îg3IØeæA¼ät2ñÒc4c\"àQ0Â :M&Èá´ÂxcC)Î;ÆfÓS¤F %9¤ÈzA\"OqÐäo:0ã,X\nFC1 Ôl7AECÉÂj :%f0u9h¨ÁÌZv¨MqM0Peðcqäe0çç:N+·MæéôÞR5M´Çj;g* ¨±¤ÏL'SÔÝÕ\$ÓyÓáyÌ=ÇW­Ê³3©²Rt¯\"pÂv2LnÎd§NhMÀ@m2)Ñ@j¨F¯~-N\$\"°úsãñ9³3ÓNÔ7¬Ã8Û-Lü?O\n77eKzé©éT7@ïÊú<oÃ0Â½®)0Ü3À Pªµ\rcr\"L;¼¦?£t\0ÑcËêa\0)£.áEbÜ2Çí¢z:lKLJ!\$ÜÑ ­Xè4ªkn²04­,KJ4ºÊ,Ã\\×©£+T2\rªc0× ¨à¢è¨\rpÆ1Ç	°Â:\0Å'&Ò¥* ïò<í%¡`@\$ãC3¡Ðñtã½D3dÜü¢Ã8_,ó¸æ;¢Ã ^(ðà6´*&7È²]Áà^0É §ÂBÐåJjÕY	¾£X:.ShÛ7ÐØ¶(xmËlÃ\\Ñ(¨:`@Î\rä4:5N.¶1W\n\0ÞÏ¼ Mù`Ø!ãd¥5,67·TÃ°5å,§³øé2#J+&R\"4ê#LÙS-1Ýà2³ìdÜÌZÉÊ£;Tó)eÀåi..j):#ñ\\¬cdÌ0ß|QW ;SPÉÄ\":¥(ð5Y()tS+­(¾=ÓVyeV)\"cH¨éK²ù­µÆÚÜ¶òOA±L¬£Jý¸¤:È\"Ay8>os2T¥Ñß.2»_	'Éöm(iC²Wöx¤¨?öx¦ÎcÒÊödKBT5÷ÒÇ£¸ñ¢7\"&\\ûb©&S³àRï×)í)Z°öÀäÊlÃZ06zc}W5³ïÁ¦}L`\"8pû@ÌyêAù\r7·Û#^~¼Fc.}Ë¬6#AB\"C?`,-ÃÆ¼µ3ê³Â¨\r0s8ÄáN\$@(ã®W{r,á 'LkÉ#ð(Ä×\nxp2°Ñ¯@%ßá`uJHÐòtWyæ\rË°Ä5ÀÈ¢³M¨\0à|:Iò(³8H\nC3\$-îDÊs\"b\"1Aõ/Ø¦Ú_q«¼A°@K\rÁ¬+×C *×%\$Ha4#JR)u2¦Ã¢SêQªX¦HÕJ«\rÀ½Ã¦5f¤éÿï #ÂËË¿fI#âVCÎxi0¥»cAQ¥ÕeUq\$íx8J±R~¤bªaM)Å<¨wTL%T«úY!MY«R­ØºiO pÛabHyáDG8n;	Ø¦v0rR\$¯áý,²	`\$g¢¿'?ÃÃ|´'î¨&ÁÌ·Æ!á3Üè3¬M§8ª<1Ä,å\nD\"ã.>\r~mJScm5F±âBwK 4@\$âÈ°tQ\0  fa	\rÄ4÷ROJh:P-î:WNÎIÃ9¥'wîZM*\r4i}%¶Ê°!ü`K8Ü_¹h2Ôê¸Ö¢Él¿IÌÖnði:g¦ªû\nh»klªäÑ@ÞÒÈ aL)dTI4?AZ|È<KÄG[Å_V&hÊ\$h¦ïò(Cl; R\"gq³vvXUðÎ¼ß£î\nv\nYcsâE^K6¶aÛ*'ÁÀ:É5\$3÷aAÊ>ÏXs{©.\0Â¡( (ªl~º¦(é6²ûgõ\$Jt5ÄÿoØÄHmva­N¥,Q#ÈÖOÉ9gtè\$äÂ+½>>ÝbI#A*Sä6®«EO- iu.±1AÉ¼Lì?)Ê(3£9KÈÈc[ä,2¯0Âp \n¡@\"¨r>I&\\ #©ì%å Öñaâ ñV°QøO*ÓhÐÿ1÷Û¾öOB2d­ÐùÏN5Â!Ä<bNp×Ë Óf)gsôèÃý¨ÙÄNm*sÒiJÊÛªE(jJ³Þ¢xÁÏè½O[ë ª«þ\"«ÈSm ¡Lã²9w%F\0éýºbNfÍ92AöÓ4¯YHõ²\$¤¦Ø°öhR&	râSK?Èkå=u¹§Ó&F´62¡Róû  9v¨7-_¯ÕnM:yF2Í¨X½¶×Wæð4èêÅ­Ñ(T¦[¨úSÃi&áÅ8>øÚ;=%íÔÅ´àéªÍÎö¹Rq¨J#ÇÉ¨!¥Çh«KçXfCÌ,\r»©Öa¦Ð?âeª¯ÐÄI)\"LÕEnP§Pa1LÅ¹÷ÞG%ª«hH%´·ß¡Q6ÉÁP 0ssÒõ{M4D¨|nÁèvÀÀÁx yËõs^À¼\$/uAè´V½C*Î£±8§´ú¤NùÅ(­Ùè±ñðÎù+G'¢^cÄøbÏçßw¦g1Î#?£5®ô0>ûèeO¡ÞÊ§Ê%½÷ïÅÝzRÏñ=OÆ}ÁúG{ÔSo,ÞigÍü[ªÌ±9.!2ã÷B1¨\nØzàægA\0W¡ý_v²/ì7È·Ð(l¥t#\$\\.#h¯Ã01CÈ¦0ãðdB\n	ú4§bÐÍ.+-%qmö¢­Þ&2ÓR§íòÓ£^<®(B£o`Ú)\$°8Çæp@Adfâ\\ÛRò#2C¤>óÊxÂ8\$ðS0V«*Ù%ÂPfbÁS²ðpxé|ì­nd\"à;´¶æNcMîÓmô æ0` {BÔÞ#0d¥îÍ¼¶0¤ÄPÎeÐ®!ð²ãF½/b3¢Ha¤WnB ØãJJî2ap\r®aDFÍâCµLÔ\nÔÍäOÐfî­9*Õ±0Qj6/mÇÔ§BÕðu,t\r+G\0N`Ê!äBïXMÑZÚOìùÏX#oPX* ¢-1±øE¯¸¤Q	kê0åèBæ,zYBm£qºÇg0ñÍwÑ×J0fñ<,ãû(2®ä.\nìAD 'PÁÆ:\rª¢ËX¸¤#À%ÊR\"]Ä¢°Êà9æüãdâ´bôË^Òl?äÆiüpñ2c8ÃxvE\"r'¨zìlqàÇne'LáåÈå åg(ö±ôjÂN]0±µ(ÑÙ±Ë	1+)éðÕ%ÕeÐD nw08\"uÃ9+°Ú±éð[+rÊG½-Ð['§5È,Ì*Ì5-§q,åèB\\ï0,¥NàÃgÈýã0cËÜ£ H0§[)²ÂÓ«QÑNÓÒ¡3S+*rä4rèZb:fEã©nmj _ëäbõ+N0³S6.ZÎ\$ø2bü2§ª=(eÖªN\nÈr,ÀÈ Nm8h &Ó²\"dw Ë9sæó:®u:¥8Ã0çÖÒ0¸ÈGdèS,ß;-dî=:ó³Ù<³ÞkSD3Ç4¯ÀèóÎdNÍîÃ??À¨È¥:.Gm-ÒýÑE.É>Ðg-`OA¤ì²-CBÔ;Óï	ðZ	>\r8gq~5è:æ%ègð²7\rkR{CnKtñ`!hPÒôfÿmn2cr`hÔcbRÝ GÏ®`pLs¿t5G_Eý ó#)# B\rVÈ@Òv#¤8dÀ¼;§)\"FÒ,:2ë¸Ò§órôC\n6ª´² ª\n p÷¯[3fÅ.óÔq´ª'²P#'v!)N*øÔÑLð5D¹5&x[ðÝãrDâÖ¾E+\"õ4@N°c½Tj0 Ñf	­\r%q\$¼æ§´u,§Ri2©nfgc8£¯¢ÔßbÏ	bmG®¦\0Ün\r=MP,õLÐsQB&ÕP°1°}Õ¯YõJÐyBU«[Õ×U´N¥h²-DÚ¬Ksg\"âB¨'b{(Â\ngh\nîÏu°Üä?Æ¯¬\n:î½`f²;Éß¼@àæ®¢ )9D¦¦õÂ\n\nÍMLÕ]ÄTÎ'ÀÜ&Dl7+â&H7(±Ydülí¬ò'SncKYW.}Z[rêÆ`	,´âäÂÅæB¾-fg\$äàîªI5üQ¶ À";
            break;
        case 'gl':$e = "%ÌÂ(o7jÀÞs4Q¤Û9'!¼@f4ÍSIÈÞ.Ä£i±XjÄZ<dH\$RI44Êr6N\$z §2¢U:ÉcÆè@Ë59²\0(`1ÆQ°Üp9\r0ã Ë 7Q!ðÓy²<u9cfx(YÁ¦s¬~\n\$g#)ç¥Ê	1s|dÂc4°ÖpMBysÍ¤ÙB02©¦jn0 ÆSvÝ£ÌÌFý]øÉ¨9b\rØógµa®¡8ËÉ²5EAá5«iÃvÓUïXÙA:^´ç¨ÝZ³Þ:n·<oUÁö½ø,KVßÆÔPQôù<¨ Òá§ï»û\rÊÓú÷/Úú!2£6	0B¨ç	£pÖªèJ~I@¦4«#*Z\niû°¢¸0Óþ¾B:BH`ÙLr(¤µC¢¡(Æ8\"~Ò2~r,Ô¯¢È É¨â2­ª è£1/²ã¹îú0£C|êÐ7³\"Çc»*2\0xï\r`ÌC@è:tã½52Â~2£8^Úãñ=>AxD¥Ãk+\rÃ3*¨h@x!ôL2¢c0<² ÔPÚ9.G¬ÂÃhÞß§O+ÒP·v\r4Ø¨ã°ó ÃxìVSi!Bº6¸¶¨(J2ò­6ýÃqË Ø:­Òµ¯±°Ûk¯këÅÚÐJ<0Î£zdP¨r+6,ªé--ò5±Ö5`àÃ«Ì³h(3±<¨KÀÇ	¡\nK<C{äÚBmÌýÚã,(aª&!	«Ãp(©Z¯l5(7/Ã?&£Ò.ÈS[sd7\$b4+@\rb\$/,zÇ­Ó\\ëvf»]cëC\0í;\\V¿í[fÃ7lÙ!»²Èbë±Ñdµâ|(;Zò'1Âb(ñÈ8;sXöH\"@S¯r\r«Ä¿?ÔcÂ9!0k¾9BCÍWV¥@æFÕ(ÒÂ1|¥-°CHøívØ\"LÛtyÛh!¢äÞi2\n\"Mò)9ÂQ«RÆÂB5p*ú,·ðËfÃàºYÒÛ(É©«47%RDyLynm¶6EPK-¥%ü§öPZA!B¨´Ê±	\\Éa~à (iðF6þÜ0N\n.µ Òàþ¤dæ^¡Þ,ý(RÍ9i4hx¶ªb8^° JÊH9'×ðD	ÁOà@õ¡BQ8;©®ÔRá¸S`ªc \"Ñ6!g|J©WÉÑ5C¸ÁÈøaZdq2STØayN¨%¸@ØCN+SRHæAr~P\n\n+¨u¢ÔjR1Æ%0ô¡SÊÇ5çÝñt-!3F¦yLÅØËI@A-±ÙàÄm2HÓTõ¢9Hî8i/å¬F^k0b´GÊ²Lá:ÈrìÞq 2WP1áÂ Ù2¬*Áâ	@#6A³7yÏV~[BH\n7¹	ø\n\nX)&ß2KÈ!&Ì³jJÈñÖ\r'`xüÁÃ£ÁK¡½2ÂìãôpfÐÌPAxFkPÇGðt¡H;3NQf@T\$f0Øt¦4ª8'TîÓÊ{N ÎBÔ2á\"1øpJ o\rs\0!0¤ E=4ü\0©è­GðYiã@ÞàYSuz\$ü*o`fMU&²Z³\"äõ\0P`Á=0eù)ÊÓÃpÐ¸ªC°eÉmWx8>òC'aI#Ñ.NAÒfÉóÿ6Ã¸G^,O\naP·ØÎXÂ\r42D)Gàêyì·ÒË²ÔÔoÌdËÇz@ï¼<2 *ÉÀ»#õTÏ¢\rÁ:s \0A\\NðF\n\rC---I#ÇXàhÜe¢üØPðÃ<-cèI#ô¹L&¤  JC\nT ÀR¦<'\0ª A\n\náp@xR\nXâPB`EÅxTÎÿWbîÇµÎÈ²f¼ÑÁ}f¹RpJCd·±« }C¬\$K	ÆGÇ`DVV];é¸×ÐiÒÂkù[<XQ½ÌäY2Lùmo=½CGè¡Ím6£Vß ~}HhTó¶¬Ó0sHlâ¼uÒ_Îy}\nÄþ{A¥òOB§¢¦åO×§j¥ e-\"Q]3U:x\0*âcÀPXÁ(îàÓ¨lÂa»\nCÔáB¥ì%ëÄØ:øpXbFÊSß9æÍà®p7<¾s\róåPMi<«r-\rÓÁ¨e\n\"`Ú?dt¶UBnûÒ\$¤â]\nÊ/»ù³ãjjO»ÿqÖ6ÒFÃYrÉ²®ñpÖæÑD°J3ecïCy08[Ì­.ÄùXÐzv6[ÀYºw`FTííÍ×åñ1y^¸X*L2u8¸<×D¾^SåµùØ3½æéKÊ¸:ï[\\auûÇ°¸ÓÕë/{lãSêæìzjçUÉÍ#SÍÃöþãØÍ1;á»ã¢UMiÖ¹ò.ùÛÙ(\\Ý`çvÎújz¾pJv`­öo(ùüzuóßÍÉp_»o¢ÚùÒaw2\nãúå3ÅJ;T`ýb©6ø\\å(ZÍÉyr9ìÉ5Ü;¯-(èZÒª¹h«uR\n18¬	2JäOÊÆXy4p¨q*­<1(þu©)i@É~â­üÕÛÚ¿°%ØNúie¥GÚüïüÞÇÐsEDHC`ì×ã¬ÇÞIg¨6\$Ààx\n6`'B<¢0ùB2´ì¦Z0È°NÚfEFbÃ'ììz«ô-¦`àD¸¬àl0r7äÌÂÀØÁ#mîFÅÖ]¢\n.Ì(LeÌÕ£òt\"üü¢©ÂÑ\$~¯P¸¢¨^p0²o0¶ÏÇ-\$ÔÏm	¶Ybu\r½\rM\04bZr°í\rG6þìîèð¦îà©ÑABø(pü)O\0íð©ÿp¯\0n\$\"¤â°\rÂC À0Àq4çñ=î(6	rôÀ¦Â5®*ÂÜá\"¨É8&\$¸±\"!Äú'	 ;NâíøÏÈÚðDW\nîlqa#q*D+D¤kNC27	¨|@Ú¼\"áì¯Ý\0000°Yâ4¯Ð­ÑÜ7°Ï­Ë\0ÃËpªÎ*¿B¬qõf¸ý¦ÀZ\0HP\r\rþ0Û cÊqgçò åì.%f@@gÄÝh¥üÛgÞ<¤ñöK\r!ÏÔ0f\$ÏìÑë\0_2^\$Òc%rlÌ\"bhýI&ÒU\".6øæ%Ò\$#a(eô]âl+§bÜZc^.<lô©8´ È¿ä\rr°«I6)®¥1·,2Æé_9+ðñ?,2JÿüåºF'RO/%/Ri ¢ä2ò¿Òrßç¡Ó\nr­å#*-\$T!Âý\"ë©T¦Jû0ñ:þÓ4}R3Î4\0	\$`ÈÖúm¢&#¸¥¦×Í)m\$#àÞñBp(c ìÓh.h*³r±èá	&\\ÉHB46HA\$ÿî±\$îÌý(K:\r3p\rVcNjJ#Ò¢`ÏÃP.Éù<Fê¶b8J2g'ðÄÀ¨ÀZ%ã¼\"Ê5nÌõÂ*¾ÎÞ!Ø¢\"(sí¥hù\0(^V²ÛÓÌI3\0cNæ.f)3ÍÄî%E!ÆÐ³3ÇHâF°aB¥íDæ4AË¸¶¥Ü9#&ÍhK×>R>i6\"y¬ððQ¾ðÐÚÔlîÿ¢ÛHpè £244Øñ'\nÔIH(.eIÐlÏ4¯fâmIQaf8cÅj·JPÛ(f°fÄÑtäfì/å4Bnä Þ¢#c|ûãK4¶ÝD&^MMÃ\$wQ\"äCû¢xBl¸Iã¼NqlØçÕ6r´dºðeÀàÃöCgC(J*Ü";
            break;
        case 'he':$e = "%ÌÂ)®k¨éÆºA®ªAÚêvºU®k©b*ºm®©ÁàÉ(«]'§¢mu]2×C!É2\nAÇB)ÌE\"ÑÔ6\\×%b1I|½:\nÌh5\rÇ4¶-\$L#ðû@£'b0åT#LIRãðQ\$Üc9L'3,ðæ.´N(Ñ	\\aMGµX£k1UPêtf×OÄn1Ì[	ÉÉSVôqC£ælql¦{Q/ÕCQD#) g¶+n^UºÂ¤ñíVnB¥¢°iÿ'Ì±k\"1hDªA³àèbÚ;9QÓuý´vGÓêìJ]/è)\$Q)·¬\n*fãyÜÜ£ä7LÄ2ó>2Y®Ä¼O²>®½(ö¥¤6²\"ÈâÎ-¯z0Ö¡ëDB²i©®Ã¥IzÚ×# Ú4Ð@2\rã(æ?¢:	#Çq Î1¸Â:#ö¾ãHè4\rã¬Øæ;À XhÐ¼ÁèD4 à9Ax^;ÎpÃF]áxÊ7òèç/Ì!xDÃl¾Æ46ÉãHÞ7xÂ@ÈÃÞï¼ñ{Ï¶Ê3Å<ðºÆTn2lèUÚÃ«ïäÊ%äÂ\"÷¤åH+#Ý% ¡(È·¬úç\$õ2¼kLCS!	\"N-i*â%K\\AZ4,\"U\\!h54çï:°é{]Å	{)S=ïu#ÄâR¢ÊzÄ¦³¢ON!SÆ´\$ãPÖ[Èâ%¤Âù!H²w¥·)TÅ¢ÄÔÍÂÇ!á\0¦(ÈKºõX·,PÌ7OCÐ~_Q¥¨òUSVJXógp¦uæ)6fæ·õf£¢È²^,	+?[O>0;Q*y\$¨T>£°ûàéõRD\"'ñE6'¡\0Ú:qôËD/# ë_Iýz9#Í+K°¸ætÌ4ûØËÁËðA/Ì\04rØ¾î0\$\0007s\\äwÏn#(ð:OòM&¿ÃKC·½~\$UMqë.UÞÛ¨îs>%èâZ\$j;r±.7.9bÑàá©öÉ\"ÄüMN¾bÉNEºÂÂÓ{JNÒÂµ8\$¼Æ£xPÚ[Qâº5¤ì>ÚåqDHÂ!:KÏz¦SË\$}K)o©Ý£Päµ_ic2Ké2ÎSJkM©½8§4ëÊOù@#@@øÔH'\\-¦\$å£Tþ¡Ç}d:Nà)%(PÒTÿb,øùC`¼M¹8'\$èÓ²xÉì9'Ôþ;©*D\"Vjñ-n@öG!å`Ç`¶`Éé'\$÷8îD¡ËOUËüó*20KIÖ\n ¾¤XP¤ÁU\r°\"ú\nA´2¬0ÂDaÕ#\$Ìd l\ráÊ%T®¹}s2p:á@a\r±­E£QÐ_²ý0@@P\0 ì÷^³\rÈÔ1ÂÜÃ oKÈ4`ÒæÃ<K\r¹xÒ\$?²u4µ´náûY04ã¤ZQí&4ºÜÿ48%¤¸\0rXaÜ4ÆMT¾Ð0Æ[ e@Ï \"\\sD\r Ä>·Ò{Ñ%>B 6h#EVfìsuMÀËHnÖ¤ôÔ¤`Ù'=æ5F'j1¨Ø=°KÈóED¶z³ãÞÅåð×9NÜ£qDDj\rZé\rÔ¨xS\nìBâj£©7qä²,\$*X1ËV\$TömmDSåØ\nÀa¡1(#IIHpEGí­S<×Ü\\'´ù\"srþk°£ªb0B#^ke¢`¾Ïy/cg@Ä¬rJ²má»iket<s(wrök+¹­5æ1l% åY¢BJÐ¬UÍ¶æBË­COauèWcÛE¼d\"Þ[@D¡!;î3ûÇ{lZþg·Õ^KòAá/ò?óÌÐÿgåÅkÆàÖa¡}äí¸µ1#TÇ%øöÇ×ôDDÞ ¦Ç¬¿		]§Íµ<s?WV|(ä{é=Ï¥òA07§yÄ`u¸9lêRÀtM®'zIEH -fÝã8H´ÑdMyç~¬ý[/Hæc%¸#Âp¹h¹÷DZZQvÏ`C5P^Ry}¡á>ZC@ÓñÅÎ´¥pCÙP\nVA<bzâË>W¤ùÆ\$ëbÙ ×á¤kö¾àA÷,f]\0 Aa!j½ÌY	R\n:\rDHmVÈË\"~`¿)?=0ÔC\"Ñ·_cí±!xJ&='~,päJjG	Se\"¤Dù*lÑ)0û	aëýÔ&Î4)?ÒC\0KA´¼ÎRÙä³ñ(µ5®Fi¢áÃK}¸FVæLS½¢OGwÍ½¼ü\\àer0\$Þ~\$¯+QµL·lé_%¡¥«T6î?,vJÛyÛ;jÁy¾lÕìC7Ãc¬ÃER}¨ZR£Ûít7¼ÁèY^²>!EÄPI.[åÛ\"øº+s-\"2½£³Ô)d>kt*ÐÖ=E%\nýkY§!°3 D¬3È!·\"0³Bw	OA+BÄnõ'zè¸è±÷û{åùßÊ£Ë/3y²ðÍÄ«ÏßæèÏ2PÂÈç²T[[víÎÉÜ-Õ»Q¶©9Ü§ØKüf}ÒøÇÄD?9N>#£q YÀðæÓÀB	yÂE_î¬]¸îñ/`Ô³×RUoÃúU­µ½ÿ4øo±'£eº%êÌ¯bÈ2ã%ïÈTm.ÍÌÜmCÿ@Tî0ÎhÚ:&°»E^\$¨ö\rzæ{o\$.Ð>f¬YíLäi«´2äH¬ðB6BÑÆºöZëjõ/®fîÿðRmh\"Î®Ê¾>lÖ\$¥üåklhcºämòZÏnèüÚn0j¦þ%×®\nMléïT>lÌf÷%á\0ÐxuÄH0íÎê.\$ÜHÅK[èk0ØäpP*Âð,=ëPLôÐÖÂö¢þFîÄ\0È0ÏGi-ét)Ù÷`Ñ±\"?élÐ1O)kîvMù8!(ñNïn1b_°NÍÐÆc]\rN»ÑnÍÈj¶°!/,d\$Ê0b`ïºÕ\"?Ñl¦uÏHk&«\"n<îb,jÀàÝ­\$*°ïL<%¦.:<Ó±¶XZ;	CßQÏ\rBj£óoÞî äVENÎ0ëb<È;\$H;º;\nÆâ%ô®®(£Ê¹%p\0·à@K@Ì qï|ûl#mÐp<ê8UcA bÐ§Çd_ÎQËEeÒï|7e»í|\$£¢c¯1ª¸â.\"5ON½çÄ%­6fË½KjÿL56\$¬N_)à\$­Ä]ñTèp*PêcÛBDq_+¼,w+<óp¬é®ÅÐ+,\rèxãäj§º9LVYFÅÖû?¥Þ^±­júEZÙÄêÏ_Ëa£.þ¦ ä¦hïK8¤#(µ)ßâJ¯ìP_ÏùDPÜñ®¤fY,kLÝ²!Xÿ³Z¦Þ\"R¾]ª-®b\ràì@ îË`ÊÉqzÄâÓi¬+ñ7Â";
            break;
        case 'hi':$e = "%ÌÂpàR¡à©X*\n\n¡AUpUô¤YAX*ª\n²\"áñ¨baTBñ¥tªA ²Ù4!RÂÜO_ÂàªIÂQ@Ìèq¨*¤Æ`¨j:\n°	Nd(ÒæO)´ú²§!å\"5)RW¨	|å`RÎÅ*?RÊTªDyKR«!\nØDµJ¯\"c°U|\nªÉÔ³u%Ãg\$êI-=a<fòHÕQHªAÔ´%Â¤[M ©ª.í_ÁDqäe0ÌµºÅGèÚøþYH¡éêsz.ýK`RC¯3Ìu±e¨ë\"#Iùr·÷®ôêU­»Üì®öIáBè#ÐR¤E#ÔÉ¿Ò >+ù¼IÚ§5)\\§Ò/ ¯b½êHºhó®öïòjÚ¥Oòæé°M¢hÏðå\n+®Æû;ÈºÕ¼)ãî¹HP4J*í\r «ój-OÓ4@#M-H!¢ä& ©1³è|H\"±ì,·óL¼D'ñHö?Dz1Ó¸§20c+2ñs50§ÎÐ!H(RjÙÄ-ì~»dÎOtÿÒÅª¯B¿4<3¤¬Ó8ÏÐQJåMK³Õ\"O{\"	äØÉ;nTTt²rýPòÃªRØ2POÑYHXó#Üþ.jûÖõrF¡!(Ô^@ü>Z°re`QJ=ÊªÛÔâiqYQBk¬OÖ`@!\0ÐãÁèD4 à9Ax^;âpÂ2\r£HÜ2ApÞ9áxÊ7ã9øøÈJÐ}2§Ïá+*@KRà^0åôÝÎ×¨ÓyLFmFü4mäa;JM¼·>ÏôÌ£O|¥6Q-MKPóI(ÙúÎÊÎÍv§/XkJ=:Õ¿D¬	 ;¦ìýlôµrX®4/ýþÓ'ãoÂ¿%&:Iñ=-+©SlC?ixp-Ôö³?VCv¤^[¶]ÍÎÎoå%ªÌr÷íöõÝ1I-Ñ\$Á¨ßñÝüÆ.p;D¥[oÝ,SÞ:6`R\\.´wd¾i¡}Ä½·u?£\\Ø÷©óÅéRxþ	ñ:*Ù+½on©7³KÁd\nbzÜ­uÝºÚÇÏk¿Hªí³¥Ô}ÛWK½ø»g­[ÿtF¥êÆ¾ÒÊzÕMÈ­t@cÚ bäª¶¶óp{TO'ðbxfb\">f	Ø(Õämq£VL)0A`Ñ<5Í§¡fm×ÄD(oa©(.·bÀgðù_AöÒê*+HÉÉ°F]M!\n\r\"ónÀ¨gLñxÁÑ·OÇ/6^å\$4\"DÏÂdÎ,#X>96ÂÃ×#MPÙwÞ¿ÔZþ\r¸+ì#°jI»E\rLf¢eZª-ðÎD©GÑÁ8B&dµÌ¢ÉTÑrØí!ªo-(ÛÊè6øKJäñGÉWmÑÀ\n\rÐ9D\":F¤sÇU¾äxªÂt]E'Éáï.FÑI¼øVmÙÏ>å5,9É	Ùv¢WÞ¦{nE°\\§éôm4ògg'Üó±ô¾·D­çZÜ×Ntå(<JSkUËð¶\"*UJ\$Ìºe\\r#P®=OÈKAX`Ì!0ÆÄìY1¦8Ç\"d7àÂMMeÝ)øÔÎä-4.[BSnÒ5IôEÜMäºþ_J¯`KA¨h-6áXMôÂq»yñX¢­m=­¤T!*ø;8ÉÚ©Î/.QwÆZø²ÊzÍoqB­®Jé	|	etÁÃ+	al5±&ÅX»clu²FÈÀtdaÎ¨²F[¦I316AW4ä\rx5ÎNÖe8aJø jqw(1]GÛrõ¬	Ö9JÈ.Qåµ½b%å:Dgá×]º4ìróSÑî×U¤ì³{ ¨Êï¥X39©\$Ê7µ&Ü«¹_æ¹dOº÷tK.h\0 ì)uIÈNÁAZ\$jÍÅ~gcnxGoÊ/jôwÎÁ¾èè®h¸ÁuÝrå=r×|£W0<;Åâ#&waëú]z*YsÄb.\\1´¹y%[>EÇfÓ/Â}.¼ÍãEâËrJ¬1*&5¢ð@ÂRÏµÞSaÌBÄ_>=ÂjåcHÓÄI.k5C|~S²lW2~ú9ÞULö|DVIZ¹XNõADéVÕRk\$¹ïQñA©RÑ®EXÔ]tiÒ	Ä2­ÝänAxp·u(&L 0É\n.7²ÙA¤¨Ssª,XsßIcê±\n<)EÌQü\\-[¨2.mÇèÉqoGÊÏ(f÷¨Å&ÑRj¬µFv~¤FoÕûçÝäTF\nFø¼Tè&×/=&ÄUnfA÷¨»¯ék®7Zá½1Û©õÔ¶8[Gß3£Xâ6yÜû¦\n wBË¸ÄÅ^9ÐAfÌùZÆÞÞ¹·74¥VM¢}F¬Ý´hènn=÷ÎO¼ðYöîWg½RßME ÌþÖ£PÀ¦ù¶«Ûz¯¶óO1Sµ<üÙÂ6¼«TóeÓÑí}iòÌÏ~úùCîM}x:vbØÞäÙH>,9?Naxaáv\\_'òh¥ÒÙ_1Îd+@èæ0,Á/ôÀoIt2é8;\\;¶5¹î\n¥Ëa°s9eg­¸O(¢UÚ7b®cêÑ,ÎS^=ZM[êçÂ¥6CmÓª½í´k­^*¥gÉÿ+Íy,\\\rÍOgÕâl!ißJä5¹ZZúÿ ÏÆKÂ.ýÎD`\rz+ïüG²ü\0?Æ{H £FÄ¯bûÐ¹®&ÁåE.ÉíþGzÚmL& È®Hðº(ÀnF¤»Iî_bÆ'6èþÌ´Þ\$8ÐcÒ¶Fê@òIdY&½¨LuêúlN@`#¯§® ¬:¦ÇzUÖÍÔ¨\$?ÂtNt]Æ·\0'løInÁEótÄN¯(CÚ}v_Å*¼,¤(æn ^ò«XtDpo&êAþRÉbýâpàHä½j&°¢Öã\"B#n;,Ê(iÿcó¥^o Eå)î¢âaH0KfpéèAãô0Ê,T±&mÑ*tQ0ó7ÐKéûçËlR3©RúáIËv@2?ª´Õn¦(ø-Z.F*\0¼ÛK±[Q_%pª3V±R±O1	Ñ½ñÂúQÆ|Ì¶ÝQÑó?Î?*\rJZyÑ;o&@½(FÁ²uFîÂz?.!æ_4®oò¥9\"Å´,i#\"´9éº>Nþ·¥Iå:«þ3Hpd2¤M%/%KÍ\"5&¾ì\nmcøOD8hâsÏK§»ãî÷1öËòlÈ|ÒDôh03&â4¤0\n;éS*ôÒNhÛgF*²¬ï(ð7+BêJ)î¯*í,äå6Â©Jï2¨?hM,²°NÎ+.'Ý\"­\"/ß,È.GDÃÞ²Ó0²û\$éhvÂÄªwJJ6é¶ýM{Zi/*p¸8FÁD¬­ñJÅcjpÆlÆý5ÓNä3b¸Ë(lÙ\0ð&ýäM.þLîþâ°Ý7©7Ìe#F£N6vçzýÄ¤cRt.M?GÑ7 jz¶°¢í¶ñ@ïrî­òò^²öÎìñl½20TZó'2NÓÛ1³ÊïNç>s@,³ß?¯µ<ô3?%ë?rá\rêê<åH'm4\"EÒÆ®È1	 ÒVò\r5sSQP÷¤mÅ\"KC3ÛCQÁ&åý	³T@F08.Y¤%?ÓðFiî¿´\noÕGT-Hjô~»äÏHD|å*ëHÐ>ñ§Ï-Ä?5q/Ð,kRM\nß>%ëFæjºÝ\$¢(­p*tQþÍÎLMëÍî±ò±¢ãõNdLjºÒrì_!9\nI-W.D[µMîùG¢=´òé¹KéZÎÿ¨.R	:îÉsòþÏ¢kÔ0JÔ	Iò2þçÕ.ß03Ù0t|ÏeW0·õWÕmuqVìH6®õRÏ³WtyI´°}Zc2ýI©AâyYÄù[|4­IÒ´SuÁP5JÅUZêß\\o¶ý#h­ío2¢§\r=Ã­òã¥QlP¯¯ÏWSÕE\\è`èd¸Òÿ[Ug,²È_V\"Ý5á1-/U®tL]p4À´Ð¡ÖNÖ&Ì\nupKd´©Y\\o%eeëc¯ÂEK½Ç\r9ÐNÆÖ^µyX¶=0l\r>ò«0tiO\rh²5{i5ïe¨HÍö?qV.6¼ýÐkdÔlv?_\\®Ìÿc´l²øph[V¹ Î?Ófrxl\"](0U\nT:Ð±°µG²ëhôa¶1\nÝ\nòöYAVÅa±d0ª¥jí)×1g3ën¶AmðÚý%sc1écgtÇ¨°ª¥¦pÕ¶3`¤ÿ6SopqæUô§[³	VEöoFI_MW~7Ìw\\¬K8ðK\"sZ\\i\$\0É^±!Ï2ÐötiDRKÃi8£zþSÔxÍóËw­Kls_Ï^di¨\rWz&d¸U5F# ª\n pËJj¿nvr¡.yP»ë§G«ª<wtr¥ÜÈ	ÊÒZq£A÷C´a¦5ªu¹¯|Óæ¡\n]pÙ\"7|_JhâØý-e|7pÀ²âØømtåh7>wÔ)oã­}ø6E÷ääÄÐ¦WÙ 2çQA¤\\^·?qWøSt»,ëzN!Å,ÊpÒöjÓÑþª-qa_rX??Hwn¦ZpÑ\nhsqï×Þô¼øø/6t±§ÏÝ¬ÿ8T­|µS:4ÈýC»î#iõÛÙ/OóÓhÎùylÙG7øç@ïCn­Þ9×Mm×'H(àÈTy¡Éh[U]\nÂØz´þ1ØLÂ4wXr³+@ÊÞ{Ê¿,é¹®AY¨NÃ%ñ\r6=×f¶<Ük®\",|rÃÌó¯¨ÿ(MuLTPaFý^ùäonü²Ï§vÁîñ_Ëf¹è3Ù(¹²ëgywJëbxk_iæùÜdgÖcM|7}NÕþ'¬vÁ";
            break;
        case 'hu':$e = "%ÌÂk\rBs7SN2DC©ß3MFÚ6e7DjD!ði¨MNlªNFS K5!J¥e @n\r5IÐÊz4åB\0PÀb2£a¸àr\n#FèÝ¥¬äQÅiÀês'£¡¾jbRà¢I¸Ç;²gÇ:ÚlÆ£èâ¦jlÁ&è¦7C¦Iá¦i¿Mc°Ã*)¡³-éqÖk£C2ÍQ©\rZt4O©hÉ97eEyÔAc;`ÆñÀäi;e·:ØPêp2iÑ3DÒ&aÒeDÙ6áì7{É­W±ùæÃÉÓÄcø>O£æ]\rO@¼,­j).ÈÜ3B¢:9)lr<£C°\$,2Ð\n£pÖéì¢9\rãð¨.\rJè¶«Ø±¬«:da`PA²FEhØC#è£l2ÖCÐ@9 ÃjVã´`@;z9»ãZ#ÆÖm\0ÎËïÐÀ¸Òà9³Cª²c¼Z2\0x\r	ØÌC@è:tã½\$2âE£8^ãù?C ^'áðÛ°Mô9\r¯Ò7Áà^0ÉP¨0OðØBè9£Z8Ú@P³HÓ ,9·Í©Rzv]\0@F45\"jb6B½pÂkJ2Ai8èÜ÷JuÝ²HÔ×ë-v8B­òÜ÷·-ØÎ<¶PÐ:®µhè¨­ê::èÃ+2ÃêØ: Pì-\r°±0Æ4ÈZV3éØÎ´@Ó5fCRHä4?uìZ1\$ØÜÎ`+ÇfÃ[X78`P×©e^ÔámË»,°Xáæ@61óÒc'^\"dö92yø6·.Lï³½²ÛÅ\$ÆMª»Zè<)©nåk7öÅ{¼¤Í~2æí·F1ÍU P ë»0ÈÎ4µ>L)`\0ß\nqhÞ=<\\Ü 9¢*c¾\r³5s òñÕ5ö3KÀÛÉß¿&XöZ z{AÔ©Ú(92F9lÍeZ%^ æuÌ4ãªwçÌ@Ð0iP=½¸£wÊÁxG2\npÝ6Ö%{ÃKB\"\"P0Ò+LÇE\nÂh  Ç\\¸pÊEÉá×@DlÚÃ®oÁTÆÒjO\n¼ê*Ó\ndMI°312ÜóP*ü<0Î_à]¡´Ð0@Ê\n)5E\0ÖebN1ÊØ±¢bjªáBmB`#ÃÅ. CõéyAõ¢RQÊAIu(¥`.S*l7ò,ú:°T`ú7ªä&÷[!\ra´zdTÝâ±x@á©²²ÈS{iõ?W^IØdÊ	BEõ¢ÔjR*MJÅhÓÔãð~OÒ6ªG¦]I¡Ñî=æÐAu%¦±ïÒ«í0¡¶=GÂ@O*'  &Å^Kéæl¦Âtb11¤*pIc&Q;5`¥ú«m 0i ×¬+p¶mÃÀä0H´þfÏhi!±ûã@Ïâ2w!ì×\$úmÍÌ5#E1#hÕ<7.ª,\0(L¡Ñ4] m%OT7ÄéÕrji®`MO2b\$m`7s6JAÙÓÓxòQiïRHÔòk{æPà*Ü[¥ò¡Üò0ÐAÂóê1'IP*D'%o\0@ÂRÄ=+èßE	c^ÀÈº\r\$+540S0td¸ÌÚÑÃA¢SÚ&Nt9¦×ãpÊ|É'éðÂNj_å7¦2A.O)5'aÅ2za©vHÕS(ÕÛ¤<OM5llÌA\riÀ(ð¦	05½ 4#ÚçbVR¨(¼e¼ LÿaPNÇ¼^AKo¤Å\0¨-YL¡ßWjU8'°a\$§iÐ @¥ 2k ;	zImË\\Fñ¤¾É¦e[lÔD¼Ü­Á4YôÇ4sjbÐÁôÃÀ)z¥îQÃ)ÑqÊâ>-ä¼R1PHjëFÉXÓMPá´ôªA«~GDe Ä,ºP#d;¶ê\\*\rC¨µ¶¤6÷ý>VIGòðÄfCp<Ñ¢;ÚáòËËf=Ú¸G?ØybÐ]M³dÃ@µk@SÀGA ZB~ hðPÐÅÀ!YpàttY`oUÿIä\" Æ9ÂæÆ^«©ÊôÞN#7°bÈÁ½J³ªÅápk¤ì6hkÂiâ!¥ÐvC(wZ¥i/éÁ:áÒ'ÛøÎßÉÙæp<7ºî³FE©aÖ9ªÖV×e¡'Vì\"(Ct¿3ÃYpf\0ñÕàË¼©×e3|½§\$wV@Ýdë»2ÃÊ;£¸bàÆãëVÛJ±ý¹WX!GÅ§8l T!\$i®Îäµ2¦zÂÂp2®3Ãè^v]\0¼ª.Òbèçý®\"Ü2y©O@É´Ö·He+S)py%ÏE49Eàù¡:æÆ9®DN[º¶êÓ«îÖI×['+¯ôÅÒ»'Leý ¦u\"ÇÕCWíòG­æJ¨}í=My28,Ûe~Üû³ÎgÒYé*ÎÂ§¿v¼LºW§¥À#sÌaôc(Ú¡¢³¸¸eGÛÖÉQù`ôÐµE÷O15ºfµÙûÒ¹jàé×¶ÂùÎ®µµd-ôØh\nbZI£êj¬¡¦9Ã·ÖïÓ#¡°è_ÎF°FY¿·@,\\ba²6óiµË¬¾4âV[­ÚÄÈ%04gf)LÚ!lë­bÎ\$<²®#LEr#K îcæ³5LX/` Wde`uj)ãT\\RW ÒÆìôÅpiÌPÅL¢1°D%d¬4\r¨fÐìÊ#g>?Â¤Úgà[¢Ú-ä\0ÎE¤î\r´z¦.\r§ælê(jË9V\0ÈNé¢ËFã\0ø»CÂÔn'öÆ	®À\rÐ<Ç'pëðÞ±íÃ\0ëÚ=Î\r|CÇ0`ã)± ìí®°ðâðÎº1|ñ ðp>:ñ(î/cs±ð.Üè±%Î¸½­È;l ÂP^¯Ì¼ÌZÁèbqXÜ±^ÝØ)°ô#°ø\$­¶@>& Æ @.ÌL\räÌDàÂùjê:oí	åQ¢)J\"àÖP\"I!R1^ç6ªp#ë2ÿ@Ï2LvsëZM`}QÒÅÂÌ\r#5°%QjÜÑ`ùCLLì&LðúH^Ñã®oÂYe¥0\"%¢'P0²%#ò°ö½°¿\"r>vìÐJÏ9\n%#PÖ8ªR¨²(½°û%2a#òB%@%êÖ¥¨o£''K'pÜáÄ	bï¦2	°\$`##vg¬<³ãB¡\0eR2Õ/øJç3qC+gÁ&Ñ½ Pu'­Äpà\r-¸&²ÜÒ-HòJÍòá.HÒû(ÒÃG@1¤°1ÂD¢|ù\rÞ¹dxêÜJ!i51Íï2É2G#é2¥s9ó6/\nÜíu2ð0íìßÌßMÿ+±g\0SZßÄ½.òÉ3\\KÒÑëÚ\nr2îÉå7òprLYíÈh²%PÈxDD~òÕ6OO9î6Hb0³ãFY/-\"TD\rlÔñBJ&ÂC{0¯ºB0Zu¥Ú?CøÎhþ«ÒþcNèäÎF\rSî7ã1?Lã>ü&KÔí	bÒ:lh#¯ºøc(\\î?ã^]¯Þ1n½BÏï®å´8)¯áC/ù~\rV¸j¹e`N,c\nbTðÆÈjNâTËøl¬gÀ\n ¨ÀZÒ0ÆBbU'¾u\$Î-IIbeI±9IçWJ0\"\$\",\"LÈc£\\2bD\$d! ÓF\nJ&0lOhüBèCó¸&KÍMãð@ÃË@,5C)1C\0Ì Ç\n%@ÕÐTâvN(På*MÀ?cÂ\\?eH/·Að^2TZf÷Åÿ°\nÈÊNmÙ@vF¹gö8)¬OéUObb!ÙU/üùÓ¦VÇÖ&£ 0SH2`èx×ÃPp#UqaMM)ÐÓB\rfpç µ®	°º5âúÚû§k@gs¤XÎ¤Ò®,	àáX.dG.)\r*ó×.\"#=`B%ã\n	\\¬7ï´*«Uä\rV¢£ÌÄfq`v=MÀ ìu ÞG\0îÙf2a8)ÀÛ=qR±æ{è\0.pûAfÐ à\0t\r Ú";
            break;
        case 'id':$e = "%ÌÂ(¨i2MbIÀÂtL¦ã9Ö(g0#)ÈÖa9D#)ÌÂrÇcç1äÃM'£Iº>na&ÈÈJs!H¤é\0éNa2)Àb2£a¸àr\n%DÍ2ÃLÏ7ADt&[\nÁDqäegÒQB½¯Æeò\$°Èi6ÃÍ3yØiR!s£\rÃ6Hqj<PS­N|L'f1Iër\"É¼ê 4N×#q¼@p9NÆaÏ%£k§IÒät4VñÆ-®K7eø÷¸Lâxn5b#qç)53ò¼eìçÍÞã_K«b)ê»\0¢Aàu¯R`Q-\n³miÞpCxäãmðÚË£­Xäº«ËÐ¯é@á7«£XpË^°#r&±jj¡{pÖ¢èà8@HÂ1i+¾1°«B9®C¸Òà4Ñ^c¼2\0xÆxÌC@è:tã¼´'Q4EÀC8^èpç\"ÈáxDÃl¹&£4À¨ãxÜã|\n\r ÒB P?¡è è9C# ë­\n4:1ã(ØýQÌs¶Ú?\"(ÝC(ÚN:BºN7;(J2#qúÄ%ã`ß«5\"V:¶\rk}k	xéW°H(2Ãê6\"°Â6£*ë?\r\0P¼;ëÎ·£¨ÑKú\"±¯{*Ñ£8êQh´%H ¢^5%ã:2h*²¿Lû|S´ 6¨¢>8A6bPÊÄï»oÍÇp¢±bhÐæ5¬\nF:Ð¸Þ:ôc\r<ï¯ØºêØ¶m¬x\nNjXkxè­[\$»pqzÆX\"õ%@PáhIú\"ø\"\\ÙQiÂöCó<OI¤9Âì3\r(ªß®Ë@®i­±í®Õâ¹iÛ¦2\rðÜïÎË¢È3£è5â\rêp;¡TS/N2Â Â3VÉknã-^2ÓU´­:N¹\rã0ÌÃÄIIdX¨7µÑò\"ÐìiÖ>\0007é¨ç\$ÐýÂmeN¦®ÊaJµ.ÿY:D*5)²Y9%°1,N9I#E[Q'J©+KÐï.|Rø]0Ìcp^2NÌìóü,&T3¶\$ITé©Em\$½%ò·Ðé'ô!Ã|J2hJ¡¾4²ÞrJI±(¥4ªÒÊ[K¯û\$Äã~pé5¶x\rªµm\r¨\$P¯Öûmd@®\$BDÃ£nC%àï@P§Éc\r!ëäÓ«6\$!ðä÷¨rp\\WnÝËUÎö- a\"@·PÉJË.zÃ%úbQbð@@P,äÀ¿HLA?%@ÂDØr\"pÜöXm\r´v5èÄ\$XNCb®ñØên\n9!¢*ÔáH!F­¬à|DH\r\0îpCDÁ)Þ\\ÆG\\7±ÂpÂRÒRYihXi6!µÚõå<â2AÔ£kÔ\0u\r¤ð(`W©R-yòfML  =­d8Â`É»m-d<bbª%:<,±¼áðâ³P( Èr>¼%ñ8j¹QB¸ðÃ+nbò'0¨¼Ã<Y¤ 9À¢×B¤åHxG)©1-á¬£0ÖØÛ-8ÄüÒPé¢N\rÚ®'DåT¯&(Ô ÌÆ`©\"kg¡È#ú5G\"	#®¨5:ÌµG'T'à@BD!P\"ªê E	¾\0 mrUöÄ\$6³Héu]ëÆ¿%S#Gúz{`È2é9'´÷&ÊTÜ)3x¡Zs»jOÑ6ÄÔÚTø´;R}B±[PX\$òx7£Â\nRaMH(«GjZVmQÜµé!ú(¡´0BÑá¡xè¹ 0QJ¬®v^å ô³ÛyH ÚÉ¸ì{qðPA¼J(pcaqÖBÞÍÐ¼&÷ªfC»Rç=ÃÖ¢±±naxî½T¡=&¸|0ÚxÌ¹ \$ìIGa%*yKmoFçd!Å`Ço!óC!¢ì ³Ü¨fGò1V¸	ä^ö	©0È{4eM\rBÒ.}ËT\n!AEÈèi7`xÊÑ¬>«-B¸ä.iiª ö§³Y¦Ä¾[ Ø^9¡Ð4Ã/^pë-éI1x©4eñ^<Âgüó¡-U&:léÐ	±ùPh îAOÙy#Gå­\n\$d¨¢ë1(b¼ºN-xABDx¡!¨Á×OiçRÍ,6PÍÂiq¹3¾áSo}Ö@ÎÞ z¿ÛSàHÒÐ»°ÍÝCNðÝ!¥§j¤LYAÓ¶aïëg»oÉtàKyïòþTs×¹\\7CÉdÍI\$oaÂ4.0üDAIùj]ÕÌ&ºY¿+(ëW.OÌÌ@Nä8zëñGÃøX+D°¶bBÆ:Gá¡¶%[ûJ²f[©0_âÊe¼Æðn~¡-·Z·]wuðæÊKÊ=±Öõºw×=ßóâcÄÊ|pê`ä³*¼ç¡19|ÚhQ¢ÑªHºÛÿpáÇcsû·@ï +ÊÖï/ä{/îN©VªøËÑAÍ4Ô½K&0á­N®×'äq00õzbMë	Ç®>»Ù5;ùïª§j èÐJ_ñþ|Ëp·ïñZãÅ¹úTö¦ÆZ»ôÁß<Ï`ÝøKð%Å!BhO¾3uúwt{þÿ¿¼ir\"oÒôlB#@ÊgKx\"\nD`XàÂ1\0¦RBK¤(ãÜ`­,Ô£¼&CL1ümÞÿ\"EïÜô.¾ÿ°FfPJümé\0F2£E¨1^6Å\0ð`\"ªË@\$xÄV#Ñ¦:4#?,j¬nâX#\0N+¨@êÌ{	S\0ìå	0z0®¤Lt7Ð¦%E		L|c ÒÈ/ðá\$ù\rPØÿÇðÖ4ïßÅ*ÈÐëôÈ%Bq¥@ÿåôA£ï\r®Â.Ì¾,/Ó.ÉÍ\0CÚh¬TfT×cbpÃÈÏðh¸ÏðÙB^\$@BòÏå~R&Ù-Æ3\rñB¼ñ.PàØjG\rbNÊ¨YE7çb\râþÇfGâDÊ´ÜÊ2¦Ø\n ¨ÀZê-±;DÑ#E	°L&#@QHxeøpã7±f\$B`C­Æ2Ãì,*6 1±ØöÓ:*R¥H'TD	øUÜ-î¸¿bt=cÚ'BH.!CApY#MÂ®ýêdìpG#¹bþieðÜ>úÏÒ\$ÇPâN\$OÔáN&ýàÞ0-«#à¦d¢Å%TR'	D÷ìÈ	¨_N¦^G\$·eÜ:âºì´µ\"HGd\$Ð ¹K+&¥3ì<è,3%ÚÞÐö8¸²;,F#*K|÷ü_Rß\$Ã='P@ÞA@îÃEÔàÈq&]ÂÌq4è.oÇïlV@";
            break;
        case 'it':$e = "%ÌÂ(a9LfiÜt7S`Ìi6Dãy¸A	:Ìf¸L0Ä0ÓqÌÓL'9tÊ%F#L5@Js!I1X¼f7eÇ3¡M&FC1 Ôl7AECIÀÓ7¤ó!Øli°ó((§\n:ãðQ\$Üc9fq©ü	Ë\"1Òs0£CoÍ&ë5´:bb14ßÂî²Ó,&Di©G3®R>i3dÙxñ Ã_¯!'iÖH@pÒ&|C)yN´¬È2bÍì­Öc±¦lêÒD8éÓ&uëúÖLç¥ÃÈÄÞ°érëõs<Ix(läúÄÌÀ\n¬Cì9.NBDí:Ô7¤Ëz2¦KsJ;4°Ö¦Bf9ìÈk½B\nÔ;ÈXôËh7²Hó9&QjÙk&à£óX80cV¥++ó pôÜ:9`@%#Bò3¡Ð:æáxï3ÃsAs 3é _(¦ÈIø|¯eÐ@ÉÌe££Hx!òL+0ïð=IèÔQbÌ4Ò	J9-C¢âë3Í¹îP-\nRÞ)°:ã²\"B@ßÌtX¶£ @1*h^×ö\n\rãb:Ê+jín!ãk[ÄÌ&¼V¨}n+,Ì4Ý¨ÂVâiBY3[t5¼¶7BÚ!´­bÖ¹Kh*?@S!ÈnþGPÀÖã@ËL¹Îe©yD¬¥H²jhåR3K;­hF£\"\")\"`0·MCìTýSQÒN|ITUH ÇB\"óMfpÃ¦ÒTFóÃ-ÛzÔ\nCzÕÂWÈÝ'¾Ð¢Ô	#l93/°ç©ê¹²ôçc#c¬\"@SÎÝ²~\r©é+3ÒóOHðÃ¹@óCÑ)4±=Zr.ÿ/;À|sú<¾Ä;Å¹»<Å?o\n7ÈùÂ¹r]B¶cªÄ´È</{û\$ì<0!?]\02Ú#/Lë=Ã\$²=6\\­:¶¹H\nü/I·0ÍNL\\Tc+ywcæ!-Õb<¼=}\rÐX\\½¡þ#q¿yôÜy±'è77¯ú«ªä2i÷¶ï»É4HÎheÁÙ)çDwðuH®ì­`ÜÜ*G¬àÎ\nkE¡É+@\\DàX	j%Ô¾SeLáÝ4Á£Jp!À½C¼GSÀ>ªN8\$jÕÛ2ME¨0¥`ÂÚyO,Ãµ`¬1(7éIò]ÊéTÄ Éslin&Ä2hMOr¦ôâxe­Íäðp#&	ì8'vÝhvF|£2>ÀM+^7æ¢IÈÑç&°²UdG\nI44¤iÅ¹#âa^@\n A(dÈe?Æ09B\"{sù:§áH<Q|;eå\"µ.ïy-ÎÓÍI«5©\$Í§30( \n (@ê\0 TÕÔ+[\"Ù&L¿HK}¡%6ì[¨ÌC¤Ö¥èiRóR)r'0Q5¤E«Ê\"^FÎS&½;8cÎBNk9G¼2#¡&hyD, ¨ÃY) ?%icÑ)S&o	L0 4Ê<øù+Ñ(ÌUÈñðä8Í?\r³ç¦\rO<ÒSLéª­9Ì]¨3 pVæXAFÃ?Dð¹GÆý0A<)JYJøe¥}\$9äPö*q*mçxìÄØßÔü£qB­²¨ÎAìì\\73F\0Lªd0 &ªáOíH4µB²¡ò¿SJWÜËq-§q1\0\0U\n @­x &[ljd\r¬Yjº@¥¨ìÌ)LÛà»)ôa|³õÊj¬-×`æ ê+æ¡²ä<iM+t7g¸¤áûdE%\$HÖ¯¥ì>Äd»ÈRä¤\n[&N¦fH4±ÕË¨ÿZ¦~SQÂ\"XLi;7û¢q[Y-yFº[q0g?XûH¯Ã7ìü¬Ä^¬Ôz¨Ä\"òæ@o	ÕdÅ§ÓDX(Ã¶ºu³\n¢D¹ÓFa§eLÄÉåäu¸1P4«,´¨î»ÐX¯Á*\\Èõ!\$8 A¹M;yÔ5»ó`Ð®j\r7N©çô+OAMWËÔÀùa¼&°÷c0 ±å5àõ¡rÛmB Aa ä²R§àsAZlYâï\"Þ¡kR\\æ±x KÈ¯¨w¡1YFDõ¢ùAäðµá°%,VMÏc2÷öÅØô'elÍ´6°aN¹>læQÃltYS*ËÉ&XwwYY&´¡oyêmR¥VöÏ[àÖo­U08	¨`T×°çaÌ\n¶±îÑ!4*è­£§Äë\nì9WÄð;Å'X´¦ÔÒÉ\"8ªÔr\\D·°áß3X§·>ëdÁLk]ä{âGCd~ùàolçDô|Lt5§>½û_'u	rÚ£ê>­Dlä\n¥c@+¬sÚ·Q	Ë2Æ[¢Q¨¡Ìº!¦¨¦/C\$ ï].Cbwb È5BÿXÍÁf¤\r4ølþ:¬´c% +ÌÜ¶Ó;úó#¦ëÖÏïo?1×Ôò9Ôú?¬¾É3¯z»ê^ÛGY!f8!Úd\"ÑZ:-Ïsë#ÄWõÃvlÆ#kw¾3øppÝÇµ>^çZ\\¼âwýî?]Sîfÿ¿³<S+÷gKòýàA¥Ùzê0\\ ÝðµT1ÇhÃôÛi.Vi49\r´	rÕCYÃ´=B&B0cÖ#Ìº!I\"­hþÃ\\¯úú\n*,&è/&Gúûe¼ýÌäêª\rìÆLÊYâ1C¬ÐM	¬R£ý%Têt<ÄÃ¥ïâëìðkeXj iÎ{.i®%´èðâOjèîl!b	fð¶VÃFïnÆ	ìà]¢*bÈØíkF{ÀhÏ õ.¦:OGorñn©¯¢Ï#¥\"RÅÖWlÎýD ¿åÂSðñ&ÀÞHÈ¾bëÀvBâ#Àè#Î\rhF&e~ªDÄ\\+Kq:p£ ¤Kª`É¬íñ>¤OkJ=Ãg,ìÏÃ8.¤ün2-\0p¤÷q~ÐÈ'\nã:÷EI*ÓMgl_kçP¸BÏ÷®Í=ñ1À«q@&fCðô-ªzFÉed4EzÌ°.Nrß}ÜKNeY\" 3\"24f,Ê¢¼.¢*4Ð,Á4#¢ÏGòXhZg¾(c^ÒÒ\"{/\"¤P\r\"LÃn¤cÞ\rVcñãY)úí%¾81³î##PJþ±¶ ¨ÀZ\räÜ'¦%f°ëí~úÃÊjI²Õì`ah\0¦0êÂR\"@4æ«Ñ&Ä(ì@Oâ*?\r&mT9ò)\"e¼/Î J\0ÒOªæ ÒâZnöF#­!¢VÊ\"2Ò¬þ¸êÉ\"ÕeZÏ@æÅ`Á	@#NÄ1Â»µ0#0é112x+Ó/2N &Q±3ó\n³E3s(ÈC¼GE3)À¦g¬ëÎÉH \"^²«52ËNÌ\$æ¹\"IEn¹Na&HËÖ+ãð:@¼.D¦®³£¬	ða5Æo«¬ì`bE0iÂ¸ÃÃ1<3,pÃÚºÆZõ¶A&²rôlEbbì|Ì£P\n	ô,ETD¦)Ø9çVâ8";
            break;
        case 'ja':$e = "%ÌÂ:\$\nq Ò®4¤ªá(b¥á*ØJòq Tòl}!MÃn4æN ªI*ADq\$Ö]HUâ)Ì ÈÐ)dºÏçt'*µ0åN*\$1¤)AJå ¡`(`1ÆQ°Üp9 ÔÑØbÚ:åW&ëåK<^î\n2·&Ó(·zñ>\n\$g#)æe¢Å×âu@«¢±xÌnè Qt\"Ê\\Òq4Ü\nqCiÒÑ\"±ùVÑÎ·T:Shiz1~åB©AXMöûáWe[W¡îPqäî¦I9kG2Ya³A\"ÜÊK¥2ÞÈzýõÄù:ª\0TªÌ9Så±3P41¤yÐ_­yA	AÄ¹\$#LÑ+DO±HéÐUÐ1z_ä¡QiÌLÉ	T+DRº\$MºAë¡_¡*cÆ'9PW%b'Òyü<kç'K³ÆrjâHÊI^Ó+0IEÊâOzriy`\\B95(<OIÔú hÒ7£@ÍdÑ:ÍSbBÑ1üBÍó¤ã9Í\$	qMÄqK\$-^råñÊ_úCB?®á@420z\r è8aÐ^öh\\0Ñ´}\"\rãÎ£p^82ÃïjxD¢ÇARYå¡`ã|ÍÒQ~S9-JäÄò<Í\\tjðM\"iõVA²Q%a	{ÜtÄzV!4ôÎs éiLr\$PD¶©¥\r!pÚüÀc(JAãDZtÎJIy.Q`g)51ÌEF'I,QÄ©`9D5ÒBiÚ¤åÒQ@ÅÙvs}è^´ùÙ2F%r¶¯ê©/¨ÏDÈ\\Tä0)Jé8Ü7MãÐè+W­îQÏP¦(©OOúâóHdç0Õ¬!PsÔ9^3%6-/8)I½w£c{4äö!ÚÍ=ÅñÃ\$T7ÞÞï\$bù¾¯ºãC<þqzòQcÌÎ·¢u'g9j¥»MéÆÍtÁîË^ÕõÄ¼û×s\"Z¦¦dã&W×·!ÒB»4z¸1+±w+1\nÅùª?G@J#öSeÁzÈs\\¬À@|­\"µ>XÊ,F°×Z|Y!\"\0@akàµÂ¤Á1zfT^å\n 2`\"M{­4qB(H a2\",má»4!J9É.=­ÔVwü\\Qûâ|Çá=3 Ú	o¨üA³qBES3A°:%éZB{cEaÌ!aE)fÐÓ'FçKæQmM1bÔ[»R7¤ÍH1NÔ%¬¡¸aÊ/ð«¢F®aLu!NªÞB(r´gH(¯ÈXÔbRÉM(ÜJ5+Fþ@¡0ÍÈBÝ©ù3-h(Vr´<¥ê¿X+\rb¬u²ÖhwYëF]­E¬¶Ðd\rá¸0@Ó:×'|<¼`»4	è2jÆ	0çØH7 1-ª%Ì.9Ï\\©\$1>(Ê×0½S6\"\rJCR#( Ä2P£é-I·Se`U±2ÈYK1g-	t´Öª×[+`<E²çzÚ\\pji\nã(Ä(äÂ,Áâki	÷òøÕ2\nZËx\"s\nVIwDúdb~ÿäHÊB9W	Érd7ÈxAKxÞÉIÂµÈPâBÅ¥t´\0¦UYáDàæ©áÔiQ_ªñ´ÐâCÁÑÌÄ;ÿ\0  ¨ãÃ¸z/Lh(¦6ÏI,ë­¯+@ß\"pvË	Æ\"åäÈy#z¦U\n©V)F\0,G(MqBmgÒ8GUòXÎ\r®'D×B)I	£Je-V9ÅHºâe´¤Èh	=àAr²Ã)ÂG\r	Ý¾B0rá>9DÜO\"àWëä®mÉcowN¾ ÂF))JÐWÎ6LÀòBG)à@\$:ÈDÚ'[âq/%ì¾Ü«/{EQ0&DÐ§Ç 3P\0[J\r*,Q?õ	+þ'¹ÀK¥/Y5&4ÇÂãhÝÁ)JxBQ@-%Á¹g*0DÀßDN53_VìlðO\naQP/)'	%Ò`¯aêêé?EÅE	¡\". :öìVzY\"äª*XÉGÎMÊá£<o¡,!*Î-MXziØÖ3K+¯*¶Ð)hüeH,¾m5¶JÍ0)°én5â¯FrÆýà('à@BD!P\"ìÍ(L»Q`(\"f¼[y/-ÂÕ«Ñ±Ø!Ã&of1-t«Þ\rÏêÕ¦[åj¡!Ö0TP¥¢\n¥N§~'.\nzH¸incäÝxgß©ÑÛ¾«ûK°3é:·´\\)9vmàGÈý ©	KÑSÏäøcDÑEÔ@Ï'i,­§3)ÞWó1¨Ù~'-²¦Êr'ô\nµèOÞ02Î+»òâM1½ÔNDÊ+Eâ´táx9Äo|ÎÝtúïT¬l<j²óm\\Î|ùWV¹Ò! Èù	al5.ø¢ããKK÷ áÌ\"Ny¶zÎe\$O·Â¤gÕÂÏ»'hÂHchßËÑ8§AekÙk»a;ÛalF»Þ\na \"0Ü´ëÞ{ïÕÈ¸xrÏÂuãÜ£Pù(cxÐ¤CM>e!<6.´~U»ÀÀ\"Ù­^6m÷ÀÒòJ.Ç¢Ä>ÇÝ´Â A÷p_+ÇUåbVejâ©Áþ^úüB<<H,8dæRàA\0@:HüL0c\ná+ ÈFRåj\"²ác±írÏg+Z³Cø·eù`«°LáCi#\n:aBtgJ:´qfâ è\"ÎþäGsD°4îfoc²Ê:*N¬Æ¢¢©DL®Á{íÈIãBËÌÀÄ¡±!ZllÃz!~tGHtÁÊ(®¦Álúdpl9#.&0Fr£Îí!p>±/cÖ=£ßÉ \0æbç-F8cÃ£Ñ2)Ï_aîí1D¼\$_b_¥6ÑÇcoOl±\\<±Nþ§ÛÎê&\"-ÏÎøæ¤úÆh¦~¨T±S¢¸Ï¨d1£Üd´7!rúÙq¬<±N¤yñºê²!ÂmÎhÄVô\$Þ¬KÉ×/EÅJÈä4cÎüñbÛ¨õáÒÝ(J0\$Ê ¦¦Á:)ªÎ2In6w±QäHî\"ßÉL±\$±h\$,,ÎÒ2ã'k2E.à4uÈ9fùX®!\"ÊüÂ¶ycNyÎøaòr\$*âE&qk&Ñn×íØb	§ò*®-ö²¢aÏe*®U)ïmRQ+Rà©ãDÀõîþ¸#DF^!(Uá ®oÞIff»%<Rã}èÃ#Á#®õ¢-0¶Úf(_ÚÊÅJ:È¿DßÜ*Â°b¹.ë2ô¤ðe²¯*|ò%`ÁÐ&o/(öR\0Ûï¦x/ñrµ&Äs]Q\"ódñæ,3n³fN<þÏ7#*ó*sm%Q¬®óßK9G8g°Âoè}q{7³ß³)F\"*S¦¶äaÊã\0Ôát9ÌáÀb/\n@}©>\$uxKRn;Hy?s&³¡&ò}?G©<2R8s¢«,w;xîi\n®h!1µBAÆó%CBdæ´/CØäI/ï÷¯~n/8Óû@²%ÏwEOøJ¶9«+føÔYFÈ\0úGOEoDCÑfªùñúúT]SýIO5òFpúI\$ùScJÁKPÂt£,¬}Î4¨üf§;Óþ\$Àþr'Eô@ÂKäéMÒ·IôæþROLr¸	,úàÉ@çr' NÆöÂ^>qa7c±K0(Õäðä;=Ô4©e+	§Ì.º(RË\\^¨V³Ó=u*éQ;cÓ£ÝGHê\rW1OÅÂEDÏèá\\ß0<Ã8&ð¬\"`ª\n p)3îA0LÞÐC£Ðô+~Âtñ±_î\"âk²ÏPå´q¦²@\"]I\n;ÉhòOWÕªUÐ2ïÂ}DhFÍxÅ	»ëÍ.dc¦B9Ûa~ì¢âÌÎh%âc=jtajÌÇL}+Ð(*Ù[3Mâ\ngÕ;®ª\"g,PÅd±IcçÓ6´àôå[Qt},di\"úem+%ðòV247ò_ãÒk¦¾Àüqh²óg3ô¡iîZ½\"I\n@klÚ*á>ggtá\0¬ Æ ê\r¥áfág-îÆºÁ?É;b§Ü@1ð/¯c@ÑFÂ¬©%²\"SMd6Få¡OdGØÂeO#\nCÔTìóg¡zaæ¯ \"#n¢ònü«¥\0";
            break;
        case 'ka':$e = "%ÌÂ)ÂRAÒtÄ5B ê ÔPt¬2'KÂ¢ª:R> äÈ5-%A¡(Ä:<PÅSsE,I5AÎâÓdNËÐiØ=	  §2Æi?ÈcXM­Í\")ôÓvÄÄ@\nFC1 Ôl7fT+U	]M²J¬ôHÌæË^¿à©x8´É94¡\$ã{]&?MÆ3Âs2Ôuiz3`ÂÈìÒÌ*Z¥%\"±xÜ¢o¯­JiðtÒµTAèÈ=D+I?« êy¼ý12¶EéQ~\rªâuúx.Òue}·2TÕðØ?¦½¯rµÝÚö¿¿¤¦â¾NÖS·¯zhÄ¬	ZØÔ¸H:»±Ûë\0'i.ðµo.Ä·IÄÂÎË[2H³öÖ¸¯3§Ð½\0µå[W-o:\rpé\$H<C'ñÂÃor.©ÐÔ+Ãéäê(dÂÉ.×½É\\3ºðâÒÆ)VòD+ë9Ì;jÈ¬:V×¥3Z½Ëj=9>­ZØµ	<À­SÌFDh¥,¬Èæ ó;×&:ÑÚO*ËObÏs«¨«äÌ4Z*èÐ\nò~­\"äãf»è­A5°4°ÔÈ6#pÊ9²ò0'M[k.¨ºµ5êrò#,*@ÖÁU6«!\0x0C:3¡Ð:æáxïqÃ\rgZÖápÞ9áxÊ7ã>9÷XÈJ|Æ(NÀPxÂ?{W+§JNá§¨Ô°íÅ/m[HÕÔø£°XÅ<-%¤ëätÞHñú+c²l¥9¢õ%>-[¶@(ººÜNnäNó£%>#5úe6°;Õ­NdLÎ*¡RØ«%-¬Ä¯6®íENOèÍ­KÒÚË	^Æ©»ª¾Äã¥04Ë'ARÃáÙ´ÞE^kc¦û¡g{M%¶Ë:Íu9À9ÔUl®IÂðZdM²ÛznK¦JQM¨éAá©ýL¤ûÁ¤³/éÛ'9£5÷óéµûþFÑ*çb\\Æ=ï	±ó¤ðÜiGÜ@ØwøÛÇP)þ4{9G¹IBé¦]è{ô[ x}bôÎïJöEèg]¾¨#<GÔÓ¡2,´èÔ¹ðYNÀ ?¢PªÄ=÷x\"IÚFìa,ÔhíÖ})²ô¬Ï \r¡Ô9E¤WÑÔ9à@a°aAÈ0ÀÉÓ_A7àÌC<4°ü @­M¤oâß«¥¢TÜr¦eð*±/`uÄÐ®ÅÂ´Zá¤o&×µ')UR9ªXÀ\0ÑÚ\\hÄeÁA%.#\0@\$­«³jÄhlÉÆºåÈË(^/µF¡ÉÓQò)£Ê¡;Ëc'½¢RkÅ{2díøÕVZoCW1+Þ±VÜ£côãTJò!ìø#V¨¯Ù8£âVHÌ	Õ¸¶È©M\$´D%*\$TFLLÐ\$\nÅs«epçåü\\f2n8 nNj¼rîu£©8×â¯E+Mj­u²¶Öêß\\+;®YÊºWZí]à¼2DPÂLE_íí²Ô|Ë¹(tHDÃjÝjÍ~ð{?UMz2	ÄÌeQ<£¦0+E9ËwÊW9j©ÆDI©'	£.|	2+. )DHêkÈõ&yHÊ;ÓñkU°¶âÞ\\r.ei9Rì]ËÀ2è»Ã^ä9ÑH¦ýÈÉÌ1OøKÄÆÐVSÐ4TÁ­Ç4HÂ!ÕB§èZè0%iÝc|Ù®)Å P(´N=ú åþÑ4÷y5þH*Ùbzå3¢bhéËeXêKÁÊ}àôÕvÆòe³k\\ã¯®\n.\n¢*¤h°Í|\nÖd´S¤3ÙÛiÊb¨X¦\nM\"¥üÚYjcõG§AR!Å4kTëñ%xá¶·ë-ìÂHvµK»°_Y'Í0©Døa/DÓ&á=ÞZ³´tÁÇcT1«Äç£Ëñ'ÆáÒ3×g\nX aL)gDiäb_7[úzÖkª,\r;Ò´bSÅN@4ù¶&<pAàAò%vÊ9*»ìámaìËGRMå¡`kGÒ¾«D|(å0tø×Ú(¾TÍ!+Hß§[¨ÂQj9;¯<ï6CÏFt+*¾­ÞF;=ôÁU¶b¦j+\0ÊôP	áL*Y%\"°Ù	4h:QAÜ\nãÉ¼â\"ØÖÌÊ§ÃØRå:,ÔìÉnÉÀ ÒZSK8m0ïe¥¸cE)« êïOÊ¾ÕþkÞìÑÊzÆÄ],SI¡µ¼a8ÅðÈ7÷ÎÏ[­@bN8xË8Øk/[:ÖYÞÇªÄå½ÀU8ädLÒëÉ·³¯9S|¨ÊºJ&UïRpÐÑ{{zþ¾»]ÔÌì»E®ÆÌç+7¬1Ô8óbâ-ÕFÓF)&3aHMAæ8&@Æåú0çÊLJV½¨ßY^:w¾1¹_Îþª\\éð¼8 pú<ª>é&LJ&¸õ¹j£úÝÜE0uHÛ9àÒÆkMF1Dÿ(²Ë¨EiKÂ«¯=ùËSÿs;ä FÔÔßð^>®ÉÄ`Æ*Ùp\"½Á8L\rë2³àà¨gV¤9ÀËZ@oË9&Q4:ÀÈ\r\0=Dë¾#d7nC¤)ÁÈ7ÒdÿuWp°àmòWð­Ëñ>:¹K¶°JJm½©íÉõãnô&!©mÌÇûøCöÁ5Pghy¡5ºh9:ï;ÿýÎôÿ	]¯ßfkÛ/pò @@òvC°Bn¼J_¦ðî®+Ê­jXMÒ×'&MnX­¢áOï¾ÓgdO¾ïÇà©V1JHT  ¨\n`ye.Íïº;ÈäÀ,UkôÍBÛíz»ÛhBWCg @âß	ÞáPb1\"ÔSä³çz±¤vÂV#.¢çâ	ä£¹ìhú¿pª(âf`¸0lç½	ìÜ0JéÎ´,Ô¢~£LBã)þ	\0HhtKõîÊºô­¦ÇÄ>ëëmTðÄÐPPÙqHð-ØMÄ60,h	FH¦ÉèfåL2È4¾+àÁBvP £f¹ÍîQ\rÔ®ÇæÎ+hy£ÂG,Ö1lê.båløl(îxÙuIDm¯è©Ï]íú¿°mèÔð12¿ çÄ°dðCèÂ¨5ã+bÙ°ê&înõh:ùêjÄuNJUc^lÐÔN:cpiFñÑÒÒ'\"¦3:®BÔ­ï\"Ìlê¿#R84¥nölæN.áñrÔ°´t¥/MD¸4´OéÊnþ ØåÖ÷\0Ð÷Èó\0Ú\rÂ\në^»QýKðH\rÀÈ÷\nÔç.nAÎ^8Í'®§ÑH¨¢h¹O+HFÎX³BBÃI+p§1ã+1¶Áå.Ó±ùêW.'b¬,-Nl°1RZnd*I5Ã¶ÞIjûB¼û¹æÕ-ÃÀîíæì\r2ÿâq×2Ó3¶¤Ó8#1ó2°Ê¿äe²,ÕLÁ\rp>§4ìÚ\$\$¹«#ânr+M52Ä\n¦úL@¼l¥\$£b\"­#ík7R`øË49Í¹9Å#5Å,ÝØ«EJ@&¯ãép&ÓG1³4S%sR)º¼gwÏdÄúÅ%6¦Lã¯õQµÎøþ¹ÓPòåð0Rî¬®hå*ñ£3ñÐÓ4rìfÛ³,ÈB4â´ÆoøãÖJ-ì1t¨K­B\"\"¤4-R'ôLsK-ªq4ZR(F0Ý,®-,ôlN4^% ÓÁé\$pTnpÔsHmÍK4ò38Cå5k/J'¡ÐøOÒ7cPÉgªûÇ¡K.Jâ|OJQ/ó%E3(Á'{/VD4Ù@iTÑN(5MeQFt{C1B©æînHÆU\$îãMRüú´P3ooCô0z4óQ2úÛïRcH?D%UbÙý)Óî²1¯Ä¯ÈZ£?\0/>ÀÈ÷à*&5SU`ÑU ÅUènEoV²ýX5v]à@óÏÒc'ÎUýçñ+M²±F±¶óZtì\rZðý4ãI[þÕQÕ%UËµ8èÕ<RJP-\"eI4ïî+	iRJXTWëQ+îÀy3Ö_\$ç_v ñáOç¢4`­ôÊátÓS4èU­ÕðÔ±©äBa¦3&ë³/tö²ãHsIÛIMã.Ø!Ó\rJÐöSýaö2³åPÁÂÐì\"ñ\$£,©ÊátÑT`ØqrñHìI¢ÇCHºsë;\r;K&RäsL½b§9À@\n ¨ÅÀp4VRNÃj²0½ZPÆö8D­á:p8ãÍ79c\\»ãËQùfÎ5ïNpPDUfuoâã#òmÉçýeõIÎ\rh´º¨¶¥Æjhy0pïHái²ØC­<Ìoo&cQÚV*%v¥º@1 rwpXh´G\"ðÛêcÑ~¯Ëá'yË£+Â¼SPë`ÅWlRN¨r?ï6Óþç´Uw{°úcH¦Üh½7ÔR·!=â¼Ój>âÇÀbÓè¼³776NÆ¦7DVìþÐ1HGDH¯«È÷í/<÷ïvt~ãÈ±ª9V»]Ç jF5qv¢ªyî~XZôö&VèâhsCÄ®~gù5ëo±l~ãäá×%­<ÕIÎif1zTD°+«Ûpv>eVlPè0ôÂè*|3AðÀß.R·ÄvàÞÅnïü¬ó@>_`UNÒlØ¨àcèS`pr¹µÙ îN#PÀ";
            break;
        case 'ko':$e = "%ÌÂbÑ\nv£Äêò%Ð®µ\nqÖN©U¡¥«­)ÐT2;±db4V:\0æBÂapØbÒ¡Z;ÊÈÚaØ§;¨©O)CÈf4ãÈ(ªs2CÉÀÊs;jGjYJÓiÇRÉAU\"K`üI7ÎFS\r¢zs Ëa±V/|XTSÉZ©vëHSè^ç+v&Òµâ¡­k¥C¥iáåÅ=#qA/iHXEÛlìKÈ¤ÅÅ;Fvì(»=ªv!È£VWj)qºÈÈÚsÉÜs])Kqö{©®¥fv!±­æûæ¾i<R¾o¨@¡Y.H ±(u3 P¦0ÃHÜ3¸kÖN.\$³zKXvEJÌ7\rcpÞ;ÁÒ9\rãVu»NáXkòÑ0J®DêâB\"Å	DJH:e4u\$!ÖÌÇ\$AÚL5Ñ£¶ç±¼r¿¹ñà/»zNµÇa0@E¬FP'a8^L:*uLÈc\\ l,´ Þ·Pèc¨à8F48Â1kxçá\0Ã@# Â11£(@;# Ð7´p@81£ï`@RãC3¡Ð:æáxï_Ãû?ÆQÎ£p_TuUXIð|6Æ4ô,3F#m24ãpx!ôgËñJ^ !@vdê¹CKÜÔRSlFP§i\"ó/m{bê@å\0¾³fZ'IãYX*6V³¥LS¹IÞ¸kèPA(É©hîÍ\$¼C+«FuäÊS°\0(Ì0£e;#`ê2Èw]Ú¥AØ®%9\"ÄËëÚA3TT\"øãÖJeX¸ðÑCj\nué=&dÈb@Lñ\näº.ÀP]66SÃÆ0Ð¢&@åR&P;äøÀsÉËÑÔÂEG¸^¸òiRe9á,yÍsôÂ2ÔmrJO(^Çeñz¼o,ÆQi¹/gi\0öòsdY.õêÌ½eÂLbðÖA6X?@þÚbx©'æÀ¢Ý|\r£¨çCÖ:£[ÀRÃä06ýÂý#|!Û£0Òßhe~ÀøÇ,cÉXD\r!Í½©çÆQpn°>0Ê¢ÉRktÈ*ÒYq/hA'¶r\"¹0tÂJj>e,ø9;D¢R( áÃtÁ½§àM0xo|\0<\0êJRA\0ØÃ:j¸:(C8að>D,©C((`¥§tóÍH!ë¤XAj]M!Ë4Äª#\0Ã [©ó)@@ðn-*¸1ÁÀÒ°\nÉ+Un®UÚ½WáÝ`¬9±CÇY ½AA è·P\"Òm¡xj!½¥8ç½Ö(Ø©¡9ä«TËr#1<¦Þ\"×æy¬&Ø2ZbÎL~\0ÒbÃ .UêÅYÉep®â¾X\nD\\±BÊðf(J ´Hm\rð6¬é\0à,õTA½H¶ZÖPå¸9-©¤°DÄ¨u Dh*¡x0ú>@Ð§|§[ª|4ÆòõsÕÍ3Ô@uK6*7¯Qj TE®¨Ã=A²BGhHó©zJ@PP	@T£Ü\n	ð)8âRÁrg%Ò  xÒTÏIººE9NË\\JÈ4¡Vøê§Q²¨PÞ£©2+¨f ÍÓ>\$&nLâàÑâÒµ¤\nj¡HÀdZ¨°n\nSªW4Ôøh\r!4F`Î­Á(§y¿¾`Ëaá4d!0¤Ð]bíâ'æÒ/lÙ=ØÊ×Â^f P\$`ì @T\"hMÚR#xÎ.rA ¼¦_àPCÁHë»Ã¬EÁLæEÊfH d}~På 7OÛE LXqhoA#\0Û!äò³´1Ås¨«ê¨¯¬(ð¦;K¢Á qJaÐ\"ö\\4É¹9'w©tÁH~xê(àAÆ;*ã[w²÷_	rÈE¼MÁ£\$KÉ°F\nC[î^N@¬2Ë®ñ/±ÂE?/,ÝÚ(\rèË~±ËÌJ\\DÈ¸bÚì9³à×ó`;)mãÀÄ¨i§´M<¡×\\Q9ã­'&1NÅÅ`M,è=\nÜ\n*C«·51Ì:#;6.\"q)RãÇ]ÓÈo=Òll<ÃÄî±0:ê»o³;ª\\º±¹L;rñÇ©|¸ó¦KúÕÎ]ìpÑvP¾+Éæ8íäuj<ÏmC:y¥ÒB!0½\\D]qñ£ð(>UÕJªT)L~«º]9'B*-×µ<¹­â;(Þìn¯\nÞ×ZûÖ:fÓ â§º}ÀÿS.§Âh86¸×Hp&õ^gu§Á¿¸4¨Ã \n³>£ÀÒ«Ê¡Ý=G»Â´1~ºI°6à)ë¡'§@åÏÉx+±	öèsFjÍiÍët¦r\03¦mgZ\rd ïhBsû&j½úL¡vZU\ryAæ&ÄÜzÓsN®wqâ-° =ì¡ ìçV*KÚop¨C	\0wL]ú¦*-FØ2â/Ó¥ïmÏ/b:ÇÁx ôè@Vª;=fÖæl?]¾ñ)=»°&	î.\"n';èÌË{£pâæ¦ÞBVÇ½aö7¿Ùl]¾¿ø^¸âvÊ%XvGyb³³1³¹BLH×á7ó&æÈÅ\rtâYý#sûÎõKFÞa¤H'\\\"fïø¢@s­0_EølðD2Cf¬k\"AÚ?¦i D¢4Co²9¦b(æBb.EÖÌÌø'Çp8ÿ#8pB7Np6Ci7hù£¨÷§CB:Nr:¢!F,o_Îù\$Hã6¨\\^¬V5¤ FpF\0à1ã(Gt;ifþðº¿Í6M¬à rÆMdñ ØäbT Ð\r®º\ræ\r¨¡\nFâxMHêÖBÖmrmÅ­sU	-dr\riÏG	¬vd!ÔÃz4q(ÕqÕÌÇ·	\r|9±*ØGZGJ÷ÐãqS°\0vçrwhæJãzð÷B48Ðß{AØÍ,ÖÍ± vÔ±¦t_ñdßùî¥QJbgãT5p¼\$&®\0\"\")!:&Æ`\"\\<¬2ÙåòGäGd@*J1a*£kp¡SÒý\"jõVFì:vB)j+£LÏêéDdÎÚfãXæÑ¦÷ñªÖ&a-o-wR;\$×OÑ¬FanaRXñ?\$òBø21%g_da­{Rh^A<7áØB&\rr¡d ñ?\"4c®qÞñsR®/g%pwR¹(1&ïµ+RÄÿ.®30ä#fÌ0CÌ=Èiß2füÅå.Lñ¦\n\$dë Êëi&1«R¥P0°-É²ë®Å1)¥h)óÓ\r1ý2³Á¾3\"&|PI0îÆdÊÆmo²¿,ÐDì®×5×Û6NÎG\r-a6îÕ72ë5Sg&R/C1	Í'ç×\n&IF×5²¼âs`7SI&_6±©oµ:¯9òW(xÌà1ª>q,Gh<¨@ìØÊ3ÓØæÌôú\r#HrLp7­l»Ä H\rv óÔ)Á0xz5kB\n¯tõ@«@r>ÏZ°7¢!ÐQ@ Øl´`Ö\nFRæhE<ÇÈÒÈT\"VÊJSÇÊÀTàª\n p).ùàÈú/x¢ly\"#kðzð]-ø¬È9\n£<)Í(Ah`cî7c{#m\n¯ò#æ¶)¦ºsR:0&&ï¯ÈØÊ\$I)ÇëæFs¡!ØÕsØÔæÄÆÍùéh)\" ÐÊËPNj£:k&<!dÃfoG æ¸ÄK!ð\n5\"ÿIÕ7Ô¢MA\\/¢TÒ-J49ÛUQ%;C\n(üÅ=2ÄZ\rààäüæ\"tnªsÕN:\r&5{§'Vvdjo^ð IôüÒg[SOÌ¢NF8ÿadCSè.\"fï\nªÄÑPr¤Û¡f¥íq,@õ@£j3Ta4Ã.kÔÏÑE­lÞã¦f1	aUb¢GÁÙá'`\rLHüökok8äÆ«";
            break;
        case 'lt':$e = "%ÌÂ(e8NÇY¼@ÄWÌ¦Ã¡¤@f0Mñp(a5Í&Ó	°êsÆcb!äÈiDS\n:Feã)Îz¦óQ: #!Ðj6 ¢±ät7Ö\rLU+	4YÊ2?MÆ3teæòªä>\"ÄK\$s¡¥¡5MÆs¤ê:o9Lætu¼YÃ)¸é¿,ã¥#)Âg¡ÅALEuþyÑ²&¶C\\MçQ¢p7C´j|eVS{/^4L+ÆR:I¿Ì'S=fÃÐPôºkéÊ¼ÄLâ¢nxÏ\n±¶O«ã4÷¢íDXÖi:zE?FÄÄ²ËC\né°*Ã[r;Á\0Ê9LB:)¨#*HýÂc´&*²°­1âØ0)_\réz\n\"(ck*ºÀ£Òð\nJDÀ°m +²N7iÓ1i\"\n k	¥{7#¢âD¬0c0aXã9x@;¿Cg-ÈÐæ;± X`Ð¶ÁèD4 à9Ax^;ÐpÃ&Éã\\Äá{*¡#ß8áh\r¬BàÆÌBHãpx!ò: \0Öñ+\nQkêU#­VbcôÎ<Í\n,Ê2ÈÀWý|ÈÈô2?Bs¦¢¥Â¿±âº¬71:\n\nü'@M¹o#/8Óq[¶øè®?jØ83*ÇHBXß2Ê³ ®Ô¶CL¨è*0:¢BJ: UQU4B35r8ß%ëkÏ¨«HÊ¿¡­z9£²\r5ðEkÀÔ¡Ã¥9\rp0ÜÊ¸HÓÃ*È9É@V\$6\r[¸cÂ7?báÅc\\NÀÃ\n*ÏO¨¿;/tb´å[{ÆjìkazÄi£D4ý\nu¶Jñî;×mth0ÆÄºQ¼ÃqÈÒÞ²XÕÇ¬¼2Wª\n)ç|cH¾èÊÒ6Ë*»\"¤<ëÉîÊ½XDÎ!ZÖ °¦\0ÚÊÓ­.¶¨C#§ÌP Â<Ôu*;ÛaðPÌVJ2ø!òâ¾Â)¢:\"&c)ç±¥û÷b2¸ÜT+÷&!4óB&´Ö>,3çYäJõ5ßl<5¬+8em\n¥# b(É´*ÅÀ7`Ì^â§ìÁBÂy\$¸<7ÆRê_Ì ÉóøÓ©¦|¡LÖêN\rËô2æ\nHèJW¡­AC^<3!Äñ!¦.GB¡¶A¤¡NâN¶ALHM97ÊC\"PN¦Õ<'¤øPÝC(x¢b\rÀ½ÕAÁ>­il¼Ô.p±(<åè¢²P·Ã9x\$@¸8h|}Î;`J<äKuðâgd\$[Nì/'Ôþ TPñN4%£ßâ|ÁK8eÀty¯=DÔ·N\$\"Eõ+/\0åØwGeÔ¢rÈRQ^d¤X1+ÆDC£\rÉ18ÞÈÓIEÂ_DÔd·Cf2çyB%ÚIób[Q `e\0WÌj¹² ÒÌù5ðÓ*C¤TÅ~kÜº\0\$üäUlPPM!À\nxá¸Mh p+7ëuÑSH\ráÞLgúÛÉH°ù\$æÎ¾Ëæ(MMÃÂBRóÐÏ@8/ÔÚ¤Lç\$1dbâÞ.Ø¸4âNHé2éÁÀÂF+Á¼ÒäO%²!¦ÞEÔó%¹l¸©W=Dt+NèÝ \$D)[ì\r¥Ôñ0à^©÷gdÂB#ò^KÐiø.H´0òlÉdXJdYprIAm,0ñt*bfJQ26ê¨M¡¬# g¤	áL*W6úlueøÆVË¾,igdAÞ TÛeîm¯ Å9¹R£´kS õchr8mC¦%¼b®KR\"æùd>À@¥­EõÎ%©yk½¥HyEÁBbù<ÿ\0©a<~aÁÐÆV^cL¶a5ÝcçúmY6c	#òÍ-P®\"Pïý¢ØÏÈ5â!Í8eDÏ±ø?AA	\"(E®¡½EývcÆhDÏ+CAå¨#õ©\rM/«ç¶Èà^toîÊ´GGèøÜÕYSXmÔ5·r-Mò³kuHä7¥àçcÂÓ&<%Ã\n+/A\nTìp\"; éçËéCèý\r×P8Ô(}ÃFiUQp¹¢	Cðê(\r!é¼w9\ráÆw1äüüXB`oPævCAZim2¡Í502?8ßazÕvtCªÌñÔëÜÜLÀ·kEBæ\"Ç1yQ(	-D¢r¸PV6¥ô¿à&+ðZ«?A\r¹2CFkZª»ô5óñ©Æç[Åt+ö¶Ø³K¤<æ3ÄÍíá1µ<ÛÈ(FSÏ½×Jr~Ý)ãfh¢  Aa Z´>à]£JÌ8:*Zæ1jù«0æQjãå!q­%¨×A­ÏDg¨ôã^,Ð[UjçH»9Zal© mÖ¦÷ò\\HUë]Ö¼j\n4ìEvWòAwiímc®Î¿ÜQù(&³»g£a@L^¡t:÷¼îfRãñ½­öÑ_ÛûQò}×.ÝßDGó^8ºùð×ßëç ÔLá+!PïÈIQ\"¬¼¬Z_ðíc\"­ÆCô\0+z¢,FÓt¼A¯cÓÒ·EYpÂÉ^:		<\\Ä±Þ<IZöðåDÀ3KÔé1ÃsKóvtÛàý¿¡Ø²LÚç6Ó2ôI6ëeò#¯ôXA.ýîÆ(À\0/øþÆï\0ÐLTÄfbÈ/MÞh@Ö/àÛÂkÂÆj\$eð2od*â-\0¦>àä<EgbJ|ãâe°sPOå°7BÅe°h£8,0Mºgc¤¦Næmã\rÍÈf Ò^ÅìÍ¸xâL\r§Ækì^D~ÄmJ ª6%èÞ`ÐÊâðM¬¾>ïÿËÍ'H;ÁË¡\rÃG<Ë0Ï!/á\r¯ôæ¼Î»ÎûÀ¤éððnÑÃ;LõêÌÆï\0G W/,óâð¬+±\$ðcÞ¤\09BÌUbø!/üý°ÞÑHÁOqUGþµ1\\pnRþMÎÝ'þ¡¼\"­Ûeh±ÅâC[É\"Ê@¦+àÞ&i>\"`ò`êA\"-Çø~,àd\$'âWÑBÀñGL^Q1ÂXöFïm,áfáiøâªÚ_/ååñS eÚ\$®þïØÍÜaM ?G.!öÊå\"cîË±a#!\\sOØüLòâ\"Q!JsPG%±\$Gé\0@Ä ²4rHfÀ;CÞ,1-,\$¬ý\0Òþ\";(£%\$Pm®è\"æ&cõ)ÀæUjdqÑW/%R A²¤=rtÄ+.WCÖòÌ±0s	èßÊßÃj/¶2¢Yeà;-Òà6Èþ'§v\"y/\n4àã/²è`0PiäàáÒ*êr0§C29 S(àäu!p|Ò¨á*BrQ,3A3RP	æ1ò4Dª*â²(ÂýD\0ýZåÊ±É(â*¯E5å¡3ð#­Î\r ÌCB1ò@Æê«r¬Âe¨VÃD\\cvÎ¯'eú,Ó 0Î£:lìÁHfU#§*Îó:R°ñð;;L#®T&T\\idj\0	Ó¯<\$}#¯|[±!1`î³ðìÎ§ï?Ñ;/%R\rV¶ÀÓ.B½Å\$Ê¦D[8:?n¾fµ©¶¨@ª\n p{H¬³\0Î#±8Ïe¢\",(Î«@÷\$¬}7tZ5ÏKEB)\0Â\"6^ãXuÌÌtí©ÀB`Ìâ ¤\"þ}Â<¥A@1ð3Ä<;I£Æ-°x021´A+IÎ7¥2ãD*Y\0\rt%4%ß\0ôÒ[\$p\$äNÂ\nR,ÚÝFz«R ôa\ntpÕ@êÊýO*(+óPG\rL\"ÅµbÃ5õH5(, Ì@¨zÄ¦6Ä<ªRè\räRg\rBêCFmQÊL;Å ®cáBd,ßñé\0KpD#jÄ@oÞ/°ª ¢ÕõÆP&%\n\rQæZÂ* Æ êÛU\$O­âÊÖæ1­eD~ÀækÀ©Q5'ÌzÉJ¯t³pËeÈÔb@UPHæTã&í²a²Ð\"È1óåL£P6Í¥ëb½	#Ô";
            break;
        case 'lv':$e = "%ÌÂ(e4S³sL¦Èq:ÆI°ê : SÚHaÑÃa@m0Îfl:ZiBf©3AÄJ§2¦W¦YàéCÈf4ãÈ(:éèT|èi8AEhà©2ÌÄqÈÙ1MÐã¡Ì~\n\$g#)æe¡å\$©¡:Úbq[8z»LçL4¤Þr4±w©´a:LPãÔ\\@n0ÃÖ=))L\\éX,Pm@n2e6Sm'2°Â	iÄ Çöf®ÜS0ú·ÎÿÆMÛ3©ÊÓ{ôq·[Í÷ÅÜ¾H=q#·\n2ø\rcÚ7¾Ï;0¶\0PÖc~¶\rxÈ0«Ïò2M!Y^¥\\&jr.;é\"LÐ ªøÊ°¬cR\n(#Þ6°SPÕ5ÈEàP:i#|\"	Üµ# Ú½\${\n:CÙ%ÎÊPÂçÆëXpÐÂÁèD4 à9Ax^;ÎpÃ'J\\ÏáxÊ7ã9ìøÈJ|;#5¢#pÌÏ¦¤\nòé\"z:xÂ%\"Hôh¢1\$SÆ¨©U£0pä:c¤CYBj8½ÖÛº:°Ò`ÒW£ÔBPÂõ'):d¾BCNbÔâ8¾6Å´4¬öí³*ÜPe¦@P¨´ ¢XÞ6Ïë{!«\r\"5°Ýh	ÃxÖmòTUÊ¶\rIS,ëTõ\rt»\r!ì ÎbUÊS@ðHÔ2C[Û¿±z	kªÃÂÜ¯72ip ¢¢,<j:¹±ÐäÕá  ¶óléÁµ^=G\"¢&.hà90Õ{µ+~9¼ÏÃÑ\\×uz3UG3`Øuåb4Döõ%ÃØIÅ\r;RÕìÌ®åÂZãWÊ±m\"»¦õ#X\$ÃHä5©H-ú³äR{\0006ý \"¦\\ÎÔI0Ò2:í£m`ár>¡¥ã¬24SÔO\\ËZ óNÓéLËEc}\$4ïhËàÌM\"Ä¥\"\"pîGs>Oþ£ØzýÊ<úgã1NüpCëVí\"Botñ¼\"òêzÔ!CMrbC-ÞÍÒA°:%Hq#)t=5Æ¸¡C)¡¢òØéI\$\\Ñ-VxGÖ2Û\"\n9B\$mÁ\0M\"á½æEÃB[)<7\"ÂbL|!¦\n|4gÀæBÎ_Rü%30¢	8XÑ|/pÅoCHlÁt9,^ø\$àìDä#Â4k	Ê0®Åéá//Q]º\$dÔUÂÂ\"¹d?!¸í4ß*4\rí1!Ò\\NAa\rA¤\$ØhI-T:%jíg©¥5¦ÔÞSuNò]='Äü !°,ïDè&é\"+ÌDÄ4©¨G	©uÑÁ0æÌ\\\rj¢B¤Ü\$R¤¥] âHa\"a5ç­6t¥¢±7'äºvO*V\$úÓóà|A¦Z(æKGè§9&Þa/EèJJ4ÆMÒúÄ|ìÄ\"IÐq^ÝË²fÍBUeè\ngàÅ\r¨xsTÆm=jÐ©³bRH¥fê¯gP-ÃÈºD\$4ýgÙ( àKL|Eâ @P5çj¥i¬ÿ\r©~xeª` ¡·OHÑ@R&°\"æhéw4Nº©¹æTÍ²5}eÂRAá!±¡êÌCDrBMDÂJQ¢6z­qÜbúgpiLhÂ*Ò0Gèú½3(»¤HC\naH#}@ê4à_¡ÎBå&ialâ>aHiD(´LI&ä@«H¶äy\$\rétbàÞÕçñ´8ìKWa¨üÐÓ\$ÔÚ­Ûs6K'ÆgÉEp¸ºXØP	Ô\rï&Äð¦(2·}ª0ÄsL°p\rK¾3D¨O¬V¸Ñ|C]ée©_Ö­ÐðC¯3<F¥¥éÓºÅ­E	# ÔÙ#äRN~ÚÙ&¸ß0ÞJF 0s\rE\nØîÅf\nØé!b3\"J¥e¼º.ÀokØ\0002Î[r¤#³¶zÃPV¢vË«%F,³d3ÎG_#ÊAxÅÕhßzcIsdo\r27tzD\"KÐvª4Q¬-N:FÆôYj\n¸ÂJ³È*ºX±¦ÅVã	%%Å<èÏR*U>¨Æ¬òpg¹ê£ë]~òyÃÉLc®£¸ÑÝÂ\"ëÍQBÐ/T®¹i#~zw7EÌDò\nÌK¨Á á«¨\nR±	7ÔácP§½vqÝB4FhF°m0!éµØUéz!dÉÙ\$ ìXµNÓÜ6oóeµ:åÔà;ârÀ{ô1®a\rA|5®£@÷ök?hîþ`h8ý4qÖa\rÑm£æ/QjÊP¾cQÁ¯¿,\0 Aa\"la[Âµªý£U<³R·ÿÆN·yR[Ç	®#Â7vî\nöÌ/-/©H,H·õAÐã¾ákÿKE¾ðÃP\\t³uº]¯Ç5t¿à¦<m-ËPÿÙiä-2,[6I)[×ó¯¾ÈóJãàgÏ¯vÖy&ã×¹ó^óÎ{TíÉÂE¤+\0¸Q.é=hÌîda9ÐÕ¡BÞv40øÙª,WóHÎ1#É\nCÑ\n\"ëæöáóÕr½rµvä-b.¢#ÐZc~7#v­lV? æá¤~ôí iT/0PÙ\"ûN\r'`ÿjÏoP\n%¬©\0*ìß&%î.%QXÓ¢ÅDßBËEÐ}Z:bÎMí\0_çd`L4kG\0BÃ/ãÀBb(W¨Þn~`Å^ÍK`ç©lÅ\0¬Úfp.ábäôlT.&B]b\n ØØNv\rx¢<\rªFtVfzµpng§9ÊxÄtÑÆò¥Kâ#ÐX}m1<½¢z]OÄ{	Tpa­Að=NÚ±ÍmHp¡VjþÌC8òq0\"¯§\r11HâïJñ+ì¨sQ?1EÏEãGìÈ:&ÈÊFÑ-½Èq/±o\rÈ?°^tpb%%Î¸ÊT2J¦È2#*\"¢\$\r¤Æ0ô6ÉúÀc`kÃ*:#j#.fíéêµÈ±ÉrâAo°FBTmNd8\$þ:ñÚX\rîÈÑmGË!hd'Í!ªÓÍ)Q¬V%X&m\"P'\0@À!Êî,RJ'25#%\nr>aQ'&2K!E\0ò,5q'g¸>æðãlVpK\"pXÔ`Þpn#òNlR(ò¡%±®lq Üb)zXlJÏ)£u)òi#rµ+þÓÌjrÅÅpHf*\"@ôæ¥ØNF®J#¦K¤þU\$l\0à7BS.òò\röÝC,I0Rìåô\"ø=mÒå È1s®W/Z\n^ßÐ#ÇË33ÒlSC3¤Xî¢â^âãg5¡&®læ%Î¥rHÌd* dc(|¤\\¯¢É%Kü¯ÒY5r(<	V§-§HBêHB3#6çï@2ØH æñ¸ò\"Ï;MH&h²Fô³¶ÌB\"ö#P\rëÚM\0ðN,ðÑLòð %Ec¯5?qyð7Û*%`¶P´p\0ëåö;@ Ø\0V.b&2¥þ¦ÁBâkt%BX£¼¤\0ª\n pÃlT9Jrìx#\rf[.è%\04V3îîã3´bs47ÌEEÎñ42ì\$´@Möò7,Ä\"\n07³Ý@³F¶HÎÿi(è< OJÔð(ÅU\r,U\$vt&Å\$r\\åHßDæNq³º>QÎ2Y\0²¥Hhìô?Ã;NÊ\rsáGôÚÒbSu\r>ÎIäFHndQQBZ}£Ö\"ÎcEþÇ§LÅLC3ÚFPú#ì\"Àfz00½*¢v^e¤Uë\0¶^B¡Fç&0pÍÞSÜ\nêJ êý6ïQãÊ:­³.e_\0Ìò\$£üÎ@/#SLülõyO³tUãONl@æ¨Ã¥Ò_¥Å'ÐÃGÞdñâ©ª6°ÂîÀ";
            break;
        case 'ms':$e = "%ÌÂ(u0ã	¤Ö 3CM9*lpÓÔB\$ 6Mg3I´êmL&ã8Èi1a#\\¬@a2M@Js!FHÑó¡¦s;MGS\$dX\nFC1 Ôl7AD©¤æ 8Læs¬0A7Nl~\n\$g#-°Ë>9Æ`ð\\64ÄåæÔ¬Ï¶\r ¢¡¦pa§À(ªbA­S\\ÔÝÇZ³*ôfÑj¢ÑäSiÂË*4\rfZõÚe;fÖS¦sW,Ö[\rfvÇ\$dÊ8ÉNJpÆ¹óiÉºa6¬²Ó®`ÑÒÖõ&Òs=2§#©ÌÝ*L=<ùCm§Ã(²¿¢¨Ü5Ãxîë=cÞ99­«X\n*v3âs°È,)bÈ³	hK CêÃ&©Û¬è7¥¯#8Eqk6¶q*Î2\r®#Ö\rã*¿¡O(à8AhPÂ1qòH&ét<Ìk¸î4@Þ:ÈËXæ9ðXÈã 4.0z\r è8aÐ^ó\\G#sÖAc8^î·.C ^'áðÛ®î Í¤*(Þ7xÂ#\"4§±Cúû:	¢l¶½[z92Îó\"É§U*xµÓ®õ%\rê\nãäâ¥(J2òõ5Ý{_ÇÀP7Ä/Â¸â ¶;nÏiü2ÀÞ	Ì\nDl!K»â3¶B,º\"±K>!ÖÌZ^%cZ¬ÂÚ²Vxêö3âBqc®ÜÉ%³Ô¬é\$»/ã­©ëÊ,ãöë\nb;I¨<,û/UàC<JñE5Rz9¶iòH9kú)æKZYÄðö\0Ú¤­ÃtÏRÃÆ_rÃ£´hÒÜ®\rl©9ËÈbuH!CÀ'éÚLT*éQºÉµm4&ì£|!Ñã0Ò².p|¼9N²~ðÂ1\r¾Ã	Ã+üFÂ2PÜQëÍ°7»0[vtËÆþ#C£H#V,ÅZ»Hÿ¼ÃÓÕÎ´²ªã0ÌÅNÈÌbÙBHEH*=q\0ë^¥iR°C»Ûeàò¡ð¢Z¢óHs1n\"Ír¥#!aN²§}é6q\rm^½lTø\"BÙ-Ç!úÆ8AGè9&mÃ77a¸ÞôÄ eLÉ¡5&ÄÜtà9'¤øy	\r*=A¨bdY!oG5ÔNÉê!5ÁÑí-§¢£Àa¦(¡ÔRøqÖhÏÞª¸draLi3¦ÖSzqéÎ§dSØnOnD9H´¡ÿ!y½=ÕlùÊù¼7Ë<3TJgxa9¡¼5¨ò*u\$,ãòDQC1~ »¤uul\"dÆ=%º¼!õ\"RBJÁÔÄXqSJ *pæv¹s°¢àØåi:çdí7-)Ö\n (A?\$¸¯GHí)\n1\"%(¦)SävÔ¹x\"ËäÏ\r¹¹¨g Lys*SzÔI_v§A\$·ÙI!ì3KIq/JÐC=G!4¿	ß*C	í¯Ò: @ÂRÓÎXsá¼1 ¹~Q)±'aæ5dMÍYÕIUw\nÁ±0ñëLI¼­¤ ¢@¢À èyÛù[!Æ@CãeE2Yæ'TRMZN4'Tvöé¢X¡ÂUèßhèDCÀ(ð¦)rxÏÂS À@´C<êh¢MY1	<ç©°\"ði¢½Y}3úCñ¦LjkÒcYM[Æ6!*KÚDsæ­&'AÔØ&Jáée­\$Ý%pÖA;à('à@BD!P\"ÚKL(L¶±l!sd²	=µ~åÌ¸PÐóÎúÑ`L;­óDÛ\r'´µ(ÉSÛ]cFPzæQ³yäï\$Eg´\nDÉ:ÈOª\$0¬æòÒE2L½\rK\\vm\\ojÏõ³ÈÌÝ2Û*<®[{_.¡!'´c×ø:oIO.)»¤ý=\$]CC)\"¡§	aÀ¦H¬e¦`Å\0¦é\"¡+j 6¢8±K1­¯\\|g|°é8eÑÇ&ÇåT]JÍUNFs­KVyÇ6&Î8ÔtF§qÈU%ÆB@èWÃ2\n?Æ0FON&e «Øì¼mYNg¹*5Ù£bþ] C2èBeN{\r`*®\$oH(TCZTÈjùPÔð²8¹þ\0 £H\n«L¥ÔßÂÄÏèQ§¬<¿IHUæ!ä¼9ÌbXFB!G@µVË`W¼l'Ò@VªÕ¹ù^NÉñ3Éa2½XCPe2RÀÐÓÆ-DjÖkkhÆ;1±µöË&!ËfÌM¡)÷0nwÄxÕü¯6±/d(XjÕK²Á\niâ?ÀÐ¡èæå&aÀzÏ>ÿ?'®BJÓ#NÁ´ZUNpÕÆ8Ö¡ÄæKoñ0äIþg=þªóYHáÄ×bprâQo{2e,ófÃÎ&&?->KïèxÓ*²_pÏD9=32»YÆ¤Ë¡UµF)á÷Ê2Þ}¢TgZ6ä±¥{M£ëa[^å×ËiÅªu¶WJþdïe6tAøÅä#;asÅÊ<Ú;Ã~E4½Ó¤ß=Ïåïõlê5÷ÂCzoé·¼íQæ/gæ=<ïzú9Z\r5õ|ÈÃ{}©¾o|~;h¶Yåìï»é/Ïo%¬áÄ%½÷ÚüÿôRgTõ¡ÒÛPysl¤R80øøjÍi¯[{ef_û,Ã\n`Í=ïGÃÏÌíºÉ:ÿ'{Þ%ãØ)búË6Ï<êBtÿlhjxm'¨êY.8ÉÏ\r.üÉ§ùï:¾ÂU£¬ûN¬¿néÊi\"N©¯>Ò&O6öÑæyfê¯\\eI£úÔ¤1ÐR?¢'`_ÊPÍcÒ1ÀàÔJ¬±\$|¨jvüÃÞBD|!£¢÷)Ao±0c«°.ûb2åI6W#åL\rçl_pH\"Æu®,pÜYm®\nMVì\\ÐJª+âb ÀÈADb2Ï@ÊÏþ~\n'I+Ðû\"0\0ªM\0 v\$v1\$\\¢äÑÏðXùÍ4ÐãÛð2Ñ­pG\rQXÁ¼/3\rôt@çûNÉ¤4@Tú­5déÐpû-®Lu<Æ¥\riÄê*(©À	i<3åJ ÆçJ\$B=	d(­ÒNnói*Û-·çD\rVcOð8¢v6-ÄýÒN°CniD|ö8ê¤@ª\n p#-ªø0.²¨Är¸âlëJÙSqÿ£º£>Á(ÄîEM£nN¢ÕE¶çWNKôEbpúQ|¢\$IîTªMágÃ£Nåè®&XÜòæ¼vê.êNw¤è°9r*p¾\ràà6ÂNh8,²9*D`Gn:%Ø©jØ|c°TîjhkÂ¸@Ôe6%rìðF/\0@Âè£òø,Hàê· ,R½,ñ-¢>)r'B¼+Ó!Ã12&¢\nRÒ <ë´¿«ÖýÊ[cëã¾#gx@ÞÃÖì½0RÒ¢Ô|ÊÂJ#´j®Tý¼";
            break;
        case 'nl':$e = "%ÌÂ(n6ÌæSa¤Ôk§3¡Üd¢©ÀØo0¦áp(a<M§SldÞe1£tF'Ìç#y¼éNb)Ì%!MâÑq¤ÊtBÎÆø¼K%FC1 Ôl7ADs)äu4Ì§)ÑDf4ÓXj»\\2y8DÁEs->8 4_F[QÈ~\n\$g#)ç¢Ò)UY¬v?!Ðhv¤,æc4mFÃ\$Þr4î7Óeû5ÄÊ°*wµÁEI}Na#×fu­©Vln¨SoÐ³i@tÔÆ\ròÙ2aÙ1hÌláÇÉ ÇÊ-ãòöæ¹µÖ×6Ü­FïG×5©!uYq|ý¾¯P+-cº1 ¨íµ«\"Ì´7H:\$ù®Ã0Þ:(»r&<¢èÚ\nk[Ú95ÐzÒµcs·îpÐ:éèä2PÛcB\n,6¡è!!£Rõ9z2\r­Xä*DB:¹\n^þè°Î*\$81CÊõ(½ã¼eFÃ X\0ÆÁèE tã¼ô\$2hÜµÍ8Î¤a{¾9q¬nJ(} =ë,99®Êã|¬F¡±í ÆA-Úz¢=F:±Dì½T;Ã©Ã»´VN\0§µ°ÒFÚØÐW«\0£ò6B#e¯BXÞÄb¢ð2B§ 9{\rá\0ÜH\r@Ü3ÄÄ¼0Æó5ØBZÒ¥GyÔÈ«húI{æ\rñ8ÂÖm=Hà#2>¼¢í°Ê¼¾MÛØÎG·ºõz¶n¸ÄÚ Î)\"d 8Ïa\0Þò>ÑÌ¿Vãurú\nm\0Êæ0Ô=5.rÀçmÿC¬~C¢ P¤2¹î\0 kuZ½D£êê¨(ö¹(62Öà£ÆÊ9`ðS°fº3\"@P×|(¡WM´s:U©«¹veCÍ1M\$»èæ[Crà3êü8|¢(k(èmá¼³Ï´-@2	 çyò¼®4âßy\0³.Õ\nè	tC!®#ÒÆJÔðÜ¬Ô[`é8Èª7ØSVá2Hàw/náC\nsÛ¸È;+Us¤sXà\"O~^åW¨ã?¦¶týVC OºÖ,øÏSÚc>@úÃ{íIï½^+`ÄüÌËö.(`Ú0êÚès?'h3\$HIB¡¡\rÁ¬¡©28é×8 )1'&°ÆU:M¸4'ä²xOAÝ>BÄþrPj2BfJ¢ÑÎä(UVÆ=Dã4;*À²ÓüFà`e~'¨î·¤mI:!Iéç£SNF\"Ë/iµ7TâÉü9O)í>¤èÃrtª~\$HâÛgd·\$³yHÐ4òìtÌ~b5úÜ%¤M½òhqÐ{ÈÜ4\0Ôx e:Ò¦+¦GùR%ò±E\$%Å\rðsív\rÀÎMdD03b)mxÁÍ×HéqÁ&¤Ä24@@Ptr\rãº\$èJ`]PRIP rä^T]:È[ë\r!Ø4CT_Q¹ÞFÒ¡.\"'y\"¤Xy¹7¦ygEe°Í2J\\ý*	\\9õ,PI©*±ÆänÓ1PIª Ðâk%Í<¶4Fj¾HjA\0C\naH#ãæñ\"±'Ò'úiÉ\\^aÁøCÂLJ	Q,f«ê,LrlcJ¨%®J§.CaRÍPbý?t+OÇ¼À\" B×XnMdÅñ³ªwçMÉxØ³Êg\r¨P	áL*Ô¼Eb¹@'dö­OÖm±v±|ÒF,:nzT9UÔ·\$J/qÆ¥VÄpËA\0k;^Åà@É`-ë¨âA/«@ ÁRqJ[m¦½×äL \0PF4áÔRÔ\0T/6qª`¨P*[¬ E	î¯¦tçº#\n°0É2×z]á#bë`',AB²Ó?eØ:Úpxm³Ä6eÑ<YòÀÚn¹ ÌÌzÂàëÛJT-A¶»&Fà>L7µÔEì¸É¡W\0A[Ê'tá:8-ìäÆnQ¥©iÕaÆ¥ÞøpÕÓRÆí·3Éá>c\$Æ/òÉÕ\0G!¶sè-¹ná°:¾r\ngÌb®ìªKCLpVªÐ2|l@n0& :BRxÌI\"@ºC2¬OÀDðúB^kQó6Ñ'ÒåUÏs(yn8lÇ	É&dmióvj9-Ôê­6¿ÌN{Wùe\0  Zää#d6 ª¯Íò(Ò§4áäSD.¯aP*Z-n¢^T!l#IPÒÜ.T¶ÒAä,à^7)8k7uãZÞãtÿK´ÔQ7hjf³äÇ?àZôÑEÕ5¬ã¡ñòC3i²Îf\\é@Ø&Õgn-50 Á|zá}Ù´w  -=çÀÑ¼QØìà-ÜMH_å\$»jeÒ)UÀÄcÃ'&ÜÔãÎimçgô80Çå¾ð\n°ïí÷jU2y¥¥¸\"öPAÉÅ`°,æå&#´\$¿I(IX\ræþóÿ·U{pÂ¹wµ÷Ù¾ûy7Àl@×Âûõð¾F­tµFu>qÛòaï[lWò/2}l»÷óþjßb»ô!UwT]ËÍ`æ_GIcÇLYä^¶Rº­!04®÷å{_Äfì7ðÛ·~SJíÞýAÂ{¾åy{[5F¬ñ8æÁÞü%fThÎ\$j¡«ò~;I÷À àólµ¿It­ÌÒö§LöøÛ¯ò¹¯øÓNÜõ­¼ÒÍ1\0|úÑZtj^ÑàÖ¼àÚ[J)ãØ;D0`ØªìC`#i\\®GFIâ/Ä®.-èj&¦^D­c¹Ð)É­ÉLüÂÔcÜÁë¶cÔÿ\0KG-6\"È¦T*¥JZE¨?£þ8\0¬V¯Xû0¦Î¯üù\r»\nålúoìáp¸ÙCèkég¢ë\$Û°ÈJ£â#\nû0Ö¤pÏÏÐ¢æÀ*\rQÑðÄñ¦Ø]&^1¸1LÄè°Zkú\ràÒXÏºÄ¯¾#o\r\"JýQ#\n¯ïÖý°èþë^Ê?%gÏØxD8ê1QBGí99þiç\0C`Zí:j 4.ÈJæReâòÛ ÓÑr1ÂlÐçlñCÚ&£1¢4Û®ÄT@Ô£t\nPÕÑ&ÂíXÕ#ß\rñ3\r[ðèA1ÈÒPôAQÐIìB_à¨%E±-BßnÖAÍ-ù1õÑÝ°ê5Ñt0\$p\nM¾ñx0F~òyã_åºJÂ(eçó\"o½r,éª2ÀÒj¢ÖÝ*@K8Öb[2#X(r\\\$ yàØk-&Ntc2ò).:®Ê7#j	ýêT/Àª\n q^A¢Àà3Âd?bJüõ\r¤¬a^lÌãÒ½'£ÜþÅ,ØËJj!*è!æ<nË:Ò&niÐvôRb1ÿ'Bì× òú\r`NJ:Ò|#bgÆ+L |MÂâ&NrÒ)2*,jJòx/v;lúcòÉÌþB&§4î!÷Ï\$JÃh&ðÉ³[\r£Ø-b=422æÉÂkpbeâý6²Ó\r638#T`b-ÄHgC5s}Î¢\n_²&«\"Ân¤\\ïi*¾F0Ð¯V÷±<¬8fåìReXDkê:!£\$gyå¦ zEÀØDý(Íµ	 7ï'7ÌÏ©~bló&ø¥°3r&b\nZ@à)ÅJ`\$X4l§ ÓP¾àð#\$Dþ\$`";
            break;
        case 'no':$e = "%ÌÂ(u7¢I¬×6NgHY¼àp&Áp(a5Í&Ó©´@tÄNHÌn&Ã\\FSaÎe9§2t2Y	¦'8C!ÆXè0cA¨Øn8G#¬<ät<'\0¢,äÈuCkÃðQ\$ÜcÄ¡s¹ôn,pÄÍ&ã=&Õ%GHé¼äi3ÞÌ&Ëmò'0¦ÉÄt¤e2b,e3,®	ÆßhG#	*\n\"Z\r¦æRs3â\rÚ,æo&wÃg a©hfã\$ÌA¦à29:ta3ÌÁ\\þTÏ¾¯Í³ÜÏ3}éu8Æþ¿hé¡B¨ý>Ìä\n)å%Ëk­W?Sq¬Ü7êp90Èè¦<¨ãkàË¶®§pÂ;\rÀP 6ãÈÔ#£\"¹:o#®½Â¢pÕ¡CsÎ6<Ø jö¤xÊÞ¿ï8æ:¢ì3Î0cn/\0ÂãÃ°úlãHè4\rã¬~ÀîÃ`@HãBf3¡Ðtã¼Ô4±9Ì0Î¨!|ª9r¼4IØ|6°ÈZö30ÍZP7Áà^0Èè¬:ã\$\nÊ2Ý+£ ê÷®#2LÉO!c°Ò2àP<»­(+#Ü½è(J2ò5½s]Æè0ß9 P¨°¶Ú6¨0óÝÊCzö5¦vdTÔ£0Â:8ìÅ¶M6àP\$#UÍ6Zmøø&ÀÐ;0Í¸ÚÙ¦££á,srâY\"¨Ö!ö2²ãL	\r`È6V5¸62ØcXø\n\"`Z5·Dj8#\".­Fâ¡tAS¹5C+V	è»agÐÃfi°ç çbS2JÆ)JQÞÎ\rphÜS²(÷#háS¾¯çó:óÔP!R0	\"'-3óaM¨Md#ÍF#²äü!ÑÐÏM»ø|7«ß3\"Êq~ òkzßî(ðjãM9£¢rKf=â\$:7òÎºQ\rmÒð:j6¶­/fLéã+pÎ°àV¬¦ak	òé2öÃ6(9ÑA)XÃYUDw Èc5¸EN¯-7Uc 0ñÜu}7JC(P9(èª:|¿3¯°CÊa0haõBêº0MÄá-7FM19K/-ÂÃ¢eLé¥5¦Ö¤CsN¨Õ	(pÜô\$CªÑÅÑ&YçJA°ÎAV\rÌQZ¢·¬¡Ü\rÊî\0òjwC*à¤Ã8ja¤Kð]2&dÐºlF0}9'HNç\"Q	ñ?5ÒðÎi8qG\r¸ÐÞiÃ§\0ápÔL1¦<å9\"¡tcÐ²NàvwG£ÀÈ®Á#¡PÙ¤`Ç	a)W\$.>@U®fkd:¾ðøÃåqÉ5'y*×i.afé\r	JJÓU[FÇÛHAq!£P	@Ë¸ M	±\n\0 NLvvdÐÍ×rdº´\$î°úKìGÎ5ô~`~Á<Ã2Q#ùVO%Ô¤³8ÊY#4ªü®PA¸8%\$¨ÂL\r¤1âl9¡@@r¬ôÓ!0¤0o``Ãz»95ftMª\0G\"\$Ö!ãFI¢ñBe7 Ðå#e¤7%75êãËi/8Ì«rA\n\n¸IêXÂÜÃ2\$Ð)©P¢ÑbK«fõ*Ò|oÈ¿fRÅ(*bÐè.\\³µ°g9	hæKÉ3Å\nH\"vÈÜ)SsùÑgg5y5 5·¢òK¼l®\$ä)²ZFÂ¦Há*ZCó`f&>°ÐTR)¸FZ²|7/S\\Ë[b¡<'\0ª A\n\\PB`E¹k	b\"±khI\n7fÃÂ±\\¡2t:â6Q\\Á²\"9b/zïi\nCa«*AHýIT}Õ4Sk¹bx9\")\0I!3ÓêÙïÖ&ÈY4l\ní±Áß»;Ûy½¨ÁÎ§+jÊ:^¤²vC9}<ñ}bl^ÙÉÿ\"õ£¨ÂÃ¥Á'T*³fÃHzY.¯?)xðD@¼&óJf l¯±ÒfPCP_Ä~á¼2¨m¹«\"±£@t¬F4ÓÆÍhÎÇ¼%¶N	Ï&òcOyë:·ÔÀlÃ!A¸Ê*tÆ0øÄ¤]í`I>v»8Ep®²'55òeÑÚÝC«Bë;úÐÎ¢¹?Eå@Â@ «Ù\r¼ûÑ;¥\"=+óÉs[ÎNÂ7\$ÄÅ|Êr¾4^«¶XÂYc	H3GÎÂZZÛF ULy;îMÌ7FêÛÙ\\ØÝß¼HìÀ1ìe±\$maö&4}_YÝê7)1Üá,mÐAÃ·Þû ¸îL]ª2<è;R~½b¶êÛ*«Ã(bkÓÞ|_Ç£Á-1ÆÐ Õp¶qR¦÷nFØ¶0ÊcLÑéC¯hî%²º'8³Ù®°r&½îÈ@¾çzÚÆd&IäfØzä´æu£Ì;n1å\$»³û5ÝÜBª\0cà\"±Ì<¤EalBå|£OòÏ®ÇÄ¬÷ù2QæÚ¿RãÆµÌõ4I¹9«&ÃúrÀXzäéi=8L´Z»,»ë\$']y¾fa3D¿e\n8Ö³Zß	ÿFN	J¡¹\\»·}ë°æ}ÃêìÖô1@\"ÃÂþúU²@àïúò\"3îß :~­¿¿hiÞjÝh£0·¡p)nÞ).âÀâ|·oþÑhïoTT\0ÿ¥Ü\0PìNüúÀÊÈn-²ª))8H ÚYôõàÊKHõÃGv&â/lê­¹f6dB\rÄµªæ¿åô;¥²)f7­Ab(¨£ð ÑpÑ¢<Ð&ÒTMÓËÌctQVR¥P¦üTàïÎ¶ùPÃ£ÎïÌîC:Ð½¬ÒU kP+\0«5hÒîÐÀ#°ðEP*ûPØeã¦´l¬3ÑY¯V%ü>\$¤_Àååçæ.â+Ic\$[ãÞþ/Í\0Ì2Æ£°ï@ü°ÿ\rp\rB|CúôTJ*3Y,gâ2]]¬ > Þ&ÀÉ`2å4+åÒm \rl\0èÊ1ã.íÑí1C1f·ñ)61éfC­%º)-.Ó ¨ÔKpî>ÓC<+qÔÓvì1-Ô1Ö,qt3ÃuãÞ\n b\n²Aâ¼ Ó ¿Â;!sk4b,Â3±%í ©ÎiÑú²ñ0,a%},Lïß\$1bOBrK XÞ«IÝÄ^ÒZ_ÍÖýÜÿN\0âÏîÝ¯Ü\07@Ø`ÖbÝ¨Øñ c¸ü	·\"(P\n¸À¨ÀpxÉÐ?¨ÞV;'.Â\$fÆkmå'0¾'2Èl0oBþ©eÈ9F4(1U\"h²ÞC¼êò+°;âp¦°QV7òT¢9*CEN¦ãngP¿CØãàëÃ\0r[<ÀåïI¤ùï3Ä&dï2ç4,4cÎzHïó@!sDÀcÎdàTâ¾í³Rõ2ûstî³y5Ü%Vb°ê3?.rpÄ  ¦©êæ!2°¢<ÏÖóaâÝ³¶Y+tTó\$ëã6ÒlËfË8Ìkàg\"Eä\$½Ø2äªYàÓL_À½ô¼ãXüÊæù«à>`\"ÓpJ'\0î0ëÕÀÈ Â\"D£¸1)Ðä7°T";
            break;
        case 'pl':$e = "%ÌÂ(®g9MÆ(àl4Î¢å7!fSi½¼Ì¢àQ4Âk9M¦a¸Â ;Ã\r¸òmD\"B¤dJs!I\n¨Ô0@i9#f©(@\nFC1 Ôl7AECÉÀò :ÇÏ'I¡Þk0gªüºe³Çà¢ÔÅù\$äy;Â¨Ðø\rfwS)3²	1æêiËz=M0 Q\nkrÆÉ!Éc:DCyÃªÏIÄ#,ÐädÃäÔá	³C¨A2eÓÍFáÕ¡Ñd£	ÂÍB7N¯^ q×R äyW~çXçzæqµÜùu&îp7vúìÊ\n£ÂBBRî¶\rh0ò1!ô	È`ô?(¢.Ç ¤ÖMz(0­P¦2Iå\0,K ÞàÁ\"@Â9¢ã{;%ísò1àØ7ï8æ4½ØÂÍ.ã¤<9£Ôö##4ÊJ*7Q:Çªc7¾ÄÈà¸ÐúØâ»QÂ(ËD^!`@%ãCh3¡Ð:æáxï?Ã4ÊpÞ9ázEEãïCxDË±21¹Ê8Ü3Pé[4xÂ(CzÐÌ¤D:C¨Ö:¨K 2¢£42¸uÙ ÖõÈáuý^À=tV¯¶°È	êL\\*´£\$>5M«k¼vÕ¬15ã£r	cxØ:©r¶®ÜxÛiHÐp ©êVñ²0«~¶Ràè#zb¦ÉÃ6¢ÄÚZ´0ÂB0ê7\rnSþ ¡(Î0âÑõW5àPÎ2Hbh6&-¬ECÖ\rRV69ÌxÇ	K5¼°¦îjQX-K`Ü7g<ãkz.ÊèÍ÷\$­#i×CKÚ±¿Á\0¦(PÞ:ì5å½Ðxµ^¸\0ô»Õ/Ts³¸N ç#\rþ*ìõ¹­þ)î¼Ä¯ÆÛt\0ß.\r¤Í¬JÊ³ÜbûL¾æØ*¦DMªD7c¢¸ò×Yö\$¸nÔB*QÕe°òYÁÕÓØ!V3×d+æ'á\0Ú:ÇóxÃJC¸äÃrq#HÆ»Ô5I8RÂ3\r1íZ5z¡òö9¦úÆæK×sI.È/Wèô÷Ä×ýc¯Ú¾BDHzÿØÃ\"ÊÙµ)(¬ÿ³RvÂ`*¨#UÜUaA±V TÀ¼3b e:;K\$ 	¸qK¼#\$l:¸TÌoÌ3íH¬Á&Ò	b<ì5©°@KÚÖIëÔ»àu!d2¦PÐC\\Aí©´Ü°bÌB9ñÄh¶âYãÑAÊÅ\$¥Q¼WéCÅ§ãC2q£Fx¡¬)­M§.æKHa\$»\"Â\"9/'ö#\$Ô!óò0*¡8&æSºyOiõ?¥%ÃQ\n(6óK i2jHdzÄËá\$%ðîXÚVÈv#èZC£Û/¬<^4FÏSTÂFeüÁM¹ãIàÊ²xOIñ?(	N¡\$ªQ*,2@ü³RaÌFlCÈâð5/÷\$^{Âµ¶ôÜhûÄHÅØ%¢ÜRXx)®¤¢^b¤#J¬é¤¤s@P	@³É3,G_Íf_-2&C`´I¸`û¡%æ5ñÃCmÍµ3Su¦nÌù³\rïÍû:X41EÚ2¥TÇê®%\"\nòBfÒY¨l5÷CQûl/MªÃh®\n	ø)JF&¥´º~\nhÅ/¦³¥¹ÀÉ`§DÅ¨íPèÂR8Ï%s2C+&!%RCj1/©¥°¢HSy«6Är\\ªZ­\"f±ÀÕ{b%õuh@òÏiÑ<<Y\$l¡äñS- @ÂRÓp)Ç/æ=SÉáÆ^£\$ÊNIû·&d#Â\$ä¥ÐÃIM°8cH¬-//õhs\"4¿Qé°ÂsÖq+åêKNèm\rd,çØGÃyBYó\nð¢+Fcl2¨¢n6r{èÒK,Öè¶þ¢:¾fd4:öÞê±cä5^kèïô'\$Dæ5°ðòÑÄÜ{CÑ\"&\r<½`©VÚzÖC³ÝGbI K\\õ)t¡æ´¤¸5®0¯`pPDï¢\r\nîk\"oÂ×ZBr 0's.ò3Ñ/ÍD%wªÀêjèãEYÁÃæFxt>ùþî\$ùC;b=U+n@uYLK¼²¼-©ÂEcv5lq¥T@ÐBÒ-Ah6µ¢Ù[avõµÚÍ4X\rê`9®6à´PSHÊÀukß:|rFìºOÑÔ;F\$ýµ¸}l¥ÀaÏÌ]!¨L®0¨h¡ÿCyµ÷£z[2 %ÆFÇj4A­ÒFªaÝ| ÖÕÒ^§¢ï­ÙÛ²\$3d@É5=°ã07o^DZw¹£-2,2N\0WØk§\rÁ¦Ð¦¹¡(;c\\Å´VÍÄÕÔ<òÙzJí®SÄ¿YÁoYó§®cØèI,cÛ¢èùÖn±Y¸ä2^	Tòa[D´D¯Ât¸uÙ©Þé\0ÕVCÒM\"ÂÒ~ÚÃT!P*%#aÝ` ÒSBïZÆ!²xÓ¡ÖØ/)ëmd©¼lÍª>Î}u|µ4»´HjfFðB·!qî\$úýG\n(Ô´Éøúc!ýGöZ¨-oJiýÁ^÷^«ÖÖLWÁÅ/ÚyÏCé=½½5ûÞ!K5výwÑ÷~¯ÞÞ·å~\\NÝßBD|ÏËñdÊ¿«ðþÓë?èaâóÒãTf¦nöÅ ÌÌÃ\"Bè ¶(ÉJªä\0U¤p]Ê©\0î.nÄ%D<1\ntÍ@È'`ÄBá2r¯ÀTáx'ò&b3c£¶	' äGD·IhÃL5=àò).ßO®ÊJV\"âÀÖèZ@îÖdÄ:Uàê%Òã£´CPx#\$ÖKg\n\$RV_pÐÎ¤0Àß­	Ê#o÷°ÖåPÜMÀl¼ÔÐ²j `ÖMÞF\0`Bjy¤¶ýT@&&1æz'âæ\n_jCü»q&=G8Ï0úÐq(jÀ¯qjÀænk¡ÀpâÖpM Øi¢þÚ]¥Î]#¶¸&w0øäqBU£(A F7	%	Ò·­pqÞøLZÕP÷¨ÄÑ¡ÅêQäDgè­¤ú\\\$«Ð]Ë¶rcÆQÄV ë£4÷¯coªøÐýQØr¥e0ÍåëàøàóM±û±ÿÝ QÆöoÍ7,²é,ºÀrípÄr\\0ér5\"ÕR>ËQâé@ÄéQñ¸¼>ç|²¨\nKe0Glödìü¶gmkÎ\"¦z cB2<d¾MÆ&\"6\rÂ)\"6g¥ç'È-Ã&è9òt]\"Pä@xRz 0g*&÷ÄæD¤éj)²P222+pØF²'þmºèìú]-6ä¥Ïqýí®HEäÃûñÄpö\nøäñß»0FðØGjWóä%ÇDtìÒ#,é#¨@|ó8¯±ü#/§\"Mu4³=A2À)­ÓÇFºêÎêM1X!À2#éÂd<#Îfd (ÂÐ@K6ªÐFYè@ºÊÍrRÇ!\rÂS³Òÿ\$±½;)<S¹%ÐóÓf82bpèj°ÙSÎCÙ2n§=ç7>çg2±öV\$yF8Ür]®«\r³Ç40nUÍ;p¹0ÉA'AÔ=1·=s=®©BÎ®.Àër[AD#î°0.Â3752L|ôC-Ñý=S##ô8ëôPëspv´lëNÄ-h3sl#dPÆqâ#Ds	@ªqÈø4cEÔEZ>P®;£&¦?óÍ!BÒ\n«%)¨5r7ÃLBÒÒ®×\"ba7ãkJjó~áôàÉ\"þè¢É\"ÃV7Âtu¶1Vñ×N Ó9£ÂÌCþÙò% ±òñWR õ%05)\0o²-EX\rVé\"ÁaPr â*1*=\"z0\"nË#ZÊÏP2Àx²'\nq-T!¢Ü\n ¨ÀZHúÐIàÂÊju-XOEX§RÊ´¶Õû)3é#XÕVÕÓ!¨ªF(X½¨!&{ý=°F;UGYIt9îÜ\"cÒSõMQ§(&Ø01\r\"úá0:ÎÅ2Ø\0®á5Ueú]JV)Q@×gê&}y&aYF?5Pæß6\$iâ61Yb\rç!¿\0Þ:Èì)¶=bTHÄvM\r?4Æeð«d©vp.édã2Ó/£¶tCÞd³ö,Òç¡]r{QV©c²J-ßvâPÌÅÌªen\rFæ®\0\\Z\nÑvc\n>5K8(Eæ%Î8uò²M6+Q5¬æ¯Å2Õ7nâ6`Ú:ÇX%ÀÈl EË¨làm«JäQ®o)\0";
            break;
        case 'pt':$e = "%ÌÂ(ÃQÄ5Hào9ØjÓ±Ø 2Æ	ÈA\n3Lf)¤äoiÜhXjÁ¤Û\n2H\$RI4* ÈR4îK'¡£,Ôæt2ÊD\0¡Äd3\rFÃqÀæTiÄC,ÜiØhQÔèi6OFÉÊTe6\"åP¹ÁDqäe0Ì´¤mßÌ,5=.ÇÈèñ¹ÌÃo;]2yÈÒg4&È6Zi§ÞC	-MæCNf;7b´×h<&1N¨^pú|BRY7DV\n8i£fÃ)Ëb:[NLþ,èhØlö½ÉIëò]½ßìbøo7[ÍÞøõìÊÞ2XùOìÔ¸I2>·\$àP¦êµ#8\"®#kRß-àÞB«<»\n£pÖ7\rã¸ÜI8äÜï\n<²ÅÆ\"Ó/ÈÚêCq£·.èÒÇ	xÂFLS.±Èh(üMø¼4²ú#.  °Ü¿,c{¾ë ê823(Â1¯¨\"½I Å¸Òóº3RÄc»J2\0x½8ÌC@è:tã½\$.òü4£8^ãù?@¡xD É»5#3J­ïÀx!òR)¢Ã(Æ4¿¬lMÒ(£Îøè9-Ã¬£^·mêDúØV écHíÓxß#ÈÚ#Ãxì\nNë`ðBº:³Ã:\n\0Ä<ª\0MÑu]ø!ã`êÕ+è}ç¿)L7¸2ð¼!\0·CiÞ1à\"Ê0£dÊÐÞÃ,pîVõÊì#^ð	kyKN=y\n	8Çh.ÃÞ6eS\0Ø7mX'À¨Ë~²`CúüâÂbm;âÉZZ§R <\\XàØÎNâ	×Þ¢&M«ïããdZvZ=\$ÇÒdoiYY9å°àÓYê¼Hâ.í¼I{D] ¹(Òí¡EyæúmW¦TaL*9'\"¯ÍxÇÍ-ø<s+¶æ#ÓnåÛõâ\r£ªA5#gQ:\\½E#óWÖ)MR^rC°°ýà|ÆU:ÖÅõm*0ÕZ¢ujj à×\nRCjý^ûüé´²8º\$²>}k ¢SWËØhà.Î¤GLXoÁ¸¥òRò^>¦(  ¨É. {0êøsÌD2óPpT\nÃa3RòJéKÅ¸2æ\nU2Å@9@£È}ËÌM¡Ì£¨ª#Î¹7¥Å,{(3¦¡BQ9H) î¥é©RêeMà^NMR­ÀF>^41§¦Lh	ê½&©Ñ\$[fäÄÃDöDUé¤ÿçaË,QQj5G©&¥bÒ+qíæ÷U¤sqñòx],³Èt	%ÌÅ,E}c9¡1°øÅ¦¸ÄK6Ò¤9D'ÄºCfÐêÁ²/¡:v<æÌBXbØd?\$p6K³BA38ïl4ÀC©-IJ,\0P	A39ÈXAA\$`§Î#xÈyg\"¡°¼¾ÏÐg6g`íºÈq¥ô¢DÔÓÑ,0sh·æBHv&eÄò@rH©ÏI{ 	®S¢sÏKCnéõ?§SÐHÝa§QK¨óMVbë*\rò0¦3É¤B@ÈÙõ\$\n­ÕpuÍ*¤(ù¶Y2&Ø½£bdák©Ü´Â_MáiÁAéGE¶gKzì\$0òtKhL`7J3ÐX;-uá\$f¡/½%sÒ§ñ)\n<)DãY[ÄH`3¥\nTAÝÔ¹óÔÐÉ/Uå'xìÜ >ïÓ­\n>c}jP82òè§k-n9ÐàATA\0F\nÐ.lUBe±m,\"*×Ô\"³±_ã½È+iñü30¨P*[Ü E	ú£ÇÄºö_fÿ£\0 GF¢(5¡â`ÌÃ# ¬¢<vF³&¤Ýd]ôöKådÃ©ObæCÃ\0[¤Þï¤èRmJ¤e Ñâèßñgæª{Ã0÷O«~ÆÝÏä^h\n}æÑ%DUàVªÄÌ&µÉÈ\n\nÅúr¢AJ4TË3üÒ\$±SÐ ä£OhØa7Ì´ ¬óVØl2fU¦³m(S.LYzXÅÕ±P+­¢ý{ì]7:Áò\nÎ<&Ñ^Vè\\CÕ#a;å£`ÊóÆ~tf§xc@PÃ¶/&ØµX­Öµc¼]\"Â5,[¶d¶o0©õwÅ¬ÛÌQßQ\$a¬·3TæÒ\rd«_àåöF@t;wGêÂüÐJëABU%óóMNÛAäLò4ÕVgØnIÈ±·ê]¯ T!\$Zi\0°pY4Q*4QI\n5Vî1\$»ÁyS]î æ®åÓÇÕ?W&Õl[£)LÊm	È./f~OÌ\nãkxæ.]Ìqç8ÝS°Gy³'6wÄ®jAäÝ(ÑtSËæ»<æ]7±i+úÇææ±0Î®m*ÕAs}»3ä,I8w ®bS2OhD!G	\$Bfêa»½ôpÊb²æøÖè\$lÞ\"²/)¥r\nþ4Ì374o(¹¬[§1ã¨m}[©u\$ßsÏ«AÛj¬ôFª¤¤¨ÚfÙYÃ÷ÅìÃÏ2AêÏé¢ì´jÍ*c;'ÿÀù	8p1È0½ÅDK·ådFVY_ï£ú¨ÿ×eæë-vÉ¿ÈI°°y]rþä¶\rÎJ£ÎØè^ÀÚ8 í` _mhLlm'\0øv4BRÈL-.V#\${&Ç8ù°AFÔ3òm°@ÙbX7îhëÎåÈçN>÷ZÎ°æ.fë®,ìN©nTå}pX:Í\\­¡ÈlÆ°Ù«Ë	HÞøðTøpÙð§Ì^ãIdª;ÄNGÂØc.«ºðGÄbn6oúP)hÙ£AIÒÿíÙ¤ ®Ê^°ÀpÌ?BµÎY°p0ç\$´pæ8å¾\$Ê\nÙ°´ËÎXïoíDÉN\0Ü&5åð\nÅ¾/pRö¬k¯	±B/é\nÆèZ±BBg*õ¯Çz±f&/hYom­mÊ%çñq\"f2i¢l^cDÝoèG)p!Lâ¸®°ý&LqJ´)jHk5cy°¨ö¨zq\nàqg-Á-pcÏqÉÑÓ±tæc±å#ëÑ¹@¨ÿ¤b¤Fþx¼%l\rc§#l²@Èü\"Ö!²,R\"vÆ\$/Ê4Ùr0:4«\$`CË\"ÐÃ4Úm¸4í½líÈ#ÑGz\ngmcë'\\Ñì(üò|í¼.êÑQ\n­úHBë&Û)D.r&¯² ßÒz	\r\rE¤/\nàÜ£ qBì]ò\0ÊfûÒbãÂl <Øÿeæeì,EÞD6Xò~]æ/.Üh/P.X`Ø`Æ.&Z¹àÊÔ)^\ràÄTâ Ì.é0õ\0Â¦^(u§^hN\n ¨ÀZ¦ax\\¢RçoX¤\nÐ2Pu6GR¢k§¾2Â~,). d°Çñ:/oÀòöìôÂ@**íµÈ!Ó3l@5hh#¢R	\"O²³í .\"ÝbfäatbBlÅC¿±ã¸\\<0®È\0PXeîf\" ¶,SægÒ/,XÆvÏÃ¸,Q!#cîÊ5ÇDÐJ)mìzøªLt\$N\ràà-ä,Ô2PoFâHô1e¬ëíÖ;æTyÄ^Æ#U@ÐúÌhDWn¶\$`\"p\0oE<2gÀì0ïo6b\nÊFN&gDðËcLZ¶5Mü.ÅºÄ£'öJM#ÄòÄLpFsÔ=94ºãL*·êa\0}@,i	¦»\0d¢";
            break;
        case 'pt-br':$e = "%ÌÂ(ÃQÄ5Hào9ØjÓ±Ø 2Æ	ÈA\nN¦±¼\\\n*M¦q¢ma¨Ol(É 9H¤£äm4\r3x\\4Js!IÈ3@nB³3ÐË'Ìh5\rÇA¦s¦cIºE¡GSÖbr4ÁEcyªU¢ú¬z0ÁDqäe0Ì¢\n<m£iÉÈi·QÌÂb4(&!No¼í¦d?S4ÕL¸<Ù-L³,Ý¼q`ðÅS Çìª§(²o:\r­>yx¦s- és8kjØFç§ñIÊ{C´tó6}cÙ3¼Ü¡\rÃª:8lØÜ¾ï¤É­®@Ò;£©£cpÎ°ÊÍ¸¢K7¥`PªÓ8¢¨Ü5Ãxî7#¨9>MË0¾éò6Ä«ªî¼Æ©lh!2,Ô´µ\"¿ãì&\ríX§éb2«BÈ \rÉòÒ7¼®ðè£3/#\n­£8@0Èìê\n\\:Ìs\"9íÈäØ41ã0z\r è8aÐ^ôh\\ÖK	ð\\Øáz2¼ò9xD£É«-£3`¨Oðx!óå\rkÈÝµ©º\n&¢éÎ¬*´ý×¥}6\n}£h\rêp¤ñÆP®­Ð²\n\0Ä<ªÀM¹o\\(!ã`ëY¬¨}ÏJqÚôÆ1ÍÜ¦3¤@¨Oì0µ#­>·PË¼Uüû×[:a·4¬4áø\n(:â¤M¢E-\rëh	ôâ2û-W¨æÂ¿ø8(8:R¥©³&«<«ÙøhØ·¯1¥~h¢\nb4îHÓ?/µa@r\$ÐHµªXüB!¯Þ½£Ç¬jÒ\"âïR6R0¤7Àj/ìj8¨«ÿ#2óì\"°#ózßÃXVO²\r«¤¿?T}9Ed×#ÍWV¤üÀæ]rØ3×Ã/F2BÛØ¤â#rø2|`VcÝ#Ü¦«8õæ åÐÚHÂ ÂÍ£Óa\npÒåê®ì!-·#·­X.+®ì£¬Þ3ÍT²ù0zP7¥é`ò]k¬Ìòc6\nNiñ	?pÂß,vk,'@Ê\n)~LÖ­ãv)Ã5¤*´NZ1.¤%e\"áýx¥¨'ã´ TPê%E¨Ðî£Ò¹mRJQKà^MÕ©ÀD]z¯\"&ÝÂ óXSáî;¸C(-jè¤ú¼¶ZIÂiXIØ©µºTÃ¡'Ôÿ\"Q\n)F(å!<Rï5d§<Qx::÷båÙ.[§ÊÖúPfLù&`zÓalXÐÓÊÑ§5&¬ÁÀÐhCBN¤ââ¡Ë[¡3F7ôßëÿ.Í8@Ð{¦yfÇ£þGcÇ6Ç04ñ#ñþ Äd4RVD|8ìA.\05¥ pRF\n¬Î8aÌ(E;©K2¦HÀàÛä[©ÙRú*fZc\"ØÑá\n-¡Ø¨6J%:£=ÒeLUH%ò\rÁÁ:FDðírü u¦`&JùS\nAÐÁCC0 Im©bÀÞ©L« ¢¦:µ2dÀDØÚ!.H ù² ÍV¡AÝÅBJGw3(\$0òvICKÄÈ³ÚZ{*±Lê8M&,l«ËãÜ¦O!'\n<)DÛSÍÙ#3¤¤UÁÓÔÄóÞÍ	q}Z¢&ºÒæ%0ùÕ¬zí\0Sç¥H±0²YgBji!2)'O\0F\n-ÒhÕ\"_ÕÖ¶\"*9Û\"³déÃÉ)ýgñið¨P*VIuÂ E	î­	nëF9x5 GF¢yhH½32XK pb¬Há°¥fG§¸ÃdÌ¥qqÓZ±D8-wâÒÓ&·6°­K¡¥×%PA¼4¦»Õ{	r\${6¶´âªoÄ-°= ç¹}+Ö((H©¹Hïib|³QGRç¤ª*KDY MéÀQñªMH¶ä¼°V1´\r&!Ã®8gy)7Á±»<ÆÁ×AÌ 2ÊÖq9ÅI²5åÕ\0zõ¤¸:`gO¦Fo[¤l2rönJ(4î0ÆQ+¨\nb5ÎÖÖY\"zwO²PÒyjÿ*¥´½5=Boî+å6Ê®°e×':²\nØë¤zôÈ+¿È\rë}q´±V[«|}\$Ïü\0-¡­ÁDh;Ï/O;¶<Ë°¡/´*@Â@ cåá.¾Ö]S i¥ghÎ;3`)qò²¸ÎÕ\rënð¦IuX)°©çTéÓçZ=5d6Ap	â<,fÍoá¼núiÇÕ³È[.[ÒxGy/'#¤qÂ®\$ô1â¬k&Ãú¸ýbä<ôÿsó\"ABäÝ'æUD8=Oç¦`Î w ®K 3DNðò3H=-ÜTnl©L'}´ävò<Ã(bv)®JN.ÕçìÁ´@æÍjW4xzÅâR0HFX¼/æ^fMlýYXu\"\$4o:´Îôô8ç #Tb%/§óá½ÉªlJtG± 5ÿVF\rRXÅ{ÞæÏ~m¥÷6{ibðíì¼é±ÅæÀMO9ÅÇ¬Ç2Ü ¡êbb¯kÙx¸~~È/ÒmyTþüÐ^¾Ù°\\Á<´i½HK¨B%O6ù#ª]¯Ôkú`åã`NMR]Ô\r£C6³ôþEÞÓ¨ÃÌZk\"àùÀÂÄïÃìdÅÐ>ôCE'øow¬D÷ãú®ÆeløåIJÖT>Ææn£çNR2Võ/@åïJä0~6pêãP.mgºÖë¹äøðDÃ¯w\nÍl¹­rÐpù0¼Ömj§PÅOôPc¬]+ÔÊ'Ô6\"2z#J@l\"£9Úy`æ&£,ÀÞOÔÃíi\r<2d¦Tn½%g@ÕdRz\"áÏTP\"|#%bËqÑ¹Á\rMq\r\$ÍkE-XÀÀÞ8jjMbab=E\0¬ÒsqJ|ôPBñ},¨ùÐßmÌ`ó¦ú^ð¹b	êtoÉ\$ñ©æÿÂö%q\r<@q¸gL`#ÑÊ^iÜa@Âh\\èJ¢?£0ôãÊFÆ¤²&é	A°g	mÿr\r¬K0½¢<Â þí@Xbc(Ö&î\\ñ!Ê#\"lt8i¾±k\nwdsC`E-x`Ö\"Ð7Ðîü\0ÞLMöÖ{%È-ï¨Ù%rZ;r^°î^­'­]©O%:\nKQÿOMÔí²*/y)²\rÑ¾ôÒ@r¦ÙÂ|Ö\r<ÖRÂÙà¨7eÝ/:Ý\n)èk°GMÔ/2­R±-Òë2+¢N	\"\r\0 ð22Æp2*8¥ÆÇ)q/Úãf®#ÓÄd7 Vnb\\æB¿î[±.<ã66¯ÒrK|9ð@ãÐ2fb8ïC2î:æ\nd\$\rV¯`Òcâ\ràÄTb\$¬\0ç5`Zd\"OÆrªæÇf\n ¨ÀZx?£BÎy®÷ë~ì³%\n2Ð;c5\n!B!Â \"IÞ\"ã\$ÅQh³ÏÑ`37ïàR*CD(O^}M´DóÔMkFX×8NÏeºTFÖàtM-ª&\$FE&\"<®H(èHÕ¢ÐùÀ7ä]fbDm&ÍJr¢ÃÍCÄ-Ó&\nõ&`Òß`EUEâ,f48SF©B;´p#àà(BMöl¦Ç#~1~\"F,Â¢é@ð<ÎD\\û-%Ì.Æìf¦±\nÈe23¢eàì1óë\n\"\nÇ¦4ÆIIÆ>Ì/b8 VcTDë@c)P>Ì80¶B0·ÌiQ%ÞèZ\"|âðJ<â]bÔXO5¦";
            break;
        case 'ro':$e = "%ÌÂ(uM¢Ôé0ÕÆãr1DcK!2i2¦Èa	!;HEÀ¢4v?!\r¦Á¦a2M'1\0´@%9ådætË¤!ºeÑâÒ±`(`1ÆQ°Üp9\r0Ó¤@dC&ÃIèÂt7ÙAE3©¸èed&ìÇ3IêrE#ðQ&(r2Nrj­E£Dj9¥Mî 4Ý¤'©ÝLq¾èL&ÀV<Ü 1mÖy1ß&§A.´¡Å2ÊÈ¦CMßeÂ±yS×\"º»Dbg3BiMðASM7Ã,§kYÏF\\SÛ>t4Ný;ãgç«ñÐsgçAÀ@1ë³B:¢ÌëÞ²¯ãýÀIÌÐ¹lKþû¼pÎÂî9<àP6 PúÄ\"¨Ü5Ãxî×¤#âiÚî6®iBB£kj´ZÚ·® P Ê\rã`ÂM4´,í»:N@7ì¢L8\$)Ü2®Î@´)\nÎ7½j9½â¤#9¯Ò(·­î(ÐíÎc9ëÈâ4410z\r è8aÐ^ôÈ\\'Røä.#8_0ãBC ^(ðÚ¸¤ÍÌ¸¦r\nã}8#ß¨é|¿ÉbhÞâ#¬<³IeYM1=LcÖ²£¬Ä<Cö+²sB3 ¡(È\rõðÊ:7uáy/#H!É#xÂ79á}ñòìÊ\r#­öÄ#Ðû¡Ø-ý(Ì0¤j@ìÕ£,rúÞOÒXºwÍô ìD²Ä_h²-4ë*ê´.s~`´\\J5¸QlâÄK¨Îaë¸ð®O¯K®<#\$i\$òú ÈÜ¹\"`@8ã£ògãoÄ,äZM¶- ÷cÛN×\0:@Êá{<\0ðî»»)ì[º5¶«6ß)\nË¡ß\\Mâ2úÍÙ&L?Uc(ÆËÈÊa`PÐh\n6ÍÃèä£ÇZ:o/Óæßµ 1y~ä(	ê£Ñ#\rZÄ!ã_¡#<×ÒWEU·àÜ/ÌG¢'éãCî¥b\"vú1ã90|íà¦x*îå\r0(æ ãk\$z5H=e¤CI\",¦ÑDâÃñGcÌØ¦zÃ+^gXÄ¨ÑKKÀ3bØÉXI/}@ÞxRðy=g(:§\"Ü©\$3må(©\n\nY{ï}{)ÒA@s%\r|FqPM¹ùºÉâ³)\ruÆ§I\n@À´ÅcQêEI©U.¦CºLyP\"Fxd@¥½øÇ\0D£nií²Äÿ!/Ô4b4T-ê!rC²	nxÞÄSkÑhu%a4Å!õBïÈçlcRJQK)4§*T1½Q´tÀ_*«U¡%ÖVÛêGÇÁePCZ±)	p9%åº¯\$êjP2&I	#l¸®¢ï±	WÑÕ>²HoÉ(rHùwÍ'¢ZsÆ½ôú|Lál¨ùÐ\\M9æx>énHáD\$òâÖWPc,Ç?	°{³¯@\0  D6üãNÇ©mÈMïx&¢Øw¨ñ5D Zang`*dd¨Qe1NB¬Iáj¢^|n!³-S¨dø|[2/ªDØj¡^öaL)i2My6MÅð1¹L ?3ó¬0¿Dzd3&¤-(#uJ['i¹uVÖ¨e{ÄoÌIÀÔ\\CE1¦¶YÃJï¥Æ,7.óà|C\\3Äm¸¯,gÊ`@fm=Ùòx©<(Á5 ÂT¨)lÊ\$µ)¡e'EÞ\"¡\"WÊ_Oa#\$GKÒXSH¶öBùp¶`ÄN2RY¬¦¼ö²pël\nmd¤µJ0T\n&ÎXÍHÖ\"R§×Ûv,Ôå1äÌsA¡0*õªFYf@\"evXÅJq:·¸%õôýNÒ!PL´¦\"&i%/¢clC:	¯Ë9iå¯_Ñ	¸Û0AcÈ*Â¬y&øZ\r½Nce°2l\\9¹ªÍâ0ÒBbÊ(6	;#¥ sf\\Ê,yr4¶{½H¡YPãê\\Öð\n\nÙ_0òPc)òÙø  /@DÑÐ¥BßsÆ1î±T²,ïÏÚüÁë@³|pmcËñÿv?%CÄ¥rqJCIËtþí\$â\r&¯M.óÈC¸\n®h2­Æ	t¹¬0ÍØ²H£iNIhIi¬¶ÔµI¡Ù[0º)²~Ü\0o`mËh´ÆÆ1\0p\$ÎËgrva¬åógÜëy%:2ßÆ%*Ü,Ah{Ô\$@ä\nNNÄ8´¤15ôÆb6¼éÉd HÃ,ÊÐ·¿½8Hlñ@¨BHkmtTÉfÓjo¦a	1é¯´Mx/`^9êï¯ËÌ¯^tì×AE®Â¢ çxtQÛ¡ôÉ¶y¨ '¦:½êDÿ£×õÆYºÂÍ6õØèÁ\$éùë±µíÎúÿa-à»£r¾¼\\åï[Â%BÔæp»tJÕÉl\"â\nk°×2ä¾êï2Koù,)DÀ<¹æÛ±ì3kÍ³×:¼5æV¡înLæPzÞl[)G5	,!ò,^]oSÚæTä±W\n¬i¾1æùoåüN/Ð2b¥§RHÝ)P±8ý°IÕÂh~&OÐI?WiZÝì°þôï;q0#ÙH#E i&_D`lØ¼Ëz4+ Önpbdº0§Í<s¬VæÃ/þ\$ð.]GË¨aCÚbÊ°âÿdÙÎÔâÂ,bbÚB Ä¦N\rÎ5àG\r\$G£¦h,@èi*>AhêIÄ6¬ýL¬2®%lIðÊOÂxüÿ«ÉÐ¹oîüIúÝð°mÐ¢\$ÏãOæ¨¤LKêd@\nbîn¢A,Ð(§\"XN¦ì®¨ð¶xäëB¦0×ÜìPàFPäYê:NÌ°&!\rRÁîÖÀ'\níÌ`	O	LÝ1\"Ü¤?ò)öüÑ Öq8\$1<ýC«Á%m\$Ã¬Bö¨ÂöA Â\r Å\r!âÍ\0¯Ìhl1d*m(Q£®\rÁd #èV©Íòì8_ã4 [­, ±&`-©DÛpÈÆôÜ\$_¥þ9\rv[eºûÑ?®³QÚ×±Þ·±,8Ïmt­x×Å¼#QPÿQ2ä±ÑÜ×æ<täÀu&,¬ÚÃu	~ý1/ötÒ([²-peÄ³#-¸ZàË#bìo/\$²eÀ\"ü!ÂæÁ@ä3h%§<f\0¦lÇlr àôÔl¦XqÑ\nXR!ñö%rr204ÒrSRFG,/E±qò¤v¬r¢(»RÁ+²`0\\\$/ñ(,¥õMÖ««TKÉÔ\rrä'~µCVaÒóâ;²ü«U*¢ï0dNÍâ\rbÂ, ÎÂÒ;\"ý³%2á\"ñÊþßÏ93r¸Yó2GmÜvÍû2dvæ!Ôà\n¤§.\r2¯ýW6§Bã7A3²37lg¯è	nÀÈHÑ	)ª?` K®JcFZÆ=ð¸Cå:Ðåí\"ïó·:ô3ó¿9ì°_c6KæD5Ï5nÈ,3ÌLS9Ïdê\\cÓÉS1ìêÃ°_d¨\rVàe­/><,_ÅÈKì\$Dî:%bZjÚxb¾\n ¨ÀZ:\"CNì]àä¯ÅÞ­J2ïíEb'd&¾ç]ã!K!äÖW£{I.vã4Ù¥kêàÒi\nG 8¦a\0ô #åi\$Kk8ÉÎb\"\0`@spö\"ÇH%mL]å^\\ÜItÆ]d#TF2&Ð~\râ[f2-å¤ýÇqóþíÏ|bóôð8M\"î¢>Õ>µÆÖpYPôõ/PÇÈ'º;òþ5í´­jFoÂbÌ[2£Bäf0öLq\0(,eö_äìFÌuZ@Õa\n¤sÃ0.eL_\"-~g£]âäÍôG 	àáSE¸dG¬JoDLJsZ\rv2ÑO¯?`!Îr¥ïdm¤VU¶£F&D\rëþícÇe\"%£Ò­BbÏC\$à	\0t	 @¦\n`";
            break;
        case 'ru':$e = "%ÌÂ) h-D\rAhÐX4móEÑFxAfÑ@C#mÃE¡#«i{ a2ÊfAÕÔZHÐ^GWqõ¢h.ahêÞhµh¢)-I¥ÓhyL®%0q )Ì9h(§HôR»DÖèLÆÑDÌâè)¬ CÈf4ãÌÔ¸h/èñ¥ý²¯¦±	4&¾µ¤ÁY9Ú¡LÐQcðQ\$Üc9L'3-çhKÇcòlqu0hÊ®üÒÊésiózxÔr#Ô^3Òõ¢KBÛ!ú­A%XÖ¡Pèì¿TÑBÝ/ð»äGÃ¡­\nô>#=¾Ii\\äÑ\"Ìê\"\$¯ò=i9*JÐQ£I±`=I3(@n:4Í<){øµ)úh¬ë4¥@FßÊ:ÐP¢D0ªÀ¨Â\r\"¤,fÆ¨ÊI¿o#4Ðcü¬´± üA%!1¼c)x%úú½£°\$±*J§)G1Û§Fìë¿ÆÆ^ªåÔ\0¤0Ä¿³Ì8Ó@+ã¨hðÚ¢¼-ûªÛ!\"³S¡9Sá\0\n\0Ê2Ê\"8þBkã@¡è1>ÎH,ºª\".r5%<Ê;ÐÑ-Ë:\\ Äôû.¿¨Ä@¦©.4\n	j# Ú4Ã(äõSÒ¹R+!'Á(z*½¤Eú4]<VòªÄ[Ñn´½1	Ô´\0X@46#0z\r è8aÐ^÷è\\0ØÖE\rãÎ£p^86cïxD²ÑU2FPo £%Hª Âd2~\\xÂ-\ré;@ÆGF«TêQ>¤	ð»·¢GaFûØÅo#ä¯#²*z[Àlü¡a ëd%KÁL6úQiléR-¡Ô!(Én®	0±lL³ìx¶Ê¾8¨3+!Ê+J~_ ÄÒØ èÐhÈ(1 ¾î\nÝ;ëÔ¶<nÎ/1!¨73ÿÉ.º±|¡ªhñ2´GNqVY6^äLhhpmÛ<Y³mQ-¼8È¢GbÌÑxXU¾¡f¯ÊiNNü×9Ó)|¯ÒÚ~ À´B¿¸ô¦{Me.@¯ê{hÝ¯ÃÊÄS 1i\nbê¼u®\"«âzHW'É*³ÖZ¥Z*gG­¤§5¾è8hmÉ3ävVCj/1@Ás<ÈLpÐ0ÉH « .2ÅUá#IUÐ©ÈÂ®ã^[ñ>¬âRêû­\$(a\n¸TµÚrÖ3nOD<­ÅÀÂiè+7n4©Ano\$\n×¾L¢Ù!QPh,{X±ð~-\"'xUÓªUNåo·Du`ab,aÏ2&KM²rr»cÙBka £Ù\n²6Å¡[?ç|#F.±Ð\"âÊé¨¬Èµ~ RBORÐ)Õù_iÃ4´,T6\",\$~ëÑwRýíc4I\\IÜEäÈa&g¡jl)ÀÀèÌ/¥!¹¾É;-ÏAÚe%]Y¥CxO×6UJ\rÓ&>³%\0KbÜ/§eg\0\\F{IH)ÔÎRêOj!n]¥.fÐVØÙw\$â\0öÔ¿emö»IÞKç&0F{*ç>¥ÄüÓþ#Ð\"ÙARWÔ\$àPÃPú¨{Ø*Ñüº+EçDê(²éàH§á\\ni¤Ïgý:´\0¶?§MAe%jTÔêÛiÔ~\$ÓE¹Â|eJéCÏ®_Ó·¶¯ºjRÔQ)Y¡*2ª3tËÓÁÅXë\$9.´ìEÉ²hñÌfv[#º1[ev®õâ¼×ª÷_+í~uþÀl#`Ì!@Þt\r61YºM9±ÏIùÞZK[ï[¥è:Lddj¸r¨.]n².n­)É>Õ'×ÂÐNå2\$â»[/q¥ÇöUxUä½²ø_Kñ0ÀØ+a, <Fí[\nbaÍC@DOp«\nzGÎÂþvµèè+æ¾\\¤ £Ë5[QUNà¼Eåêé¥¨	à¤ÉdùC&MJ.|-©ò\",E<ªrÒY	 ¬ÑÖ¢ÙçÙÚ&\nöé¼jæLYúÁKVÂ³PÄüUÞÄÃ@¬<§iHðî Íi­hÑ\rJs#2¢\n«ÄôúÖ}]ZUþ®#Ò\n()-âg,º¥ËNoá'§2HxniäÝH#«IäÉCcwói²²'8Ë¥å|SUn&;ìÝ[Ú²èbì¦OcÈhÝÝT®fàÈ\n¬xTýD­Âv«`¢«HqÓ Äñ¦I|vÊÆ;ã12\0C\naH#ÜoÍM,´»MÕ:M!´&iHÊR6ZJ×Ve¶±kÇ\rÞ¹BÓ¦hñÇ-Í£3Ëe­µ\$©¡Fr÷Úº¦R¸	R}'¦ÜÚd\\¤*Þ6À9Ô¹kéõÍ`9¤òÜÐdG®Èav\$ð+¢ÚéPdéßZ,ð¹ö1:Q>eN¢*\0 ÂT\n²¥ZXËÔj¦PP«sr~R©xÑEN¡ÂmRåb?4¦z5`¶1ÒõÒÂmÆ8OÈüQÍeb·ócßâ²ðÃ½	\\ðÐ×ý/¨i2Ì÷5ÙÊ¿WîpJð»Y #@¡q9¥Ñp×FF-ðîÉ\$))ü¬)Ë?J(×^º¾3\\MnáY|êvúíðëýZK¡Ñ:äðªkÿ£}jß;hä×:3x¼¬=¬u1^Ë/¨nÜ¢°xLp©5ÁH¡ 	²Uÿr\\áÇÒ	¶\\ä¤®r÷Oêððü\rü¸ hU	Â²õeZÎÇýÃ¦ÅNîehäiêÀL¤ÿïÆGFÐ)®RÿÎ®©b©î8Mj&FâünhÅÂn§K,iå`¶)Eò\$(\n\0\n\r¬Í,´¦ïF¨bÊ#6\"©Òâ\r*@t6ÅjbÊhG¢¿Îôõ(Ñ¸i¯^¶£h\n0~P+GRp\n'týãÿ\0§LåBjÚ(DM£r\"#v\$K`Ñ'22®,v\"~`æ6\0Ê¾àÐ\ràè'Cú«£ À~So¨p¦æ·	c7	§&TF´i\nß -CÝ\n/5ÀÐY@Òæ\0Ì@Þ\r¦t%æ¾'®(p@mÕè¾(å(ÂªF'åÎ¸M¨éM¯­\$¼ý\nÇÇ\0ì¬NñIÊº.3HÊW\$|ªÏ<'¡1Æþ-§h¶'æjW±c v\$Z\rÑ&Q*¦Æ@òw\rÔn\0Ã8·äâ§p®¥C¶ðâbcãúÔxâÙ¯\0ømFy¼ø\n¨ 	ðãgõn\$ÈE &æji0\$â.é(æ,PÉ\$(r ÊFÐãmhÂ mT·)%RN\"\r°2/\"B*B¨«OQdLâæ¡òjá§òe!²hj¯Å''kD,Rî¦Û('t#²&²êtãÒzÖÉ*2¬'ÄÙ\ræeÀ¾Tfàiàls(­#òÖ¯Ú¬vjÊÒ+.#ìîØÆÊÖ.ìy/\$=/rÁ/£÷	'{0é0BÝ0ðôB(¬p¶zÏ(@²VÒb7Fj7³0-â%¢ª9ó¢nFå|R*»Ñì8oðáë5\"óÇ).-&ÄñdV±mloåÍr6n¢ÔæÉÕ8êñA1I¦ålovy°IDÄ3>Ajâ#*DBPá¡(²öi	³Ñ=SÆëlöÓÎ-\$XÈsæP<ÓO?P FDÓþ.R¹ó	ñ@Óü*ô	1ý@oOeÀùß\n1PnÃ [T7@Ô:r£8ræÀñbDï\"t&zzÃ,£Xèø4g5\ntmñ¢OC*±SïMT\"/f±¢R±<3Ñ@².°1ã\"Óô4@ù \rÎ`±*\r8`Þ\rê\r Ü £ÈùôGÔ0÷¤£L@Ü*¿%Nf1æàT8aö¯NO\$ÈjÐiO(Öÿ=þÈu§¡@)NÐ)PSéAdÏO¦UGï\0Tõ>¢¯M³NHbdDg ]røjÓÆÄU/²»@j,5?-uE6²TÆíUS+òjK§UÃS/UgV©·SG5tkuxU15XA®¡O)ß'/O@Ç¤ êJtñSÊÀ´e1s[±xÏá\\5*Òs\\ÏCB°Õ]\"UöS%xÃð5M?I\$|§¸>æ¥MÖôî8Ê\$C.ÃxÏç\"ê²è<uù\r2ìØ\$²{>\"ËQ¢bÍM_ï*@M\"Äü@(þr+zÔ¿^¸Ë¼çA\neËaÎI¢îòBh:á@ñD&[mºØHVjsTóîiÏQâ'Zô£2|Ïqi±s5­@4ù[F]jï[iÌ]j#\\ªDØVÀñÑØ6ÇkuÇlVÓn3æÝcÇkua[3ò÷PklÔ+oè±jsG	7MãÈ-l×æ¢BtYj&òìÂf×2\"&¼ñÈáç2ãr+~Jhhy¯r\"µUV¶ë=Ö­1w[YõÂÈu±S4-k÷hwmlQµ\$-ch,uGhh+m{TîÔPryuht\$§©zvÖK5swÓBV/¨åg«ª)	nñ~«c#c'×g}i#ûwC©~#ù}£p×y\r{Ës(4Q¹j¸wÕß§ØA5Ú(Ø-8\n-£î<·qQªù¯Ñµq÷»<=ÃN@>ðrfê *ûÆ§ d³c\\±ÈÙÊý1^ð4%8l¥¸_eØe*õVÎJÂÐµ¹;þ××uÀJ¸oCéÓ.#¢üäÛV)n*2µWÕÃ§ø¹Xx¼¶)Ó+Ê{ð6Õßt¿lo¨sTÉjBÐoV¢g±S®î¸4lSÃ&Ì´e¸ÏAWëX¨ñUu\"ò-Qw92y 2©£\$Îsy2wRÆt³±¢àØa 1^q00ÅÄ*F0Ï\$mî¡ÁL>b4ÐÖ¤7#Ñ63JñD+ö1UÏÀì¶'!@ª\nª6Qïb\rITrLóÆ=\r­y¶±fay¿d§8îlr^Q­6¢@1S(þfd2ÍôBE;x¶M­¶øìö\0kNIx8Ív¯3NÐï+É\n±DÅbìâ¾à@¹áCq4x\$qHL\"§a6CðÏ4EM/÷¨uÎ&\"¬è®L9<n¯=¢MAÓøhOûO(|ÄbcU@ÚPzP°>Üp§Úh#HÒªòç¥Jtªn×ß)ú¨¨Öÿ¯VÝjt#«ú°QéÂÉQÝ?z´ÿº§m¥qÈ® )ºV&4©D@Ðçn¨Ñv@üa<tÎ¦*&*¶;lÁdJKðC¶ÓÏ½e%`>âîs°2I­º½Ø&Ó0pSN}:ÝÙ:(ÀFqo P°ûæZToºx³®ýW4\"=ghHÜ[;xü­kP¶oOWµ9ª½,'uTj¬écMÆ ;õ+³q(cQoîð\0àÕò\$'å¨ØÂæÀ\\";
            break;
        case 'sk':$e = "%ÌÂ(¦Ã]ç(!@n2\ræC	ÈÒl7ÃÌ&¥¦Á¤ÚÃP\rÐèØÞl2¥±¾5Îqø\$\"r:\rFQ\0æBÁá0¸yË%9´90cA¨Øn8¬Uó\rZv0&Ëã­©'È(a7&¡ø(n1¦!»Ç%iA¸ÓD9Ï¡fó´?B¢Keó|i3fRSzi0\"	ë75d%StìiÑ&áK¥ÓêuqmNÇe¨mB~×ÇQ%b	®¤a6OR¦j5#'Mn¾q²±oÛïI¿{<ÍqÖ\"7)RÍ©PcCÚ÷¿(pìõ7ÁG»)B³,CXØÔ¦cÂChÂ½7\"T6<mÐò1#­È2M4@1Â¤*Þ6 Ã(ä@P 7«\"Ì´-I¸\$£0KZ/QÒ,4\r@çÂÀP©»x@Ú2¤±àP2\r­l¦¢C\"\$ (rØä³*#¨à\ra(òRb1E\0Ñ\"Âa`@!ÈàÊ3¡Ð:æáxïIÍLÊAr3ëh^80îxD£Í¸Â1\rÌ¹.êÌã|ËàüNÉCÉ\rñ\$7ÁÃ¢ë!\n06Ì	äðó\rc­f¿(Ðµ+³(¢OP×È«îÇBá(ÈKØèÜ×EÔ<Ý=Òð²0èbCÊ1ÊË%ö`Ü:%È:¼AþÀ¹	é'£\"\\4sò&7\rõýr8WmÈ:Ã[º2ÌC;3»DEkÍKð\rc Ê¦é¬B-c(¶\"öIÒ¤8\"\0P\r8x|,úâ§¼C´ÅeB\0¢q`Ê\rMc\\Ø.¢¢&MË5¾=XÊ\\7#¬ÜÃ6¤í%O%õiGcÖ*@1¥\\>ð4oH6òTÅs¼¹.¢{PÎ¢9\rã#dë¹jÀeBª(*#Jü6Íxx<t1úÏÙÏ+ai;WEl,Ü¨Á\0Ú:ª4ÃT0Jä7:*5¥lÐ@7Ã3@:°^0|¢uoÃÃªP²9}«°2¡RÛ(7¦î_ =AË3èú¢aðû!8BrE|:®¤F°P¤HúrKÚ]B¢`­#VV3*\r{@Þ1Ù\"'D¼(R¹jEì âhZÏÑfPæ&äºa`ª¶¢QJ)>à½UÎ¥Ø@uÀ&ØJ(a:Ø|Pª¹Ì)Ñ¸]CrâFaÑh9vÄåâ,G,¶%»¢2pØåÃ1¡ì]Bè:AÈ=\"6¯2¥2eSY¨E\\ ³ªC¡éG¥³ØÔº6ª	B'ã¢RQÊAI)E,ÊS¡¬GZCKÌTÀù.5ôxC+Ò#DÐaÒ\\JEJo00äÀaPQ><´,Ñf1e£¤&Á;?)@ÿ!RÊHÈ_L&ÔPê%E¨Õ¤TwR©P) ä§sí0Ë°Ü©@ItÁ)a,aâ£(#ZRÜÏ\"äd¬bÐF%ÈsÉ¹é¬íÁ¨]ä¦(gZyÝ!ÓØ*DÜU_H9?É­ È8×´/S,hBp¬´9iI\rIipËËé¯­0ã.e2qh	hÞ\"j2e ÔM*J©fr\nIR\r\$H¡¾Äê\r¹0ëZÒâxËìM´xÎ\n&}û²:^Ã*¸dR§å\0QjÌéL\$èx|H\$âÐ²'º8µSx­>¦Wû-b¸k& !0¤§=Ä¥¢E^¼0!0\"_Eä¦p^»bHIA*¡HúÁÄÄî%_[¥p£/V)rDV¿°ÃY¨&ä6°è{[.%ÉÔ2tÎÌ<BPYbôéÃm!\$¨ÙCt\$7 ´xS\nôå\"ð@ÛCr¶¸0ò>çÁ.½ÙbÆ©Ló;Ð75¬@Ò`Ag¡`+²ÝqZó`aÔ»agô:Dm­VäQà ÁP(?¢LèO¹<ðToeIC¶LË²nB.h\nMfPÑÕÄÃZ¡Ö§`í2=²È@'\$ÈXTWÀph_6·(X¨í#©!¤Àùðb.µ¨É*Sld¢ÉEÑèJ`Ø´­ÂDÉÅÓfÞdÍI:Ä._Pý1µõ\\\"<{§Ü;stÍûJ·´Ä¥ÎAÉSÆ À¦²ö?Æéª4QÓírU`(ùêUL0ÄµF»C~É(À('(Þ{Ý5Ë²90GTyö!°'Fä)ÜÁY!¹	ðæì;ª	7!07#BC©¡\rÄ9°Éë6y#-Gì³Ú¹j²Øè7²Å³¹Þ~/Á¹?`èõC3FýëI¢ä¼29\0ÔH\\¢m\n~CS;tË+Qt}WûÌXÄ¼º·Íh~i7CÒéXpÎ´6WG§#n7ÇmªòÐhÙ\r	¡jc7RÖ©\0 ¬óöÁºÍÔcéËL(Ieß0¨C	XÈ³ø}~æ¡©Ly, \n] ¼«.Ó)Zñ¦Øù/NZtEltQ>MØ~¤	ÅzÏ·bcq!ô<I¬6ÆCæãß·ò\0Ïú/IP=1ÐKK´í·/Xl¢'¯C~r>û7ÅåÊ	\n#ñ]Ñê}îº2à«´Ø»©Ó¤²èÛßf>å¨]¾\rÄ;}|~Ù+¢ÿº¯ùãÿàýRÖ¯þÏôÑ&eí\r2O&8¥ê1\":}\")egFÄpZBÆ§j:`îÂâ\\,G<Ý§p¨2DrC<n£¢,EOXM'Ò]0HDbê	\0ÒFAf8D´g:7#h6ÄÄrâ¼!Ï4/CF­\"^½lÂ:î®C	v°@ëÐ3¢JÍÏ	b^!ÍRXÍWð´ã0¸BoôðÄã¡Pº«ñÍÃ*Yæ¸BAÚ\ræ bC6¢é°ÃäÛÈß\rô?\"/ ¤/&d¬|±LLjâêÎ®9É.	.c\"êä¡÷ÂB\næzÎJZ@\ræãÀÐFÒàÚà`Í(ãQ(^§\rÌMGI\rlnPÙÐ¾¿Ðá\rH°f\nBÔmci¹\rðÅãPÚ÷1Æ&eæ¤r'&B:ô?Ðº4eùPÎ÷K÷VÏ]\0ÈY1¤q»ojúúÞñârQçQê/Qî%Ó	1ô«¯ê\\ï¢õðÊK&,ÄèÊî~¯IÑ¸¢çR\$ç¬±\"ÑÕJ»#r\"ç(>².v¨¢BNZ\ràÔ[bà!ÆF&òd¼ârJ0JbCBxÜJnÓå¶¸¢ä%eÏ&¡}(jrÎòÎjrvë¸£m*PëHáëåBNÂCjè.æÁ²v£B&?Hçr&çÎæ\"Ö[h4æ~Ðy)Åô_vâÅä·òE%)//°Ã0öâRR2V­Z¿â&Zâg4;\rÖ?00óó&sy	ÿ372±É\rÆÏ3Cû#òÊs(NØmRss*{+F-#äè#¢_#Á}j¼@DvNÃäyë\"BôjIÑåG3u1¿ 3ÑF%\nÒ×Ãp«1SKRè\0Ìd ×ïSF÷;NÛ;ÓÀ;³Ï1ÓÉ:Ì.Ã#¦µ6Î³æ\0`g!Ó¥\r©?ÿ²U;t;®?å¤Jã@éâS÷\$nAäÏ\$3§1*ê´\"ûó%³Æ?.©BZA4FêÇä³C5víéH!}0q3BZGÔ^-r//ô5FéHuTÑô)ÀÐ#ÁC+ó>öGf&=<-Ñ7/Î\0ê¤Ó¥Î6±ÀoÌÔô­Kýt·:ÀæýòJóLoèùåÛKzÿ\0#ÞeåÚr@Ø'\$h\n´Ä=ß	#zfoH##(°\0òq¯Ê%\rVB@Ò\rc9c¦ºkD¢rPC¬!d´@'\$9or7Âw\"M'*-ÁfÎ¦@ª\n pûÏ>Èêî®´õO}\0üêVó+ñùW1ýWrWÒÏ!TÝ!úÿÔubM&\"ª¦8vF_.t}J#bêNPtæª®`C¾¥èÃf0.GÄl'+ª±[ÃýTìÄmÞ­â?-ÂB,%~.ãêÞü¤d.\nthd\$.\"Db*ºæ®µ¯`ï!p×4g`q`ôúÐÓqêm\rÆ@u<ñó5ª¦¬ÖðV?°½FqbðÉÀ9°4CPð\"pd5b¸S 5.2øWô@Æ´Z¸@QL\rÒ'E_V')¥om\0¢äÊH£ÄÏ)g°mMäbÌeà§Ap-r\"V\ról6»[ã~ck3b±\nÑM&Ò¢2LÌK/ÝndÒ~`FÉ#~uªvrê\0Ü7KÈÃ(õê£¢";
            break;
        case 'sl':$e = "%ÌÂ(eMç#)´@n0\rìUñ¤èi'CyÐÊk2 ÆQØÊÄF\"	1°Òk7ÎÜv?5B§25åfèA¼Å2dB\0PÀb2£a¸àr\n*!f¸ÒÅPãs¤SËY¦Pa¯ÁDqa9Îr\"tDÂg¸NfÊo¢BæùAÜoBÍ&sL@Ù±×¦ÉVd³©k1:0v9L&9dÞu2hy¾Ôr4é\rS9æ §Õ¤èh4ïÎÍÜ¦h9\\,ÜþxAcFÃQÔÔ =p¡£täÛgètºæéfÇYöyS=ÌôbÜX,Ä£)ê^¬+NÄ³\n£pÖÇï`Ê9H[ä£Ï:Ð\"ð×\0+jê¾°¶ÃHÚ2B;|BÂ= P Ü%M\"	\nÔ6ãL[# ÚÊÁhÈÞß6©æáÀ)Â1mðæú	[®/@î\r\r|µ/î`@%nÊ3¡Ð:æáxï7ÎÔv¢Ar3ìp^8K2Øä2áv!HcÖ3!cj64±à^0ÉûÁhÂ´â¤PÞ¡\"Àô3º# Ä7¬ëZÇ3#7R\nu4>ë+C;v2 ÃxìÛ±ÐÊ:B¸Â9+ÃpÎ£ @1\$ bØöHÃeØÖ@ÔÚm§[,èàØ8£*\r#%õ{9>HPÈ2Æm²´1£³Â:ä6±'U7\0P:ÀÃmC ä:Ó¨ðì¹¬	BÌ5§#`,èZ/Sâ451l@&CÈ´R\"\rãe;(*@z¼Î|\rcà¬(O¦(PÞ2- Pæô«Õ*W£@õVUÕB4!µ ª^MÆ§ª½jW§D[ç\$ÓIÞ8,K:>.· ÞÅHª;\rïóíºnÚ¸é¬Õ5^^!CM)§hHêKã\r­&½û¢<ÑÔE0Pb32|pËÊÁ\nì'ióôMú1ÝGÕð£(ð\rÒ[½F)A\r#fN¨s°· ­6ì)¿¨w²µþ\\µØäi7ÍÐ×6ú3Ér	ã\$Z/6øÞÛQCpòáv£¬%×À@2¬¼:_Zþ0»%Ã©Ì\$V+pëp°D\r7ê 9\$°9Nh-/7nW\\LÄ&DÌRlMÉÁ9#ÄêÓÈnáÄ(³ Aô4\"]a:»	Y+Eßà@É\réx­ðìcÑz5|dxÒðG ª|ii.:ÀðYLüFi&tÒÓjoéÅBäìÂzvNÑÛC%ÁðI\r¡ÀÝ®g@êHJ»J«à¨¢@uÉÜO¤ÇHÐÐ	+E¼Á(V¾EÂåmìÖÀö¨C1\0)ÆjªR}cÍ#îI1mø³WèvÇ8|9>²¹¡¤»C4S`'cjÈ}2Õ¼\n (\0PNÁI#ÑºÃ Ðn(&Únâ¹SPße0oêä¾RZJ!·ldoµE\r=XsO\$õË²eCKm?,`îrÖGa3¬rqÈC:pôÏóg3Èá =) 'pV¾ø , Ç³£#%\$ä¤xDôå'%zk£¸NB¦/\$PÇRY\nÚ¡¬ô¤y5äfOrò¤!È7ä08¯×iPºÀÆDÌ»ÉðÞÀbKIzîhdlMVxS\n->ÔX*õ:5ÁMð¥ªáoZd æx	[¥Ob`yÚI«É~7d²b\0&épXÈy\"Èc\\Qxr\$ÉÕoâ0Îàub@(ûªÌlÒv1ÃXëZ?òÝôÎÐpgEÎ_§Ã¢ßÃ'e,­Ò&/yªeöïÙÕfyMâ)Emyº/Ü:ÐÐË¾duM,þ3t¯¢rQ;86È ÔUG¾}MDíY¬+ü4ÓÛPzyFÜ7Ë5°«ZY¦ÙLæ²?Ä©Ô©\0©×°JÃÍÇRa³©ç°LlNÀPOdN ´îz03Fì³¸\$@æ£ÜD ÝÊsrnÁ2Ô®µ«õ ¬äæG@TV©äi(wSJÉ\\`©ÑIt ÄZ	×çZÓj'XÊ®°ÖùTt]\rÞ¦©ÚK(cç\0ïwIDèjGÎîZhÊÝBø¢÷»Ô,Y´Ñ\$ ¯VÄ-¡ôZ!å ¹ÞBêNA	¾YË`J\$©¥00ëZ8he#¨C	\0¶©Tö«2DcSð§ÂK&\$Ê³,À^Sa¨5Léjï\".Á·©©Þá|ï55ºÊÈ&\nXCÖëwÉzbðñßF¬¥\0cÀ	ªZ\n¡Æ8^³áÕ-ò·D±·±«\r »þ1À«Ï¡\\pxf©xKÎ&ygá<bÁx9×èaðÝ1É&·åü¾êÏUµ é[­QÞgÒy&¸·»îzV¹jüv(£Øû)Y£¥ >YÚù§m6Õç¸Ò\"N³?.î¤÷ÁÆûé6ÝÏÁuÅ%ÒË3\$GdÍwâé&ÄÂæGñÁ¯/wæ:ÙóÈSÐ³aÐaÂÄ`âù¼8rÑqF;;³S8EH\$¾6ÄCs0à­jbWÈ\")²¹½¥]¹z«÷ÒWP­]8iuj_ÙWAÑÂ ìhòÐS\n,-óúªìÆ§*úü?_2ojÿû©)~9oäDb17,ì-#PW+â\"æ^A àu\"ä¦#¢EÖ%É¤SP'¢,\$ËÈª\"·7d@\nÃ?bÌ.IbïntU\\#×\$p\rþJÎsEø\r§jL½F?Æ/QÜÂ	&0OÚýâDDÐª\"CÊª^HÈèí¢ÿ°¨úê1Ð°÷Â\\¹`%Æt=@fjû¢8[Öç.Fu¯¨âMæâ°ÂâÈ!ÌºPÒ#ðØéæ¤íÐàÞîV0ço©ðÐlðôäPø +¦ç®RèKM§{\rqþÅPê/\0çÍæí:Óë¯ØÌ,-¿BQPZû+oôk/ÂnâÑìÒ£¬lÅ¶[¢+c®½0B0¬,	â9bT\"ñ~:0b>²t\r)­ÅÊ(Â¢0Æ@ØÄÆAcz°Wí=!t%p\0003dRÅ\r0ÖèóÆR[¼¦,øXÏé©±JÂ¡|1ÒVo¨pïöQjoFøQú¾fà#òýÒÆQ\"`×Û#	6ÑÒ\nòÒæGÒ4+\r¢ö>#ìÀ1¢\\c[ üàÂS*b´T¯\\÷¢c27	M\"élÐÕ#(²\$2±é\$§â`¤lj³%ÃËb`Ö`ÆA%*ÒÆ¢ì±\\-DÕCb/\n<ælå¸DmG#cd7åTâ\"\rÉ.Õ.òÚ®ÍNÜ_\$zÕ-HiMjBÏ¨hl^q\\Õ:ÖÂ_)52-i2soù\$r³2\"³(Bò²kN1cþ/\$hr°\$5(\"áã;66g\0C7(-)²Fö\"`4ªTc*ä.e	Ê,ÇÂÌìyeÈìý®ÎA©få¢-äiÌÏ:Ã}C;GÈñsºÄ£}3³:q´êLHÄÀì\$PÂ1\$ÎõÄø;å&¥>>áðß;¯´À?¯îóËC@Æâ´	³ÈWÇ4©ÿAËÐVÒæ\0Øk2©É²~ëG<f*Çz!\0Û!J<¡ ª\n puêhcè3¦èýô'îÕF².ä´Ñ\0îôt1ÎyãVðæ½ñ7Fî¨ ÔL!búª~o¬¨½Ã@Íx\nQå+Àæm'<4x+j<H¿	¤\$Ì(!	&þ£+DC!L\$LÈµE\n!Tæ4[#ªÌà%IÐ3mDíÙ¯â3nyKdd²/Pëg¥]&® ç`áR¦ç\"õ4º¤6Ci/#Ølâ.Glí.FºQE¿SjV%gæ\"dI¤S<fÏä ÐÏpÆ\"\\&Rë[|°'Ñ£à¿K\nÄÌ)HßÀ¬Hàêx,NpSæÒËö¸bÌ#-[F8¦\"Ö>sëQaC>RCN#&1eÄÖ¿²aS\r,«äãp#Â°XþVâ4õG»ú,eÂ";
            break;
        case 'sr':$e = "%ÌÂ) ¡h.Úi µ4¶	 ¾ÃÚ¨|EzÐ\\4SÖ\r¢h/ãP¥ðºHÖPön¯vÎ0GÖÖ h¡ä\r\nâ)E¨ÑÈ:%9¥Í¥>/©ÍéÙM}H×á`(`1ÆQ°Üp9C£\nD¢?!¥GÊâË:® ÕÚ'°a%e£|¿ÁDqäe0Ì¢\nÅm=c£/\"í¬mF¯°:¬¢D\"Uêj8°­Þék:]\nHÆøH²Á ±Âër9æ«a (ÚhÍÿÊÂ_(ÓïHY7D	ÛFn7#IØÒl2Ì§1Óâ: Â:4c Ð4¿ Â1?\nÚé+Ê4¤ÂI(°k³¹¯+<F\$70²)pE0k¸/ìñ¦x)½£HÜ3 Î£Ë©C°hHÊ2xÃKÊ¾¾\$1°*Ã[à;Á\0Ê9Cxå/«·²\"ËÉÎ¢±°Ú/;hz'RZÆß»îrxÆ¥IÒ&D3Á OÈ\nFçCH1E\"Bò\"_ÐªµBÈ6ÇÒØ@2\rïØ@ø\$\08KÕÂ1oØçá\0ÃMÀp[BÑãLôÔÏB9òðÈå^43ã0z\r è8aÐ^ö\\0ÒôÌ¿/áxÊ7ãw^C ^+ðÛ/4qðÍ/\r°Ò7Áà^0Îv¹é¤¡Gµsú2ª Ô\$¡79Å²@É\"¬¼\$]a1\"JìÈÃº\n1w\"ÝÃ³ëY¢I{W\ra\ng<\nãä7U(J2\".(9~b£%k_9¡	Í*:t¡QJY ºÊfs4,¨©M\"¾!F\n(Ì0£eB;#`ê2È7»Ð¨1I	#øa¤6SE´ÜNT#êù`:2X¸kÉé¤O\n ÉÒTç_ÄÃ-6;~þ®BX3¦ÌXd¥Íß3¸R?Lñs¢²#ÓT!S,hb)\"b#ØÎ.r??ñ¨Ê}´¤ñ\\2âb'æIÚK~OÊöçÔã^vhÉ.Þ0Rq-RC×'º^cB4Õ1¾xÝc×õÔ«³«Êv¨1A§¸»Ê`aáHïÕ¿\"bbÎÑ>'m@¡nÈro¦\$æ/*BXxDD\0Aeôv\nÀ \r¡Ô9ª¹Løt¬­W%Ty^KÐBpæðÁ¤3Â°ËÁñ¤?ú!àYø@/% ÝM!°2ZðelH¤BÒbÊzÀ±³¬ÑJIÍ0#¾çÈ¡\"äI§dMPà)¯#ÎzUq3Å2ihP7ÚÈ ±`:ªTè \r¼3£àæ° r!3²\rÁÕæ\nJÛ%©2¤b¿dR%ÁPö%SøºÃUhü)iTÖ\0cA¤2)¥zÖ*ÇY+-f¬õ¢ÖÕ\rÉl-´·äO½n }8Wr?mÅ½ÂnòS)RFÌ²%BQÅÀjIB\\¤«6/ÜÓB&\nÞjñ_% ðI¹`¬9¢²RÌYËAi-IµÃÙ[kj+-¸³9W(I\r¡ÀüÕ¶\"K¥ÊÝèDCZéT!Á-Èú^VÄãP;£ZMgó0ò\$@NæèDSÝ\0y*Ë°ÐhÕ<ã^\0ZÀØ£§Ê`Ræ`C5TRZÉ¿&äêYV¨þÕrip 3ÁféÆØbÙßIE9êÔ7®æßùVG§ç©SxE B\0 7v\0PVIuèÆ¤\$ãÊa.!ºm9Ç1)±ó>§Üü×Ãä©büT\nº¯ðï_\nÙ0(ÈjÈ£Èy i«vµY-®ø«ÃÞU \" eÒ¬WT-p3\0îÃhPñdÖ]\\4W\\FI±aL)g~¿h£z/`Û§dj]'ÆB>JVØë\"t^ÏCtû1x%¤;öÍÛöxå\0ë*aÆw¿5ü¸Æòï8æÍ0ó \$ÌSá½P ÝMP9ü3áÅ±ÂP@èm³iM]d²äÙ¢®¨!]¬ÆÊ)MÆ(ð¦,{ò_ëÔ,\0Ñ³ìuäÔ¼´ÜÓ\nc¬ã3âÈ,Ù61ªû´1£L¡uÍ¼Ã4;Rc\"Æ¢ÄXþz¯JY@mj*êÁ¼wÐî%e^¥8ÞêRµKu@\rùÑ#ÜÐ³|_U	,R®G._8íEHO	À*\0B E³Óì¥æ¤Üì¬kÉÊ6Iá`\n@Vº\"À°vØ¥-àF<,_uÁÚ@'¢,³«B5A{èEøCê3Q-Î¿×6iÔ|±QÂ¢÷íMÞ)1JÕÅö£ùÂpü;¦^-Ï#ùc)¸&ÆDQ^ÚRrÝ÷'âõ\$\\_Üå@oØ@NðX©QdÅªöOkçTtûûáÐÅ§§§¹;©!©5æF|ñu8'´¢­ø^³9¬4¨ByyªKR@ê%Ô³TFÛÓ²ögÙµÿ;Í¤B`1é|AIá}Îp¾ë`	íFö©yf·E²r\na¤=6HsJåcd\naüZêù6ù!HÆ(&õ©m©`u®\$9×ºL1 \n Ô 4¶	m°eî\"£2hÑ[9Ñoær«@é/òexfú©¼ÛýR£Â-å!<q\0A1H>ùoÎÌùm3}ÕW UÃk ¡Ï 3GxC/Ùî¹O³®üøC°ÝíUµ¶AåE(Â­-ò{H/'¢uQ |ÌHÖáÌ,øÝ4DLêåÄØ`*\n¨ 	\0@Ç¶\r%<eCÞÆDHD¢hèodå¦´éüaælà@êÆ`Î&v</~gFlÎ,Øsc´ÿ¡ÁÄbÿhÄÞ)haLXjyÐx©c#N6\0\\.'Px2ÉgâÆ6Pª£Iç´(c åP+ðCÐ\n)ý\nbM\n©k\nîWIÜ6Ï~PÀí0Ç	\"îD°mÂovCª£,ánÚÜ.cÄb,¬É\rP}\r£c\rñ+S0PÌÞ,îU\nË±&Á¥Æê©0î\$×©Ý1	ö%¯!#°	ÒSBK î §T6ç^´xç6Aê¤/ÎjÀ¨cz,\"'1¬¢1ë\rç\0u*ËQtÁ Á.\"Ög0ØÛ\npHMÎÁ\$äc,ú^N0°2¤K¯Gª\0î¢¤HFF®îê1ÀdîÐ¥îå©ùâAÒ³«\rã¸70ÌÕ&L`ÈÌâ¶ïïbð1N7H;!\rÀ²cöh²#rJ9\$ão	1ª#GÆÕcÌÜFÜè+#¤n Ã¸²l£X²|FKÛ&Ãu¯~Îd¤1@K àC5\0ÍG©(²®øâ 'ªð.Pm)Ã\n÷NøcR Eö-f0Á~Nv.^R Øä¼VÀÐ\r¯¬\ræÄ\r¨° ÁhÞ¨àsbFëVÆ*´\rÄòlÎL³MCHäMÌOúè2>Qæ60Ñÿò&2ó\"v:rò1±ãó! ²@^òV:òZãV\$E(«\$¦©¢LÐd=pd<pcÐÐ\nlÃRm3f7íí8PmÕ3Ó6ä&QÒ9ã80»8nÊd:ÐÊ62/¤©MZÕòS ó(µ2)5SØúLóÞ:²Ù	Óç=d;=³ï&SRúNd~ä#ò@0ô(Ý.+A±àþÇ%G0¤9Ê²à!etâCAÄx,N¾Qo:ø/xSÖ1´R^ï;s#Â#D¦¨FÇ H%BÎ¨b¨åtùCQ=3é?óì)/HGAFiðò>üó2CpÿAæ19f>ài?2»³å\0Kµ\$NK´­Këý@GY&§½\"Ö8Ï\\ùÃ!Úú%>Õb¶æÄ7<²U?t»O)N´Ñ&T0lC£ÉO´ëÝQWQÇáövÌ¶/4\$EK0Q¸_/<z í±]SèGù0´Ã>3V÷:YPePU_V3¾tñ&n1>Z{/~1ámLßT&ù93.é)aÑ¶ÿîuMMýXdZm´<\$Ns\0.wJM´éüÏTªdoìvæ&hÏ¬û\0Ö=cB¼Å¶Sr¤§DúïÃ]#Ø?Ã]¦Â¡\\õç]UìµÚ¯o½@kÅ4üÄN0OËU´óMoÇaÙKSgK¶)v'<µy[t»X\$ýV\$EfÕZ4%d1dA¡,GXâ¹RuR¢õþ#v\$t»f4ófaubl+gcfM¹Põ{@°­MP¯UUdCÏ¹,Jàmñ;H1FlèçN0Ô_*©96«[¶¯®St*6±k¨½kî_k6Çô÷ÕSf\"eã³kBEQìRsO+Î:òÇF<´+8óÎª·!£8¶]C¯\0 Øn)h.)q,w1Ð³ì@¶DB¬+¨K ª\n pÉÆ\$~%ÐLóºE­(0vkl,P¸³iw0èGík7oqëØÁõ&çìÁ2:Òw&	 ÞÆ Ìþ7rHõÃ¤ÒåÔxÿ.w¶iPÄR,bÌ0µüÒ(iè#4§ç÷'(sû9¢O­ÿ[íI\"6F6äZbî§\0r^çkl-Á!mK#o\0FøÛCSê:ñ%Ø'wP¯wÔ\$Ø\$«2vx9'=:§;çÄ^ðíGÕç7'táKÁ'lrõGG®Î*±7Inx\"¨\$Ë('(DÆsZsÎ5ÓL~áU.ÝC z±YæPêh\0¬Tàê²ùzå!8cî¿LØæ®t£ØÎ´|#ÿifrâx;ØáéãB×9ãXçîqXÀËnÂi)öê5Jï ÞÔàîöÆÉV)\n®d&3T§ 7ÎcÕ;á~";
            break;
        case 'sv':$e = "%ÌÂ(e:ì5)È@i7¢	È 6ELÔàp&Ã)¸\\\n\$0ÖÆsÒ8t!CtrZo9I\rb%9¤äiC7áñ,X\nFC1 Ôl7ADqÚznä\na¡!ÆC¬zk³ÁDqäe0ât\n<pÅÑ9=NÒÚù7'Lñ	²ænÂ%Æ#)²Hr¦LÃ3ð¹|ÉÊ+f-¦5/2p9NÔ\\C*Ä!7øÜK\\ 2QÑ9ÉÊg6§ÕÌfàèsð¢+¾Ï¦uøý®äCS7Oe½n¦ºÎT»ÞÄ0Ö­«ªøÈ	ãZ¾Ìë¤<¥£(Úô¬Ã²Í\0+Å\"ÀÃ&£hÜ	Rô7'0³kÌ8Õ3((,ÃhÂ7\$¨J 6E0­j ß&òfß\"ÆÅãØ:¥ÌÒb(Î+¼£©pÜCÉ,Bâ84.ã0z\rà9Ax^;Ír+F\rp3é(^êcïá	²\0ÜÔQr`7Áà^0É8¬S4â°Ò9¦Ã ä:c ê³­+Í³¬µ:28ã°ò	ÃÊ0¿â´0Â0ÀPJCÊ\$Öaj ¢Xß#Ã@P¨²UðÚ6Æ5\$\rô&¯ãßïL¤°¨ääTYIFB0ê7S#M		#8Î»°´6)¥OúU	5Ìðèô»Ï@Ê£+\0ÊC-Ö¯v¥2¢e1ËA\n:!iôq3Îk6c\\_\nbvl\\°\rëA,¦¯ãª´ò9'Q3Dó4È#·:ØÓ©\rÂ\rHf3®oEmËv7«8¥\$Gk8¦2p	ºî\$£ò×B(ñ­,O@§%îuB@T+p(\0Ú:¥²ÈÃ=®ôÐå)Åè ä×45ËSØB\$C=62ïÁðA¶¢Xæéj(;qÒc <#Ä8!­Bßm±îu:5@ëcÂq46».Üt³Xè`{ã-7 PÙK Ëk7Ã0Ù¤âdPI·!Â9N£`ò1ãNJ]V£7å°äðÉÛK)Ù¼Z/6Ip#zcoªÌû7·î¢qj¬äÇô>£Îûb¹~Ø¼b|qmIÂBN~ãå8\$\$¶DI(å¦¾D*\\\$+Åæo!	K@.T¼dLÉ ;¦¨HAroN!¸E4D@ú#&ÐÂâNeN±&Rd-Ã¯Âã}d\$PìC\$WCèERâÊQKÎÃtÂeLé¥5´ØraH¤ÄòÚÁAÈÄÓ8<ÖC·\"ð\ró\$2p|m,ª£¤_Îô H9Ä|ý*\rØì9ÁJIÆb+l£2QÒ5Å*dÞÓÜ\"¯}ÿÜhÕZÇ0¢bÂþdÃ45µÑÕÔká9L( f¨¤tL9¾Ó-!J\0Èè!Ä¢Jb¦àÝÀÒió`%Æ¤'5E)'7d¦\\ZÎg¢l4m\$A¸¤¤ÃÄ²òE#!¥Amå¤[d¸4åaÇEä2-GKCI0¤) 8yp\$À±wTuamÔÀxÈJbI2Þ}*MMúß!'2¾pÖnNMz>¸hJq|@²Ðbê3üðh3-!JaRuz ¦Ã|x( ¥ç ¶Æ&RËòdVÂT©9)ÉtA¹ 412B\\ù9bÚ´;&KºåBT-âØ&ò`HÙ#õÌæ:ÇÉl¥dp#@ ëOkYkv|×iÈK5ËHRL¥ýÙ,ì®âÌaèI*\0 T¡H\$àvnØB	áH*@Âuå¼êù`QÀÂ°Bu÷Jr¸[îBaºXc<ø>]UV\"\\¸^ÑÂz+j¤ÏÂº?tâÒF`^M@/7%-R\\dÎþ,J)F(ì>A[agx½¢©HòÏÝ0Á¤çõ¬²)KÒR¨@tå`;í³~pà²eã¡·#äðFâÍXªüÝcrÏÃBnÄ¦\0Y2¨¢ÄÍy0ÔÉË°e\$¡Ì4õîQ%×2ªNJµN>alåÑS]rÑt9!á2â¤Ö*# (&ÅDËU!§ÁTÑ\\R{sðnft6z÷(C.ú3w¸VÃ²fê<bµÔÂ.æ_F\$­:,À¥D,Â¨tPL#Ïð~1gh¾ÿxopC	\0¶]WªîN,Á*%,3;Iw-v¯\nÈðA½®àï¤&ý6æ¯c]æùÎú¿¤1z?ÇüÎµü)púkÈt®á¦½ÇÍÈ|MÈóOMñ¼Hg<9ù+Q¹ãã¡,àfv¦ùãs,[\"AnW\rÆaÉz!Y­ËÓ2W ;6G/â	2ÊK³ÔC¶2&Ñ¸hÍ-2¤-vr*ÈÜsqAJÍÖ°\"öRvm(#.;çIV6Þá[ñ\$ãº)÷|O±»QÞí¾Tk»Âðë»Ùã©â{àlC(l6ççqÓºÛ[G%\ræÙBPP0ÄÃ/eèÆÅÔzH·LG2úO³ø~ôIôX6s\"BpwÔ°³0ïÊÁpeÕ(M°Ðÿgïø§µâKô{¼¡\$&R/';»Öú(ÚøïoóÚäî:Äµù×e6¬jêðwÝÎ³,Ü°´²`þBþºþÎã®6àÂ¹P\"mT{ÞïÀÂí,TµÔæFÕ-Vñã°.¹°3Ð6ìïÌý­4«zJ\rÀñb	T6EÅ%ÂÆÈ!d_Àñ\0ðX\r¨täÚ«Øß(l,Í\nIÐXJ6&°AFº 2Í;¥@3Í*Ô\$8ÍHIpX-ö°<üðÇP(ú¥ºUOlÛB8TÆºjÏ¥o¢Nj¤cäã°ôÙ°Úï/Tc¦¬=&ÈIéÌÞ\nd^å\\@Ô_à\r#89pZ·CvÊQ!\0ÿÇ\rï\rQA\0Àý;°M\rÎ;8ÅÆ\\¯ËúLl-1`\\Ýó±nSf8Íº¢prÕ­^íä 3Â>4bNÕÀÊ#híLôo	H-§Q¬(ÌòÐ1¶÷|ÖÃþ-rMv.ðë\r/×\rcQØîïlñ1Ñ-yq­@¦QÓeÿ'\r\nÒ@pþ^â¸Û±òîàÄycv!.ÖÄçK!d!£k!éú«pËh>ó\"'!Ò ØÎð:¶	ò6¥&\"fU 4år7lN®Î1Iï&qliH7Îï'üàFRvâ\\xgÜ×s&L¤ìÒE#h4öÈ,ßV.E#2FA*Õ¥,\rV\rg¨ëùVö\"&²ècã¬\"ú3ðHJb+¸\n~\rÈÚÄH\$ð\n\"îî\r+l0>å®..Ï0J4ÏÚú)Ò[àÔl¬ÄCq]ã:Î,ãÊì#O\"ÈA	hËâXøÊRÚöÓ\"¥'ç¥-e ç,V § ãN¢Z%3	\n(xm^ï#(µ*Cd\"Ä5ïÌR²!ÇÂÏÏ83ãààó\nñS±Gó ìàÞ31:\nf4ó·åLÌeãòí,|	¯,,çT2£À|øÆ	ÀÃÓb\nNvv\"z#d	;Ó^4ëèÓb¸2¤=D>£ÑÁàÂ\$Ô¢éä¦Æ#9Ì·:fÄ«ãt'lè³Äg¤\\tcVRB.(Â`@qi ";
            break;
        case 'ta':$e = "%ÌÂ)À®J¸è¸:ªÂ:º¬¢ðu>8â@#\"°ñ\0 êp6Ì&ALQ\\! êøò¹_ FK£hÌâµ¯ã3XÒ½.B!PÅt9_¦Ð`ê\$RT¡êmq?5MN%ÕurÎ¹@W DS\nÂâ4ûª;¢Ô(´pP°0cA¨Øn8ÒUUÉ¼§_AìØårÂª®Z×.(qg¤ª+S¤¿\\+²5¹~\n\$g#)æeµíô«GKN@çrú|º,¯¼FÕÑÝ,u]ÇFÉdòX¦Gi§óST­rPÅå+ú_Ë5ÉÈÊÆîÉaÊ^i6OCµÌåq)ÕJ½·jÉ^E.QÅ@Ð+°W@J§êã,W(I{ø»¿ËÒ\$¤#xê\rìÜ\rÃx@8CHì4(Î2a\0é\$ã Â:7 Ð4Æ# Â1E­ÛHµ%ú!¤p¢°#%9nÚÒ@P#xó;èj¹\"r\\ìÂK<ç´<2Jj°ï2èt ª8ª³³1ÍPd··Ï2âóN°x)ÄCHÜ3´(Q*ÚãÊÅ¢¤2Ó(7¨L(\n£p×ãp@2CÞ9HJÑp¾ïS cÈç\r¡Jü½E¢¸L»\rYPDceI=LkÔª5L\$«o_).ëÅ1Am\" ÒJÏÖÊYÏUiÚïSÒë´,G(Ô, X+K&²C(ÔµvÊ]u<×#8Z3 # ÛAT\0È7ÆJ: 8áQáÆ1Æ3Çí³p;1Ô=Äí°æ;Ôc X°ÐÚÁèD4 à9Ax^;çrWÕ\0]QáxÊ7ãEC ^-ðÛQ·ÍQ±¸Ò7Áà^0Ð4³«Ü¨å2Í*Ð»BÅzd)öj½T5æðO³Vý·êÒãDß7ç9¼õ¹mìÐÕ¹AZ83½×<^{AZ³(XIu\"ÊÌrÊºñQâ¸Â9\rØ\nr{¾ OY×p¯=3Ó)]umo,#v´hg)lÆçB½¤üµôÈ*{Ú\"£0Â:@ì0¨Ê¤ÀIÞÊ¨lésIµÒ:¶C¦®7 ²]ÏÜìÐ2J ã;h3êÚÅ~ãNC·)\"8«JÝZN=¶­Ò»Rm+HM#!¢e 2LgÜ.÷ zÉÃÃ]¯Iº!ÓÂêPå\$rbLÛ	 }a°6#\$~H c!¹?KË>ÏÑâ¿\$*äI.)¢\n§e°àQyÐ°Ê'×)ÜKÎoé£72êuÏ1Ì^êQÃ9SäÂrÝåA·á##, ,Sy¡4S&b®³<qd8ñ°âé¬[ÎèøEÆfÎ8 ½ó¨z£j]{Ç³¸cVvÂ¸ñÂ´àtaêm¥{ÁÇ-]Ûz+i	dpÝÑ;ê!/¤î	(I\r¬,9Gþóc;Abº_ÄÓèÜÁ\\o<9öÊy£ªñW0\"'ÀÙ	«åLÐ½¶.ØhuUì 0´Óh«¨bªyÓ ÂZÓ\\)¥¦6°Hg!|ãr¹)Csq9Õ\rÔ:ÑÎCÂ8~¼Ý@2Þä¸°\0¹±~qäY00èuÔø¡jfL0xâ¦JçõJ2¾ç#ì 7ËÜö<.ïééZ[f`­,\$2IB8e\rá´2Óò¬ÈrHuºspÃ0f\r\0ÝÖgÜáÊq`(*ôLÕCpy°9VÄC3Ø¤3¨ æÉê³&»6xQÀ(`¦²ÎJTò\$n1ÙôêÌdªmj³6RKFÒR¢!SHÅ©*æÄ _¬ñP2pÆÖd`,¥2Æ\\Ì4fÌà;³¥üÀ#@hMÑ7ÜÖX>¸­YAÐUJÚ8·+ Aò5ª]&1ÙKRr~V©ó ºCøWHºà^i{CñyH	¦ÙZfÙ%Sáà8h/%´eoVÛ³fÍY»9gw&ÐÚhlJä4Àæ¦(µP7JAê£u¨ÎtÖÔX@pTÁ¡KÚîºµ®ÉT¾Oêø[q6ò½ A;½øÛ@\rÃ¸êyC@@Æ*Vª:ÐÂ¯«	®÷â¼ÃZùBXâ:FY ¨\re¶Xt4½Ê;w×=J~À¢Q ç)ä¶E!\\ØPP	@cZf¶¶pB²­³ÙÏMKf|Ç\$ÿ¡°?s=oºÔâDPb.ÍH#xÁ{\r¼;æ¤±ëLv¨¤çE,%1å\"¥ºMngý-hUQF,X9´f!Bò:j!¸8XC~C­èì1Ñ@YOG9N ÊRS\nA2Þôv3m-¯?]sVï&¶ÈBì¤È¯y¨ú¨È¥=%\nßbæFp,ÌÙy\"¢ó£©.ÖÆ«µÂ8TB¦\\4ÞÉ3dZo2ðWd3ö7A¼ªGÛøaêÞ	PÓÞô§8îåKÅªsðS)c÷ß5p»ñÆò\r`y{Ñ¼¤Pó\r.µFÑÚ16ÅïN°Ì¨mª·ìöÓl¥<ÉI·Ìz£\"óvd<áñî3k0@xS\n²'ÍâC÷N=]\n>¸d÷+ø?Bßq«vÞ*RôzR¼bKkÃu\0 SÛ%ô£i¦ÑPeÒQÇð :L:SÇLel@A\0SÀCVÅ0TÐíÖÃ(CêõO§%{cb_8åõ1(I:ý*qZ§*Fç¥ÂÂp \n¡@\"¨@WÐ\"Àõ×½Lu.Uük¶wQN}E¾cjAÓæjª¨4ê½Ò©ÝB«0à%f9L§ä*©Èøæë<\"h¶wÈ\0AÇ\$pÄÎ|oóÇt8í(åækçrp8qâeÉ\n\\ÑÐ%®f\\HVè(ÒïM©lüÆÅ¦vù§@Ö#VX6ð¢BkÎê[Jî¼VírMvÿÈîÑÎNtÈz0v|5ÎîÃrDOÂgÈÑ-Ö*G!ÄèªÞÏ é¬üh?Hò©/m­ÐM®P>ÅI'+K®è\$ ¬tìÚH`ãDçÃ(FJÄ¦Ä|Nt§á#JGED ÎÚÍÐêÂgÞ7#å¥êÐ|q(-2OB¥t\0¤z£h`Ðï?ÂwOi¡\nÔdþ\r ô{éük{à¦¤ZÓlÕs\0ô­F%0¯ÀlÒÂ`ÐÕ ¾kêÍªÇ'ZE`Êé¾7®q\0Ô¼.í§6,E è´°nø¨Ä*üÎ é\rËd[ øc¯6<qB{B/¤!IqrÇHëäñ)¶¤-c@l`Ö  6n7\rª²Q²ã­ôsJ\rçt@Rg\0r8x).`|CAÅ@c­F@ò\\Íg&2¬rq%k.<êÒß\raIãW©	C¢\0jnð½©¤q ÇQFÏÞl§\rèé\r)¶@ï¬\n`©(¦\n«ÍPa\nê	G\0\$ß -}/Êqk04¯ÞOÇfu¯²<(Çe2Ðÿ)<wR;	x©î`ýp¢<QP¤\rÿ+ÄÁbÒdçg2ïÊèìó®M5îsd¼`Ö¨AsMrÔ×²ÓúÇ³U6	H¼Si3É'Ï×I7(Ð7Ó7³bXcÚSQ,íw9\$é.(ãÕÎ(qÔSªJXÇ-ÚÒÓjøÑ1ò®c\0Ìý<5<Ó\\ÇÓU.fÓ6²È?1ðfù7ÓÐY;Må=³HÅï÷9VKÐªÀöÆ:Dçââ²£¨¹ò4yÃ¤%KóOm/(D=0N»îb;çÌýÉGËª7®/âÑXãTP'pè0)-´^Â\\\nU<«:èBIä*½±òrçB%V@¨ìÈdÜè\rX-nÓ±\rdp&\0E§NâÈNîÒ~ðh\0JÅÏã*-æ»Éc*Ô7t;7ãw.\rýQ/PæuLÔ±Q¶âUåD\rQµB#PiV\r#?TlET¿StNS2é¦½u\rLðQTÇUÓ|ÑiE8rB±wU±ÓXs:ß4QQÌàU/@53*õwXSUn©r¡;Gi?t|£Á\\ÉmJ¨NÑâ¡?\rSâ¨zÇ°ÊQd âòp8 Dr5ôµ D+9s~AâÞùý)vJµ³<0°Qn\rT'K¯Û®~æßA÷±:ðî9µ*5¡;µ¤,Í_V\$²Çbª0\0¨,TV@\rÎTf:\r\0ÚÒàÞ{ Ú~#IÕµeÂÓ\0Èc§ß	Ð{2ð_VwJÕgYµU@kÅ÷AS[6`ô2tâpôVõXU_W¡bRoßL!]YOYSaWVíáh£8}jvt½PíkiwmQ¶^Âl©E&©MLñôT¹Pö»c|Li?jI:6IÖGnéqHHçÆ^ôqBmá86±p0*ÉO^-v,V!c²®<×n³!Fs?buÅcñnÕ§*÷5X=§;ôtRAgVPSu!uVÝS°ø78Ov×*ä·?Õ£lxR/x3-õ>0'S½zöÞÏ²&µdüHéb)Xüód6ï Ñø£6¥2wþîAy=°ªÇªØ#dÔo«.Í\$ry]ô^)FÓY=SÅA'H¾+Ë&adyB3§x®l=O>,û¯÷WvW»}Ã×1phÃ.©p	2åW]¬6P  ­\$Ï8^rë¹5Ëx,ÈRj¥{R6øsFucÕW§\r\"LvðWWò.ü'or3=®w(4eð¾È±Ê)ÏlØµ#võVÖöñròxöµ©ä½Håk	±.XëV¸ïoóvô\$Kúõpõ©l4¢`\0P¤FÃ­%×i¸Ýck)k,Å²M¸ÙW?ùCU¾¤ù+õÙPýxÈ[67zØ¥|¹ZÑÑm9G\0²¹1zXK)YIÇ*´ãÙkl÷¿)©EX±\ri]T@QGAø-²KøüQdÇµ#3ÆH\$ÔÞ)K¹Lî_iCZI|7«m¹YpñtC/w©ñ{ïyYòÝVÑ|syýoÖ}±?rF3nb¡hÅGµ¢o0k\rç'|ð¹£izO¹×N.¸¼AG\$Ò`\rd@6Í´hf^¬U0ºu%zF,¡¨¸¾­\\ÒòO§C©\0Ë¨Ó%³e¬³%òbKÒw7¹ru×T-¡«·Bmz:-yûou8³òeYéÓ©¤²ã¦0u:ËÒu®»z	WÒ-§¯õ{®ºÕ:=¯ÿ¯D®\\Ja\"Â%¥¨-\"i(WqrÙHç\r¡Hì|cw³õ³íõ´6}³¬[+nñ%&ºyó´W-£/¡¹©¯w¯·	lÉ\0Éoâtëpv)}]Ð\nw*×+\n)Z0}3+ãüpÐÁ¯\$+?;*yÔ8yyUÁ@÷»·I¦öç±ûÂ2ù¼;Ú#;Ð¥Øc19eNf°óYÍ¬à\"Öu´Õ½ó¼Y~¥{ËW·Úi¶I­¶4]îQwrs³ëm%²×kÂ-ÂÐÃÕuíÀ§\0ØnÀ\r:¤,{gºG*Þ\röÊäc¢ÍB	Ôa Ù¤\n p¢K_h%)ë7ºÇ6ó\\ØFÉöéÒZTæZ3¶V¿ÃS¥}ñ46²WÎJü¢ç¹dÃ&ÊÔ&°\rËPéÄØ£½­°g\r\nö±fÑÊçÒÿkÊ<ÙÑ\0Ø>eÊ	¼h\r<m\\6òè|Á+ñ^\rú~É¹){\"2ÛSo4£ö3Ä7`lyÉtÕ®½7»TR¦õ)%iÐj.GÀÕüPåqÐâ3³\\^«àd·ÏÎ5ªFë¥»IÙW7âJ§ØS±1c¿VÎüÜÝ·X°ø®R×å,(·ÔOEs[´|ö¿ÎtQWý|}Á®@¨¡ÄbD\$G§å<\ràà}ÅÿÜNÂx]ÅzýévÖ9¶ë4éM(õjG\rÞ^ÙT÷1V³ÎÃË[½\r¥`É¿{¿iHWl²~ ñw\\_`c\0@Ãh{øYbWHPú`ê¶gÈ÷ÔÖÕ}\0P	þD§|¼qÜÌ\\CÌoÙÂa\\md&5}Íä\\XmÝâ¥¢J\nåÚrZò»Ý0¿çå2-ìJ\nU\\Ð3ÛpKOY¿~÷wÍÝ#@\råzñáçä5x:â9^xÄ²êâ_M}»FÚ	\0@	 t\n`¦";
            break;
        case 'th':$e = "%ÌÂáOZAS0U/Z\$CDAUPÈ´qp£¥ ªØ*Æ\n  ª¸*\nÅW	ùlM1ÄÑ\"èâT¸®!«R4\\K3uÄmp¹¡ãPUÄåq\\-c8UR\n%bh9\\êÇEY*uq2[ÈÄS\ny8\\E×1ÌBñH¥#'\0PÀb2£a¸às=Gà«\n ASZåg\\ZsÕòf{2ª®q4\rv÷ ®u´Tq,É..+h(n1¦æs»®6t9òK'ÙvKüÖ!ÎAvyOS.lU²äØ´t.}pçûâTkî½pü+næC®í´³>æË>èB¶¼¾iô¾\"êËXì È*~-h+øæ#Ð\0,ã¨@4#³7\rá\0à9\r#°Ò6£8Ê9¤R: Â:8Ã Ð4Æ Â1FKô=º\n[;Iì·+c¿:l¤¨Ö´p,,µC éÃì\$45·¯Ëâ Ê0¨¥=È9s?ÂûB.jQ@oëB`P§#pÎ¥Ï.(cæñ´OsÌB¨Ü5Å¸Ü£ä7NKÊ=ïÌjäæ¶ÐÃÂF'«Ku43NÌ³J<­*zæ·BÍ¾Ï:zH4²Û×.»*MV-JÎ¯<ÕGâal ¥ûUö×iWV«'k¿OåPi8 mP ß@èqÀáR_cÆ1Æc3\rëÈn#;1ôCÅN æ;Ô XPÐáÁèD4 à9Ax^;åpÃxÞu-H3ã(Ü¶09xDºÃmHãQ#5H6ÇcHÞ7xÂ@0EÝÃ\rUe£ªzZù¶ju¶©)údæ­sÂ÷?ö{#F«O´øöJ[Hý¼ì&Ú=e>ÔÂ[s\\íïclîïH,ôíî_Ð7x+#Ý`PJ2 £ô©m:¸éÉÉZ-sºJ'¼j=>\\-Ãø\\Yó¬ïoó½\$>\n­·ÃCÔ¨0|B Ê3#¨Ù}ÃØ:¶~§Ï)ê\$-Â[\$ÓXZîklPóÏZÜIê°ÔË\rXûö{­zÙFöÖßpÂ*°¡\rqîCÇllÔB§õ¶òm»sw©õHòz¶Öòá('t ¸¨,PlF©\r/ÀÆCt\nvÅ9îÁQ	í;ôÌÈ¸JOTÊ¦ÛKãoL\0éÅJWMe/XhÛ\\BníÈµÂ`ÐZA=lËD¨Åh°T0cí -EÄG<ë*%Ö´³5IBàL §:âãyX~)Pâô3Yî¸¯ôüAM\\wI0eD¥\$Tßj±iÅ®6æãÁÿqt½¼Pô+TI¥ÐÐê×Ûh:W\$ÂTûA<´ÖO%PsÁ\r¥`ÒåpeÀøãqÉàDHãJEH¨tÌ³>RPðsig Ä'¦Ù\$D6øA¶Q¯¥3¨ÁÒA³¢ï×x%fE¸§!ãMÉ?(dÈÕÇ!*XrCè @Ã0f\rÌä¡ªAKãþ\n ý	 z)i¸<\0ë6C«`¡å\0ØÃ:ll:*JC8aóËÊUQà(`¦½J0GÖU*¥p¿7øRåOMæÔDH¦Ñ£FT( @¼nT,l1Í°Ò£Dl2FLÊS,ì¹V&d£6ëÚhFÍÁ>¯%EL2yCÚSçI­´0ÊõSo1¤ÈZ\nCW?«MÔª;&Ï©|CÐÙ¶3zÐðM8ò­³ÎÜÄÖ¤á@\\Çõldl²VËY}aT ¹³Vo5¦ÅgÍ\0\$Ðàk6bÝ\$æ´¥!­¢/°à¨iÒ=5E®¸NO¥µ0ZÿUb»	uù¥ !b8×|9U«¢æfµò°KK!/¦*}#äk|Caº àZÐ«óÎáTê~Ùö6ÉE¸äù0âB/ÓÌG@11ÝB0F*\0\0(.¦2Ç¤SýôK×ÁE'`*^êÅ|Ø:5E(­¢ôcS\0F|ö~xwÂÇ&Í,ÈÞRrP	|Ù¨Úª²¡hÑ6rÁ&2GÍ7\nÅm{¾Èþ1SàÎÉúÁçÊpÊÔH-¦VçÚXÇVS\nAÌæíÓînsð¾HBèØçÒñÖ<n«Jé\r¤ê90·bò³[,[xÐ¶º£J)LCÔúzµZAÎYHðjl½\0vX©ÏÚIoy¡³åÕöqCuØGèÐáGôf¾Ã2£\rµ~º¯Lö§Ã/8¸?.3e/&©:xøûbÓIfbän~\0].¯\\;0FSQ\"#An¢àK9é-oì·5Úu^üH­Øã[9³y^ô,â DPO£^ËØNðÂ	}	Qs\nÁSB0ns\0Û£u/ÅB,\n~°¦%¨ÝÚ¼.'¼ªv¾*Ö0O	À*\0B E]@/iV²\\rèÎ£]	®tIÚ,=Äø5\\'ñYhm¼VtøÎù(S±ã\reÃ¿U¿ÏPµI´U(\0¸¼ìh?FÎ:·u¨ÉQQ¨Ï´baÖÓÅóPHmEÐ÷­Y>T,ÕíMw?ªO lýtIß>úJ)->N§ûê'I!äfÒ²ÏNø;>'lßnæ¨Ö.F=uÉÂÄõ)'@¿å§° ÆØàº|ÙÈ%¶~5OÞÿ£¢6î6´òãr?¼[ÀS	Úû&Ò\n`ÒG¢¨¢¤`¾l,õcä	Þeì°¹àêÁg*l*fÀæ\r¼KTµÒyÐ.Ë\\ îd Ïãú~#~äX²ÝH.>%l(\"\" ¨®ÁpØGJI0Jp)ML÷¦ùà'TÈ/ÆünQ©þ\\-¦OJBO,:îìêä¨ë/Th®`Ör 8DvÑò\rgÔºK\rsåfòOBjEÎFJeBb,°@óCº-+OÌéèúLÌ¨s\n\rn(ÎhðKâPJ:'¯`Èù¦ÎnÏâWC>È¥*neí\0¨ 	\0@ÜÄX_\"Dì¼Àà_àäGlNNAfÀöd(ÍÎkÖrÇ0ã§¦îP\nj6k)ÔW#¦&HV´íL²Dâ¬V.g1dú\n\0bÜåDN\rñÎÑLäN¾U\"©D#qj)\"yínP*ÉtÑên±ÔV %ÎÒ3e	b @T`îr§>IV'ð¶½d0ëê4\\%Z\\%b¥h\nàÊCN çÌ¬8ñlRäþgþ(ã¬ïcpmãR[n¸r~Y%ÂB\$ÄïÅÂ¢Ê\09¤ÒJ¼ßä=Äê(iÐRª\$c!(xdìYâdñ&îKn&B~+\rbnpÆu¬ÿ!£¦øc¬N2*pÃ5\"Øò©îò& ÃÅiI.ó9Ë!°Øòcêøò9/rîl-/Æ¸MÒÞKfé	51!N9(¶ùâØüßîÃ³L9%m*'?5*îù¼×tÉ½&¶ä¥,1k/n4ÀT`à8à~'²»&Hr;§¸5Â~[hïó)±:DÖînúsóxA3³:0®§Ê£0Êo¨oè}\rB¬IÓ;\"½7N8÷ß\nð]àØåHb Ð\r¬\rç\r©²ðâ<¢=ófMÜ&\"¯å÷q^úàÓ363Ó¤ïhï£7ïboç1Qdà(BÌð+t5(~3Ù7¯ÏB¯|näá>êdÜWXÑSbÝ2D©Òð4rÏ!Ç26q½BãgH5¯¾OÃ&T}N­EÆ­¨oøo=\rôºê¥ÊëÄâÓÛÓ×\râ¼´K4î¤ªTÔkNL1M/SRô39/´yOPnqüË0øÑ/k ûFæ&D0Q§ïQªÍæ²~âN9Ô,\\0úãl\rï öï¶Åsó73äñADe×E?3Dûu(oònn¼ÐÄk\$÷Km\\êßüëPß´:X3ä§4\"}Gfk5rt²tpxCmHÔëCg\ntø©ÞpÇ/uÇ2õ·\\ÔÜæºµ]4Ï2s#zp§Z§Øâi)õC4îÒ4u÷ûLu}F0Ï\nUÅEõó`9¶]±¨Öï^6AFTéFÝaç_b. UnÖ@²_§å2#8ïNãÜ(\")2{_dÊYò WÃ5\"Ø/f#pE0\nF&^s#MôÉ^SéhÊicFù\\;a¶V¯ÊÅi5Ü1g6DÔ`üh@8è6ÇNP£lÐÔEEF`È4O5VÐ¤ì ivïPVä\röénÓ\\åÂ3ç<16þu.ïU\0ËDF8¦l^³»ñr\$Dh¿W.yËXÌl7's÷,Þýx'^År1\n¹b¶PMvvÖ£[ö9aOCWyåé\\öKl3xÊn'úC·¡v¥BYòN\nû6Uir\n3±oH-)Ö¯m³Õm÷F·Â@ÉTgGvÅbÖ¾WÚWkÕ|¬C@8Ð¤¾ÇÂën&Øi\nØÂ ï¯N§§ùIò²µ©mm/6)ø²_q%ãÜq°³Ã.N[.²zvÉ×Yõ7JdV).MJèåK%Á|é0¦ Ømâ\r7\$ÚNèÕ¤®@á8J2''iL*Ï.È\n p\nÊÛ ñÞJ§é(2ÕwTðÅ-ûA.&ÈÂVèÎÍ¯\0ßâdÉPd\rí¸Ñ/\rïk\"vÄê¢\n¨ïd³.'&ùVÎ\\)t£4ðN-;>C,ÊjVNbq/vm¬phJGDY9Å:DÈmW8v-fð|qÈú¥Ã-5:GYTíLr¦¶õêùl-ôuBul+mÎM³7FÂÝGYsmþMWhDMr·.\ràà¯Åå\nwb355¹nJµÊñsf#¤³Æ(9£3ë³îûtCÑcØÌÿVæçîØîÏsÃ\nÕ\\+39ØqHNOç\rt¤ñ`¬`@ê´êB9\"U~(êW(a=ðgÇ>£ÙÓ2îUÞïr^ïÓ*U-å<P'¹ôÃè2&Þ{õôWHÏCïI9úh=x@\rî ðz\$úvÃp(2_VÈ¬HøS¡Ä6z¨-ÇP	\0@	 t\n`¦";
            break;
        case 'tr':$e = "%ÌÂ(o9L\";\rln2NFaÚi<ÎBàS`z4hPË\"2B!B¼òu:`Eºhr§2r	L§cÀAb'âÁ\0(`1ÆQ°Üp9Î¦Ãal±1ÎN5áÊ+bò(¹ÎBi=ÁDqäe0Ì³£úUÃâ18¸Êt5ÈhæZM,4¤&`(¨a1\râÉ®}d=Iâ¶^a<ÍÃ~xB3©|2Éu2×\"ÆSXÒÃSâ8|Iºá¬×iÏ1¥gQÌÞÌ\rï;M¸no+¡\$ÍÇ#ÓÒAE>yÉF½qH7Òµ\\¯¦ãY¸Þ;¤Hä#Ò9³Ö:Ãª¬«jê¾° P 0Àjè+\$¡.1+É #(Oµé;Î4#¢o\r#\"Ö(ãxÊ9óV9£à5cÆ1ÆëpÜ3XÉ³Êö;# Ð7¨ÈXà¼cº42\0x¸ÌC@è:tã¼Ü1BÈÐÎ¤á|®9Ë2Ø^'aðÚ/iÌ\r±hÒñxÂ#â&®\0,#hÔ-±m\"L³f&JB,FMºÈ6¬[TUBÌT\rmCTý!IÁb®@PJ2%@èØv(òîìÐÜË,²£h'}T·¢å6­uÕ~¯VDýB)Ò¥8#<CZÜÖ³W·-Øo PÐßãJÎ\"HÖ²è=a6x4¢îhÃ±BH£¥NÚÌ»¶Ù3,ÛdòQ#D)\"eqm&¨0ÒÕËÃÃpîA¦©ÍL4Ö	O\"×5ÓõN} Q!:Õ­ÅIªøAè94*\$­CiRXÛ{M`êèó{#Î: yDðÂs¶mÐû5¥\rf§ Â½RÉØ@6°D¼0Ð+ºb9\$WD0ò4mKôñÃHÏ§¼|¾7Év@í÷Á#L0ÝÓtO2HÜ·<Kë×s^Ìº&4:!DÙûô3½X}@³ã,HØ4Äc-´Ã­`é`d:Â>jÊøôz© ×í^«ZÃô®é\\]«\$¸J\"77ks9L¦g²XÖE(iW'ñ´Ï r1oÐ<÷¾µßqÛD\rÅ_6rÏdq£8Ð^e\$=ã|pR	j¬5­`Î:_	2¦4ÊÓJkM©½8Áäêºy%.­T\$þ\$(û§\n9dDê¸^ç<GÃN}áÍh£sÎJ_ÐuK¡°²@ÌIÁ\$1´H	`ã«je(3sH¢áeÈÖ0à@iÃa4Bá\0b¡4É4&¤ØºpNOÐ§dðÃ±\$îÒL(\0æB%!W<®!	_\rÑbµ@cNáÅP?²6ÆXÙK¦¡©¿\0aíUÛì\ròµ\nwòþÛjè&ÔzYé&HµÀªÃrà9Àr*ý	P	@ôNuRYÉó\0 fÀ(E£3°ÙÊ¾rÐXRsDúGúh¬JBèBä-±\"HDÃa#2Ü#®Ò\$[Ò¸b,Þ° Ù0ç\n (TqÒºDt1¦:àQÚ}k ;¯ðÆï¸gLÄ°½¥\"÷@Ü(e#uµ*A<ÂS\nA{£x¬~^ÉÂFyÈÉø#`ÅQrLIé}d-EÌÕJ'äÒ?ÈËÝ8TÄJxd·É¼p5äÉ aSróÈÀ%(Áz8Ô1%Ê_LPW)]pJ·Ê!ß8b,ä²Ð ÂT:\$L·%ÚÍRKzPûràB3:J<DyÃ¨ysdf¡Ë×9°\$Ô`©;+\rÁ}^ZAyIX(eD¤ W¬zN&`g ÒÉ\0/gáS q>{I%Õ¦d0½Èr®KTuµÂp \n¡@\"¨gH=ÐÖsj\0D1¢Æ×S()ÀD\"ÀðNØ6?®cVYÆÃh%¬ÓÄ_Ú¸Å	éÞ¼c7Ðcy\rg®?CÒ°MGòNÓÔC­ù,Aý¯¶ã£NL{hW<ÚSë6QD}HVn½[-Ø¤÷åÕ'ÉË]ÊHóæ:ÒÚjÁ#Òäí¼µ@[§\"³á¬ê¬³*´^Ðt_ëS°ÞNäyÉ2ÂGòÙýÈUÑQL/Ù]¿«ÿ·m(kØ¸\nÁK8!¥Ù\nÓÌÊ¡07§Ü5hunÍ¿`Ë&ÒE¤Ê\rnÕdTZÛ [h@Ü«Õ!.aº0è#ªÚc69y<=·ðhlôçWðQÙç\n4fßôÉ^7ÚhÓÔé`c^@(!})øeßFYcþ·26ÓÚ©G[ òs¤	` á¤Ð¤Døh*à5òkfÄ ~9Î\rí`à`¨BH<1\0­æ]9=@­;TÒbÂX¼¦¬djö00êãsNY1ÄþaÀàPMF¯´Ø±Ñ+[¾~,MYÄ¬ù>kÒ:Sìé¼G§×>¤Nº¡ÐêýdÄu°Òø?Nh·,®Ð3zÙa«3¤qdÁúLìB¹.¡úUåno°x\"ØÞ×ìÝÃÂwoáb/0»`Ð]ææÇåÁpÊ\$¥(¨ñÝÔEçZÞU\$ÆðÞrå(íðöd\rS¦P,)Èº'à]õ ¨«â¤Q­n\\!çoúÝ`ÛETnÚÁýa×ÃJ>h·vw6uèd>ß8¹IüºqDJ\rl®%ÈwÐ¯bÌ]b(/äÈ,r¢\n4`e^yÐ;oôB¬\$)*øT-¾\"-ºFÞÂª0Ì­¶\" ðCÅ¢,ö¤ã JE¬ræò\r­x ôÇ ê)&\\F-ø<CÞ¥Í´gÔÌµÜC|c&\"IÆ¢äFòþ¨úf¬Ï\n%;	-ªâ¦ZD¶;NððâhênêÌL.Ü¦¸kÐÇÐÌìî}\rn´ðHæÞMéo²¾Mà¾ædmÇPûPõ0ùF}Ðª9MÎwªXÝFVYÅI-Ì',²,bÊ6â\"n\"K±&±*\$0æãÞk\"X+dQ.\"w¯\0È´BKV<éö(£VY±\\ÓC\rTÄL´3MØhÜÅ¥á-ÙÅ_°þ%VÙ­ÏÙq§±fþñ£\nþHZæÍHl£QÐ¶ûQÈ1½Ñµ&tÂqUQÓføL¤ÕÃÅ(wãòcÃ´+¦ï\0äZ¢òDPßO°ý;pµoµ!qé pª^ø!%A±ÌZKxA0\rÉ\"ä>+\nq¾26\"Ò\"y¥&:bM3ßò7¤XG\0Ä×âP@d|=Â8ß&ò¨Ð2.àÉ'²fçm'\0ßÄ(ðW(®&n¨à²3êU+Q±\"Qµ+%R*£{+àC´?1Òãä\$LÒýpH>òÞâP³#Ñ\règ	+ïì«/j«¼R\r%¬VÎÚ\$Rî´Ä®¢X%h8\\/&ñ#¡2RPs+b8Å à!D4[-[EæÀ¹EÂçNSØyBNëñN×\rðû@`pòðhÂ7©\$C¨\"gè\0z`ØeÀ4¥Ì³Ï`À@¨ÀZV ¢jæñt lï6«ÔRkØÌ.¢³fíP×<#Ï<s<ý£6ÍBL²:ª#ÇDó`X#¦úcªÝ;!³~ã%ècö¢\"¼\\º'(@äX}Ô}²»ãG\$Ë®ãâX#Ø³úe¡©¦¥,ÔÑ.%¦â¶e~sÝãd¿sTÿ\r(esBa´YDËu*ïµG\rê\$K¸npÇðHgæE«t<¾T4xæì²¸À	©>PÇëá>eÐ:v´³\rP<ü`è^ÅÆ¾_ Rùö£ÂGéLãÔz¢P\"ùÃ´`tåLnzC^ÊHp°T^ÊeDÉ³A4Eú\rê.í­OÖÊFyØç}-lD-	# ";
            break;
        case 'uk':$e = "%ÌÂ) h-ZÆù ¶h.ÚÊ h-Ú¬m ½h £ÑÄ& h¡#Ëº.(.<»h£#ñvÒÐ_´Ps94R\\ÊøÒñ¢h %¨ä²p	Nm¹ ¤ÄcØL¢¡4PÒá\0(`1ÆQ°Üp9(¦«ù;Au\r¨Äè*u`ÑCÓâ°dö-|E¬©X~\n\$g#)æe¬ëÉxôZ9 G\"HûES°ÎÑXÄj8±ÀRáÙ9ÚÖ½|_b#rkü:-HB!PÅ£RÐÜD¤¨iÍyA	Çx]5Òà¤KOcJ×vf[5{¸±ÙfØt¤k Òâ,TIjh´0'\rz~²8È°²\$\ry¢ê*©.ç#Î4nÀ¡NÛÆ4Ãþ¥Ãª*Ìü0(r}¤48ì£ÙÃ'plA\rDnÄ<©èÃø@¤Èã#)Û¡Fñ^ÕÆ­s§Èã¤ï	X Äó À°ðúì?Vù¿	ú/å¼P°RDå£hÁ\"#O J#Ë\"JB ÓdrDh14ÜÍ©¸áè1>á·HrNhãmÏ,øhh«\\ÊZ8^ít&ü¨ÀP2\r£HÜ2Oî 1E¥ý!j¢JRÜÂõê0ÜRKì¢Ïv°[Ì¬=\0x0C83¡Ð:æáxïqÃ\rYWVpÞ9áxÊ7ã<9÷XÈJÐ}-¶iÁh+%N\næAê|\0ã|÷&¥¹\"Xì­Í2,»Wä\n0<AÆêsg=EÎ|,é5I ÒdÖDãG§¯,¤6¨òø8ºÈEéôIÁ(Ée*©¥i8Ôéú\\5¦°e¢MÚü¯+Þ¶!¥GÈ1!M\$[#ñ¸L{_©Îµ¤|n²0ä>ñç®n4HìLfÓI¹Áy'¢¼û\n²&Pµf±~lÃòÛÑ¡E¸\$F>bQlF®Ék>·47·¢h3&â9µ¹t´Ül¨~ÿ'Néú,'nkì¿0rDïsÝ;¹&zûOC´ûÌ(ÛÁYfÒ)\"g <H4Âõ0),¼IQ'3/fÄ¾ØÊJz³ÞK÷|Øá¢B¼ÞQBI³}çÀ¢?ÃÿÙæN-]\"§gpýûåSKõ\rtßSGGªÐÝ ó°­±sæ\0#²PCÉm00¨,4168E×d¨%^Cµu¢	>Àþ`#*{,%T@øCåh0x´×ÓàA(ä´¤FMÛaÄº(5öBÃtL8´	(+if¾eT0(TÃCüp0É¿&I_SH5ñ2:ãBW:mAIaà±·%C~0íh\nÒOûMýèÉ,8°L	3°Ûùm	86@äãÁÁ%DHªøÚÔ¡¤z®ÜHhæåê²#«)Wi	ø±GÐ&0\\F\$Ü}t²á7³Ô¦ÁÂG\$)f5Í­À%©YxÛZÀöau-1ÏÌÉoòfÌñ¡4TlÓjgÈMsù6[ôÛ#%e¥Íòk8SÐ.éâÙ/Ý¾7ZbN÷£<HtÊSSÒLé¡2(ÉjÎ~¼'ç6|Ý ¯òpQ{Qò0ARù)Æt¬Çh£ãêÎ	jpDhxq_+É-GØ¢G%V«UxrZ1J9¨|¨ÂÒZX2­´·òà\\KsUÒº×jïá7àÂMm_ìÉ!²hCÔgMuÙæ=óÖk[ñ6©Í7NF,4M5Ã©ºT#Ú>§G?^/HåÔ¢i+²EfÇÔhH¡i\"³=i­U®¶VÚÝ[ëquÊ¹êê]¹xPðxs®+Á|Åé*vôûF{C*Iø´45£Qk4 %9Kèf¯Í,OD.½ªkAU£H	l¡5z`\n\nlÂZWÛØê©v¨î·	gfñª°¦5îìóº/ÌÚ=vÕÍY'am,PÔ&lÐ£¿EJñÊLöåÇô^Ha6h¨ÞÓ -ÄÞ@ ²rAZ7P`*©N(b:8ATÛ'©OÉá>­Zù8ÈÄ¬)~ÏéØ»hÑLh¥\rÉ´pfâ5ãX/±	Ø)\n[zØ¬?ráG<ò×ÈVg£Õv'Éý¨\0ï¥HÄØËsI\nV×èÞ2ÂRÈR¼ ¹í,{w6QÄØ@,÷QX#4úmMM¡#Ê,UeüñHÅÖç­äÇ,¯Ñ|Ë©BcÚ-rtÕ7Ð[³¢²Þ6a^ò.­ùV[H·²J!\nÐÆÙð>jsS×ER¯ÐJîZí%ì4dï\"g#ywÉâ{-}LBà \n<)GSßÜöfÛijMü®ÈY{ûg·UÿÃlàYÄy²«u)HS &§9ß²RMQrÂ^hsV}û5ìÒ8dËR6Ô]¸÷©CêY*®Y¡;z-àôSü#hf`lß®TvS)à+ÃÁóbç\\LHL!ÝhvQ cDÃÁmblhCÚ*Å4ÛÁ¶Òuö #]ï%Âm*RäxNúÔ¶ »ü.ñX±ÈêáÛ(ÏXæjëY'Cnq3<>-Üe2Jiyç9½q2Ë¥yHÀÜ=°ï½8#¤Rß2¼îâSðN3)wþH \rI½ÞÃâ)X ¢èÐpFµ9lîo¦¥×4þ±®4 ê9¬Ì_q&-ç©a´¥7Gò~ï½ÚT=y7ÂFÂ{¦B:â)¦²Kkz/VCÞKmTbÎÞJ	Ü\rìËÑ®|	hs-æ<æ3`Ê¸ Ð\ràè%ç`üf¹#f#D®Ãífl@¦s¢)©/\0,2Bä3 ÐV\0Òæ\0Ì@Þ\r¢\"È'ÎBÁÇï\$\\Þp'¨ìGåçÔk¢Í Å/°¨O(H`yî¬2®tÊ&Z1. DÂ0)<è|HL°á-è/(\$fÄrr0ÈFS¨03\0Ý å`Êi`Ä&F¤Å¬¾Ö°?\0plòlâbÀÔÆÞñG¦o#°ìH?L;©\$U@¨\n`u-ÈÉ,|VÌ(ÅtÈöÈÌLÊzK\$pqhëf âÚÆ<gÆFª=ß¢¯áe1h¿&¾kÇªÅtv°Ò!­W-æÌê	N/Ê¢9/wjA	 ÀñÄg¯³ÑíùjÆh¿e+ÂîAÇ\$ñ¨|ØÒ\$ÉÄ1Dñµ GªA¢\\j\$ÜÑß#åi¤¡R9nk \$ qç\$r!²LjÒP|Ò)\"òW²ZÀ2^ÍS#¨wo	1\"2æP6E«ÜäîxD	ê\$rÀçæob!F\$T£P\rå¾(¤\\*\"d@X\n\\júdì8*QÐ¤~G^ò¥âÍøØÚ\"Pæ:ú`ÇK#Áè\0¨>;Nùd~!1°M¥0¦»O{\$³hFsH²A3¤Ú§2äb,ÿòaV*¦s ÌÎ³Hé\0h3èÊx³]\0P|@ÓdxÏúù/ÖMNò¾ãsO\0h(m¤jFöQ~@ÏbÜp°®Bõ\"¯0\$L+C.¸ð±èùc0ºÒg³8/8e#SÈ*óo³	bw\n­i:g¬÷\"y6Ù3FÂ\"}ÓäóeT\rÎ]p\r`Þ\rê\r Ü ±ÒxrÎ0ÀA\0Ü¸e øk,ø³S4,3[Ce\$ù§êt>Ç®¤?ãfw©ý oiCnóíÓzPF+döTi6N)ó4°pÊ\"jM\$¨3rTç	ÌúcÜ*:K£\$æ}1kÝ5©ÉI-ITßf3*Ê@Í\$WGJreJ¦~Gó3&·I'It¾tL£LñÏ#¯¬ÌT}Kk?IÊê²I\rM/ì¤1R¦B¾S%PpÑP¥6ëõP' 2PÓRpØÊu.<|o5dò+×-Ó¦)nú)'¢Sí\nBùFdö¨ QÂtHé <Na5Rño2³*ÑdS?Ã°Ö\$NöBÉ¨6»ñ0ñxnËÿ/WX-\"n©R2SÕ\r¥½.Ä®zðze<}°¤KÑJ%H®üCc©#QRó1K5_fn'µüf#PVË¥JõEGuGñß_nyÍ&Ýô¯öHV,aA_Óð7c¥QT4nTs^e<U·/²Ùbæ\\tcjðPT²a0ÀPb\"!¥[¬pr¸cÆ5\0H§ÒGE;Xô `µ44Éè;j4ÌÁÿ_õc ö¶²Ôÿa¶QQöTe&y,eÖÒæª1O¤dv_b2Ãm§+cÖîõk6ÄdG/SøÇ¼ñh¨Cqz¿ðÞEðâ)j5tEQï¡qÐôM51dN§q§Õqíèï`ýu5n6\$h;r÷Gjy5WT´3d5H³Wb*VdìOHWIeqÂ£o±KU×n ÉfÔ'=gÊØlWXõWjlw±U64Ûs7¦zçl·utB¼¤6`0bApÊ)3ÎØ³4Ì¶VT¹5VïF­noª1×3.Ü²#\0®ÕÁN4»GìØüDNqåVÂ¼ÒkfºHÊv2945vóá}(÷øÄÓÁï¼+³kÕ3Orf&xwiÇ¾±8{XiONµt*ÀØpØ6hòaN3LJK=ÖÏËª:µbNHR§,uµ,ã)X³ë	Ë®\n ¨ÀZ6 ±!uT4ZËæ<ï®eN÷´Ù(ïUí¬ÑxË(£Ù/ØÙFÊN~ßhGv5ð}nmHD@%Bg4Æ0ÒæJ#å±'Z¬ªÕ­ÂÓa3p49òG.öî·~ÓßÚÞBvM\"WÐ7Ã¼ÈøèSnÏ¸ /Sp{Uü80ò÷_Yc7LoM{y]µdàÇÌ·ÓoD!E\$í`×\\2¹Ã8¨76Dìhl(f0Ï)è¨>sLyÆÎNQ`b|r!PcùKÖ@SDRé-97abßî#G wzà5	ø{îßñ\$dÂÜ0¬IífWDfqoî(#ÇHïYEh'f'\"mføQÐ_[ç#,Öÿ\$¼Ó°ÿ°0ôOÖ¼ÏEn\$Ð@¢Nµlw9ÃÀr)G'A§#©¹MUbJHµ#Áh1lçVo/jö4=QnVìWExð¬ú\\";
            break;
        case 'uz':$e = "%ÌÂ(a<\rÆäêk6LB¼Nl6Lp(a5Í1`äu<Ì'Aèi6Ì&á%4MFØ`æBÁá\"ÉØÔu2Kc'8è0cA¨Øn8Áç!\"n:faÐêr ¢IÌÐo7XÍ&ã9¤ô 5çHþÙq9L'3(}AÃañp-rµLfqÜ°J«ÖlXã*M«FÐ\n%mRûp(£+7éNYÑ>|B:\rYô.3ºã\r­ë4«¢AÔãÎÎsÑÒãÂuzúah@tÑi8[êéÚõ-:KíZÞ×ºa¼O7;¬|kÕulÌÚ7*è'ìÒÖ´®+ÉÓËÐÂ£h@<6`Ò5´²Fì£íÁjê¾°®A\0Ü½-bÂ9\rì²?\n?ã£81#Óµµ/[N\"\réÒ<¯ËZ àÝ_¹*\$&6B£|m¢ÌvH±ó|8¢Ãb\"1®xìÍJèÊ¸4\0xø4c(ÌCCÒ8aÐ^óè\\IQ\\ØáxÊ7òpæ9íÈIð|¼ã`8ÊQ¤¬ã|Ær¤j´·Ï\0Üì\n¨ôAïº,9%OÛ.Þ!Hb,=&CÕµzçVÖÁ=q×mh²à!(Ø2´8éãxÐ¯Ø¶^ªLp<M³,ÛH*k0ÜU#Ø=Z@P;zðIÏÙS+µÐ@8ÈhÍÐÔí2ã¤2u#;ò0µÃJ7IÈÚ·JÜêPkZ53T\"ÏÇc@Ê\rã²<¦S\r.·rItL#M0ÝÒJ9ä[Jö\n\"b(:)8Î­czäÖ¥NÛ[PÊ²»gGOµa×R3ø<YsaW6«³°c~;Yj±¬Gh&Ñ­j^éW¡a£(È³àíB\\£Z]\r\"¸7£iUÄ\$£cg©%]Vyê¼ßíuà!U3°MÃ\r\$¥¤Cµö<ðÓNSÏ;[TöÃJäåÍôæ7ï.aOö½¾Ç.ëðÃvÎÒpË§XEõû;~.è¤ëd7ª7>ó48ÙèøéÝBKjË`Öüq\0P2¯IrÕC`éu\nk³y\rÅtäÖRÎàJ)ÁJXCf&Ëñ°×üYÕùn-)µoÕøHÑ3 YäÂ¸| Á{ &¸ÌÔ:ÌxRàÙ)0yB	;«çµB Î¨ 2­\räO¡v#©!@8YQ]& ×¥ ¹Æ&t4/äÚÎaNiÕ;§öCºI%ÍA(E¢!p%a¤¸)\0|K_j°··ØÈI^`\"Ät@¢äm)tðêLÚm&¼ÿ4Û	]>Mß°äÉ,P²Ér4G\0U<\$EøiÅ9¤ÑÒPæPÑ¾	Æ5§`èÒ|OÊ:%*PÁà:(rETsÌ¯%ÀÞ[ÉtxgP2É¾Ó\ncL¥4\"t¥¹2&T òáS\$PÕ!r`¦UVFäÂI(cá¤M?ú-B0Nòfá1ÓÈÊò:Ô92[¤ÏéÒD§ZgÎWÏ @@P>/J°u~òAA>'029øZF)(Á× ÝÞMríY.L[á'%\$¬\"`FgB+Ä´ºpFTÓV¢çä\"hl9sÐÆ¢¶ÉmFä#'=%Ádá´üê¤µV!) SIL¾ë\"²5íbz	&MQåôB3}7Ë!pÁÐà@_Ñ#ðqÛYz)(×r¹hô9c}&m\r±JW²ÒmÁ¢µCDI±¾:AÂÔBØJåP	áL*PªZem H¼ûÍÅ¹8bÎwÌ7`ÒÜìMó4Ö¨d±¢ä\0¦ÑI©7zÁ*R£c\"iq!Ýã\r\r\$uA×¢]ät.fÓ/ÂðWÒâ/!ì8I	\\#ápP'a \$,5Bv\\kã¢¤XsY¯ïÅÆAYê7ÛFt°I|Nsß>õ+Ä\\¹Râ@Æ´'¡¢&x ÓªuæFÊ±@Z²Yk\$qÕn®ÇÉµ2],ÂÜ/C¢`Y»3æ;ãÙ­3téYT`	ÃËì-h®\"È¸YYî«HÓÌ¤ô¦Ñ èRîÝ÷ÂeÿP¹ärt>K!ãÁsIÅ ÚB\ráT2¿<±S4ádÅDu}\0(935{åqm\")H¯5>]!yéZöTjÝ°fF¼\$À6VÜ~Þ\$*­¶V¼ËÃr8eáÄLB×1¡-Ø9!¢X>F4áSoäÜ²º·á!ßÇ\rµgöZ8gD+*pl÷ÆúÚÕ:j­ òBHäó£Ù»+AÉf\\À¸4]Då¹pUdB T8Ö2ÜÓ¾5bdLÓ´ÿ:vU{-9o±ã¥¾ÊrßÄ9-âÒÍÞè.ªAbj87fFLÃ§WÓð?9°\0§ÔáV}`u§« çO6Ë°în¹:íôÚ´NvÍÒ6¤uo»ùË{LJ¡=·½w~Ëázê¾+¶%¶L9ÿyòæ´×ôÍÉS5¥ß£°ÑXì2¦Kÿ£o	wä5§5¡`Øº©5éh]ÝIî«Õ¼4%¨<3tvC!Ó\nð£°VZÍ\rÄ³&v>öbÎ7úýÿ²³\\óU3ìÕ÷¿b´î«øßÚæùwëþßÉãfzÉIª2À¦_`ò_ÅÄt.@«ÔG`ô'üFr1¥]ÏIê\r¥Å4BÏú-`¢_ªF]Mº¾3nîÏô/ðL\$),-EÜ&,Ê@F\$*\rÂ\nfENÇD\"ÆÐncôÎÎÞKBDýïäËìÆIBÿ¢¾çðjðìð«°¨Jp\nÐ/é	fRf(od¬0þc¯AÖî.¾6/æk¯0Ìo°Ò>N_ï\rÎ îÁPVÞ@ÐÞ ûþ¥ÐÁ©ï°âWnÉ-çmìs÷â>Ý'Â>2Ý48X}Ú ðì¾j°^ÉBZKàÇd\$ÛmÛÅÌ-#.pZvÑVZ\rcÞ9Âã¦À£è °¸mÃf(¢[P&®Ið\$,ÞÍ×&xgÍlÂWÈ\\ÉÐß\"È5­¢ÙãñçñÂ#û°í¦à³qØdoò5§q£áPW¤ñ±BU´Ì\0lQÈÜò EyL²A	\\VO¢jãTÔb@fm|hDðÇj#±°Ðpr<oÒA ð\$pï!QÆaÒ\r¦Í%\0\rÐìF)%Ü`©ÏcO&PÕ'\\±}åÐß¦½\$1à/u2aàrTçòkÒkDáÄ+g\$Ñ+¢Ì»î*±³*òÈIÇÈÖdJÍX\$\r%nT&rç±å\$ådRÿ1	ÊZÈ¾Æù#æ:6ê5 epàåx[î¶!à;â,øñâ2lú3~<25\0Ø.\rRd\"zÝÀ©2óA2\"YOÂûrõe¿5Ï´Ì2½ðC\0æ`Ü²Î8itÅ `Øb*>Éôb\n p^l(4ÊÇiîD1[äìrqS5\rîé;%õ2¸s&|ú­äÂ#\"6Ýò¬4à8é¾\"PFt/ªhcN7_ÒÍ	rC¿>±}D¡¨¹/¤{%zR¥.¹Äj1OD×mz\rÃþ@& :¢È&À\rñFÖ7¸|^äOîÕ®È¢s44F3¤ÍnE\$°,\0æC¦þ-W	t]+ à64ÑÊÌ¦nsÉG£f;ÍÅG#±Kæ\$ÜàÐ\0YPÑ|'b×­¿Jìm)«¦]O:ê%¯5/`@GbGG¤(Æ(4%¦ó A&'ì6ôLàÔðÞq5Lß\nì£ÁPD Ôd`I@±'-«1«ÖèD|k8bÓ?eª";
            break;
        case 'vi':$e = "%ÌÂ(ha­\rÆqÐÐá] á®ÒÓ]¡Îc\rTnAjÓ¢hc,\"	³b5HÅØq 	Nd)	R!/5Â!PÃ¤A&n®&°0cA¨Øn8Á1Ö0±Lâ³thb*L ¢QCH1°Öb	,Q^cMÆ3Âs2ÎNr=v©8]&-.Çcö\rF 1XîE)¶CÒñâ	ÆÜnz4Ý77ÈJqm¬©U`Ô-MÈ@da¬±¦H¾9[×µê\r²ÝH ê!¡Äêy i=¤×Y®d\$ÉIÔäXWÓxmmt¿ÑWjYoqwµóùD¹Ä:<6½¨£à\ncì4¡`P°7e'í@@¸°#hß¢,*ÃXÜ7ê@Ê9Cxä	úNÒA\"l<®1N\nò¥(:¸ÌZ£ @ ¢KÌ!Å\nðÁvã\rAó×!åÑZ8B;ã&rº²ÈØÚ2\"ã8Ò0ÀP2\r°DFßã(ç	òæ:M!#Ç=pBè0Kû;(pÞ:Ð\0à¿c¼L2\0xèÌC@è:tã½d3¤íÄÃ8^2Á}69Ó´ø^'AðÛ00@Í\r´pÒ7Áà^0É°2À\$ã§ï0).â¼:Ft¼6o»¼5£<-uHc@ë,edâÛ¿àP®0Cu£#0<(P9 P48Þ·\r8R]\$éKLJû)4A\" Ê3#¨Ù!ÃØ:°U±A©jêÃ±3h¬kQË©Y\0\roq~Ý4,1¢K(aO3f]èØñKuÙl¢<ã/%h¼²·âNA¯8.;JìÅÙ8	ºËdÌ(¦S5AÌí»3CwzB¼6'ûX¦(Pñ,h41Ä]¨¸)+oÆíÛ\r²Ü7ócG;\\HmËs*nÁvítÎ»7-K.:NÈ(ËF±#w¬ïË2Ýã\"¼ÂÀêÎa©òÐ;÷p!pÙZt\r£¨ç!Tv*ú:·èA8|q'iZº9Â 3\r#?Â2ýóAB\"\r# ×DÀ^?øà×¡áå´ïwè@ð¤.ÄëybìK±	0se¡ \"ã.Qz4aå)¶	ÈÃ\n*]òÞ2Q\rÁ(Xim)FaRH¸t&ä\\ Â³Zu[J,­y¿µPÜ¨H@£F	¬ûÆ!ÊóSLM!ãI\nU2Ä¤ ð´<=N­=¬°äÒ	Í:Ã°ä¨C\r!;ª0@©Xú¨UJ±W+dÕ¢¶À¹\\«µzÎZIÜ³[õ\$!@4lM\0@äü8ÖEøtðÊ1Èí\"ñJ) \$\$.3G2òÁ!5²@¨\"¦J­Vªõb¬Õ¬}DrP9+¥x®àb¼êõb>ÃnË¡\$² 6¿VØD ¹ÆhìH¶ÂÈ\$Vk°ABÏ\$,À¨Wp´\r°Ìàã°m¬0fÓúQÀÞB!ÄÀ¿ê]ì\rÎáANPË¡¾	e«=]Ñ\n'ÄÆÅÐ·HA@\$NÏñîrJè	ÑZì1»\0pA¥À\0ÏBÔÒOoõ>(J(Y=kÆ'¸QÉOdÔ9©µþ\nAY!¸8)4§ðr`AÞRÎ4êÕU£ÑC	ÈhrW&4G\r2Î-!7dbøb±Ey3¡Vaë>HN\\=v.©htc'øÜà@\n#QÈä]4ÄÛZ9æÓ[A;SÍ!2&Æ¨R|Q)Líµ!Y¢¹g¥\$\$0ó\r\$«³à70\"WéÖOI3\"T½.°7\n~ÔÝWgnEÌ½\n<)H bð»s´ÙV¤EYã\$Ä:ÚÃ!!áX·%¶¨Æ7JB±&À`®Ît¤¹ÅÂ\$QH¬I'æFS£Ó¥>?Y2³`]JKi5m%¥hdâ¬\0Â²Ø2ÆìÚw-ÒL½£IO:ÓK(ß\0¤ª\rüKFl¹²ñ½ÍÅgÉpÂr¸R/Ë¦FÐi×Ì§ÄÃÁ¬EåWT,e®¥Êh	\\eÛÀÊpüÂlÏ¡[Î.?¨>ÚáâÁlú¸®\\2[4â4¦1¢)%1;æ?pQO)ô³oV¦±¸dÏd5kR\n	º\0¹väç7P(ò	-z\$`²PJ°xF¢´²±\"!ÐT?aÊÖ¬OF(s/mCæTr#MéD=UC¹»^i'hKÓ¢ªaÒ:Þm¬.ÄM.>k î£!ÀnYôAïÈ£'Õ³Wyø_ç!Å Ï¡:0¢ûºQ\r`*R6|¦[Yÿf¢òYwS`A<}RtC·>wnsf^µMI¥¯¢Q[¬Íç¡0ÏoàÃGË¸ @¨BH©=C[¶Ô\n¬,°¹¸9nIÓoØCíXÄÁ\0\nzç\0á.ëbÅØ±%¸pÞBÕg\$Û=&ÒÄ{ÊDbî/FAfoK¼w©¸	!EÅ¤öÆ×¡ø1Jd!»bâPáéòÝó¶ódê(w ¯8Ç\"_~t·R¸fäJxP(Q/FdûÞg#'ïþ\n20[[Ñ.ÅY(94ßvh;¿ø¬ÎCëÂÇ8Ï°­Û9UtRÐÈîùPPÆ!òqÊÓ÷F»XjN\"]³	³½ýBÞâôsr0lð2£.&khl&sêÄÕP@ÆÇ\"ê=â8?EÈB¤¼À:ÿÊ\\§)Z.D¢6'@~äBà¥ÖAbA)HiÂÜbäj2jW!hIä!û>ùÍ¡t6£Æãà/\0\r.ß`Ð7GÜdàÚÌ½Æòl&#¤â}ËºR©ôaM\nÿÑdþ°G4C&ÏðäÃÍ(Ð°ó\0§%ºÚæ\"îð2ø\n`väìT0üs\".ñCñvî\nOA\rðlâè¾ô¨£ê;¢B ñ(È±;)ð\"yéâ¹,4ZÆl°HI\\Ù®V1ââêk¹åB¥iè§\nu\nnC,&í#7ÑjlfÇ«êJbNËdb¨nÅAv(ì<qIn0ÿO2® rðþfÊÍ1Ë\"Þk,^¢Á#P\rä^í\rÃ51ÏQ×>¶±VÒbzsåãËNoØÇG\\úqÂr.>Òp0û !nú6N\"§ï®Òr\"ÏZA_LµÌ	n~?ÅÎÎÊ@×%&&eBd\\ë £/ äJSí!p\r'w\"n'jµ!§4ôuïN#ÒGA):tÌí°Ð¦0E¢*?c²bNDâþ ¥x7ðT àÈÑ²ÊqöMÈ)L¡îÆãN9,Òã.j@/Îd\$0®¡îBãqÎXiâ/Â&³óý³\nÅ=1îü-Ò0^,å¤*o2î\\è0M¤ÞN%Ó\"28ýqJéí GlÂêsà¤q5®£6' fM_KÌªm)R×lfìØ36:Ç\\;DhÁ¢[9²N(ÄwÓ±Ô\$Ï@[O¢\$'@\$¦äKtùó²îF1\n[\0ÂB	Ä²CGR\\ÆfLnMÐA+Íqâ%1±þø³\"íÂ\0004j:eäH¢\n ¨ÀZòÊVÌ.êIÌ|[±F7J+¦[)¦;§X§È0ÁS÷1t5átOÎ?Q)SI>ã49cÄeKéCèDàLbÜKA ÆÏÊn¦îÌ^=ä:Î\0QéB;82¼ñ%\0Pop°>³¸F3¾ÐLÉ1/­q÷	Ãä& @<£dsLÌl' MêÊÆwcÞj·GQ\\à¥4Ã¤IP2ð6=*IílÚG,Q­9\n¯B«í,ò]ÅÒ¯zM®)&r¸4Ú=\"´+B×ða8F.ü¢jeá^-mJO¸A,ÚÎªâk,pICìÇÇåâe¬èÍã(b\$;«KÞÄFíöe,¾§+FûèH×ï\\MÇÈ\rç«V,+´;OÀê";
            break;
        case 'zh':$e = "%ÌÂ:\$\nr.®ör/d²È»[8Ð S8r©NT*Ð®\\9ÓHH¤Z1!S¹VøJè@%9£QÉl]m	F¹U©*qQ;CÈf4ãÈ)ÎT9w:ÅvåO\"ã¨%CB®r«¤i»½xMÆ3Âs2ÍÎbèìV}¨\n%[L«Ñã`§*9>åSØË%yèPâ£uâYÐ¾HÇQé)\":¥Vdjºæ²dò©ÄK:t¦RdÚÒ(°t/Ó0Vc5_§hIG*å\\­ëµ?M[Ïh9¼¾ÙÍ£ÒÂQp·C«qåH\nt+Õ®B½_âc©ÅS>R\$¡2øêAÎYnà(\$QBr%B¬¬+E¹ÊH¬a^CåµMbÐ]j±2¯¤«sDNrÅ P2\r£HÜ2GIvL\$å\"s|XÅùÒKÍ,rÃpëÑZrÄq!\0ÐºÁèD4 à9Ax^;ÍpÃFÑÀ\\7C8^2Áxà»c¼à2áp>%1ÌA§AN8GI+ã|¤\$jE2VÖ£¤Á|sÓ¦IÄñäI\$¡rãTNSÇ)6_/¥Ê]T´Dø¥1ÊH@PJ2\n.tHáÊDê²¼°2EQEW§AU¤QPrDõGQÔxW)\0^[´ÍJ19{K[¤ÝZHé Nå¡DeÙÌB&és¥r:G4Õ¸Ü¨83àFuqh¾èñXsSTátÜ9lÆZÇ!zN1)PãÄIVïÛ¢&Whqv÷ä¤Ê^>Ç#åÕ55m)ÄÉrsyynXäÝgZè9¹ÄuLA::ÛãaZHåA*h5dÓÆCEãK²¤9tõèÕ¥Q¦J-#I5ðæ\r£¨æ:Äþº¨ä70CÂ<h|¾oÃ|!ãpÌ4üËÆÑËÐA/KãÄ+ò´KBå{Ó·LD')bJµíUÓ¨ñ>så´ØG9XS!%×a³(AtF8:1í%<JÕÅôa{ ä±¬¥4]û	\rEûTMz!btÉXÊµN	R³â[¦öC\$\$r!b½Ú²´ÆDPï]ñ'Ç@°LxüfQ¸r~Â¼[\0 Ç@\$Ô­B1v+ÂZKy0&\$È@wMI²¦ôâÓ¨dr¡:*Añ¬7b°_á:X±%£¤PQÒ'En.qG²\"&ÊâÉù;¨6-à¿o ¤¾PðåqtN	òw\nRØeK©}0¦4ÊÓJk	¹8'\$èÃÀtNÎ'Tüä»%v.Î*:a,ÏÅ\$îÄW1Ì,Àä\"rÁ,R(¿±0¯TV\"¼YH		ñZ)0¬xN%ZQ :ÕðPÄq6.¸%B´ÔEÑÔSkÌ¡NÉ\$£i*ÒÃ@\$\0A4ÝåòTDbq£)\n\$\\<9p¥XâÀs~mÇ8«óQóPDDÅDF¼²öd!E,ÃIq,y ç*ÔPÇìp§¯!¬LO±^/´Hâ¸t!1Br®øÔ@ÂRÀIë3ÈÛ÷\"YYñ\"¤I1¢6ÁÐ-(¿b\$}úN²NJIZÂÂ¸Z©e¶NßºØËTP©@ÓÕ6\\èÍaEÄtM¡ÊqSñ\0I àæ\"\n/Afæ4¯:( ÂTéH´\nE¨)=X¤G!\"Ó<f®9áÒ!Wûe´Ö-yÆEÐ\0(#@ `0Y¨&Ò^tkBZ¨=¤ÆÍB%\0¤Èa@Åx¹A<'\0ª A\náÜPB`E¹mXKÕ1jÐÌâJZì-Ñ*/Ò ¦\\¢&&\\#Äy&qß4 AEÝíwÀ«\nqv{=-nPEµ{DÌ°°ÅH.e)~Ñ+Ehø øñ6ÖZÚÈUL-²¬\"õonÄD\$[Ôç27à¥éóÌq8=bÜßÛ~.±ÉnqB9ÅrÉBÉ¹×xL\ré¬2¬\0à¨gFÁÌ2èdh\ráÐ«;	:gcÕ>W\\B8\r!Ñ»`äÃiV^ÌÍ3eJ.W¨¢Ît]	¬&ÒH ìør¾\"!·bQ<ÒÃ^TeÖÇ£Ã^cWTù!,Û>:t¸ìÒ³^M<Á6.H\"L_OMAKk·Í¡¢.À´úBnöÙ°nPT!\$cþ|E¯Ùâ¿=6¤&UòÀàl	Gv¢8´ÁÍ²¿ÛÂFN!.åS@Ù6ËÌ'37vÔm®áÈræî=û·Þßcjô¦ _ïqjbi\n«dµ W¡ÛB²_Æ,8\"Bôò¡µ¼Ã@Ö!Z½È¸bmNítcr­ïsGJ±3ú§¯ñì=×_ãÀÍé¿éþ+Ôiz®'VÿãÕs²ªëý?¤¶ÑuØê5BC0^	Yb#ê;G«ruÜ^ùëU¢1O,²ÃÞ¤ëk~î÷Cû\n¦Ï9Ñg>ÚWJÿ!°3§Ö3Ê´7e@Ø8t\r1Q` n®BøqeÝ°¿À\"r\$EíØÁpDvowí<K?ÀwðÅÁ~XkMs|ücºVÎß;Ãyßy4U¼ìÿ;ýï;7ÞéÄó>µñÊ\$¦·ÏÍdJF(JøÆÒ_L!0ò1+RÊýMkâýÉH¡áp<¡p?¯z`(Îzÿ/dIH;·-`NýePø#²kï\\ø%U¥^úî¼Ì|ü§ª{°TõÏ`l&Æ#/ûUÂ}i0°tTÐzcæÄ'ÎØ\$\"*ÁvT:Aa^h\\ÜÁc(\0\"éf9!Î­°Èo`ù, k¶û¦¯f\rÍaï\r(­arm%ÖáÐH©L mEØØ'åãá6Ïöïí­ KBîÕl°pÈÍàà/àÑ Åg\n{\$q/PÓnñ.N@Êí*øòiQFÓhéLPÍDÓ°ÊìMB­±döù­8B0ÎñaÄ#\rA1åG~yPo¬Åâ·oØao\rQ#	îÍ ÈjÏkf<6ãA>¿\rÔ8Â\rò:l<åër('d'ÖÁB0eï°ë-Üo¨ÞhÎ¡g´\rº¨g¨åôá 8èy©ø#^äÐþ	x+\0ª\n p;ßÏ¢ÝÀ£ãJ\"!¦æ\\áR>.(y0<®ÁÐ!(fÃ*!^è#²42.,®4\rfÖ©ã^EÆ\rz]cz~c),¯h^â<\$\0#¡,SêÂõÎzª\$ìã2ê²àíhÜÒÑ\\Úñx(=-hLz¨OÑÌÊÿÃ¡ÅæÏ2ÎÞ×ÄD+¾¼,äÏNÛ1ÀKÄiÂHçá¯NJ!há È\nÀÂ`ê ÚB.l¾#¡G+CþïEBÏÌì\$\$3+òÂ>{,Ëí\nåi*ÁÊ¾`cCàÄ8a,>`a³ 1pæ-Qæø1`";
            break;
        case 'zh-tw':$e = "%ÌÂ:\$\ns¡.eUÈ¸E9PK72©(æP¢h)Ê@º:i	Æaè§Je åR)Ü«{º	Nd(ÜvQDCÑ®UjaÊTOABÀPÀb2£a¸àr\nr/Wît¢¡ÐBºT)ç*yX^¨ê%Ó\\r¥ÑÎõâ|I7ÎFS	ÌË99SùTB\$³r­ÖNu²MÐ¢U¹P)Êå&9G'Üª{;ds'.ÌLº9hëo^^+ðieDÁçô:=.R¡FRÈ%F{A¢,\\¨õ{Xs&Öu¥\0r zM6£U¬!TDÇÇE©ëãt×l6N_ÓÔÛ'¡è¸zÎVÊÁ~N¾ÅÁZRZRGATO\$DÐ­«¬8UäùJt|R)Nã|rEYÎYg9jX«átÐ¨dÉPÐL®Ç)^C-eäÞµ!V%Ú>R°epr\$)Ï\"ÈàP2\r£HÜ2GI@H\$EjsiZ\$EQÊJî3wGÚDRJºàÂ\rØÌC@è:tã½2¤­,Ãxä3ã(Üèæ;ÑÃ ^'AñÐT¢\ntÄ[¬Ôex!òt%ÁÌIó2,EyÎRQQ s3]05³¿aX©ERÖ;ðÛËRq6WA¥iLr\$P\0Ä<(P9,XB+\$mp±¬«:D]9Gó1JÖ#o\\Krë³VhåéÊ^õÁ6C¤Áâf³Ùvs}Òs¶¡ÌGQ&¨d1TÉÌ\\w*Z­àP¨2 @t¥¼HS#×º²N#Ä*½ 7î)AÄ~)\"`Al¸¬s\$ÒK,ï£ÎòýJXº\$dl3W_XÉw^nÞïí\rm!n,ë¡A#ÅÑrº°]?ÙlC²ËÄåÒ=µûnÍîq-qXÖd=¶9hê9æÓµ:½¨ä7P0CÂ<ÕukÑað7Ã0Ò3ôÃ/b0¬À0G9L@²¾	ÌDÓ9?ä§AKyDh§1PPÅD#J\n'så+BOÄÉ«hñZK¡åKØç#ÞëWJ<\\ÊÉ*`tFâ6(p~Â¥\$ôãR »CÌ%D¹Î!oXâ`& èm¤ån¸:DFÂ0tq§Å1Â¼E<ô ãNã V	Ò²BSJ©\\9B\$ @ÄÙ\"vDvO	é>'å\0 \"ê!EDE£Ôà0@ÒîØ>Â5_Á|]@èpXKs,Ð.%ß ¦Csî ÄÇ#<FTÐ¸ê@äRzFGhöBÁ×ÎOby©í>§õ Ô*Q1\rF(å ¤5RR\n4)ÇkZ³zâÄJ»÷%É£ W	2ÜÒRL®,KGé+\$ ¿,\"8eN­ÌF³òf!Ê9ÅcÃó*9ÄÛáÀa\$UÐLrÂ±Ê'Å¹ª8&\\·¬öÓÜ¢ÜqÅEÛþL	ZQH\nJ{W²öÞìp'QîHHIÖl|R¸`9ÄL¹9ó¼P@ÒÄEÂDt\nÑp èBZé¦ìþ9ÅJ/ñ:*s½Â`W1:;×MrAC29DÝ%JÒLS\nA@ÞÔ=´FÈÑ2/±!5cK\näB-#äÓwÞ9pµââYÊ'0&B\0^V¦Ú~fà 0(ra9ð8b û£E;DôBQaÉ'×\0H[hæ\"\nE.'¸ÓJ<:(PP	áL*æ)11'¬¬M:XÑ_fÞÈ¼¿zÿ¦¢£±T6 ÔÁP(³bkL	 ôÚÈåFU:Gr`% ¾â4Z1@(Iê	á8P T *ý\0B`EÀKtÒ\$hç\$dxG´ÀÃpgè]3Æ´Mµ6çìþôJÈ(»+â|UPY«xB9QV[%®	@eØÆÎ=¶6ãºv²7£H#ÆôÈô5h4WÀBXÂ³½@\"\r¬.ièahE\n¡^Oº` 8VÃ©LJQ×Þü£ü±ï4A¢¢ç%U:AõÀÞ¢C*Ý°:t¬ÀPs/AUÞ@3fq,`ÅRËË7)¹2îBX\r!ÑÎ`äÃi¿öº×ÅÈç×zô¬_!Ì.Zoi[ëõl¤¾Ètß@õc\rz`!¸Ê_ÃÙ\rh4SKzårKrîte«õrÖzEoÒmWÈ6X!Åãa*Û\"9øV­ÊÅuµ0´iB Aa s!¥?n¿¼^ÒÁ>ðªApð@¶Öìò#ÓÂyq®.xKNy±.9Eè±\"|_+=DQb£Îùâp,¡KL0Dj>Xp!#O;\\¸Tñ;Ñ¹S/pÛåsPL1#Âøý^8Qw7ö}Ñ×óX8}®@úD³B´QåD®UßvC¨|l¶f\\|B\r}2©­t7(*cÀ ZBºBó\"P_IGDéE£8+µî1K<Ozu§dýQ1¸ñ¹<qëÙÍÛã¿÷X÷§z¹á\$5ê³Ëñ.\nZ¿ôZ0\0ý0Áâafú£ëý¿¯>9±{Ý5å°µà¼°7aþ¢ÄY>ÐA\rGk0Ñ®]¾\r¡»La¶v#ÏÂgn\rÀÈÖiÏÄFÇCö|ôLì6 ÃlôMÔÈ°&ö/vl,HK(.äÂFöoÏ:Îfå#óÎpçNy@ÇËÞ¾+æ¯E0fÚé0j\rÙ¢¡\$>\r°p!ÊD´>ãv	Äª¥lýåÚN²õ\rÊ+½	§p±#´ý\rz=\"zäàlP½	Â+(ÚPhÚ«ëPÕÉx:¤Ún9Ì]I%Y¿\rmW¯c®\r\rX<µ-S¦Çpbìp¡mïÌl2b±7oç:1Hpñ\"?	pk6,æÐ¢ìÁ^a)²-Áyb^Ãê(¯È<q/FE(!ýñMæ\nàFÈ\$´%âî\n±ñ¦a¶TÄ¶<´«È\"p¬ûM°­´Oú[Í8u\0ÈÖàà\r\"0QÓ`Ñ ÅçR\$±­®ÜQØsöR@@Óm¾þEÂRÜQH^-Ì­íÑÅ\"Ýø25\"Äe22Ý²@Ý2=\$Ýîð}R#é¼+«#<ád,àÒb+Òp2à/­bófcìÀ9çLbä|¡\rni	\0+|2T²îøD!HV¡F¢¡bê*|!k+û)´õÏ8înjç.vç®2þ¡h\rï.Ãú?n*%ã´_çðP28B-	i(2l\0ª\n p>\${¦®¢JL!bÈ%hSádÕÐ2W^òäBi¾ò#bßAji0ã.Ïô>àh¦­\$,±(V?³xj´4FÐ³1ed¨/lö!Ð¡Å3#§&£1Ò¶ºôSªó§ð(ÌmãVøfn°ø©±ÐÆÁÌb&&#ÏÔùSÒ/üÃ­tý%ðg/þVÊîÁ, îºª\nÉ¨à\nÀÂ`ê Úsz+#<MPN4ú«0Ù-~¡RñqÓ9ì\\£Â>É~ÈtFÅ\$:òºA¡LÁÐc:dD#ÂÁß%a,}°3á";
            break;
    }$ki = [];
    foreach (explode("\n", lzw_decompress($e)) as $X) {
        $ki[] = (strpos($X, "\t") ? explode("\t", $X) : $X);
    }

    return $ki;
}abstract class SqlDb
{
    public static $instance;

    public $extension;

    public $flavor = '';

    public $server_info;

    public $affected_rows = 0;

    public $info = '';

    public $errno = 0;

    public $error = '';

    protected $multi;

    abstract public function attach($P, $V, $H);

    abstract public function quote($zh);

    abstract public function select_db($Db);

    abstract public function query($J, $ri = false);

    public function multi_query($J)
    {
        return $this->multi = $this->query($J);
    }

    public function store_result()
    {
        return $this->multi;
    }

    public function next_result()
    {
        return false;
    }
}if (extension_loaded('pdo')) {
    abstract class PdoDb extends SqlDb
    {
        protected $pdo;

        public function dsn($bc, $V, $H, array $wf = [])
        {
            $wf[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_SILENT;
            $wf[\PDO::ATTR_STATEMENT_CLASS] = ['Adminer\PdoResult'];
            try {
                $this->pdo = new \PDO($bc, $V, $H, $wf);
            } catch (\Exception$wc) {
                return $wc->getMessage();
            }$this->server_info = @$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

            return '';
        }

        public function quote($zh)
        {
            return $this->pdo->quote($zh);
        }

        public function query($J, $ri = false)
        {
            $K = $this->pdo->query($J);
            $this->error = '';
            if (! $K) {
                [, $this->errno, $this->error] = $this->pdo->errorInfo();
                if (! $this->error) {
                    $this->error = lang(23);
                }

                return false;
            }$this->store_result($K);

            return $K;
        }

        public function store_result($K = null)
        {
            if (! $K) {
                $K = $this->multi;
                if (! $K) {
                    return false;
                }
            }if ($K->columnCount()) {
                $K->num_rows = $K->rowCount();

                return $K;
            }$this->affected_rows = $K->rowCount();

            return true;
        }

        public function next_result()
        {
            $K = $this->multi;
            if (! is_object($K)) {
                return false;
            }$K->_offset = 0;

            return @$K->nextRowset();
        }
    }class PdoResult extends \PDOStatement
    {
        public $_offset = 0;

        public $num_rowsvar;

        public function fetch_assoc()
        {
            return $this->fetch_array(\PDO::FETCH_ASSOC);
        }

        public function fetch_row()
        {
            return $this->fetch_array(\PDO::FETCH_NUM);
        }

        private function fetch_array($Te)
        {
            $L = $this->fetch($Te);

            return $L ? array_map([$this, 'unresource'], $L) : $L;
        }

        private function unresource($X)
        {
            return is_resource($X) ? stream_get_contents($X) : $X;
        }

        public function fetch_field()
        {
            $M = (object) $this->getColumnMeta($this->_offset++);
            $U = $M->pdo_type;
            $M->type = ($U == \PDO::PARAM_INT ? 0 : 15);
            $M->charsetnr = ($U == \PDO::PARAM_LOB || (isset($M->flags) && in_array('blob', (array) $M->flags)) ? 63 : 0);

            return $M;
        }

        public function seek($jf)
        {
            for ($t = 0; $t < $jf; $t++) {
                $this->fetch();
            }
        }
    }
}function add_driver($u, $E)
{
    SqlDriver::$drivers[$u] = $E;
}function get_driver($u)
{
    return SqlDriver::$drivers[$u];
}abstract class SqlDriver
{
    public static $instance;

    public static $drivers = [];

    public static $extensions = [];

    public static $jush;

    protected $conn;

    protected $types = [];

    public $insertFunctions = [];

    public $editFunctions = [];

    public $unsigned = [];

    public $operators = [];

    public $functions = [];

    public $grouping = [];

    public $onActions = 'RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT';

    public $partitionBy = [];

    public $inout = 'IN|OUT|INOUT';

    public $enumLength = "'(?:''|[^'\\\\]|\\\\.)*'";

    public $generated = [];

    public static function connect($P, $V, $H)
    {
        $f = new Db;

        return $f->attach($P, $V, $H) ?: $f;
    }

    public function __construct(Db $f)
    {
        $this->conn = $f;
    }

    public function types()
    {
        return call_user_func_array('array_merge', array_values($this->types));
    }

    public function structuredTypes()
    {
        return array_map('array_keys', $this->types);
    }

    public function enumLength(array $m) {}

    public function unconvertFunction(array $m) {}

    public function select($R, array $O, array $Z, array $s, array $yf = [], $_ = 1, $G = 0, $og = false)
    {
        $Xd = (count($s) < count($O));
        $J = adminer()->selectQueryBuild($O, $Z, $s, $yf, $_, $G);
        if (! $J) {
            $J = 'SELECT'.limit(($_GET['page'] != 'last' && $_ && $s && $Xd && JUSH == 'sql' ? 'SQL_CALC_FOUND_ROWS ' : '').implode(', ', $O)."\nFROM ".table($R), ($Z ? "\nWHERE ".implode(' AND ', $Z) : '').($s && $Xd ? "\nGROUP BY ".implode(', ', $s) : '').($yf ? "\nORDER BY ".implode(', ', $yf) : ''), $_, ($G ? $_ * $G : 0), "\n");
        }$vh = microtime(true);
        $L = $this->conn->query($J);
        if ($og) {
            echo adminer()->selectQuery($J, $vh, ! $L);
        }

        return $L;
    }

    public function delete($R, $wg, $_ = 0)
    {
        $J = 'FROM '.table($R);

        return queries('DELETE'.($_ ? limit1($R, $J, $wg) : " $J$wg"));
    }

    public function update($R, array $Q, $wg, $_ = 0, $dh = "\n")
    {
        $Ii = [];
        foreach ($Q as $z => $X) {
            $Ii[] = "$z = $X";
        }$J = table($R)." SET$dh".implode(",$dh", $Ii);

        return queries('UPDATE'.($_ ? limit1($R, $J, $wg, $dh) : " $J$wg"));
    }

    public function insert($R, array $Q)
    {
        return queries('INSERT INTO '.table($R).($Q ? ' ('.implode(', ', array_keys($Q)).")\nVALUES (".implode(', ', $Q).')' : ' DEFAULT VALUES').$this->insertReturning($R));
    }

    public function insertReturning($R)
    {
        return '';
    }

    public function insertUpdate($R, array $N, array $ng)
    {
        return false;
    }

    public function begin()
    {
        return queries('BEGIN');
    }

    public function commit()
    {
        return queries('COMMIT');
    }

    public function rollback()
    {
        return queries('ROLLBACK');
    }

    public function slowQuery($J, $Wh) {}

    public function convertSearch($v, array $X, array $m)
    {
        return $v;
    }

    public function value($X, array $m)
    {
        return method_exists($this->conn, 'value') ? $this->conn->value($X, $m) : $X;
    }

    public function quoteBinary($Rg)
    {
        return q($Rg);
    }

    public function warnings() {}

    public function tableHelp($E, $be = false) {}

    public function inheritsFrom($R)
    {
        return [];
    }

    public function inheritedTables($R)
    {
        return [];
    }

    public function partitionsInfo($R)
    {
        return [];
    }

    public function hasCStyleEscapes()
    {
        return false;
    }

    public function engines()
    {
        return [];
    }

    public function supportsIndex(array $S)
    {
        return ! is_view($S);
    }

    public function indexAlgorithms(array $Gh)
    {
        return [];
    }

    public function checkConstraints($R)
    {
        return
            get_key_vals('SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME
WHERE c.CONSTRAINT_SCHEMA = '.q($_GET['ns'] != '' ? $_GET['ns'] : DB).'
AND t.TABLE_NAME = '.q($R)."
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'", $this->conn);
    }

    public function allFields()
    {
        $L = [];
        if (DB != '') {
            foreach (get_rows('SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length'.(JUSH == 'sql' ? ", COLUMN_KEY = 'PRI' AS `primary`" : '').'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = '.q($_GET['ns'] != '' ? $_GET['ns'] : DB).'
ORDER BY TABLE_NAME, ORDINAL_POSITION', $this->conn) as $M) {
                $M['null'] = ($M['nullable'] == 'YES');
                $L[$M['tab']][] = $M;
            }
        }

        return $L;
    }
}class Adminer
{
    public static $instance;

    public $error = '';

    public function name()
    {
        return "<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace('~\\?.*~', '', ME).'?file=logo.png&version=5.4.1')."' width='24' height='24' alt='' id='logo'>Adminer</a>";
    }

    public function credentials()
    {
        return [SERVER, $_GET['username'], get_password()];
    }

    public function connectSsl() {}

    public function permanentLogin($h = false)
    {
        return password_file($h);
    }

    public function bruteForceKey()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function serverName($P)
    {
        return h($P);
    }

    public function database()
    {
        return DB;
    }

    public function databases($Rc = true)
    {
        return get_databases($Rc);
    }

    public function pluginsLinks() {}

    public function operators()
    {
        return driver()->operators;
    }

    public function schemas()
    {
        return schemas();
    }

    public function queryTimeout()
    {
        return 2;
    }

    public function afterConnect() {}

    public function headers() {}

    public function csp(array $xb)
    {
        return $xb;
    }

    public function head($Ab = null)
    {
        return true;
    }

    public function bodyClass()
    {
        echo ' adminer';
    }

    public function css()
    {
        $L = [];
        foreach (['', '-dark'] as $Te) {
            $o = "adminer$Te.css";
            if (file_exists($o)) {
                $Mc = file_get_contents($o);
                $L["$o?v=".crc32($Mc)] = ($Te ? 'dark' : (preg_match('~prefers-color-scheme:\s*dark~', $Mc) ? '' : 'light'));
            }
        }

        return $L;
    }

    public function loginForm()
    {
        echo "<table class='layout'>\n",adminer()->loginFormField('driver', '<tr><th>'.lang(24).'<td>', input_hidden('auth[driver]', 'server').'MySQL / MariaDB'),adminer()->loginFormField('server', '<tr><th>'.lang(25).'<td>', '<input name="auth[server]" value="'.h(SERVER).'" title="hostname[:port]" placeholder="localhost" autocapitalize="off">'),adminer()->loginFormField('username', '<tr><th>'.lang(26).'<td>', '<input name="auth[username]" id="username" autofocus value="'.h($_GET['username']).'" autocomplete="username" autocapitalize="off">'),adminer()->loginFormField('password', '<tr><th>'.lang(27).'<td>', '<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db', '<tr><th>'.lang(28).'<td>', '<input name="auth[db]" value="'.h($_GET['db']).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".lang(29)."'>\n",checkbox('auth[permanent]', 1, $_COOKIE['adminer_permanent'], lang(30))."\n";
    }

    public function loginFormField($E, $qd, $Y)
    {
        return $qd.$Y."\n";
    }

    public function login($we, $H)
    {
        if ($H == '') {
            return lang(31, target_blank());
        }

        return true;
    }

    public function tableName(array $Gh)
    {
        return h($Gh['Name']);
    }

    public function fieldName(array $m, $yf = 0)
    {
        $U = $m['full_type'];
        $hb = $m['comment'];

        return '<span title="'.h($U.($hb != '' ? ($U ? ': ' : '').$hb : '')).'">'.h($m['field']).'</span>';
    }

    public function selectLinks(array $Gh, $Q = '')
    {
        $E = $Gh['Name'];
        echo '<p class="links">';
        $ve = ['select' => lang(32)];
        if (support('table') || support('indexes')) {
            $ve['table'] = lang(33);
        }$be = false;
        if (support('table')) {
            $be = is_view($Gh);
            if (! $be) {
                $ve['create'] = lang(34);
            } elseif (support('view')) {
                $ve['view'] = lang(35);
            }
        }if ($Q !== null) {
            $ve['edit'] = lang(36);
        }foreach ($ve as $z => $X) {
            echo " <a href='".h(ME)."$z=".urlencode($E).($z == 'edit' ? $Q : '')."'".bold(isset($_GET[$z])).">$X</a>";
        }echo doc_link([JUSH => driver()->tableHelp($E, $be)], '?'),"\n";
    }

    public function foreignKeys($R)
    {
        return foreign_keys($R);
    }

    public function backwardKeys($R, $Fh)
    {
        return [];
    }

    public function backwardKeysPrint(array $Ba, array $M) {}

    public function selectQuery($J, $vh, $Gc = false)
    {
        $L = "</p>\n";
        if (! $Gc && ($Qi = driver()->warnings())) {
            $u = 'warnings';
            $L = ", <a href='#$u'>".lang(37).'</a>'.script("qsl('a').onclick = partial(toggle, '$u');", '')."$L<div id='$u' class='hidden'>\n$Qi</div>\n";
        }

        return "<p><code class='jush-".JUSH."'>".h(str_replace("\n", ' ', $J))."</code> <span class='time'>(".format_time($vh).')</span>'.(support('sql') ? " <a href='".h(ME).'sql='.urlencode($J)."'>".lang(12).'</a>' : '').$L;
    }

    public function sqlCommandQuery($J)
    {
        return shorten_utf8(trim($J), 1000);
    }

    public function sqlPrintAfter() {}

    public function rowDescription($R)
    {
        return '';
    }

    public function rowDescriptions(array $N, array $Uc)
    {
        return $N;
    }

    public function selectLink($X, array $m) {}

    public function selectVal($X, $A, array $m, $Hf)
    {
        $L = ($X === null ? '<i>NULL</i>' : (preg_match('~char|binary|boolean~', $m['type']) && ! preg_match('~var~', $m['type']) ? "<code>$X</code>" : (preg_match('~json~', $m['type']) ? "<code class='jush-js'>$X</code>" : $X)));
        if (is_blob($m) && ! is_utf8($X)) {
            $L = '<i>'.lang(38, strlen($Hf)).'</i>';
        }

        return $A ? "<a href='".h($A)."'".(is_url($A) ? target_blank() : '').">$L</a>" : $L;
    }

    public function editVal($X, array $m)
    {
        return $X;
    }

    public function config()
    {
        return [];
    }

    public function tableStructurePrint(array $n, $Gh = null)
    {
        echo "<div class='scrollable'>\n","<table class='nowrap odds'>\n",'<thead><tr><th>'.lang(39).'<td>'.lang(40).(support('comment') ? '<td>'.lang(41) : '')."</thead>\n";
        $_h = driver()->structuredTypes();
        foreach ($n as $m) {
            echo '<tr><th>'.h($m['field']);
            $U = h($m['full_type']);
            $db = h($m['collation']);
            echo "<td><span title='$db'>".(in_array($U, (array) $_h[lang(6)]) ? "<a href='".h(ME.'type='.urlencode($U))."'>$U</a>" : $U.($db && isset($Gh['Collation']) && $db != $Gh['Collation'] ? " $db" : '')).'</span>',($m['null'] ? ' <i>NULL</i>' : ''),($m['auto_increment'] ? ' <i>'.lang(42).'</i>' : '');
            $k = h($m['default']);
            echo (isset($m['default']) ? " <span title='".lang(43)."'>[<b>".($m['generated'] ? "<code class='jush-".JUSH."'>$k</code>" : $k).'</b>]</span>' : ''),(support('comment') ? '<td>'.h($m['comment']) : ''),"\n";
        }echo "</table>\n","</div>\n";
    }

    public function tableIndexesPrint(array $x, array $Gh)
    {
        $Pf = false;
        foreach ($x as $E => $w) {
            $Pf |= (bool) $w['partial'];
        }echo "<table>\n";
        $Ib = first(driver()->indexAlgorithms($Gh));
        foreach ($x as $E => $w) {
            ksort($w['columns']);
            $og = [];
            foreach ($w['columns'] as $z => $X) {
                $og[] = '<i>'.h($X).'</i>'.($w['lengths'][$z] ? '('.$w['lengths'][$z].')' : '').($w['descs'][$z] ? ' DESC' : '');
            }echo "<tr title='".h($E)."'>","<th>$w[type]".($Ib && $w['algorithm'] != $Ib ? " ($w[algorithm])" : ''),'<td>'.implode(', ', $og);
            if ($Pf) {
                echo '<td>'.($w['partial'] ? "<code class='jush-".JUSH."'>WHERE ".h($w['partial']) : '');
            }echo "\n";
        }echo "</table>\n";
    }

    public function selectColumnsPrint(array $O, array $d)
    {
        print_fieldset('select', lang(44), $O);
        $t = 0;
        $O[''] = [];
        foreach ($O as $z => $X) {
            $X = idx($_GET['columns'], $z, []);
            $c = select_input(" name='columns[$t][col]'", $d, $X['col'], ($z !== '' ? 'selectFieldChange' : 'selectAddRow'));
            echo '<div>'.(driver()->functions || driver()->grouping ? html_select("columns[$t][fun]", [-1 => ''] + array_filter([lang(45) => driver()->functions, lang(46) => driver()->grouping]), $X['fun']).on_help("event.target.value && event.target.value.replace(/ |\$/, '(') + ')'", 1).script("qsl('select').onchange = function () { helpClose();".($z !== '' ? '' : " qsl('select, input', this.parentNode).onchange();").' };', '')."($c)" : $c)."</div>\n";
            $t++;
        }echo "</div></fieldset>\n";
    }

    public function selectSearchPrint(array $Z, array $d, array $x)
    {
        print_fieldset('search', lang(47), $Z);
        foreach ($x as $t => $w) {
            if ($w['type'] == 'FULLTEXT') {
                echo '<div>(<i>'.implode('</i>, <i>', array_map('Adminer\h', $w['columns'])).'</i>) AGAINST'," <input type='search' name='fulltext[$t]' value='".h(idx($_GET['fulltext'], $t))."'>",script("qsl('input').oninput = selectFieldChange;", ''),checkbox("boolean[$t]", 1, isset($_GET['boolean'][$t]), 'BOOL'),"</div>\n";
            }
        }$Pa = 'this.parentNode.firstChild.onchange();';
        foreach (array_merge((array) $_GET['where'], [[]]) as $t => $X) {
            if (! $X || ("$X[col]$X[val]" != '' && in_array($X['op'], adminer()->operators()))) {
                echo '<div>'.select_input(" name='where[$t][col]'", $d, $X['col'], ($X ? 'selectFieldChange' : 'selectAddRow'), '('.lang(48).')'),html_select("where[$t][op]", adminer()->operators(), $X['op'], $Pa),"<input type='search' name='where[$t][val]' value='".h($X['val'])."'>",script("mixin(qsl('input'), {oninput: function () {  $Pa }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});", ''),"</div>\n";
            }
        }echo "</div></fieldset>\n";
    }

    public function selectOrderPrint(array $yf, array $d, array $x)
    {
        print_fieldset('sort', lang(49), $yf);
        $t = 0;
        foreach ((array) $_GET['order'] as $z => $X) {
            if ($X != '') {
                echo '<div>'.select_input(" name='order[$t]'", $d, $X, 'selectFieldChange'),checkbox("desc[$t]", 1, isset($_GET['desc'][$z]), lang(50))."</div>\n";
                $t++;
            }
        }echo '<div>'.select_input(" name='order[$t]'", $d, '', 'selectAddRow'),checkbox("desc[$t]", 1, false, lang(50))."</div>\n","</div></fieldset>\n";
    }

    public function selectLimitPrint($_)
    {
        echo '<fieldset><legend>'.lang(51).'</legend><div>',"<input type='number' name='limit' class='size' value='".intval($_)."'>",script("qsl('input').oninput = selectFieldChange;", ''),"</div></fieldset>\n";
    }

    public function selectLengthPrint($Uh)
    {
        if ($Uh !== null) {
            echo '<fieldset><legend>'.lang(52).'</legend><div>',"<input type='number' name='text_length' class='size' value='".h($Uh)."'>","</div></fieldset>\n";
        }
    }

    public function selectActionPrint(array $x)
    {
        echo '<fieldset><legend>'.lang(53).'</legend><div>',"<input type='submit' value='".lang(44)."'>"," <span id='noindex' title='".lang(54)."'></span>",'<script'.nonce().">\n",'const indexColumns = ';
        $d = [];
        foreach ($x as $w) {
            $_b = reset($w['columns']);
            if ($w['type'] != 'FULLTEXT' && $_b) {
                $d[$_b] = 1;
            }
        }$d[''] = 1;
        foreach ($d as $z => $X) {
            json_row($z);
        }echo ";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";
    }

    public function selectCommandPrint()
    {
        return ! information_schema(DB);
    }

    public function selectImportPrint()
    {
        return ! information_schema(DB);
    }

    public function selectEmailPrint(array $ic, array $d) {}

    public function selectColumnsProcess(array $d, array $x)
    {
        $O = [];
        $s = [];
        foreach ((array) $_GET['columns'] as $z => $X) {
            if ($X['fun'] == 'count' || ($X['col'] != '' && (! $X['fun'] || in_array($X['fun'], driver()->functions) || in_array($X['fun'], driver()->grouping)))) {
                $O[$z] = apply_sql_function($X['fun'], ($X['col'] != '' ? idf_escape($X['col']) : '*'));
                if (! in_array($X['fun'], driver()->grouping)) {
                    $s[] = $O[$z];
                }
            }
        }

        return [$O, $s];
    }

    public function selectSearchProcess(array $n, array $x)
    {
        $L = [];
        foreach ($x as $t => $w) {
            if ($w['type'] == 'FULLTEXT' && idx($_GET['fulltext'], $t) != '') {
                $L[] = 'MATCH ('.implode(', ', array_map('Adminer\idf_escape', $w['columns'])).') AGAINST ('.q($_GET['fulltext'][$t]).(isset($_GET['boolean'][$t]) ? ' IN BOOLEAN MODE' : '').')';
            }
        }foreach ((array) $_GET['where'] as $z => $X) {
            $bb = $X['col'];
            if ("$bb$X[val]" != '' && in_array($X['op'], adminer()->operators())) {
                $lb = [];
                foreach (($bb != '' ? [$bb => $n[$bb]] : $n) as $E => $m) {
                    $lg = '';
                    $kb = " $X[op]";
                    if (preg_match('~IN$~', $X['op'])) {
                        $Dd = process_length($X['val']);
                        $kb
                            .= ' '.($Dd != '' ? $Dd : '(NULL)');
                    } elseif ($X['op'] == 'SQL') {
                        $kb = " $X[val]";
                    } elseif (preg_match('~^(I?LIKE) %%$~', $X['op'], $C)) {
                        $kb = " $C[1] ".adminer()->processInput($m, "%$X[val]%");
                    } elseif ($X['op'] == 'FIND_IN_SET') {
                        $lg = "$X[op](".q($X['val']).', ';
                        $kb = ')';
                    } elseif (! preg_match('~NULL$~', $X['op'])) {
                        $kb
                            .= ' '.adminer()->processInput($m, $X['val']);
                    }if ($bb != '' || (isset($m['privileges']['where']) && (preg_match('~^[-\d.'.(preg_match('~IN$~', $X['op']) ? ',' : '').']+$~', $X['val']) || ! preg_match('~'.number_type().'|bit~', $m['type'])) && (! preg_match("~[\x80-\xFF]~", $X['val']) || preg_match('~char|text|enum|set~', $m['type'])) && (! preg_match('~date|timestamp~', $m['type']) || preg_match('~^\d+-\d+-\d+~', $X['val'])))) {
                        $lb[] = $lg.driver()->convertSearch(idf_escape($E), $X, $m).$kb;
                    }
                }$L[] = (count($lb) == 1 ? $lb[0] : ($lb ? '('.implode(' OR ', $lb).')' : '1 = 0'));
            }
        }

        return $L;
    }

    public function selectOrderProcess(array $n, array $x)
    {
        $L = [];
        foreach ((array) $_GET['order'] as $z => $X) {
            if ($X != '') {
                $L[] = (preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~', $X) ? $X : idf_escape($X)).(isset($_GET['desc'][$z]) ? ' DESC' : '');
            }
        }

        return $L;
    }

    public function selectLimitProcess()
    {
        return isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    }

    public function selectLengthProcess()
    {
        return isset($_GET['text_length']) ? "$_GET[text_length]" : '100';
    }

    public function selectEmailProcess(array $Z, array $Uc)
    {
        return false;
    }

    public function selectQueryBuild(array $O, array $Z, array $s, array $yf, $_, $G)
    {
        return '';
    }

    public function messageQuery($J, $Vh, $Gc = false)
    {
        restart_session();
        $sd = &get_session('queries');
        if (! idx($sd, $_GET['db'])) {
            $sd[$_GET['db']] = [];
        }if (strlen($J) > 1e6) {
            $J = preg_replace('~[\x80-\xFF]+$~', '', substr($J, 0, 1e6))."\nâ¦";
        }$sd[$_GET['db']][] = [$J, time(), $Vh];
        $sh = 'sql-'.count($sd[$_GET['db']]);
        $L = "<a href='#$sh' class='toggle'>".lang(55)."</a> <a href='' class='jsonly copy'>ð</a>\n";
        if (! $Gc && ($Qi = driver()->warnings())) {
            $u = 'warnings-'.count($sd[$_GET['db']]);
            $L = "<a href='#$u' class='toggle'>".lang(37)."</a>, $L<div id='$u' class='hidden'>\n$Qi</div>\n";
        }

        return " <span class='time'>".@date('H:i:s').'</span>'." $L<div id='$sh' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($J, 1000).'</code></pre>'.($Vh ? " <span class='time'>($Vh)</span>" : '').(support('sql') ? '<p><a href="'.h(str_replace('db='.urlencode(DB), 'db='.urlencode($_GET['db']), ME).'sql=&history='.(count($sd[$_GET['db']]) - 1)).'">'.lang(12).'</a>' : '').'</div>';
    }

    public function editRowPrint($R, array $n, $M, $yi) {}

    public function editFunctions(array $m)
    {
        $L = ($m['null'] ? 'NULL/' : '');
        $yi = isset($_GET['select']) || where($_GET);
        foreach ([driver()->insertFunctions, driver()->editFunctions] as $z => $bd) {
            if (! $z || (! isset($_GET['call']) && $yi)) {
                foreach ($bd as $Zf => $X) {
                    if (! $Zf || preg_match("~$Zf~", $m['type'])) {
                        $L
                            .= "/$X";
                    }
                }
            }if ($z && $bd && ! preg_match('~set|bool~', $m['type']) && ! is_blob($m)) {
                $L
                    .= '/SQL';
            }
        }if ($m['auto_increment'] && ! $yi) {
            $L = lang(42);
        }

        return explode('/', $L);
    }

    public function editInput($R, array $m, $wa, $Y)
    {
        if ($m['type'] == 'enum') {
            return (isset($_GET['select']) ? "<label><input type='radio'$wa value='orig' checked><i>".lang(10).'</i></label> ' : '').enum_input('radio', $wa, $m, $Y, 'NULL');
        }

        return '';
    }

    public function editHint($R, array $m, $Y)
    {
        return '';
    }

    public function processInput(array $m, $Y, $r = '')
    {
        if ($r == 'SQL') {
            return $Y;
        }$E = $m['field'];
        $L = q($Y);
        if (preg_match('~^(now|getdate|uuid)$~', $r)) {
            $L = "$r()";
        } elseif (preg_match('~^current_(date|timestamp)$~', $r)) {
            $L = $r;
        } elseif (preg_match('~^([+-]|\|\|)$~', $r)) {
            $L = idf_escape($E)." $r $L";
        } elseif (preg_match('~^[+-] interval$~', $r)) {
            $L = idf_escape($E)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i", $Y) && JUSH != 'pgsql' ? $Y : $L);
        } elseif (preg_match('~^(addtime|subtime|concat)$~', $r)) {
            $L = "$r(".idf_escape($E).", $L)";
        } elseif (preg_match('~^(md5|sha1|password|encrypt)$~', $r)) {
            $L = "$r($L)";
        }

        return unconvert_field($m, $L);
    }

    public function dumpOutput()
    {
        $L = ['text' => lang(56), 'file' => lang(57)];
        if (function_exists('gzencode')) {
            $L['gz'] = 'gzip';
        }

        return $L;
    }

    public function dumpFormat()
    {
        return (support('dump') ? ['sql' => 'SQL'] : []) + ['csv' => 'CSV,', 'csv;' => 'CSV;', 'tsv' => 'TSV'];
    }

    public function dumpDatabase($j) {}

    public function dumpTable($R, $Ah, $be = 0)
    {
        if ($_POST['format'] != 'sql') {
            echo "\xef\xbb\xbf";
            if ($Ah) {
                dump_csv(array_keys(fields($R)));
            }
        } else {
            if ($be == 2) {
                $n = [];
                foreach (fields($R) as $E => $m) {
                    $n[] = idf_escape($E)." $m[full_type]";
                }$h = 'CREATE TABLE '.table($R).' ('.implode(', ', $n).')';
            } else {
                $h = create_sql($R, $_POST['auto_increment'], $Ah);
            }set_utf8mb4($h);
            if ($Ah && $h) {
                if ($Ah == 'DROP+CREATE' || $be == 1) {
                    echo 'DROP '.($be == 2 ? 'VIEW' : 'TABLE').' IF EXISTS '.table($R).";\n";
                }if ($be == 1) {
                    $h = remove_definer($h);
                }echo "$h;\n\n";
            }
        }
    }

    public function dumpData($R, $Ah, $J)
    {
        if ($Ah) {
            $Ee = (JUSH == 'sqlite' ? 0 : 1048576);
            $n = [];
            $_d = false;
            if ($_POST['format'] == 'sql') {
                if ($Ah == 'TRUNCATE+INSERT') {
                    echo truncate_sql($R).";\n";
                }$n = fields($R);
                if (JUSH == 'mssql') {
                    foreach ($n as $m) {
                        if ($m['auto_increment']) {
                            echo 'SET IDENTITY_INSERT '.table($R)." ON;\n";
                            $_d = true;
                            break;
                        }
                    }
                }
            }$K = connection()->query($J, 1);
            if ($K) {
                $Qd = '';
                $La = '';
                $ee = [];
                $cd = [];
                $Ch = '';
                $Jc = ($R != '' ? 'fetch_assoc' : 'fetch_row');
                $tb = 0;
                while ($M = $K->$Jc()) {
                    if (! $ee) {
                        $Ii = [];
                        foreach ($M as $X) {
                            $m = $K->fetch_field();
                            if (idx($n[$m->name], 'generated')) {
                                $cd[$m->name] = true;

                                continue;
                            }$ee[] = $m->name;
                            $z = idf_escape($m->name);
                            $Ii[] = "$z = VALUES($z)";
                        }$Ch = ($Ah == 'INSERT+UPDATE' ? "\nON DUPLICATE KEY UPDATE ".implode(', ', $Ii) : '').";\n";
                    }if ($_POST['format'] != 'sql') {
                        if ($Ah == 'table') {
                            dump_csv($ee);
                            $Ah = 'INSERT';
                        }dump_csv($M);
                    } else {
                        if (! $Qd) {
                            $Qd = 'INSERT INTO '.table($R).' ('.implode(', ', array_map('Adminer\idf_escape', $ee)).') VALUES';
                        }foreach ($M as $z => $X) {
                            if ($cd[$z]) {
                                unset($M[$z]);

                                continue;
                            }$m = $n[$z];
                            $M[$z] = ($X !== null ? unconvert_field($m, preg_match(number_type(), $m['type']) && ! preg_match('~\[~', $m['full_type']) && is_numeric($X) ? $X : q(($X === false ? 0 : $X))) : 'NULL');
                        }$Rg = ($Ee ? "\n" : ' ').'('.implode(",\t", $M).')';
                        if (! $La) {
                            $La = $Qd.$Rg;
                        } elseif (JUSH == 'mssql' ? $tb % 1000 != 0 : strlen($La) + 4 + strlen($Rg) + strlen($Ch) < $Ee) {
                            $La
                                .= ",$Rg";
                        } else {
                            echo $La.$Ch;
                            $La = $Qd.$Rg;
                        }
                    }$tb++;
                }if ($La) {
                    echo $La.$Ch;
                }
            } elseif ($_POST['format'] == 'sql') {
                echo '-- '.str_replace("\n", ' ', connection()->error)."\n";
            }if ($_d) {
                echo 'SET IDENTITY_INSERT '.table($R)." OFF;\n";
            }
        }
    }

    public function dumpFilename($zd)
    {
        return friendly_url($zd != '' ? $zd : (SERVER ?: 'localhost'));
    }

    public function dumpHeaders($zd, $Ve = false)
    {
        $Jf = $_POST['output'];
        $Bc = (preg_match('~sql~', $_POST['format']) ? 'sql' : ($Ve ? 'tar' : 'csv'));
        header('Content-Type: '.($Jf == 'gz' ? 'application/x-gzip' : ($Bc == 'tar' ? 'application/x-tar' : ($Bc == 'sql' || $Jf != 'file' ? 'text/plain' : 'text/csv').'; charset=utf-8')));
        if ($Jf == 'gz') {
            ob_start(function ($zh) {
                return gzencode($zh);
            }, 1e6);
        }

        return $Bc;
    }

    public function dumpFooter()
    {
        if ($_POST['format'] == 'sql') {
            echo '-- '.gmdate('Y-m-d H:i:s e')."\n";
        }
    }

    public function importServerPath()
    {
        return 'adminer.sql';
    }

    public function homepage()
    {
        echo '<p class="links">'.($_GET['ns'] == '' && support('database') ? '<a href="'.h(ME).'database=">'.lang(58)."</a>\n" : ''),(support('scheme') ? "<a href='".h(ME)."scheme='>".($_GET['ns'] != '' ? lang(59) : lang(60))."</a>\n" : ''),($_GET['ns'] !== '' ? '<a href="'.h(ME).'schema=">'.lang(61)."</a>\n" : ''),(support('privileges') ? "<a href='".h(ME)."privileges='>".lang(62)."</a>\n" : '');
        if ($_GET['ns'] !== '') {
            echo (support('routine') ? "<a href='#routines'>".lang(63)."</a>\n" : ''),(support('sequence') ? "<a href='#sequences'>".lang(64)."</a>\n" : ''),(support('type') ? "<a href='#user-types'>".lang(6)."</a>\n" : ''),(support('event') ? "<a href='#events'>".lang(65)."</a>\n" : '');
        }

        return true;
    }

    public function navigation($Se)
    {
        echo '<h1>'.adminer()->name()." <span class='version'>".VERSION;
        $df = $_COOKIE['adminer_version'];
        echo " <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION, $df) < 0 ? h($df) : '').'</a>',"</span></h1>\n";
        switch_lang();
        if ($Se == 'auth') {
            $Jf = '';
            foreach ((array) $_SESSION['pwds'] as $Ki => $fh) {
                foreach ($fh as $P => $Gi) {
                    $E = h(get_setting("vendor-$Ki-$P") ?: get_driver($Ki));
                    foreach ($Gi as $V => $H) {
                        if ($H !== null) {
                            $Gb = $_SESSION['db'][$Ki][$P][$V];
                            foreach (($Gb ? array_keys($Gb) : ['']) as $j) {
                                $Jf
                                    .= "<li><a href='".h(auth_url($Ki, $P, $V, $j))."'>($E) ".h("$V@".($P != '' ? adminer()->serverName($P) : '').($j != '' ? " - $j" : ''))."</a>\n";
                            }
                        }
                    }
                }
            }if ($Jf) {
                echo "<ul id='logins'>\n$Jf</ul>\n".script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");
            }
        } else {
            $T = [];
            if ($_GET['ns'] !== '' && ! $Se && DB != '') {
                connection()->select_db(DB);
                $T = table_status('', true);
            }adminer()->syntaxHighlighting($T);
            adminer()->databasesPrint($Se);
            $ha = [];
            if (DB == '' || ! $Se) {
                if (support('sql')) {
                    $ha[] = "<a href='".h(ME)."sql='".bold(isset($_GET['sql']) && ! isset($_GET['import'])).'>'.lang(55).'</a>';
                    $ha[] = "<a href='".h(ME)."import='".bold(isset($_GET['import'])).'>'.lang(66).'</a>';
                }$ha[] = "<a href='".h(ME).'dump='.urlencode(isset($_GET['table']) ? $_GET['table'] : $_GET['select'])."' id='dump'".bold(isset($_GET['dump'])).'>'.lang(67).'</a>';
            }$Ed = $_GET['ns'] !== '' && ! $Se && DB != '';
            if ($Ed) {
                $ha[] = '<a href="'.h(ME).'create="'.bold($_GET['create'] === '').'>'.lang(68).'</a>';
            }echo $ha ? "<p class='links'>\n".implode("\n", $ha)."\n" : '';
            if ($Ed) {
                if ($T) {
                    adminer()->tablesPrint($T);
                } else {
                    echo "<p class='message'>".lang(11)."</p>\n";
                }
            }
        }
    }

    public function syntaxHighlighting(array $T)
    {
        echo script_src(preg_replace('~\\?.*~', '', ME).'?file=jush.js&version=5.4.1', true);
        if (support('sql')) {
            echo '<script'.nonce().">\n";
            if ($T) {
                $ve = [];
                foreach ($T as $R => $U) {
                    $ve[] = preg_quote($R, '/');
                }echo 'var jushLinks = { '.JUSH.':';
                json_row(js_escape(ME).(support('table') ? 'table' : 'select').'=$&', '/\b('.implode('|', $ve).')\b/g', false);
                if (support('routine')) {
                    foreach (routines() as $M) {
                        json_row(js_escape(ME).'function='.urlencode($M['SPECIFIC_NAME']).'&name=$&', '/\b'.preg_quote($M['ROUTINE_NAME'], '/').'(?=["`]?\()/g', false);
                    }
                }json_row('');
                echo "};\n";
                foreach (['bac', 'bra', 'sqlite_quo', 'mssql_bra'] as $X) {
                    echo "jushLinks.$X = jushLinks.".JUSH.";\n";
                }if (isset($_GET['sql']) || isset($_GET['trigger']) || isset($_GET['check'])) {
                    $Lh = array_fill_keys(array_keys($T), []);
                    foreach (driver()->allFields() as $R => $n) {
                        foreach ($n as $m) {
                            $Lh[$R][] = $m['field'];
                        }
                    }echo "addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('".idf_escape('')."', ".json_encode($Lh)."); });\n";
                }
            }echo "</script>\n";
        }echo script("syntaxHighlighting('".preg_replace('~^(\d\.?\d).*~s', '\1', connection()->server_info)."', '".connection()->flavor."');");
    }

    public function databasesPrint($Se)
    {
        $i = adminer()->databases();
        if (DB && $i && ! in_array(DB, $i)) {
            array_unshift($i, DB);
        }echo "<form action=''>\n<p id='dbs'>\n";
        hidden_fields_get();
        $Eb = script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");
        echo "<label title='".lang(28)."'>".lang(69).': '.($i ? html_select('db', ['' => ''] + $i, DB).$Eb : "<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n").'</label>',"<input type='submit' value='".lang(22)."'".($i ? " class='hidden'" : '').">\n";
        foreach (['import', 'sql', 'schema', 'dump', 'privileges'] as $X) {
            if (isset($_GET[$X])) {
                echo input_hidden($X);
                break;
            }
        }echo "</p></form>\n";
    }

    public function tablesPrint(array $T)
    {
        echo "<ul id='tables'>".script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");
        foreach ($T as $R => $wh) {
            $R = "$R";
            $E = adminer()->tableName($wh);
            if ($E != '' && ! $wh['partition']) {
                echo '<li><a href="'.h(ME).'select='.urlencode($R).'"'.bold($_GET['select'] == $R || $_GET['edit'] == $R, 'select')." title='".lang(32)."'>".lang(70).'</a> ',(support('table') || support('indexes') ? '<a href="'.h(ME).'table='.urlencode($R).'"'.bold(in_array($R, [$_GET['table'], $_GET['create'], $_GET['indexes'], $_GET['foreign'], $_GET['trigger'], $_GET['check'], $_GET['view']]), (is_view($wh) ? 'view' : 'structure'))." title='".lang(33)."'>$E</a>" : "<span>$E</span>")."\n";
            }
        }echo "</ul>\n";
    }

    public function processList()
    {
        return process_list();
    }

    public function killProcess($u)
    {
        return kill_process($u);
    }
}class Plugins
{
    private static $append = ['dumpFormat' => true, 'dumpOutput' => true, 'editRowPrint' => true, 'editFunctions' => true, 'config' => true];

    public $plugins;

    public $error = '';

    private $hooks = [];

    public function __construct($eg)
    {
        if ($eg === null) {
            $eg = [];
            $Fa = 'adminer-plugins';
            if (is_dir($Fa)) {
                foreach (glob("$Fa/*.php") as $o) {
                    $Fd = include_once "./$o";
                }
            }$rd = " href='https://www.adminer.org/plugins/#use'".target_blank();
            if (file_exists("$Fa.php")) {
                $Fd = include_once "./$Fa.php";
                if (is_array($Fd)) {
                    foreach ($Fd as $dg) {
                        $eg[get_class($dg)] = $dg;
                    }
                } else {
                    $this->error
                        .= lang(71, "<b>$Fa.php</b>", $rd).'<br>';
                }
            }foreach (get_declared_classes() as $Ya) {
                if (! $eg[$Ya] && preg_match('~^Adminer\w~i', $Ya)) {
                    $Eg = new \ReflectionClass($Ya);
                    $nb = $Eg->getConstructor();
                    if ($nb && $nb->getNumberOfRequiredParameters()) {
                        $this->error
                            .= lang(72, $rd, "<b>$Ya</b>", "<b>$Fa.php</b>").'<br>';
                    } else {
                        $eg[$Ya] = new $Ya;
                    }
                }
            }
        }$this->plugins = $eg;
        $ia = new Adminer;
        $eg[] = $ia;
        $Eg = new \ReflectionObject($ia);
        foreach ($Eg->getMethods() as $Qe) {
            foreach ($eg as $dg) {
                $E = $Qe->getName();
                if (method_exists($dg, $E)) {
                    $this->hooks[$E][] = $dg;
                }
            }
        }
    }

    public function __call($E, array $Nf)
    {
        $sa = [];
        foreach ($Nf as $z => $X) {
            $sa[] = &$Nf[$z];
        }$L = null;
        foreach ($this->hooks[$E] as $dg) {
            $Y = call_user_func_array([$dg, $E], $sa);
            if ($Y !== null) {
                if (! self::$append[$E]) {
                    return $Y;
                } $L = $Y + (array) $L;
            }
        }

        return $L;
    }
}abstract class Plugin
{
    protected $translations = [];

    public function description()
    {
        return $this->lang('');
    }

    public function screenshot()
    {
        return '';
    }

    protected function lang($v, $F = null)
    {
        $sa = func_get_args();
        $sa[0] = idx($this->translations[LANG], $v) ?: $v;

        return call_user_func_array('Adminer\lang_format', $sa);
    }
}Adminer::$instance = (function_exists('adminer_object') ? adminer_object() : (is_dir('adminer-plugins') || file_exists('adminer-plugins.php') ? new Plugins(null) : new Adminer));
SqlDriver::$drivers = ['server' => 'MySQL / MariaDB'] + SqlDriver::$drivers;
if (! defined('Adminer\DRIVER')) {
    define('Adminer\DRIVER', 'server');
    if (extension_loaded('mysqli') && $_GET['ext'] != 'pdo') {
        class Db extends \MySQLi
        {
            public static $instance;

            public $extension = 'MySQLi';

            public $flavorvar = '';

            public function __construct()
            {
                parent::init();
            }

            public function attach($P, $V, $H)
            {
                mysqli_report(MYSQLI_REPORT_OFF);
                [$vd, $fg] = host_port($P);
                $uh = adminer()->connectSsl();
                if ($uh) {
                    $this->ssl_set($uh['key'], $uh['cert'], $uh['ca'], '', '');
                }$L = @$this->real_connect(($P != '' ? $vd : ini_get('mysqli.default_host')), ($P.$V != '' ? $V : ini_get('mysqli.default_user')), ($P.$V.$H != '' ? $H : ini_get('mysqli.default_pw')), null, (is_numeric($fg) ? intval($fg) : ini_get('mysqli.default_port')), (is_numeric($fg) ? null : $fg), ($uh ? ($uh['verify'] !== false ? 2048 : 64) : 0));
                $this->options(MYSQLI_OPT_LOCAL_INFILE, 0);

                return $L ? '' : $this->error;
            }

            public function set_charset($Ra)
            {
                if (parent::set_charset($Ra)) {
                    return true;
                }parent::set_charset('utf8');

                return $this->query("SET NAMES $Ra");
            }

            public function next_result()
            {
                return self::more_results() && parent::next_result();
            }

            public function quote($zh)
            {
                return "'".$this->escape_string($zh)."'";
            }
        }
    } elseif (extension_loaded('mysql') && ! ((ini_bool('sql.safe_mode') || ini_bool('mysql.allow_local_infile')) && extension_loaded('pdo_mysql'))) {
        class Db extends SqlDb
        {
            private $link;

            public function attach($P, $V, $H)
            {
                if (ini_bool('mysql.allow_local_infile')) {
                    return lang(73, "'mysql.allow_local_infile'", 'MySQLi', 'PDO_MySQL');
                }$this->link = @mysql_connect(($P != '' ? $P : ini_get('mysql.default_host')), ($P.$V != '' ? $V : ini_get('mysql.default_user')), ($P.$V.$H != '' ? $H : ini_get('mysql.default_password')), true, 131072);
                if (! $this->link) {
                    return mysql_error();
                }$this->server_info = mysql_get_server_info($this->link);

                return '';
            }

            public function set_charset($Ra)
            {
                if (function_exists('mysql_set_charset')) {
                    if (mysql_set_charset($Ra, $this->link)) {
                        return true;
                    }mysql_set_charset('utf8', $this->link);
                }

                return $this->query("SET NAMES  $Ra");
            }

            public function quote($zh)
            {
                return "'".mysql_real_escape_string($zh, $this->link)."'";
            }

            public function select_db($Db)
            {
                return mysql_select_db($Db, $this->link);
            }

            public function query($J, $ri = false)
            {
                $K = @($ri ? mysql_unbuffered_query($J, $this->link) : mysql_query($J, $this->link));
                $this->error = '';
                if (! $K) {
                    $this->errno = mysql_errno($this->link);
                    $this->error = mysql_error($this->link);

                    return false;
                }if ($K === true) {
                    $this->affected_rows = mysql_affected_rows($this->link);
                    $this->info = mysql_info($this->link);

                    return true;
                }

                return new Result($K);
            }
        }class Result
        {
            public $num_rows;

            private $result;

            private $offset = 0;

            public function __construct($K)
            {
                $this->result = $K;
                $this->num_rows = mysql_num_rows($K);
            }

            public function fetch_assoc()
            {
                return mysql_fetch_assoc($this->result);
            }

            public function fetch_row()
            {
                return mysql_fetch_row($this->result);
            }

            public function fetch_field()
            {
                $L = mysql_fetch_field($this->result, $this->offset++);
                $L->orgtable = $L->table;
                $L->charsetnr = ($L->blob ? 63 : 0);

                return $L;
            }

            public function __destruct()
            {
                mysql_free_result($this->result);
            }
        }
    } elseif (extension_loaded('pdo_mysql')) {
        class Db extends PdoDb
        {
            public $extension = 'PDO_MySQL';

            public function attach($P, $V, $H)
            {
                $wf = [\PDO::MYSQL_ATTR_LOCAL_INFILE => false];
                $uh = adminer()->connectSsl();
                if ($uh) {
                    if ($uh['key']) {
                        $wf[\PDO::MYSQL_ATTR_SSL_KEY] = $uh['key'];
                    }if ($uh['cert']) {
                        $wf[\PDO::MYSQL_ATTR_SSL_CERT] = $uh['cert'];
                    }if ($uh['ca']) {
                        $wf[\PDO::MYSQL_ATTR_SSL_CA] = $uh['ca'];
                    }if (isset($uh['verify'])) {
                        $wf[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $uh['verify'];
                    }
                }[$vd, $fg] = host_port($P);

                return $this->dsn("mysql:charset=utf8;host=$vd".($fg ? (is_numeric($fg) ? ';port=' : ';unix_socket=').$fg : ''), $V, $H, $wf);
            }

            public function set_charset($Ra)
            {
                return $this->query("SET NAMES $Ra");
            }

            public function select_db($Db)
            {
                return $this->query('USE '.idf_escape($Db));
            }

            public function query($J, $ri = false)
            {
                $this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, ! $ri);

                return parent::query($J, $ri);
            }
        }
    }class Driver extends SqlDriver
    {
        public static $extensions = ['MySQLi', 'MySQL', 'PDO_MySQL'];

        public static $jush = 'sql';

        public $unsigned = ['unsigned', 'zerofill', 'unsigned zerofill'];

        public $operators = ['=', '<', '>', '<=', '>=', '!=', 'LIKE', 'LIKE %%', 'REGEXP', 'IN', 'FIND_IN_SET', 'IS NULL', 'NOT LIKE', 'NOT REGEXP', 'NOT IN', 'IS NOT NULL', 'SQL'];

        public $functions = ['char_length', 'date', 'from_unixtime', 'lower', 'round', 'floor', 'ceil', 'sec_to_time', 'time_to_sec', 'upper'];

        public $grouping = ['avg', 'count', 'count distinct', 'group_concat', 'max', 'min', 'sum'];

        public static function connect($P, $V, $H)
        {
            $f = parent::connect($P, $V, $H);
            if (is_string($f)) {
                if (function_exists('iconv') && ! is_utf8($f) && strlen($Rg = iconv('windows-1250', 'utf-8', $f)) > strlen($f)) {
                    $f = $Rg;
                }

                return $f;
            }$f->set_charset(charset($f));
            $f->query('SET sql_quote_show_create = 1, autocommit = 1');
            $f->flavor = (preg_match('~MariaDB~', $f->server_info) ? 'maria' : 'mysql');
            add_driver(DRIVER, ($f->flavor == 'maria' ? 'MariaDB' : 'MySQL'));

            return $f;
        }

        public function __construct(Db $f)
        {
            parent::__construct($f);
            $this->types = [lang(74) => ['tinyint' => 3, 'smallint' => 5, 'mediumint' => 8, 'int' => 10, 'bigint' => 20, 'decimal' => 66, 'float' => 12, 'double' => 21], lang(75) => ['date' => 10, 'datetime' => 19, 'timestamp' => 19, 'time' => 10, 'year' => 4], lang(76) => ['char' => 255, 'varchar' => 65535, 'tinytext' => 255, 'text' => 65535, 'mediumtext' => 16777215, 'longtext' => 4294967295], lang(77) => ['enum' => 65535, 'set' => 64], lang(78) => ['bit' => 20, 'binary' => 255, 'varbinary' => 65535, 'tinyblob' => 255, 'blob' => 65535, 'mediumblob' => 16777215, 'longblob' => 4294967295], lang(79) => ['geometry' => 0, 'point' => 0, 'linestring' => 0, 'polygon' => 0, 'multipoint' => 0, 'multilinestring' => 0, 'multipolygon' => 0, 'geometrycollection' => 0]];
            $this->insertFunctions = ['char' => 'md5/sha1/password/encrypt/uuid', 'binary' => 'md5/sha1', 'date|time' => 'now'];
            $this->editFunctions = [number_type() => '+/-', 'date' => '+ interval/- interval', 'time' => 'addtime/subtime', 'char|text' => 'concat'];
            if (min_version('5.7.8', 10.2, $f)) {
                $this->types[lang(76)]['json'] = 4294967295;
            }if (min_version('', 10.7, $f)) {
                $this->types[lang(76)]['uuid'] = 128;
                $this->insertFunctions['uuid'] = 'uuid';
            }if (min_version(9, '', $f)) {
                $this->types[lang(74)]['vector'] = 16383;
                $this->insertFunctions['vector'] = 'string_to_vector';
            }if (min_version(5.1, '', $f)) {
                $this->partitionBy = ['HASH', 'LINEAR HASH', 'KEY', 'LINEAR KEY', 'RANGE', 'LIST'];
            }if (min_version(5.7, 10.2, $f)) {
                $this->generated = ['STORED', 'VIRTUAL'];
            }
        }

        public function unconvertFunction(array $m)
        {
            return preg_match('~binary~', $m['type']) ? "<code class='jush-sql'>UNHEX</code>" : ($m['type'] == 'bit' ? doc_link(['sql' => 'bit-value-literals.html'], "<code>b''</code>") : (preg_match('~geometry|point|linestring|polygon~', $m['type']) ? "<code class='jush-sql'>GeomFromText</code>" : ''));
        }

        public function insert($R, array $Q)
        {
            return $Q ? parent::insert($R, $Q) : queries('INSERT INTO '.table($R)." ()\nVALUES ()");
        }

        public function insertUpdate($R, array $N, array $ng)
        {
            $d = array_keys(reset($N));
            $lg = 'INSERT INTO '.table($R).' ('.implode(', ', $d).") VALUES\n";
            $Ii = [];
            foreach ($d as $z) {
                $Ii[$z] = "$z = VALUES($z)";
            }$Ch = "\nON DUPLICATE KEY UPDATE ".implode(', ', $Ii);
            $Ii = [];
            $re = 0;
            foreach ($N as $Q) {
                $Y = '('.implode(', ', $Q).')';
                if ($Ii && (strlen($lg) + $re + strlen($Y) + strlen($Ch) > 1e6)) {
                    if (! queries($lg.implode(",\n", $Ii).$Ch)) {
                        return false;
                    }$Ii = [];
                    $re = 0;
                }$Ii[] = $Y;
                $re += strlen($Y) + 2;
            }

            return queries($lg.implode(",\n", $Ii).$Ch);
        }

        public function slowQuery($J, $Wh)
        {
            if (min_version('5.7.8', '10.1.2')) {
                if ($this->conn->flavor == 'maria') {
                    return "SET STATEMENT max_statement_time=$Wh FOR $J";
                } elseif (preg_match('~^(SELECT\b)(.+)~is', $J, $C)) {
                    return "$C[1] /*+ MAX_EXECUTION_TIME(".($Wh * 1000).") */ $C[2]";
                }
            }
        }

        public function convertSearch($v, array $X, array $m)
        {
            return preg_match('~char|text|enum|set~', $m['type']) && ! preg_match('~^utf8~', $m['collation']) && preg_match('~[\x80-\xFF]~', $X['val']) ? "CONVERT($v USING ".charset($this->conn).')' : $v;
        }

        public function warnings()
        {
            $K = $this->conn->query('SHOW WARNINGS');
            if ($K && $K->num_rows) {
                ob_start();
                print_select_result($K);

                return ob_get_clean();
            }
        }

        public function tableHelp($E, $be = false)
        {
            $ye = ($this->conn->flavor == 'maria');
            if (information_schema(DB)) {
                return strtolower('information-schema-'.($ye ? "$E-table/" : str_replace('_', '-', $E).'-table.html'));
            }if (DB == 'mysql') {
                return $ye ? "mysql$E-table/" : 'system-schema.html';
            }
        }

        public function partitionsInfo($R)
        {
            $Zc = 'FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = '.q(DB).' AND TABLE_NAME = '.q($R);
            $K = $this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $Zc ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");
            $L = [];
            [$L['partition_by'], $L['partition'], $L['partitions']] = $K->fetch_row();
            $Vf = get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Zc AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");
            $L['partition_names'] = array_keys($Vf);
            $L['partition_values'] = array_values($Vf);

            return $L;
        }

        public function hasCStyleEscapes()
        {
            static $Ma;
            if ($Ma === null) {
                $th = get_val("SHOW VARIABLES LIKE 'sql_mode'", 1, $this->conn);
                $Ma = (strpos($th, 'NO_BACKSLASH_ESCAPES') === false);
            }

            return $Ma;
        }

        public function engines()
        {
            $L = [];
            foreach (get_rows('SHOW ENGINES') as $M) {
                if (preg_match('~YES|DEFAULT~', $M['Support'])) {
                    $L[] = $M['Engine'];
                }
            }

            return $L;
        }

        public function indexAlgorithms(array $Gh)
        {
            return preg_match('~^(MEMORY|NDB)$~', $Gh['Engine']) ? ['HASH', 'BTREE'] : [];
        }
    }function idf_escape($v)
    {
        return '`'.str_replace('`', '``', $v).'`';
    }function table($v)
    {
        return idf_escape($v);
    }function get_databases($Rc)
    {
        $L = get_session('dbs');
        if ($L === null) {
            $J = 'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME';
            $L = ($Rc ? slow_query($J) : get_vals($J));
            restart_session();
            set_session('dbs', $L);
            stop_session();
        }

        return $L;
    }function limit($J, $Z, $_, $jf = 0, $dh = ' ')
    {
        return " $J$Z".($_ ? $dh."LIMIT $_".($jf ? " OFFSET $jf" : '') : '');
    }function limit1($R, $J, $Z, $dh = "\n")
    {
        return limit($J, $Z, 1, 0, $dh);
    }function db_collation($j, array $b)
    {
        $L = null;
        $h = get_val('SHOW CREATE DATABASE '.idf_escape($j), 1);
        if (preg_match('~ COLLATE ([^ ]+)~', $h, $C)) {
            $L = $C[1];
        } elseif (preg_match('~ CHARACTER SET ([^ ]+)~', $h, $C)) {
            $L = $b[$C[1]][-1];
        }

        return $L;
    }function logged_user()
    {
        return get_val('SELECT USER()');
    }function tables_list()
    {
        return get_key_vals('SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME');
    }function count_tables(array $i)
    {
        $L = [];
        foreach ($i as $j) {
            $L[$j] = count(get_vals('SHOW TABLES IN '.idf_escape($j)));
        }

        return $L;
    }function table_status($E = '', $Hc = false)
    {
        $L = [];
        foreach (get_rows($Hc ? 'SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() '.($E != '' ? 'AND TABLE_NAME = '.q($E) : 'ORDER BY Name') : 'SHOW TABLE STATUS'.($E != '' ? ' LIKE '.q(addcslashes($E, '%_\\')) : '')) as $M) {
            if ($M['Engine'] == 'InnoDB') {
                $M['Comment'] = preg_replace('~(?:(.+); )?InnoDB free: .*~', '\1', $M['Comment']);
            }if (! isset($M['Engine'])) {
                $M['Comment'] = '';
            }if ($E != '') {
                $M['Name'] = $E;
            }$L[$M['Name']] = $M;
        }

        return $L;
    }function is_view(array $S)
    {
        return $S['Engine'] === null;
    }function fk_support(array $S)
    {
        return preg_match('~InnoDB|IBMDB2I'.(min_version(5.6) ? '|NDB' : '').'~i', $S['Engine']);
    }function fields($R)
    {
        $ye = (connection()->flavor == 'maria');
        $L = [];
        foreach (get_rows('SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '.q($R).' ORDER BY ORDINAL_POSITION') as $M) {
            $m = $M['COLUMN_NAME'];
            $U = $M['COLUMN_TYPE'];
            $dd = $M['GENERATION_EXPRESSION'];
            $Ec = $M['EXTRA'];
            preg_match('~^(VIRTUAL|PERSISTENT|STORED)~', $Ec, $cd);
            preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~', $U, $_e);
            $k = $M['COLUMN_DEFAULT'];
            if ($k != '') {
                $ae = preg_match('~text|json~', $_e[1]);
                if (! $ye && $ae) {
                    $k = preg_replace("~^(_\w+)?('.*')$~", '\2', stripslashes($k));
                }if ($ye || $ae) {
                    $k = ($k == 'NULL' ? null : preg_replace_callback("~^'(.*)'$~", function ($C) {
                        return stripslashes(str_replace("''", "'", $C[1]));
                    }, $k));
                }if (! $ye && preg_match('~binary~', $_e[1]) && preg_match('~^0x(\w*)$~', $k, $C)) {
                    $k = pack('H*', $C[1]);
                }
            }$L[$m] = ['field' => $m, 'full_type' => $U, 'type' => $_e[1], 'length' => $_e[2], 'unsigned' => ltrim($_e[3].$_e[4]), 'default' => ($cd ? ($ye ? $dd : stripslashes($dd)) : $k), 'null' => ($M['IS_NULLABLE'] == 'YES'), 'auto_increment' => ($Ec == 'auto_increment'), 'on_update' => (preg_match('~\bon update (\w+)~i', $Ec, $C) ? $C[1] : ''), 'collation' => $M['COLLATION_NAME'], 'privileges' => array_flip(explode(',', "$M[PRIVILEGES],where,order")), 'comment' => $M['COLUMN_COMMENT'], 'primary' => ($M['COLUMN_KEY'] == 'PRI'), 'generated' => ($cd[1] == 'PERSISTENT' ? 'STORED' : $cd[1])];
        }

        return $L;
    }function indexes($R, $g = null)
    {
        $L = [];
        foreach (get_rows('SHOW INDEX FROM '.table($R), $g) as $M) {
            $E = $M['Key_name'];
            $L[$E]['type'] = ($E == 'PRIMARY' ? 'PRIMARY' : ($M['Index_type'] == 'FULLTEXT' ? 'FULLTEXT' : ($M['Non_unique'] ? ($M['Index_type'] == 'SPATIAL' ? 'SPATIAL' : 'INDEX') : 'UNIQUE')));
            $L[$E]['columns'][] = $M['Column_name'];
            $L[$E]['lengths'][] = ($M['Index_type'] == 'SPATIAL' ? null : $M['Sub_part']);
            $L[$E]['descs'][] = null;
            $L[$E]['algorithm'] = $M['Index_type'];
        }

        return $L;
    }function foreign_keys($R)
    {
        static $Zf = '(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';
        $L = [];
        $ub = get_val('SHOW CREATE TABLE '.table($R), 1);
        if ($ub) {
            preg_match_all("~CONSTRAINT ($Zf) FOREIGN KEY ?\\(((?:$Zf,? ?)+)\\) REFERENCES ($Zf)(?:\\.($Zf))? \\(((?:$Zf,? ?)+)\\)(?: ON DELETE (".driver()->onActions.'))?(?: ON UPDATE ('.driver()->onActions.'))?~', $ub, $Ae, PREG_SET_ORDER);
            foreach ($Ae as $C) {
                preg_match_all("~$Zf~", $C[2], $oh);
                preg_match_all("~$Zf~", $C[5], $Ph);
                $L[idf_unescape($C[1])] = ['db' => idf_unescape($C[4] != '' ? $C[3] : $C[4]), 'table' => idf_unescape($C[4] != '' ? $C[4] : $C[3]), 'source' => array_map('Adminer\idf_unescape', $oh[0]), 'target' => array_map('Adminer\idf_unescape', $Ph[0]), 'on_delete' => ($C[6] ?: 'RESTRICT'), 'on_update' => ($C[7] ?: 'RESTRICT')];
            }
        }

        return $L;
    }function view($E)
    {
        return ['select' => preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU', '', get_val('SHOW CREATE VIEW '.table($E), 1))];
    }function collations()
    {
        $L = [];
        foreach (get_rows('SHOW COLLATION') as $M) {
            if ($M['Default']) {
                $L[$M['Charset']][-1] = $M['Collation'];
            } else {
                $L[$M['Charset']][] = $M['Collation'];
            }
        }ksort($L);
        foreach ($L as $z => $X) {
            sort($L[$z]);
        }

        return $L;
    }function information_schema($j)
    {
        return ($j == 'information_schema') || (min_version(5.5) && $j == 'performance_schema');
    }function error()
    {
        return h(preg_replace('~^You have an error.*syntax to use~U', 'Syntax error', connection()->error));
    }function create_database($j, $db)
    {
        return queries('CREATE DATABASE '.idf_escape($j).($db ? ' COLLATE '.q($db) : ''));
    }function drop_databases(array $i)
    {
        $L = apply_queries('DROP DATABASE', $i, 'Adminer\idf_escape');
        restart_session();
        set_session('dbs', null);

        return $L;
    }function rename_database($E, $db)
    {
        $L = false;
        if (create_database($E, $db)) {
            $T = [];
            $Ni = [];
            foreach (tables_list() as $R => $U) {
                if ($U == 'VIEW') {
                    $Ni[] = $R;
                } else {
                    $T[] = $R;
                }
            }$L = (! $T && ! $Ni) || move_tables($T, $Ni, $E);
            drop_databases($L ? [DB] : []);
        }

        return $L;
    }function auto_increment()
    {
        $za = ' PRIMARY KEY';
        if ($_GET['create'] != '' && $_POST['auto_increment_col']) {
            foreach (indexes($_GET['create']) as $w) {
                if (in_array($_POST['fields'][$_POST['auto_increment_col']]['orig'], $w['columns'], true)) {
                    $za = '';
                    break;
                }if ($w['type'] == 'PRIMARY') {
                    $za = ' UNIQUE';
                }
            }
        }

        return " AUTO_INCREMENT$za";
    }function alter_table($R, $E, array $n, array $Tc, $hb, $lc, $db, $ya, $Uf)
    {
        $qa = [];
        foreach ($n as $m) {
            if ($m[1]) {
                $k = $m[1][3];
                if (preg_match('~ GENERATED~', $k)) {
                    $m[1][3] = (connection()->flavor == 'maria' ? '' : $m[1][2]);
                    $m[1][2] = $k;
                }$qa[] = ($R != '' ? ($m[0] != '' ? 'CHANGE '.idf_escape($m[0]) : 'ADD') : ' ').' '.implode($m[1]).($R != '' ? $m[2] : '');
            } else {
                $qa[] = 'DROP '.idf_escape($m[0]);
            }
        }$qa = array_merge($qa, $Tc);
        $wh = ($hb !== null ? ' COMMENT='.q($hb) : '').($lc ? ' ENGINE='.q($lc) : '').($db ? ' COLLATE '.q($db) : '').($ya != '' ? " AUTO_INCREMENT=$ya" : '');
        if ($Uf) {
            $Vf = [];
            if ($Uf['partition_by'] == 'RANGE' || $Uf['partition_by'] == 'LIST') {
                foreach ($Uf['partition_names'] as $z => $X) {
                    $Y = $Uf['partition_values'][$z];
                    $Vf[] = "\n  PARTITION ".idf_escape($X).' VALUES '.($Uf['partition_by'] == 'RANGE' ? 'LESS THAN' : 'IN').($Y != '' ? " ($Y)" : ' MAXVALUE');
                }
            }$wh
                .= "\nPARTITION BY $Uf[partition_by]($Uf[partition])";
            if ($Vf) {
                $wh
                    .= ' ('.implode(',', $Vf)."\n)";
            } elseif ($Uf['partitions']) {
                $wh
                    .= ' PARTITIONS '.(+$Uf['partitions']);
            }
        } elseif ($Uf === null) {
            $wh
                .= "\nREMOVE PARTITIONING";
        }if ($R == '') {
            return queries('CREATE TABLE '.table($E)." (\n".implode(",\n", $qa)."\n)$wh");
        }if ($R != $E) {
            $qa[] = 'RENAME TO '.table($E);
        }if ($wh) {
            $qa[] = ltrim($wh);
        }

        return $qa ? queries('ALTER TABLE '.table($R)."\n".implode(",\n", $qa)) : true;
    }function alter_indexes($R, $qa)
    {
        $Qa = [];
        foreach ($qa as $X) {
            $Qa[] = ($X[2] == 'DROP' ? "\nDROP INDEX ".idf_escape($X[1]) : "\nADD $X[0] ".($X[0] == 'PRIMARY' ? 'KEY ' : '').($X[1] != '' ? idf_escape($X[1]).' ' : '').'('.implode(', ', $X[2]).')');
        }

        return queries('ALTER TABLE '.table($R).implode(',', $Qa));
    }function truncate_tables(array $T)
    {
        return apply_queries('TRUNCATE TABLE', $T);
    }function drop_views(array $Ni)
    {
        return queries('DROP VIEW '.implode(', ', array_map('Adminer\table', $Ni)));
    }function drop_tables(array $T)
    {
        return queries('DROP TABLE '.implode(', ', array_map('Adminer\table', $T)));
    }function move_tables(array $T, array $Ni, $Ph)
    {
        $Hg = [];
        foreach ($T as $R) {
            $Hg[] = table($R).' TO '.idf_escape($Ph).'.'.table($R);
        }if (! $Hg || queries('RENAME TABLE '.implode(', ', $Hg))) {
            $Mb = [];
            foreach ($Ni as $R) {
                $Mb[table($R)] = view($R);
            }connection()->select_db($Ph);
            $j = idf_escape(DB);
            foreach ($Mb as $E => $Mi) {
                if (! queries("CREATE VIEW $E AS ".str_replace(" $j.", ' ', $Mi['select'])) || ! queries("DROP VIEW $j.$E")) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }function copy_tables(array $T, array $Ni, $Ph)
    {
        queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        foreach ($T as $R) {
            $E = ($Ph == DB ? table("copy_$R") : idf_escape($Ph).'.'.table($R));
            if (($_POST['overwrite'] && ! queries("\nDROP TABLE IF EXISTS $E")) || ! queries("CREATE TABLE $E LIKE ".table($R)) || ! queries("INSERT INTO $E SELECT * FROM ".table($R))) {
                return false;
            }foreach (get_rows('SHOW TRIGGERS LIKE '.q(addcslashes($R, '%_\\'))) as $M) {
                $li = $M['Trigger'];
                if (! queries('CREATE TRIGGER '.($Ph == DB ? idf_escape("copy_$li") : idf_escape($Ph).'.'.idf_escape($li))." $M[Timing] $M[Event] ON $E FOR EACH ROW\n$M[Statement];")) {
                    return false;
                }
            }
        }foreach ($Ni as $R) {
            $E = ($Ph == DB ? table("copy_$R") : idf_escape($Ph).'.'.table($R));
            $Mi = view($R);
            if (($_POST['overwrite'] && ! queries("DROP VIEW IF EXISTS $E")) || ! queries("CREATE VIEW $E AS $Mi[select]")) {
                return false;
            }
        }

        return true;
    }function trigger($E, $R)
    {
        if ($E == '') {
            return [];
        }$N = get_rows('SHOW TRIGGERS WHERE `Trigger` = '.q($E));

        return reset($N);
    }function triggers($R)
    {
        $L = [];
        foreach (get_rows('SHOW TRIGGERS LIKE '.q(addcslashes($R, '%_\\'))) as $M) {
            $L[$M['Trigger']] = [$M['Timing'], $M['Event']];
        }

        return $L;
    }function trigger_options()
    {
        return ['Timing' => ['BEFORE', 'AFTER'], 'Event' => ['INSERT', 'UPDATE', 'DELETE'], 'Type' => ['FOR EACH ROW']];
    }function routine($E, $U)
    {
        $oa = ['bool', 'boolean', 'integer', 'double precision', 'real', 'dec', 'numeric', 'fixed', 'national char', 'national varchar'];
        $ph = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";
        $nc = driver()->enumLength;
        $pi = '(('.implode('|', array_merge(array_keys(driver()->types()), $oa)).")\\b(?:\\s*\\(((?:[^'\")]|$nc)++)\\))?"."\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?(?:\\s*COLLATE\\s*['\"]?[^'\"\\s,]+['\"]?)?";
        $Zf = "$ph*(".($U == 'FUNCTION' ? '' : driver()->inout).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$pi";
        $h = get_val("SHOW CREATE $U ".idf_escape($E), 2);
        preg_match("~\\(((?:$Zf\\s*,?)*)\\)\\s*".($U == 'FUNCTION' ? "RETURNS\\s+$pi\\s+" : '').'(.*)~is', $h, $C);
        $n = [];
        preg_match_all("~$Zf\\s*,?~is", $C[1], $Ae, PREG_SET_ORDER);
        foreach ($Ae as $Mf) {
            $n[] = ['field' => str_replace('``', '`', $Mf[2]).$Mf[3], 'type' => strtolower($Mf[5]), 'length' => preg_replace_callback("~$nc~s", 'Adminer\normalize_enum', $Mf[6]), 'unsigned' => strtolower(preg_replace('~\s+~', ' ', trim("$Mf[8] $Mf[7]"))), 'null' => true, 'full_type' => $Mf[4], 'inout' => strtoupper($Mf[1]), 'collation' => strtolower($Mf[9])];
        }

        return ['fields' => $n, 'comment' => get_val('SELECT ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME = '.q($E))] + ($U != 'FUNCTION' ? ['definition' => $C[11]] : ['returns' => ['type' => $C[12], 'length' => $C[13], 'unsigned' => $C[15], 'collation' => $C[16]], 'definition' => $C[17], 'language' => 'SQL']);
    }function routines()
    {
        return get_rows('SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()');
    }function routine_languages()
    {
        return [];
    }function routine_id($E, array $M)
    {
        return idf_escape($E);
    }function last_id($K)
    {
        return get_val('SELECT LAST_INSERT_ID()');
    }function explain(Db $f, $J)
    {
        return $f->query('EXPLAIN '.(min_version(5.1) && ! min_version(5.7) ? 'PARTITIONS ' : '').$J);
    }function found_rows(array $S, array $Z)
    {
        return $Z || $S['Engine'] != 'InnoDB' ? null : $S['Rows'];
    }function create_sql($R, $ya, $Ah)
    {
        $L = get_val('SHOW CREATE TABLE '.table($R), 1);
        if (! $ya) {
            $L = preg_replace('~ AUTO_INCREMENT=\d+~', '', $L);
        }

        return $L;
    }function truncate_sql($R)
    {
        return 'TRUNCATE '.table($R);
    }function use_sql($Db, $Ah = '')
    {
        $E = idf_escape($Db);
        $L = '';
        if (preg_match('~CREATE~', $Ah) && ($h = get_val("SHOW CREATE DATABASE $E", 1))) {
            set_utf8mb4($h);
            if ($Ah == 'DROP+CREATE') {
                $L = "DROP DATABASE IF EXISTS $E;\n";
            }$L
                .= "$h;\n";
        }

        return $L."USE $E";
    }function trigger_sql($R)
    {
        $L = '';
        foreach (get_rows('SHOW TRIGGERS LIKE '.q(addcslashes($R, '%_\\')), null, '-- ') as $M) {
            $L
                .= "\nCREATE TRIGGER ".idf_escape($M['Trigger'])." $M[Timing] $M[Event] ON ".table($M['Table'])." FOR EACH ROW\n$M[Statement];;\n";
        }

        return $L;
    }function show_variables()
    {
        return get_rows('SHOW VARIABLES');
    }function show_status()
    {
        return get_rows('SHOW STATUS');
    }function process_list()
    {
        return get_rows('SHOW FULL PROCESSLIST');
    }function convert_field(array $m)
    {
        if (preg_match('~binary~', $m['type'])) {
            return 'HEX('.idf_escape($m['field']).')';
        }if ($m['type'] == 'bit') {
            return 'BIN('.idf_escape($m['field']).' + 0)';
        }if (preg_match('~geometry|point|linestring|polygon~', $m['type'])) {
            return (min_version(8) ? 'ST_' : '').'AsWKT('.idf_escape($m['field']).')';
        }
    }function unconvert_field(array $m, $L)
    {
        if (preg_match('~binary~', $m['type'])) {
            $L = "UNHEX($L)";
        }if ($m['type'] == 'bit') {
            $L = "CONVERT(b$L, UNSIGNED)";
        }if (preg_match('~geometry|point|linestring|polygon~', $m['type'])) {
            $lg = (min_version(8) ? 'ST_' : '');
            $L = $lg."GeomFromText($L, $lg"."SRID($m[field]))";
        }

        return $L;
    }function support($Ic)
    {
        return preg_match('~^(comment|columns|copy|database|drop_col|dump|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(5.1) ? '|event' : '').(min_version(8) ? '|descidx' : '').(min_version('8.0.16', '10.2.1') ? '|check' : '').')$~', $Ic);
    }function kill_process($u)
    {
        return queries('KILL '.number($u));
    }function connection_id()
    {
        return 'SELECT CONNECTION_ID()';
    }function max_connections()
    {
        return get_val('SELECT @@max_connections');
    }function types()
    {
        return [];
    }function type_values($u)
    {
        return '';
    }function schemas()
    {
        return [];
    }function get_schema()
    {
        return '';
    }function set_schema($Tg, $g = null)
    {
        return true;
    }
}define('Adminer\JUSH', Driver::$jush);
define('Adminer\SERVER', ''.$_GET[DRIVER]);
define('Adminer\DB', "$_GET[db]");
define('Adminer\ME', preg_replace('~\?.*~', '', relative_uri()).'?'.(sid() ? SID.'&' : '').(SERVER !== null ? DRIVER.'='.urlencode(SERVER).'&' : '').($_GET['ext'] ? 'ext='.urlencode($_GET['ext']).'&' : '').(isset($_GET['username']) ? 'username='.urlencode($_GET['username']).'&' : '').(DB != '' ? 'db='.urlencode(DB).'&'.(isset($_GET['ns']) ? 'ns='.urlencode($_GET['ns']).'&' : '') : ''));
function page_header($Yh, $l = '', $Ka = [], $Zh = '')
{
    page_headers();
    if (is_ajax() && $l) {
        page_messages($l);
        exit;
    }if (! ob_get_level()) {
        ob_start('ob_gzhandler', 4096);
    }$ai = $Yh.($Zh != '' ? ": $Zh" : '');
    $bi = strip_tags($ai.(SERVER != '' && SERVER != 'localhost' ? h(' - '.SERVER) : '').' - '.adminer()->name());
    echo '<!DOCTYPE html>
<html lang="',LANG,'" dir="',lang(80),'">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$bi,'</title>
<link rel="stylesheet" href="',h(preg_replace('~\\?.*~', '', ME).'?file=default.css&version=5.4.1'),'">
';
    $yb = adminer()->css();
    if (is_int(key($yb))) {
        $yb = array_fill_keys($yb, 'light');
    }$od = in_array('light', $yb) || in_array('', $yb);
    $md = in_array('dark', $yb) || in_array('', $yb);
    $Ab = ($od ? ($md ? null : false) : ($md ?: null));
    $Ke = " media='(prefers-color-scheme: dark)'";
    if ($Ab !== false) {
        echo "<link rel='stylesheet'".($Ab ? '' : $Ke)." href='".h(preg_replace('~\\?.*~', '', ME).'?file=dark.css&version=5.4.1')."'>\n";
    }echo "<meta name='color-scheme' content='".($Ab === null ? 'light dark' : ($Ab ? 'dark' : 'light'))."'>\n",script_src(preg_replace('~\\?.*~', '', ME).'?file=functions.js&version=5.4.1');
    if (adminer()->head($Ab)) {
        echo "<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace('~\\?.*~', '', ME).'?file=logo.png&version=5.4.1')."'>\n";
    }foreach ($yb as $Bi => $Te) {
        $wa = ($Te == 'dark' && ! $Ab ? $Ke : ($Te == 'light' && $md ? " media='(prefers-color-scheme: light)'" : ''));
        echo "<link rel='stylesheet'$wa href='".h($Bi)."'>\n";
    }echo "\n<body class='".lang(80).' nojs';
    adminer()->bodyClass();
    echo "'>\n";
    $o = get_temp_dir().'/adminer.version';
    if (! $_COOKIE['adminer_version'] && function_exists('openssl_verify') && file_exists($o) && filemtime($o) + 86400 > time()) {
        $Li = unserialize(file_get_contents($o));
        $ug = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
';
        if (openssl_verify($Li['version'], base64_decode($Li['signature']), $ug) == 1) {
            $_COOKIE['adminer_version'] = $Li['version'];
        }
    }echo script('mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick'.(isset($_COOKIE['adminer_version']) ? '' : ", onload: partial(verifyVersion, '".VERSION."', '".js_escape(ME)."', '".get_token()."')")."});
document.body.classList.replace('nojs', 'js');
const offlineMessage = '".js_escape(lang(81))."';
const thousandsSeparator = '".js_escape(lang(4))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'></div>\n",script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"),"<div id='content'>\n","<span id='menuopen' class='jsonly'>".icon('move', '', 'menu', '').'</span>'.script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");
    if ($Ka !== null) {
        $A = substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1);
        echo '<p id="breadcrumb"><a href="'.h($A ?: '.').'">'.get_driver(DRIVER).'</a> Â» ';
        $A = substr(preg_replace('~\b(db|ns)=[^&]*&~', '', ME), 0, -1);
        $P = adminer()->serverName(SERVER);
        $P = ($P != '' ? $P : lang(25));
        if ($Ka === false) {
            echo "$P\n";
        } else {
            echo "<a href='".h($A)."' accesskey='1' title='Alt+Shift+1'>$P</a> Â» ";
            if ($_GET['ns'] != '' || (DB != '' && is_array($Ka))) {
                echo '<a href="'.h($A.'&db='.urlencode(DB).(support('scheme') ? '&ns=' : '')).'">'.h(DB).'</a> Â» ';
            }if (is_array($Ka)) {
                if ($_GET['ns'] != '') {
                    echo '<a href="'.h(substr(ME, 0, -1)).'">'.h($_GET['ns']).'</a> Â» ';
                }foreach ($Ka as $z => $X) {
                    $Ob = (is_array($X) ? $X[1] : h($X));
                    if ($Ob != '') {
                        echo "<a href='".h(ME."$z=").urlencode(is_array($X) ? $X[0] : $X)."'>$Ob</a> Â» ";
                    }
                }
            }echo "$Yh\n";
        }
    }echo "<h2>$ai</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";
    restart_session();
    page_messages($l);
    $i = &get_session('dbs');
    if (DB != '' && $i && ! in_array(DB, $i, true)) {
        $i = null;
    }stop_session();
    define('Adminer\PAGE_HEADER', 1);
}function page_headers()
{
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Frame-Options: deny');
    header('X-XSS-Protection: 0');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: origin-when-cross-origin');
    foreach (adminer()->csp(csp()) as $xb) {
        $pd = [];
        foreach ($xb as $z => $X) {
            $pd[] = "$z $X";
        }header('Content-Security-Policy: '.implode('; ', $pd));
    }adminer()->headers();
}function csp()
{
    return [['script-src' => "'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'", 'connect-src' => "'self'", 'frame-src' => 'https://www.adminer.org', 'object-src' => "'none'", 'base-uri' => "'none'", 'form-action' => "'self'"]];
}function get_nonce()
{
    static $ff;
    if (! $ff) {
        $ff = base64_encode(rand_string());
    }

    return $ff;
}function page_messages($l)
{
    $Ai = preg_replace('~^[^?]*~', '', $_SERVER['REQUEST_URI']);
    $Pe = idx($_SESSION['messages'], $Ai);
    if ($Pe) {
        echo "<div class='message'>".implode("</div>\n<div class='message'>", $Pe).'</div>'.script('messagesPrint();');
        unset($_SESSION['messages'][$Ai]);
    }if ($l) {
        echo "<div class='error'>$l</div>\n";
    }if (adminer()->error) {
        echo "<div class='error'>".adminer()->error."</div>\n";
    }
}function page_footer($Se = '')
{
    echo "</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";
    adminer()->navigation($Se);
    echo "</div>\n";
    if ($Se != 'auth') {
        echo '<form action="" method="post">
<p class="logout">
<span>',h($_GET['username'])."\n",'</span>
<input type="submit" name="logout" value="',lang(82),'" id="logout">
',input_token(),'</form>
';
    }echo "</div>\n\n",script('setupSubmitHighlight(document);');
}function int32($Xe)
{
    while ($Xe >= 2147483648) {
        $Xe -= 4294967296;
    }while ($Xe <= -2147483649) {
        $Xe += 4294967296;
    }

    return (int) $Xe;
}function long2str(array $W, $Pi)
{
    $Rg = '';
    foreach ($W as $X) {
        $Rg
            .= pack('V', $X);
    }if ($Pi) {
        return substr($Rg, 0, end($W));
    }

    return $Rg;
}function str2long($Rg, $Pi)
{
    $W = array_values(unpack('V*', str_pad($Rg, 4 * ceil(strlen($Rg) / 4), "\0")));
    if ($Pi) {
        $W[] = strlen($Rg);
    }

    return $W;
}function xxtea_mx($Wi, $Vi, $Dh, $de)
{
    return int32((($Wi >> 5 & 0x7FFFFFF) ^ $Vi << 2) + (($Vi >> 3 & 0x1FFFFFFF) ^ $Wi << 4)) ^ int32(($Dh ^ $Vi) + ($de ^ $Wi));
}function encrypt_string($yh, $z)
{
    if ($yh == '') {
        return '';
    }$z = array_values(unpack('V*', pack('H*', md5($z))));
    $W = str2long($yh, true);
    $Xe = count($W) - 1;
    $Wi = $W[$Xe];
    $Vi = $W[0];
    $I = floor(6 + 52 / ($Xe + 1));
    $Dh = 0;
    while ($I-- > 0) {
        $Dh = int32($Dh + 0x9E3779B9);
        $cc = $Dh >> 2 & 3;
        for ($Kf = 0; $Kf < $Xe; $Kf++) {
            $Vi = $W[$Kf + 1];
            $We = xxtea_mx($Wi, $Vi, $Dh, $z[$Kf & 3 ^ $cc]);
            $Wi = int32($W[$Kf] + $We);
            $W[$Kf] = $Wi;
        }$Vi = $W[0];
        $We = xxtea_mx($Wi, $Vi, $Dh, $z[$Kf & 3 ^ $cc]);
        $Wi = int32($W[$Xe] + $We);
        $W[$Xe] = $Wi;
    }

    return long2str($W, false);
}function decrypt_string($yh, $z)
{
    if ($yh == '') {
        return '';
    }if (! $z) {
        return false;
    }$z = array_values(unpack('V*', pack('H*', md5($z))));
    $W = str2long($yh, false);
    $Xe = count($W) - 1;
    $Wi = $W[$Xe];
    $Vi = $W[0];
    $I = floor(6 + 52 / ($Xe + 1));
    $Dh = int32($I * 0x9E3779B9);
    while ($Dh) {
        $cc = $Dh >> 2 & 3;
        for ($Kf = $Xe; $Kf > 0; $Kf--) {
            $Wi = $W[$Kf - 1];
            $We = xxtea_mx($Wi, $Vi, $Dh, $z[$Kf & 3 ^ $cc]);
            $Vi = int32($W[$Kf] - $We);
            $W[$Kf] = $Vi;
        }$Wi = $W[$Xe];
        $We = xxtea_mx($Wi, $Vi, $Dh, $z[$Kf & 3 ^ $cc]);
        $Vi = int32($W[0] - $We);
        $W[0] = $Vi;
        $Dh = int32($Dh - 0x9E3779B9);
    }

    return long2str($W, true);
}$bg = [];
if ($_COOKIE['adminer_permanent']) {
    foreach (explode(' ', $_COOKIE['adminer_permanent']) as $X) {
        [$z] = explode(':', $X);
        $bg[$z] = $X;
    }
}function add_invalid_login()
{
    $Da = get_temp_dir().'/adminer.invalid';
    foreach (glob("$Da*") ?: [$Da] as $o) {
        $q = file_open_lock($o);
        if ($q) {
            break;
        }
    }if (! $q) {
        $q = file_open_lock("$Da-".rand_string());
    }if (! $q) {
        return;
    }$Vd = unserialize(stream_get_contents($q));
    $Vh = time();
    if ($Vd) {
        foreach ($Vd as $Wd => $X) {
            if ($X[0] < $Vh) {
                unset($Vd[$Wd]);
            }
        }
    }$Ud = &$Vd[adminer()->bruteForceKey()];
    if (! $Ud) {
        $Ud = [$Vh + 30 * 60, 0];
    }$Ud[1]++;
    file_write_unlock($q, serialize($Vd));
}function check_invalid_login(array &$bg)
{
    $Vd = [];
    foreach (glob(get_temp_dir().'/adminer.invalid*') as $o) {
        $q = file_open_lock($o);
        if ($q) {
            $Vd = unserialize(stream_get_contents($q));
            file_unlock($q);
            break;
        }
    }$Ud = idx($Vd, adminer()->bruteForceKey(), []);
    $ef = ($Ud[1] > 29 ? $Ud[0] - time() : 0);
    if ($ef > 0) {
        auth_error(lang(83, ceil($ef / 60)), $bg);
    }
}$xa = $_POST['auth'];
if ($xa) {
    session_regenerate_id();
    $Ki = $xa['driver'];
    $P = $xa['server'];
    $V = $xa['username'];
    $H = (string) $xa['password'];
    $j = $xa['db'];
    set_password($Ki, $P, $V, $H);
    $_SESSION['db'][$Ki][$P][$V][$j] = true;
    if ($xa['permanent']) {
        $z = implode('-', array_map('base64_encode', [$Ki, $P, $V, $j]));
        $pg = adminer()->permanentLogin(true);
        $bg[$z] = "$z:".base64_encode($pg ? encrypt_string($H, $pg) : '');
        cookie('adminer_permanent', implode(' ', $bg));
    }if (count($_POST) == 1 || $Ki != DRIVER || $P != SERVER || $_GET['username'] !== $V || $j != DB) {
        redirect(auth_url($Ki, $P, $V, $j));
    }
} elseif ($_POST['logout'] && (! $_SESSION['token'] || verify_token())) {
    foreach (['pwds', 'db', 'dbs', 'queries'] as $z) {
        set_session($z, null);
    }unset_permanent($bg);
    redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1), lang(84).' '.lang(85));
} elseif ($bg && ! $_SESSION['pwds']) {
    session_regenerate_id();
    $pg = adminer()->permanentLogin();
    foreach ($bg as $z => $X) {
        [, $Xa] = explode(':', $X);
        [$Ki, $P, $V, $j] = array_map('base64_decode', explode('-', $z));
        set_password($Ki, $P, $V, decrypt_string(base64_decode($Xa), $pg));
        $_SESSION['db'][$Ki][$P][$V][$j] = true;
    }
}function unset_permanent(array &$bg)
{
    foreach ($bg as $z => $X) {
        [$Ki, $P, $V, $j] = array_map('base64_decode', explode('-', $z));
        if ($Ki == DRIVER && $P == SERVER && $V == $_GET['username'] && $j == DB) {
            unset($bg[$z]);
        }
    }cookie('adminer_permanent', implode(' ', $bg));
}function auth_error($l, array &$bg)
{
    $gh = session_name();
    if (isset($_GET['username'])) {
        header('HTTP/1.1 403 Forbidden');
        if (($_COOKIE[$gh] || $_GET[$gh]) && ! $_SESSION['token']) {
            $l = lang(86);
        } else {
            restart_session();
            add_invalid_login();
            $H = get_password();
            if ($H !== null) {
                if ($H === false) {
                    $l
                        .= ($l ? '<br>' : '').lang(87, target_blank(), '<code>permanentLogin()</code>');
                }set_password(DRIVER, SERVER, $_GET['username'], null);
            }unset_permanent($bg);
        }
    }if (! $_COOKIE[$gh] && $_GET[$gh] && ini_bool('session.use_only_cookies')) {
        $l = lang(88);
    }$Nf = session_get_cookie_params();
    cookie('adminer_key', ($_COOKIE['adminer_key'] ?: rand_string()), $Nf['lifetime']);
    if (! $_SESSION['token']) {
        $_SESSION['token'] = rand(1, 1e6);
    }page_header(lang(29), $l, null);
    echo "<form action='' method='post'>\n",'<div>';
    if (hidden_fields($_POST, ['auth'])) {
        echo "<p class='message'>".lang(89)."\n";
    }echo "</div>\n";
    adminer()->loginForm();
    echo "</form>\n";
    page_footer('auth');
    exit;
}if (isset($_GET['username']) && ! class_exists('Adminer\Db')) {
    unset($_SESSION['pwds'][DRIVER]);
    unset_permanent($bg);
    page_header(lang(90), lang(91, implode(', ', Driver::$extensions)), false);
    page_footer('auth');
    exit;
}$f = '';
if (isset($_GET['username']) && is_string(get_password())) {
    [, $fg] = host_port(SERVER);
    if (preg_match('~^\s*([-+]?\d+)~', $fg, $C) && ($C[1] < 1024 || $C[1] > 65535)) {
        auth_error(lang(92), $bg);
    }check_invalid_login($bg);
    $wb = adminer()->credentials();
    $f = Driver::connect($wb[0], $wb[1], $wb[2]);
    if (is_object($f)) {
        Db::$instance = $f;
        Driver::$instance = new Driver($f);
        if ($f->flavor) {
            save_settings(['vendor-'.DRIVER.'-'.SERVER => get_driver(DRIVER)]);
        }
    }
}$we = null;
if (! is_object($f) || ($we = adminer()->login($_GET['username'], get_password())) !== true) {
    $l = (is_string($f) ? nl_br(h($f)) : (is_string($we) ? $we : lang(93))).(preg_match('~^ | $~', get_password()) ? '<br>'.lang(94) : '');
    auth_error($l, $bg);
}if ($_POST['logout'] && $_SESSION['token'] && ! verify_token()) {
    page_header(lang(82), lang(95));
    page_footer('db');
    exit;
}if (! $_SESSION['token']) {
    $_SESSION['token'] = rand(1, 1e6);
}stop_session(true);
if ($xa && $_POST['token']) {
    $_POST['token'] = get_token();
}$l = '';
if ($_POST) {
    if (! verify_token()) {
        $Nd = 'max_input_vars';
        $Ie = ini_get($Nd);
        if (extension_loaded('suhosin')) {
            foreach (['suhosin.request.max_vars', 'suhosin.post.max_vars'] as $z) {
                $X = ini_get($z);
                if ($X && (! $Ie || $X < $Ie)) {
                    $Nd = $z;
                    $Ie = $X;
                }
            }
        }$l = (! $_POST['token'] && $Ie ? lang(96, "'$Nd'") : lang(95).' '.lang(97));
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $l = lang(98, "'post_max_size'");
    if (isset($_GET['sql'])) {
        $l
            .= ' '.lang(99);
    }
}function print_select_result($K, $g = null, array $Bf = [], $_ = 0)
{
    $ve = [];
    $x = [];
    $d = [];
    $Ia = [];
    $qi = [];
    $L = [];
    for ($t = 0; (! $_ || $t < $_) && ($M = $K->fetch_row()); $t++) {
        if (! $t) {
            echo "<div class='scrollable'>\n","<table class='nowrap odds'>\n",'<thead><tr>';
            for ($y = 0; $y < count($M); $y++) {
                $m = $K->fetch_field();
                $E = $m->name;
                $Af = (isset($m->orgtable) ? $m->orgtable : '');
                $_f = (isset($m->orgname) ? $m->orgname : $E);
                if ($Bf && JUSH == 'sql') {
                    $ve[$y] = ($E == 'table' ? 'table=' : ($E == 'possible_keys' ? 'indexes=' : null));
                } elseif ($Af != '') {
                    if (isset($m->table)) {
                        $L[$m->table] = $Af;
                    }if (! isset($x[$Af])) {
                        $x[$Af] = [];
                        foreach (indexes($Af, $g) as $w) {
                            if ($w['type'] == 'PRIMARY') {
                                $x[$Af] = array_flip($w['columns']);
                                break;
                            }
                        }$d[$Af] = $x[$Af];
                    }if (isset($d[$Af][$_f])) {
                        unset($d[$Af][$_f]);
                        $x[$Af][$_f] = $y;
                        $ve[$y] = $Af;
                    }
                }if ($m->charsetnr == 63) {
                    $Ia[$y] = true;
                }$qi[$y] = $m->type;
                echo '<th'.($Af != '' || $m->name != $_f ? " title='".h(($Af != '' ? "$Af." : '').$_f)."'" : '').'>'.h($E).($Bf ? doc_link(['sql' => 'explain-output.html#explain_'.strtolower($E), 'mariadb' => 'explain/#the-columns-in-explain-select']) : '');
            }echo "</thead>\n";
        }echo '<tr>';
        foreach ($M as $z => $X) {
            $A = '';
            if (isset($ve[$z]) && ! $d[$ve[$z]]) {
                if ($Bf && JUSH == 'sql') {
                    $R = $M[array_search('table=', $ve)];
                    $A = ME.$ve[$z].urlencode($Bf[$R] != '' ? $Bf[$R] : $R);
                } else {
                    $A = ME.'edit='.urlencode($ve[$z]);
                    foreach ($x[$ve[$z]] as $bb => $y) {
                        if ($M[$y] === null) {
                            $A = '';
                            break;
                        }$A
                            .= '&where'.urlencode('['.bracket_escape($bb).']').'='.urlencode($M[$y]);
                    }
                }
            } elseif (is_url($X)) {
                $A = $X;
            }if ($X === null) {
                $X = '<i>NULL</i>';
            } elseif ($Ia[$z] && ! is_utf8($X)) {
                $X = '<i>'.lang(38, strlen($X)).'</i>';
            } else {
                $X = h($X);
                if ($qi[$z] == 254) {
                    $X = "<code>$X</code>";
                }
            }if ($A) {
                $X = "<a href='".h($A)."'".(is_url($A) ? target_blank() : '').">$X</a>";
            }echo '<td'.($qi[$z] <= 9 || $qi[$z] == 246 ? " class='number'" : '').">$X";
        }
    }echo ($t ? "</table>\n</div>" : "<p class='message'>".lang(14))."\n";

    return $L;
}function referencable_primary($bh)
{
    $L = [];
    foreach (table_status('', true) as $Hh => $R) {
        if ($Hh != $bh && fk_support($R)) {
            foreach (fields($Hh) as $m) {
                if ($m['primary']) {
                    if ($L[$Hh]) {
                        unset($L[$Hh]);
                        break;
                    }$L[$Hh] = $m;
                }
            }
        }
    }

    return $L;
}function textarea($E, $Y, $N = 10, $eb = 80)
{
    echo "<textarea name='".h($E)."' rows='$N' cols='$eb' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";
    if (is_array($Y)) {
        foreach ($Y as $X) {
            echo h($X[0])."\n\n\n";
        }
    } else {
        echo h($Y);
    }echo '</textarea>';
}function select_input($wa, array $wf, $Y = '', $rf = '', $cg = '')
{
    $Oh = ($wf ? 'select' : 'input');

    return "<$Oh$wa".($wf ? "><option value=''>$cg".optionlist($wf, $Y, true).'</select>' : " size='10' value='".h($Y)."' placeholder='$cg'>").($rf ? script("qsl('$Oh').onchange = $rf;", '') : '');
}function json_row($z, $X = null, $uc = true)
{
    static $Oc = true;
    if ($Oc) {
        echo '{';
    }if ($z != '') {
        echo ($Oc ? '' : ',')."\n\t\"".addcslashes($z, "\r\n\t\"\\/").'": '.($X !== null ? ($uc ? '"'.addcslashes($X, "\r\n\"\\/").'"' : $X) : 'null');
        $Oc = false;
    } else {
        echo "\n}\n";
        $Oc = true;
    }
}function edit_type($z, array $m, array $b, array $Vc = [], array $Fc = [])
{
    $U = $m['type'];
    echo "<td><select name='".h($z)."[type]' class='type' aria-labelledby='label-type'>";
    if ($U && ! array_key_exists($U, driver()->types()) && ! isset($Vc[$U]) && ! in_array($U, $Fc)) {
        $Fc[] = $U;
    }$_h = driver()->structuredTypes();
    if ($Vc) {
        $_h[lang(100)] = $Vc;
    }echo optionlist(array_merge($Fc, $_h), $U),'</select><td>',"<input name='".h($z)."[length]' value='".h($m['length'])."' size='3'".(! $m['length'] && preg_match('~var(char|binary)$~', $U) ? " class='required'" : '')." aria-labelledby='label-length'>","<td class='options'>",($b ? "<input list='collations' name='".h($z)."[collation]'".(preg_match('~(char|text|enum|set)$~', $U) ? '' : " class='hidden'")." value='".h($m['collation'])."' placeholder='(".lang(101).")'>" : ''),(driver()->unsigned ? "<select name='".h($z)."[unsigned]'".(! $U || preg_match(number_type(), $U) ? '' : " class='hidden'").'><option>'.optionlist(driver()->unsigned, $m['unsigned']).'</select>' : ''),(isset($m['on_update']) ? "<select name='".h($z)."[on_update]'".(preg_match('~timestamp|datetime~', $U) ? '' : " class='hidden'").'>'.optionlist(['' => '('.lang(102).')', 'CURRENT_TIMESTAMP'], (preg_match('~^CURRENT_TIMESTAMP~i', $m['on_update']) ? 'CURRENT_TIMESTAMP' : $m['on_update'])).'</select>' : ''),($Vc ? "<select name='".h($z)."[on_delete]'".(preg_match('~`~', $U) ? '' : " class='hidden'")."><option value=''>(".lang(103).')'.optionlist(explode('|', driver()->onActions), $m['on_delete']).'</select> ' : ' ');
}function process_length($re)
{
    $pc = driver()->enumLength;

    return preg_match("~^\\s*\\(?\\s*$pc(?:\\s*,\\s*$pc)*+\\s*\\)?\\s*\$~", $re) && preg_match_all("~$pc~", $re, $Ae) ? '('.implode(',', $Ae[0]).')' : preg_replace('~^[0-9].*~', '(\0)', preg_replace('~[^-0-9,+()[\]]~', '', $re));
}function process_type(array $m, $cb = 'COLLATE')
{
    return " $m[type]".process_length($m['length']).(preg_match(number_type(), $m['type']) && in_array($m['unsigned'], driver()->unsigned) ? " $m[unsigned]" : '').(preg_match('~char|text|enum|set~', $m['type']) && $m['collation'] ? " $cb ".(JUSH == 'mssql' ? $m['collation'] : q($m['collation'])) : '');
}function process_field(array $m, array $oi)
{
    if ($m['on_update']) {
        $m['on_update'] = str_ireplace('current_timestamp()', 'CURRENT_TIMESTAMP', $m['on_update']);
    }

    return [idf_escape(trim($m['field'])), process_type($oi), ($m['null'] ? ' NULL' : ' NOT NULL'), default_value($m), (preg_match('~timestamp|datetime~', $m['type']) && $m['on_update'] ? " ON UPDATE $m[on_update]" : ''), (support('comment') && $m['comment'] != '' ? ' COMMENT '.q($m['comment']) : ''), ($m['auto_increment'] ? auto_increment() : null)];
}function default_value(array $m)
{
    $k = $m['default'];
    $cd = $m['generated'];

    return $k === null ? '' : (in_array($cd, driver()->generated) ? (JUSH == 'mssql' ? " AS ($k)".($cd == 'VIRTUAL' ? '' : " $cd").'' : " GENERATED ALWAYS AS ($k) $cd") : ' DEFAULT '.(! preg_match('~^GENERATED ~i', $k) && (preg_match('~char|binary|text|json|enum|set~', $m['type']) || preg_match('~^(?![a-z])~i', $k)) ? (JUSH == 'sql' && preg_match('~text|json~', $m['type']) ? '('.q($k).')' : q($k)) : str_ireplace('current_timestamp()', 'CURRENT_TIMESTAMP', (JUSH == 'sqlite' ? "($k)" : $k))));
}function type_class($U)
{
    foreach (['char' => 'text', 'date' => 'time|year', 'binary' => 'blob', 'enum' => 'set'] as $z => $X) {
        if (preg_match("~$z|$X~", $U)) {
            return " class='$z'";
        }
    }
}function edit_fields(array $n, array $b, $U = 'TABLE', array $Vc = [])
{
    $n = array_values($n);
    $Jb = (($_POST ? $_POST['defaults'] : get_setting('defaults')) ? '' : " class='hidden'");
    $ib = (($_POST ? $_POST['comments'] : get_setting('comments')) ? '' : " class='hidden'");
    echo "<thead><tr>\n",($U == 'PROCEDURE' ? '<td>' : ''),"<th id='label-name'>".($U == 'TABLE' ? lang(104) : lang(105)),"<td id='label-type'>".lang(40)."<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".lang(106),'<td>'.lang(107);
    if ($U == 'TABLE') {
        echo "<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".lang(42)."'>AI</abbr>",doc_link(['sql' => 'example-auto-increment.html', 'mariadb' => 'auto_increment/']),"<td id='label-default'$Jb>".lang(43),(support('comment') ? "<td id='label-comment'$ib>".lang(41) : '');
    }echo '<td>'.icon('plus', 'add['.(support('move_col') ? 0 : count($n)).']', '+', lang(108)),"</thead>\n<tbody>\n",script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");
    foreach ($n as $t => $m) {
        $t++;
        $Cf = $m[($_POST ? 'orig' : 'field')];
        $Ub = (isset($_POST['add'][$t - 1]) || (isset($m['field']) && ! idx($_POST['drop_col'], $t))) && (support('drop_col') || $Cf == '');
        echo '<tr'.($Ub ? '' : " style='display: none;'").">\n",($U == 'PROCEDURE' ? '<td>'.html_select("fields[$t][inout]", explode('|', driver()->inout), $m['inout']) : '').'<th>';
        if ($Ub) {
            echo "<input name='fields[$t][field]' value='".h($m['field'])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST['add'][$t - 1]) ? ' autofocus' : '').'>';
        }echo input_hidden("fields[$t][orig]", $Cf);
        edit_type("fields[$t]", $m, $b, $Vc);
        if ($U == 'TABLE') {
            echo '<td>'.checkbox("fields[$t][null]", 1, $m['null'], '', '', 'block', 'label-null'),"<td><label class='block'><input type='radio' name='auto_increment_col' value='$t'".($m['auto_increment'] ? ' checked' : '')." aria-labelledby='label-ai'></label>","<td$Jb>".(driver()->generated ? html_select("fields[$t][generated]", array_merge(['', 'DEFAULT'], driver()->generated), $m['generated']).' ' : checkbox("fields[$t][generated]", 1, $m['generated'], '', '', '', 'label-default')),"<input name='fields[$t][default]' value='".h($m['default'])."' aria-labelledby='label-default'>",(support('comment') ? "<td$ib><input name='fields[$t][comment]' value='".h($m['comment'])."' data-maxlength='".(min_version(5.5) ? 1024 : 255)."' aria-labelledby='label-comment'>" : '');
        }echo '<td>',(support('move_col') ? icon('plus', "add[$t]", '+', lang(108)).' '.icon('up', "up[$t]", 'â', lang(109)).' '.icon('down', "down[$t]", 'â', lang(110)).' ' : ''),($Cf == '' || support('drop_col') ? icon('cross', "drop_col[$t]", 'x', lang(111)) : '');
    }
}function process_fields(array &$n)
{
    $jf = 0;
    if ($_POST['up']) {
        $le = 0;
        foreach ($n as $z => $m) {
            if (key($_POST['up']) == $z) {
                unset($n[$z]);
                array_splice($n, $le, 0, [$m]);
                break;
            }if (isset($m['field'])) {
                $le = $jf;
            }$jf++;
        }
    } elseif ($_POST['down']) {
        $Xc = false;
        foreach ($n as $z => $m) {
            if (isset($m['field']) && $Xc) {
                unset($n[key($_POST['down'])]);
                array_splice($n, $jf, 0, [$Xc]);
                break;
            }if (key($_POST['down']) == $z) {
                $Xc = $m;
            }$jf++;
        }
    } elseif ($_POST['add']) {
        $n = array_values($n);
        array_splice($n, key($_POST['add']), 0, [[]]);
    } elseif (! $_POST['drop_col']) {
        return false;
    }

    return true;
}function normalize_enum(array $C)
{
    $X = $C[0];

    return "'".str_replace("'", "''", addcslashes(stripcslashes(str_replace($X[0].$X[0], $X[0], substr($X, 1, -1))), '\\'))."'";
}function grant($ed, array $rg, $d, $pf)
{
    if (! $rg) {
        return true;
    }if ($rg == ['ALL PRIVILEGES', 'GRANT OPTION']) {
        return $ed == 'GRANT' ? queries("$ed ALL PRIVILEGES$pf WITH GRANT OPTION") : queries("$ed ALL PRIVILEGES$pf") && queries("$ed GRANT OPTION$pf");
    }

    return queries("$ed ".preg_replace('~(GRANT OPTION)\([^)]*\)~', '\1', implode("$d, ", $rg).$d).$pf);
}function drop_create($Yb, $h, $Zb, $Sh, $ac, $B, $Oe, $Me, $Ne, $mf, $bf)
{
    if ($_POST['drop']) {
        query_redirect($Yb, $B, $Oe);
    } elseif ($mf == '') {
        query_redirect($h, $B, $Ne);
    } elseif ($mf != $bf) {
        $vb = queries($h);
        queries_redirect($B, $Me, $vb && queries($Yb));
        if ($vb) {
            queries($Zb);
        }
    } else {
        queries_redirect($B, $Me, queries($Sh) && queries($ac) && queries($Yb) && queries($h));
    }
}function create_trigger($pf, array $M)
{
    $Xh = " $M[Timing] $M[Event]".(preg_match('~ OF~', $M['Event']) ? " $M[Of]" : '');

    return 'CREATE TRIGGER '.idf_escape($M['Trigger']).(JUSH == 'mssql' ? $pf.$Xh : $Xh.$pf).rtrim(" $M[Type]\n$M[Statement]", ';').';';
}function create_routine($Og, array $M)
{
    $Q = [];
    $n = (array) $M['fields'];
    ksort($n);
    foreach ($n as $m) {
        if ($m['field'] != '') {
            $Q[] = (preg_match('~^('.driver()->inout.')$~', $m['inout']) ? "$m[inout] " : '').idf_escape($m['field']).process_type($m, 'CHARACTER SET');
        }
    }$Lb = rtrim($M['definition'], ';');

    return "CREATE $Og ".idf_escape(trim($M['name'])).' ('.implode(', ', $Q).')'.($Og == 'FUNCTION' ? ' RETURNS'.process_type($M['returns'], 'CHARACTER SET') : '').($M['language'] ? " LANGUAGE $M[language]" : '').(JUSH == 'pgsql' ? ' AS '.q($Lb) : "\n$Lb;");
}function remove_definer($J)
{
    return preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~', '`@`(%|\1)', logged_user()).'`~', '\1', $J);
}function format_foreign_key(array $p)
{
    $j = $p['db'];
    $gf = $p['ns'];

    return ' FOREIGN KEY ('.implode(', ', array_map('Adminer\idf_escape', $p['source'])).') REFERENCES '.($j != '' && $j != $_GET['db'] ? idf_escape($j).'.' : '').($gf != '' && $gf != $_GET['ns'] ? idf_escape($gf).'.' : '').idf_escape($p['table']).' ('.implode(', ', array_map('Adminer\idf_escape', $p['target'])).')'.(preg_match('~^('.driver()->onActions.')$~', $p['on_delete']) ? " ON DELETE $p[on_delete]" : '').(preg_match('~^('.driver()->onActions.')$~', $p['on_update']) ? " ON UPDATE $p[on_update]" : '');
}function tar_file($o, $ci)
{
    $L = pack('a100a8a8a8a12a12', $o, 644, 0, 0, decoct($ci->size), decoct(time()));
    $Wa = 8 * 32;
    for ($t = 0; $t < strlen($L); $t++) {
        $Wa += ord($L[$t]);
    }$L
    .= sprintf('%06o', $Wa)."\0 ";
    echo $L,str_repeat("\0", 512 - strlen($L));
    $ci->send();
    echo str_repeat("\0", 511 - ($ci->size + 511) % 512);
}function doc_link(array $Yf, $Th = '<sup>?</sup>')
{
    $eh = connection()->server_info;
    $Li = preg_replace('~^(\d\.?\d).*~s', '\1', $eh);
    $Ci = ['sql' => "https://dev.mysql.com/doc/refman/$Li/en/", 'sqlite' => 'https://www.sqlite.org/', 'pgsql' => 'https://www.postgresql.org/docs/'.(connection()->flavor == 'cockroach' ? 'current' : $Li).'/', 'mssql' => 'https://learn.microsoft.com/en-us/sql/', 'oracle' => 'https://www.oracle.com/pls/topic/lookup?ctx=db'.preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s', '\1\2', $eh).'&id='];
    if (connection()->flavor == 'maria') {
        $Ci['sql'] = 'https://mariadb.com/kb/en/';
        $Yf['sql'] = (isset($Yf['mariadb']) ? $Yf['mariadb'] : str_replace('.html', '/', $Yf['sql']));
    }

    return $Yf[JUSH] ? "<a href='".h($Ci[JUSH].$Yf[JUSH].(JUSH == 'mssql' ? "?view=sql-server-ver$Li" : ''))."'".target_blank().">$Th</a>" : '';
}function db_size($j)
{
    if (! connection()->select_db($j)) {
        return '?';
    }$L = 0;
    foreach (table_status() as $S) {
        $L += $S['Data_length'] + $S['Index_length'];
    }

    return format_number($L);
}function set_utf8mb4($h)
{
    static $Q = false;
    if (! $Q && preg_match('~\butf8mb4~i', $h)) {
        $Q = true;
        echo 'SET NAMES '.charset(connection()).";\n\n";
    }
}if (isset($_GET['status'])) {
    $_GET['variables'] = $_GET['status'];
}if (isset($_GET['import'])) {
    $_GET['sql'] = $_GET['import'];
}if (! (DB != '' ? connection()->select_db(DB) : isset($_GET['sql']) || isset($_GET['dump']) || isset($_GET['database']) || isset($_GET['processlist']) || isset($_GET['privileges']) || isset($_GET['user']) || isset($_GET['variables']) || $_GET['script'] == 'connect' || $_GET['script'] == 'kill')) {
    if (DB != '' || $_GET['refresh']) {
        restart_session();
        set_session('dbs', null);
    }if (DB != '') {
        header('HTTP/1.1 404 Not Found');
        page_header(lang(28).': '.h(DB), lang(112), true);
    } else {
        if ($_POST['db'] && ! $l) {
            queries_redirect(substr(ME, 0, -1), lang(113), drop_databases($_POST['db']));
        }page_header(lang(114), $l, false);
        echo "<p class='links'>\n";
        foreach (['database' => lang(115), 'privileges' => lang(62), 'processlist' => lang(116), 'variables' => lang(117), 'status' => lang(118)] as $z => $X) {
            if (support($z)) {
                echo "<a href='".h(ME)."$z='>$X</a>\n";
            }
        }echo '<p>'.lang(119, get_driver(DRIVER), '<b>'.h(connection()->server_info).'</b>', '<b>'.connection()->extension.'</b>')."\n",'<p>'.lang(120, '<b>'.h(logged_user()).'</b>')."\n";
        $i = adminer()->databases();
        if ($i) {
            $Ug = support('scheme');
            $b = collations();
            echo "<form action='' method='post'>\n","<table class='checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr>'.(support('database') ? '<td>' : '').'<th>'.lang(28).(get_session('dbs') !== null ? " - <a href='".h(ME)."refresh=1'>".lang(121).'</a>' : '').'<td>'.lang(122).'<td>'.lang(123).'<td>'.lang(124)." - <a href='".h(ME)."dbsize=1'>".lang(125).'</a>'.script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');", '')."</thead>\n";
            $i = ($_GET['dbsize'] ? count_tables($i) : array_flip($i));
            foreach ($i as $j => $T) {
                $Ng = h(ME).'db='.urlencode($j);
                $u = h('Db-'.$j);
                echo '<tr>'.(support('database') ? '<td>'.checkbox('db[]', $j, in_array($j, (array) $_POST['db']), '', '', '', $u) : ''),"<th><a href='$Ng' id='$u'>".h($j).'</a>';
                $db = h(db_collation($j, $b));
                echo '<td>'.(support('database') ? "<a href='$Ng".($Ug ? '&amp;ns=' : '')."&amp;database=' title='".lang(58)."'>$db</a>" : $db),"<td align='right'><a href='$Ng&amp;schema=' id='tables-".h($j)."' title='".lang(61)."'>".($_GET['dbsize'] ? $T : '?').'</a>',"<td align='right' id='size-".h($j)."'>".($_GET['dbsize'] ? db_size($j) : '?'),"\n";
            }echo "</table>\n",(support('database') ? "<div class='footer'><div>\n".'<fieldset><legend>'.lang(126)." <span id='selected'></span></legend><div>\n".input_hidden('all').script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };")."<input type='submit' name='drop' value='".lang(127)."'>".confirm()."\n"."</div></fieldset>\n"."</div></div>\n" : ''),input_token(),"</form>\n",script('tableCheck();');
        }if (! empty(adminer()->plugins)) {
            echo "<div class='plugins'>\n",'<h3>'.lang(128)."</h3>\n<ul>\n";
            foreach (adminer()->plugins as $dg) {
                $Pb = (method_exists($dg, 'description') ? $dg->description() : '');
                if (! $Pb) {
                    $Eg = new \ReflectionObject($dg);
                    if (preg_match('~^/[\s*]+(.+)~', $Eg->getDocComment(), $C)) {
                        $Pb = $C[1];
                    }
                }$Vg = (method_exists($dg, 'screenshot') ? $dg->screenshot() : '');
                echo '<li><b>'.get_class($dg).'</b>'.h($Pb ? ": $Pb" : '').($Vg ? " (<a href='".h($Vg)."'".target_blank().'>'.lang(129).'</a>)' : '')."\n";
            }echo "</ul>\n";
            adminer()->pluginsLinks();
            echo "</div>\n";
        }
    }page_footer('db');
    exit;
}adminer()->afterConnect();
class TmpFile
{
    private $handler;

    public $size;

    public function __construct()
    {
        $this->handler = tmpfile();
    }

    public function write($pb)
    {
        $this->size += strlen($pb);
        fwrite($this->handler, $pb);
    }

    public function send()
    {
        fseek($this->handler, 0);
        fpassthru($this->handler);
        fclose($this->handler);
    }
}if (isset($_GET['select']) && ($_POST['edit'] || $_POST['clone']) && ! $_POST['save']) {
    $_GET['edit'] = $_GET['select'];
}if (isset($_GET['callf'])) {
    $_GET['call'] = $_GET['callf'];
}if (isset($_GET['function'])) {
    $_GET['procedure'] = $_GET['function'];
}if (isset($_GET['download'])) {
    $a = $_GET['download'];
    $n = fields($a);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.friendly_url("$a-".implode('_', $_GET['where'])).'.'.friendly_url($_GET['field']));
    $O = [idf_escape($_GET['field'])];
    $K = driver()->select($a, $O, [where($_GET, $n)], $O);
    $M = ($K ? $K->fetch_row() : []);
    echo driver()->value($M[0], $n[$_GET['field']]);
    exit;
} elseif (isset($_GET['table'])) {
    $a = $_GET['table'];
    $n = fields($a);
    if (! $n) {
        $l = error() ?: lang(11);
    }$S = table_status1($a);
    $E = adminer()->tableName($S);
    page_header(($n && is_view($S) ? $S['Engine'] == 'materialized view' ? lang(130) : lang(131) : lang(132)).': '.($E != '' ? $E : h($a)), $l);
    $Mg = [];
    foreach ($n as $z => $m) {
        $Mg += $m['privileges'];
    }adminer()->selectLinks($S, (isset($Mg['insert']) || ! support('table') ? '' : null));
    $hb = $S['Comment'];
    if ($hb != '') {
        echo "<p class='nowrap'>".lang(41).': '.h($hb)."\n";
    }if ($n) {
        adminer()->tableStructurePrint($n, $S);
    }function tables_links(array $T)
    {
        echo "<ul>\n";
        foreach ($T as $R) {
            echo "<li><a href='".h(ME.'table='.urlencode($R))."'>".h($R).'</a>';
        }echo "</ul>\n";
    }$Md = driver()->inheritsFrom($a);
    if ($Md) {
        echo '<h3>'.lang(133)."</h3>\n";
        tables_links($Md);
    }if (support('indexes') && driver()->supportsIndex($S)) {
        echo "<h3 id='indexes'>".lang(134)."</h3>\n";
        $x = indexes($a);
        if ($x) {
            adminer()->tableIndexesPrint($x, $S);
        }echo '<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.lang(135)."</a>\n";
    }if (! is_view($S)) {
        if (fk_support($S)) {
            echo "<h3 id='foreign-keys'>".lang(100)."</h3>\n";
            $Vc = foreign_keys($a);
            if ($Vc) {
                echo "<table>\n",'<thead><tr><th>'.lang(136).'<td>'.lang(137).'<td>'.lang(103).'<td>'.lang(102)."<td></thead>\n";
                foreach ($Vc as $E => $p) {
                    echo "<tr title='".h($E)."'>",'<th><i>'.implode('</i>, <i>', array_map('Adminer\h', $p['source'])).'</i>';
                    $A = ($p['db'] != '' ? preg_replace('~db=[^&]*~', 'db='.urlencode($p['db']), ME) : ($p['ns'] != '' ? preg_replace('~ns=[^&]*~', 'ns='.urlencode($p['ns']), ME) : ME));
                    echo "<td><a href='".h($A.'table='.urlencode($p['table']))."'>".($p['db'] != '' && $p['db'] != DB ? '<b>'.h($p['db']).'</b>.' : '').($p['ns'] != '' && $p['ns'] != $_GET['ns'] ? '<b>'.h($p['ns']).'</b>.' : '').h($p['table']).'</a>','(<i>'.implode('</i>, <i>', array_map('Adminer\h', $p['target'])).'</i>)','<td>'.h($p['on_delete']),'<td>'.h($p['on_update']),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($E)).'">'.lang(138).'</a>',"\n";
                }echo "</table>\n";
            }echo '<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.lang(139)."</a>\n";
        }if (support('check')) {
            echo "<h3 id='checks'>".lang(140)."</h3>\n";
            $Ta = driver()->checkConstraints($a);
            if ($Ta) {
                echo "<table>\n";
                foreach ($Ta as $z => $X) {
                    echo "<tr title='".h($z)."'>","<td><code class='jush-".JUSH."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($z))."'>".lang(138).'</a>',"\n";
                }echo "</table>\n";
            }echo '<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.lang(141)."</a>\n";
        }
    }if (support(is_view($S) ? 'view_trigger' : 'trigger')) {
        echo "<h3 id='triggers'>".lang(142)."</h3>\n";
        $ni = triggers($a);
        if ($ni) {
            echo "<table>\n";
            foreach ($ni as $z => $X) {
                echo "<tr valign='top'><td>".h($X[0]).'<td>'.h($X[1]).'<th>'.h($z)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($z))."'>".lang(138)."</a>\n";
            }echo "</table>\n";
        }echo '<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.lang(143)."</a>\n";
    }$Ld = driver()->inheritedTables($a);
    if ($Ld) {
        echo "<h3 id='partitions'>".lang(144)."</h3>\n";
        $Qf = driver()->partitionsInfo($a);
        if ($Qf) {
            echo "<p><code class='jush-".JUSH."'>BY ".h("$Qf[partition_by]($Qf[partition])")."</code>\n";
        }tables_links($Ld);
    }
} elseif (isset($_GET['schema'])) {
    page_header(lang(61), '', [], h(DB.($_GET['ns'] ? ".$_GET[ns]" : '')));
    $Ih = [];
    $Jh = [];
    $da = ($_GET['schema'] ?: $_COOKIE['adminer_schema-'.str_replace('.', '_', DB)]);
    preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~', $da, $Ae, PREG_SET_ORDER);
    foreach ($Ae as $t => $C) {
        $Ih[$C[1]] = [$C[2], $C[3]];
        $Jh[] = "\n\t'".js_escape($C[1])."': [ $C[2], $C[3] ]";
    }$fi = 0;
    $Ea = -1;
    $Tg = [];
    $Dg = [];
    $pe = [];
    $pa = driver()->allFields();
    foreach (table_status('', true) as $R => $S) {
        if (is_view($S)) {
            continue;
        }$gg = 0;
        $Tg[$R]['fields'] = [];
        foreach ($pa[$R] as $m) {
            $gg += 1.25;
            $m['pos'] = $gg;
            $Tg[$R]['fields'][$m['field']] = $m;
        }$Tg[$R]['pos'] = ($Ih[$R] ?: [$fi, 0]);
        foreach (adminer()->foreignKeys($R) as $X) {
            if (! $X['db']) {
                $ne = $Ea;
                if (idx($Ih[$R], 1) || idx($Ih[$X['table']], 1)) {
                    $ne = min(idx($Ih[$R], 1, 0), idx($Ih[$X['table']], 1, 0)) - 1;
                } else {
                    $Ea -= .1;
                }while ($pe[(string) $ne]) {
                    $ne -= .0001;
                }$Tg[$R]['references'][$X['table']][(string) $ne] = [$X['source'], $X['target']];
                $Dg[$X['table']][$R][(string) $ne] = $X['target'];
                $pe[(string) $ne] = true;
            }
        }$fi = max($fi, $Tg[$R]['pos'][0] + 2.5 + $gg);
    }echo '<div id="schema" style="height: ',$fi,'em;">
<script',nonce(),'>
qs(\'#schema\').onselectstart = () => false;
const tablePos = {',implode(',', $Jh)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$fi,';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'',js_escape(DB),'\');
</script>
';
    foreach ($Tg as $E => $R) {
        echo "<div class='table' style='top: ".$R['pos'][0].'em; left: '.$R['pos'][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($E).'"><b>'.h($E).'</b></a>',script("qsl('div').onmousedown = schemaMousedown;");
        foreach ($R['fields'] as $m) {
            $X = '<span'.type_class($m['type']).' title="'.h($m['type'].($m['length'] ? "($m[length])" : '').($m['null'] ? ' NULL' : '')).'">'.h($m['field']).'</span>';
            echo '<br>'.($m['primary'] ? "<i>$X</i>" : $X);
        }foreach ((array) $R['references'] as $Qh => $Fg) {
            foreach ($Fg as $ne => $Ag) {
                $oe = $ne - idx($Ih[$E], 1);
                $t = 0;
                foreach ($Ag[0] as $oh) {
                    echo "\n<div class='references' title='".h($Qh)."' id='refs$ne-".($t++)."' style='left: $oe".'em; top: '.$R['fields'][$oh]['pos']."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: ".(-$oe)."em;'></div></div>";
                }
            }
        }foreach ((array) $Dg[$E] as $Qh => $Fg) {
            foreach ($Fg as $ne => $d) {
                $oe = $ne - idx($Ih[$E], 1);
                $t = 0;
                foreach ($d as $Ph) {
                    echo "\n<div class='references arrow' title='".h($Qh)."' id='refd$ne-".($t++)."' style='left: $oe".'em; top: '.$R['fields'][$Ph]['pos']."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$oe)."em;'></div>".'</div>';
                }
            }
        }echo "\n</div>\n";
    }foreach ($Tg as $E => $R) {
        foreach ((array) $R['references'] as $Qh => $Fg) {
            foreach ($Fg as $ne => $Ag) {
                $Re = $fi;
                $Ge = -10;
                foreach ($Ag[0] as $z => $oh) {
                    $hg = $R['pos'][0] + $R['fields'][$oh]['pos'];
                    $ig = $Tg[$Qh]['pos'][0] + $Tg[$Qh]['fields'][$Ag[1][$z]]['pos'];
                    $Re = min($Re, $hg, $ig);
                    $Ge = max($Ge, $hg, $ig);
                }echo "<div class='references' id='refl$ne' style='left: $ne"."em; top: $Re"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($Ge - $Re)."em;'></div></div>\n";
            }
        }
    }echo '</div>
<p class="links"><a href="',h(ME.'schema='.urlencode($da)),'" id="schema-link">',lang(145),'</a>
';
} elseif (isset($_GET['dump'])) {
    $a = $_GET['dump'];
    if ($_POST && ! $l) {
        save_settings(array_intersect_key($_POST, array_flip(['output', 'format', 'db_style', 'types', 'routines', 'events', 'table_style', 'auto_increment', 'triggers', 'data_style'])), 'adminer_export');
        $T = array_flip((array) $_POST['tables']) + array_flip((array) $_POST['data']);
        $Bc = dump_headers((count($T) == 1 ? key($T) : DB), (DB == '' || count($T) > 1));
        $Zd = preg_match('~sql~', $_POST['format']);
        if ($Zd) {
            echo '-- Adminer '.VERSION.' '.get_driver(DRIVER).' '.str_replace("\n", ' ', connection()->server_info)." dump\n\n";
            if (JUSH == 'sql') {
                echo "SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST['data_style'] ? "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
" : '').'
';
                connection()->query("SET time_zone = '+00:00'");
                connection()->query("SET sql_mode = ''");
            }
        }$Ah = $_POST['db_style'];
        $i = [DB];
        if (DB == '') {
            $i = $_POST['databases'];
            if (is_string($i)) {
                $i = explode("\n", rtrim(str_replace("\r", '', $i), "\n"));
            }
        }foreach ((array) $i as $j) {
            adminer()->dumpDatabase($j);
            if (connection()->select_db($j)) {
                if ($Zd) {
                    if ($Ah) {
                        echo use_sql($j, $Ah).";\n\n";
                    }$If = '';
                    if ($_POST['types']) {
                        foreach (types() as $u => $U) {
                            $qc = type_values($u);
                            if ($qc) {
                                $If
                                    .= ($Ah != 'DROP+CREATE' ? 'DROP TYPE IF EXISTS '.idf_escape($U).";;\n" : '').'CREATE TYPE '.idf_escape($U)." AS ENUM ($qc);\n\n";
                            } else {
                                $If
                                    .= "-- Could not export type $U\n\n";
                            }
                        }
                    }if ($_POST['routines']) {
                        foreach (routines() as $M) {
                            $E = $M['ROUTINE_NAME'];
                            $Og = $M['ROUTINE_TYPE'];
                            $h = create_routine($Og, ['name' => $E] + routine($M['SPECIFIC_NAME'], $Og));
                            set_utf8mb4($h);
                            $If
                                .= ($Ah != 'DROP+CREATE' ? "DROP $Og IF EXISTS ".idf_escape($E).";;\n" : '')."$h;\n\n";
                        }
                    }if ($_POST['events']) {
                        foreach (get_rows('SHOW EVENTS', null, '-- ') as $M) {
                            $h = remove_definer(get_val('SHOW CREATE EVENT '.idf_escape($M['Name']), 3));
                            set_utf8mb4($h);
                            $If
                                .= ($Ah != 'DROP+CREATE' ? 'DROP EVENT IF EXISTS '.idf_escape($M['Name']).";;\n" : '')."$h;;\n\n";
                        }
                    }echo $If && JUSH == 'sql' ? "DELIMITER ;;\n\n$If"."DELIMITER ;\n\n" : $If;
                }if ($_POST['table_style'] || $_POST['data_style']) {
                    $Ni = [];
                    foreach (table_status('', true) as $E => $S) {
                        $R = (DB == '' || in_array($E, (array) $_POST['tables']));
                        $Bb = (DB == '' || in_array($E, (array) $_POST['data']));
                        if ($R || $Bb) {
                            $ci = null;
                            if ($Bc == 'tar') {
                                $ci = new TmpFile;
                                ob_start([$ci, 'write'], 1e5);
                            }adminer()->dumpTable($E, ($R ? $_POST['table_style'] : ''), (is_view($S) ? 2 : 0));
                            if (is_view($S)) {
                                $Ni[] = $E;
                            } elseif ($Bb) {
                                $n = fields($E);
                                adminer()->dumpData($E, $_POST['data_style'], 'SELECT *'.convert_fields($n, $n).' FROM '.table($E));
                            }if ($Zd && $_POST['triggers'] && $R && ($ni = trigger_sql($E))) {
                                echo "\nDELIMITER ;;\n$ni\nDELIMITER ;\n";
                            }if ($Bc == 'tar') {
                                ob_end_flush();
                                tar_file((DB != '' ? '' : "$j/")."$E.csv", $ci);
                            } elseif ($Zd) {
                                echo "\n";
                            }
                        }
                    }if (function_exists('Adminer\foreign_keys_sql')) {
                        foreach (table_status('', true) as $E => $S) {
                            $R = (DB == '' || in_array($E, (array) $_POST['tables']));
                            if ($R && ! is_view($S)) {
                                echo foreign_keys_sql($E);
                            }
                        }
                    }foreach ($Ni as $Mi) {
                        adminer()->dumpTable($Mi, $_POST['table_style'], 1);
                    }if ($Bc == 'tar') {
                        echo pack('x512');
                    }
                }
            }
        }adminer()->dumpFooter();
        exit;
    }page_header(lang(67), $l, ($_GET['export'] != '' ? ['table' => $_GET['export']] : []), h(DB));
    echo '
<form action="" method="post">
<table class="layout">
';
    $Fb = ['', 'USE', 'DROP+CREATE', 'CREATE'];
    $Kh = ['', 'DROP+CREATE', 'CREATE'];
    $Cb = ['', 'TRUNCATE+INSERT', 'INSERT'];
    if (JUSH == 'sql') {
        $Cb[] = 'INSERT+UPDATE';
    }$M = get_settings('adminer_export');
    if (! $M) {
        $M = ['output' => 'text', 'format' => 'sql', 'db_style' => (DB != '' ? '' : 'CREATE'), 'table_style' => 'DROP+CREATE', 'data_style' => 'INSERT'];
    }if (! isset($M['events'])) {
        $M['routines'] = $M['events'] = ($_GET['dump'] == '');
        $M['triggers'] = $M['table_style'];
    }echo '<tr><th>'.lang(146).'<td>'.html_radios('output', adminer()->dumpOutput(), $M['output'])."\n",'<tr><th>'.lang(147).'<td>'.html_radios('format', adminer()->dumpFormat(), $M['format'])."\n",(JUSH == 'sqlite' ? '' : '<tr><th>'.lang(28).'<td>'.html_select('db_style', $Fb, $M['db_style']).(support('type') ? checkbox('types', 1, $M['types'], lang(6)) : '').(support('routine') ? checkbox('routines', 1, $M['routines'], lang(63)) : '').(support('event') ? checkbox('events', 1, $M['events'], lang(65)) : '')),'<tr><th>'.lang(123).'<td>'.html_select('table_style', $Kh, $M['table_style']).checkbox('auto_increment', 1, $M['auto_increment'], lang(42)).(support('trigger') ? checkbox('triggers', 1, $M['triggers'], lang(142)) : ''),'<tr><th>'.lang(148).'<td>'.html_select('data_style', $Cb, $M['data_style']),'</table>
<p><input type="submit" value="',lang(67),'">
',input_token(),'
<table>
',script("qsl('table').onclick = dumpClick;");
    $mg = [];
    if (DB != '') {
        $Ua = ($a != '' ? '' : ' checked');
        echo '<thead><tr>',"<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Ua>".lang(123).'</label>'.script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);", ''),"<th style='text-align: right;'><label class='block'>".lang(148)."<input type='checkbox' id='check-data'$Ua></label>".script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);", ''),"</thead>\n";
        $Ni = '';
        $Mh = tables_list();
        foreach ($Mh as $E => $U) {
            $lg = preg_replace('~_.*~', '', $E);
            $Ua = ($a == '' || $a == (substr($a, -1) == '%' ? "$lg%" : $E));
            $og = '<tr><td>'.checkbox('tables[]', $E, $Ua, $E, '', 'block');
            if ($U !== null && ! preg_match('~table~i', $U)) {
                $Ni
                    .= "$og\n";
            } else {
                echo "$og<td align='right'><label class='block'><span id='Rows-".h($E)."'></span>".checkbox('data[]', $E, $Ua)."</label>\n";
            }$mg[$lg]++;
        }echo $Ni;
        if ($Mh) {
            echo script("ajaxSetHtml('".js_escape(ME)."script=db');");
        }
    } else {
        echo "<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-databases'".($a == '' ? ' checked' : '').'>'.lang(28).'</label>',script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);", ''),"</thead>\n";
        $i = adminer()->databases();
        if ($i) {
            foreach ($i as $j) {
                if (! information_schema($j)) {
                    $lg = preg_replace('~_.*~', '', $j);
                    echo '<tr><td>'.checkbox('databases[]', $j, $a == '' || $a == "$lg%", $j, '', 'block')."\n";
                    $mg[$lg]++;
                }
            }
        } else {
            echo "<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";
        }
    }echo '</table>
</form>
';
    $Oc = true;
    foreach ($mg as $z => $X) {
        if ($z != '' && $X > 1) {
            echo ($Oc ? '<p>' : ' ')."<a href='".h(ME).'dump='.urlencode("$z%")."'>".h($z).'</a>';
            $Oc = false;
        }
    }
} elseif (isset($_GET['privileges'])) {
    page_header(lang(62));
    echo '<p class="links"><a href="'.h(ME).'user=">'.lang(149).'</a>';
    $K = connection()->query('SELECT User, Host FROM mysql.'.(DB == '' ? 'user' : 'db WHERE '.q(DB).' LIKE Db').' ORDER BY Host, User');
    $ed = $K;
    if (! $K) {
        $K = connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");
    }echo "<form action=''><p>\n";
    hidden_fields_get();
    echo input_hidden('db', DB),($ed ? '' : input_hidden('grant')),"<table class='odds'>\n",'<thead><tr><th>'.lang(26).'<th>'.lang(25)."<th></thead>\n";
    while ($M = $K->fetch_assoc()) {
        echo '<tr><td>'.h($M['User']).'<td>'.h($M['Host']).'<td><a href="'.h(ME.'user='.urlencode($M['User']).'&host='.urlencode($M['Host'])).'">'.lang(12)."</a>\n";
    }if (! $ed || DB != '') {
        echo "<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='".lang(12)."'>\n";
    }echo "</table>\n","</form>\n";
} elseif (isset($_GET['sql'])) {
    if (! $l && $_POST['export']) {
        save_settings(['output' => $_POST['output'], 'format' => $_POST['format']], 'adminer_import');
        dump_headers('sql');
        if ($_POST['format'] == 'sql') {
            echo "$_POST[query]\n";
        } else {
            adminer()->dumpTable('', '');
            adminer()->dumpData('', 'table', $_POST['query']);
            adminer()->dumpFooter();
        }exit;
    }restart_session();
    $td = &get_session('queries');
    $sd = &$td[DB];
    if (! $l && $_POST['clear']) {
        $sd = [];
        redirect(remove_from_uri('history'));
    }stop_session();
    page_header((isset($_GET['import']) ? lang(66) : lang(55)), $l);
    $ue = '--'.(JUSH == 'sql' ? ' ' : '');
    if (! $l && $_POST) {
        $q = false;
        if (! isset($_GET['import'])) {
            $J = $_POST['query'];
        } elseif ($_POST['webfile']) {
            $rh = adminer()->importServerPath();
            $q = @fopen((file_exists($rh) ? $rh : "compress.zlib://$rh.gz"), 'rb');
            $J = ($q ? fread($q, 1e6) : false);
        } else {
            $J = get_file('sql_file', true, ';');
        }if (is_string($J)) {
            if (function_exists('memory_get_usage') && ($Le = ini_bytes('memory_limit')) != '-1') {
                @ini_set('memory_limit', max($Le, strval(2 * strlen($J) + memory_get_usage() + 8e6)));
            }if ($J != '' && strlen($J) < 1e6) {
                $I = $J.(preg_match("~;[ \t\r\n]*\$~", $J) ? '' : ';');
                if (! $sd || first(end($sd)) != $I) {
                    restart_session();
                    $sd[] = [$I, time()];
                    set_session('queries', $td);
                    stop_session();
                }
            }$ph = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$ue)[^\n]*\n?|--\r?\n)";
            $Nb = ';';
            $jf = 0;
            $kc = true;
            $g = connect();
            if ($g && DB != '') {
                $g->select_db(DB);
                if ($_GET['ns'] != '') {
                    set_schema($_GET['ns'], $g);
                }
            }$gb = 0;
            $sc = [];
            $Of = '[\'"'.(JUSH == 'sql' ? '`#' : (JUSH == 'sqlite' ? '`[' : (JUSH == 'mssql' ? '[' : ''))).']|/\*|'.$ue.'|$'.(JUSH == 'pgsql' ? '|\$([a-zA-Z]\w*)?\$' : '');
            $gi = microtime(true);
            $ja = get_settings('adminer_import');
            while ($J != '') {
                if (! $jf && preg_match("~^$ph*+DELIMITER\\s+(\\S+)~i", $J, $C)) {
                    $Nb = preg_quote($C[1]);
                    $J = substr($J, strlen($C[0]));
                } elseif (! $jf && JUSH == 'pgsql' && preg_match("~^($ph*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i", $J, $C)) {
                    $Nb = "\n\\\\\\.\r?\n";
                    $jf = strlen($C[0]);
                } else {
                    preg_match("($Nb\\s*|$Of)", $J, $C, PREG_OFFSET_CAPTURE, $jf);
                    [$Xc, $gg] = $C[0];
                    if (! $Xc && $q && ! feof($q)) {
                        $J
                            .= fread($q, 1e5);
                    } else {
                        if (! $Xc && rtrim($J) == '') {
                            break;
                        }$jf = $gg + strlen($Xc);
                        if ($Xc && ! preg_match("(^$Nb)", $Xc)) {
                            $Na = driver()->hasCStyleEscapes() || (JUSH == 'pgsql' && ($gg > 0 && strtolower($J[$gg - 1]) == 'e'));
                            $Zf = ($Xc == '/*' ? '\*/' : ($Xc == '[' ? ']' : (preg_match("~^$ue|^#~", $Xc) ? "\n" : preg_quote($Xc).($Na ? '|\\\\.' : ''))));
                            while (preg_match("($Zf|\$)s", $J, $C, PREG_OFFSET_CAPTURE, $jf)) {
                                $Rg = $C[0][0];
                                if (! $Rg && $q && ! feof($q)) {
                                    $J
                                        .= fread($q, 1e5);
                                } else {
                                    $jf = $C[0][1] + strlen($Rg);
                                    if (! $Rg || $Rg[0] != '\\') {
                                        break;
                                    }
                                }
                            }
                        } else {
                            $kc = false;
                            $I = substr($J, 0, $gg + ($Nb[0] == "\n" ? 3 : 0));
                            $gb++;
                            $og = "<pre id='sql-$gb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($I)."</code></pre>\n";
                            if (JUSH == 'sqlite' && preg_match("~^$ph*+ATTACH\\b~i", $I, $C)) {
                                echo $og,"<p class='error'>".lang(150)."\n";
                                $sc[] = " <a href='#sql-$gb'>$gb</a>";
                                if ($_POST['error_stops']) {
                                    break;
                                }
                            } else {
                                if (! $_POST['only_errors']) {
                                    echo $og;
                                    ob_flush();
                                    flush();
                                }$vh = microtime(true);
                                if (connection()->multi_query($I) && $g && preg_match("~^$ph*+USE\\b~i", $I)) {
                                    $g->query($I);
                                }do {
                                    $K = connection()->store_result();
                                    if (connection()->error) {
                                        echo ($_POST['only_errors'] ? $og : ''),"<p class='error'>".lang(151).(connection()->errno ? ' ('.connection()->errno.')' : '').': '.error()."\n";
                                        $sc[] = " <a href='#sql-$gb'>$gb</a>";
                                        if ($_POST['error_stops']) {
                                            break 2;
                                        }
                                    } else {
                                        $Vh = " <span class='time'>(".format_time($vh).')</span>'.(strlen($I) < 1000 ? " <a href='".h(ME).'sql='.urlencode(trim($I))."'>".lang(12).'</a>' : '');
                                        $la = connection()->affected_rows;
                                        $Qi = ($_POST['only_errors'] ? '' : driver()->warnings());
                                        $Ri = "warnings-$gb";
                                        if ($Qi) {
                                            $Vh
                                                .= ", <a href='#$Ri'>".lang(37).'</a>'.script("qsl('a').onclick = partial(toggle, '$Ri');", '');
                                        }$_c = null;
                                        $Bf = null;
                                        $Ac = "explain-$gb";
                                        if (is_object($K)) {
                                            $_ = $_POST['limit'];
                                            $Bf = print_select_result($K, $g, [], $_);
                                            if (! $_POST['only_errors']) {
                                                echo "<form action='' method='post'>\n";
                                                $hf = $K->num_rows;
                                                echo "<p class='sql-footer'>".($hf ? ($_ && $hf > $_ ? lang(152, $_) : '').lang(153, $hf) : ''),$Vh;
                                                if ($g && preg_match("~^($ph|\\()*+SELECT\\b~i", $I) && ($_c = explain($g, $I))) {
                                                    echo ", <a href='#$Ac'>Explain</a>".script("qsl('a').onclick = partial(toggle, '$Ac');", '');
                                                }$u = "export-$gb";
                                                echo ", <a href='#$u'>".lang(67).'</a>'.script("qsl('a').onclick = partial(toggle, '$u');", '')."<span id='$u' class='hidden'>: ".html_select('output', adminer()->dumpOutput(), $ja['output']).' '.html_select('format', adminer()->dumpFormat(), $ja['format']).input_hidden('query', $I)."<input type='submit' name='export' value='".lang(67)."'>".input_token()."</span>\n"."</form>\n";
                                            }
                                        } else {
                                            if (preg_match("~^$ph*+(CREATE|DROP|ALTER)$ph++(DATABASE|SCHEMA)\\b~i", $I)) {
                                                restart_session();
                                                set_session('dbs', null);
                                                stop_session();
                                            }if (! $_POST['only_errors']) {
                                                echo "<p class='message' title='".h(connection()->info)."'>".lang(154, $la)."$Vh\n";
                                            }
                                        }echo $Qi ? "<div id='$Ri' class='hidden'>\n$Qi</div>\n" : '';
                                        if ($_c) {
                                            echo "<div id='$Ac' class='hidden explain'>\n";
                                            print_select_result($_c, $g, $Bf);
                                            echo "</div>\n";
                                        }
                                    }$vh = microtime(true);
                                } while (connection()->next_result());
                            }$J = substr($J, $jf);
                            $jf = 0;
                        }
                    }
                }
            }if ($kc) {
                echo "<p class='message'>".lang(155)."\n";
            } elseif ($_POST['only_errors']) {
                echo "<p class='message'>".lang(156, $gb - count($sc))," <span class='time'>(".format_time($gi).")</span>\n";
            } elseif ($sc && $gb > 1) {
                echo "<p class='error'>".lang(151).': '.implode('', $sc)."\n";
            }
        } else {
            echo "<p class='error'>".upload_error($J)."\n";
        }
    }echo '
<form action="" method="post" enctype="multipart/form-data" id="form">
';
    $yc = "<input type='submit' value='".lang(157)."' title='Ctrl+Enter'>";
    if (! isset($_GET['import'])) {
        $I = $_GET['sql'];
        if ($_POST) {
            $I = $_POST['query'];
        } elseif ($_GET['history'] == 'all') {
            $I = $sd;
        } elseif ($_GET['history'] != '') {
            $I = idx($sd[$_GET['history']], 0);
        }echo '<p>';
        textarea('query', $I, 20);
        echo script(($_POST ? '' : "qs('textarea').focus();\n")."qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '".js_escape(remove_from_uri('sql|limit|error_stops|only_errors|history'))."');"),'<p>';
        adminer()->sqlPrintAfter();
        echo "$yc\n",lang(158).": <input type='number' name='limit' class='size' value='".h($_POST ? $_POST['limit'] : $_GET['limit'])."'>\n";
    } else {
        $jd = (extension_loaded('zlib') ? '[.gz]' : '');
        echo '<fieldset><legend>'.lang(159).'</legend><div>',file_input("SQL$jd: <input type='file' name='sql_file[]' multiple>\n$yc"),"</div></fieldset>\n";
        $Cd = adminer()->importServerPath();
        if ($Cd) {
            echo '<fieldset><legend>'.lang(160).'</legend><div>',lang(161, '<code>'.h($Cd)."$jd</code>"),' <input type="submit" name="webfile" value="'.lang(162).'">',"</div></fieldset>\n";
        }echo '<p>';
    }echo checkbox('error_stops', 1, ($_POST ? $_POST['error_stops'] : isset($_GET['import']) || $_GET['error_stops']), lang(163))."\n",checkbox('only_errors', 1, ($_POST ? $_POST['only_errors'] : isset($_GET['import']) || $_GET['only_errors']), lang(164))."\n",input_token();
    if (! isset($_GET['import']) && $sd) {
        print_fieldset('history', lang(165), $_GET['history'] != '');
        for ($X = end($sd); $X; $X = prev($sd)) {
            $z = key($sd);
            [$I, $Vh, $fc] = $X;
            echo '<a href="'.h(ME."sql=&history=$z").'">'.lang(12).'</a>'." <span class='time' title='".@date('Y-m-d', $Vh)."'>".@date('H:i:s', $Vh).'</span>'." <code class='jush-".JUSH."'>".shorten_utf8(ltrim(str_replace("\n", ' ', str_replace("\r", '', preg_replace("~^(#|$ue).*~m", '', $I)))), 80, '</code>').($fc ? " <span class='time'>($fc)</span>" : '')."<br>\n";
        }echo "<input type='submit' name='clear' value='".lang(166)."'>\n","<a href='".h(ME.'sql=&history=all')."'>".lang(167)."</a>\n","</div></fieldset>\n";
    }echo '</form>
';
} elseif (isset($_GET['edit'])) {
    $a = $_GET['edit'];
    $n = fields($a);
    $Z = (isset($_GET['select']) ? ($_POST['check'] && count($_POST['check']) == 1 ? where_check($_POST['check'][0], $n) : '') : where($_GET, $n));
    $yi = (isset($_GET['select']) ? $_POST['edit'] : $Z);
    foreach ($n as $E => $m) {
        if (! isset($m['privileges'][$yi ? 'update' : 'insert']) || adminer()->fieldName($m) == '' || $m['generated']) {
            unset($n[$E]);
        }
    }if ($_POST && ! $l && ! isset($_GET['select'])) {
        $B = $_POST['referer'];
        if ($_POST['insert']) {
            $B = ($yi ? null : $_SERVER['REQUEST_URI']);
        } elseif (! preg_match('~^.+&select=.+$~', $B)) {
            $B = ME.'select='.urlencode($a);
        }$x = indexes($a);
        $ti = unique_array($_GET['where'], $x);
        $xg = "\nWHERE $Z";
        if (isset($_POST['delete'])) {
            queries_redirect($B, lang(168), driver()->delete($a, $xg, $ti ? 0 : 1));
        } else {
            $Q = [];
            foreach ($n as $E => $m) {
                $X = process_input($m);
                if ($X !== false && $X !== null) {
                    $Q[idf_escape($E)] = $X;
                }
            }if ($yi) {
                if (! $Q) {
                    redirect($B);
                }queries_redirect($B, lang(169), driver()->update($a, $Q, $xg, $ti ? 0 : 1));
                if (is_ajax()) {
                    page_headers();
                    page_messages($l);
                    exit;
                }
            } else {
                $K = driver()->insert($a, $Q);
                $me = ($K ? last_id($K) : 0);
                queries_redirect($B, lang(170, ($me ? " $me" : '')), $K);
            }
        }
    }$M = null;
    if ($_POST['save']) {
        $M = (array) $_POST['fields'];
    } elseif ($Z) {
        $O = [];
        foreach ($n as $E => $m) {
            if (isset($m['privileges']['select'])) {
                $ua = ($_POST['clone'] && $m['auto_increment'] ? "''" : convert_field($m));
                $O[] = ($ua ? "$ua AS " : '').idf_escape($E);
            }
        }$M = [];
        if (! support('table')) {
            $O = ['*'];
        }if ($O) {
            $K = driver()->select($a, $O, [$Z], $O, [], (isset($_GET['select']) ? 2 : 1));
            if (! $K) {
                $l = error();
            } else {
                $M = $K->fetch_assoc();
                if (! $M) {
                    $M = false;
                }
            }if (isset($_GET['select']) && (! $M || $K->fetch_assoc())) {
                $M = null;
            }
        }
    }if (! support('table') && ! $n) {
        if (! $Z) {
            $K = driver()->select($a, ['*'], [], ['*']);
            $M = ($K ? $K->fetch_assoc() : false);
            if (! $M) {
                $M = [driver()->primary => ''];
            }
        }if ($M) {
            foreach ($M as $z => $X) {
                if (! $Z) {
                    $M[$z] = null;
                }$n[$z] = ['field' => $z, 'null' => ($z != driver()->primary), 'auto_increment' => ($z == driver()->primary)];
            }
        }
    }edit_form($a, $n, $M, $yi, $l);
} elseif (isset($_GET['create'])) {
    $a = $_GET['create'];
    $Sf = driver()->partitionBy;
    $Wf = ($Sf ? driver()->partitionsInfo($a) : []);
    $Cg = referencable_primary($a);
    $Vc = [];
    foreach ($Cg as $Hh => $m) {
        $Vc[str_replace('`', '``', $Hh).'`'.str_replace('`', '``', $m['field'])] = $Hh;
    }$Ef = [];
    $S = [];
    if ($a != '') {
        $Ef = fields($a);
        $S = table_status1($a);
        if (count($S) < 2) {
            $l = lang(11);
        }
    }$M = $_POST;
    $M['fields'] = (array) $M['fields'];
    if ($M['auto_increment_col']) {
        $M['fields'][$M['auto_increment_col']]['auto_increment'] = true;
    }if ($_POST) {
        save_settings(['comments' => $_POST['comments'], 'defaults' => $_POST['defaults']]);
    }if ($_POST && ! process_fields($M['fields']) && ! $l) {
        if ($_POST['drop']) {
            queries_redirect(substr(ME, 0, -1), lang(171), drop_tables([$a]));
        } else {
            $n = [];
            $pa = [];
            $Di = false;
            $Tc = [];
            $Df = reset($Ef);
            $na = ' FIRST';
            foreach ($M['fields'] as $z => $m) {
                $p = $Vc[$m['type']];
                $oi = ($p !== null ? $Cg[$p] : $m);
                if ($m['field'] != '') {
                    if (! $m['generated']) {
                        $m['default'] = null;
                    }$tg = process_field($m, $oi);
                    $pa[] = [$m['orig'], $tg, $na];
                    if (! $Df || $tg !== process_field($Df, $Df)) {
                        $n[] = [$m['orig'], $tg, $na];
                        if ($m['orig'] != '' || $na) {
                            $Di = true;
                        }
                    }if ($p !== null) {
                        $Tc[idf_escape($m['field'])] = ($a != '' && JUSH != 'sqlite' ? 'ADD' : ' ').format_foreign_key(['table' => $Vc[$m['type']], 'source' => [$m['field']], 'target' => [$oi['field']], 'on_delete' => $m['on_delete']]);
                    }$na = ' AFTER '.idf_escape($m['field']);
                } elseif ($m['orig'] != '') {
                    $Di = true;
                    $n[] = [$m['orig']];
                }if ($m['orig'] != '') {
                    $Df = next($Ef);
                    if (! $Df) {
                        $na = '';
                    }
                }
            }$Uf = [];
            if (in_array($M['partition_by'], $Sf)) {
                foreach ($M as $z => $X) {
                    if (preg_match('~^partition~', $z)) {
                        $Uf[$z] = $X;
                    }
                }foreach ($Uf['partition_names'] as $z => $E) {
                    if ($E == '') {
                        unset($Uf['partition_names'][$z]);
                        unset($Uf['partition_values'][$z]);
                    }
                }$Uf['partition_names'] = array_values($Uf['partition_names']);
                $Uf['partition_values'] = array_values($Uf['partition_values']);
                if ($Uf == $Wf) {
                    $Uf = [];
                }
            } elseif (preg_match('~partitioned~', $S['Create_options'])) {
                $Uf = null;
            }$D = lang(172);
            if ($a == '') {
                cookie('adminer_engine', $M['Engine']);
                $D = lang(173);
            }$E = trim($M['name']);
            queries_redirect(ME.(support('table') ? 'table=' : 'select=').urlencode($E), $D, alter_table($a, $E, (JUSH == 'sqlite' && ($Di || $Tc) ? $pa : $n), $Tc, ($M['Comment'] != $S['Comment'] ? $M['Comment'] : null), ($M['Engine'] && $M['Engine'] != $S['Engine'] ? $M['Engine'] : ''), ($M['Collation'] && $M['Collation'] != $S['Collation'] ? $M['Collation'] : ''), ($M['Auto_increment'] != '' ? number($M['Auto_increment']) : ''), $Uf));
        }
    }page_header(($a != '' ? lang(34) : lang(68)), $l, ['table' => $a], h($a));
    if (! $_POST) {
        $qi = driver()->types();
        $M = ['Engine' => $_COOKIE['adminer_engine'], 'fields' => [['field' => '', 'type' => (isset($qi['int']) ? 'int' : (isset($qi['integer']) ? 'integer' : '')), 'on_update' => '']], 'partition_names' => ['']];
        if ($a != '') {
            $M = $S;
            $M['name'] = $a;
            $M['fields'] = [];
            if (! $_GET['auto_increment']) {
                $M['Auto_increment'] = '';
            }foreach ($Ef as $m) {
                $m['generated'] = $m['generated'] ?: (isset($m['default']) ? 'DEFAULT' : '');
                $M['fields'][] = $m;
            }if ($Sf) {
                $M += $Wf;
                $M['partition_names'][] = '';
                $M['partition_values'][] = '';
            }
        }
    }$b = collations();
    if (is_array(reset($b))) {
        $b = call_user_func_array('array_merge', array_values($b));
    }$mc = driver()->engines();
    foreach ($mc as $lc) {
        if (! strcasecmp($lc, $M['Engine'])) {
            $M['Engine'] = $lc;
            break;
        }
    }echo '
<form action="" method="post" id="form">
<p>
';
    if (support('columns') || $a == '') {
        echo lang(174).": <input name='name'".($a == '' && ! $_POST ? ' autofocus' : '')." data-maxlength='64' value='".h($M['name'])."' autocapitalize='off'>\n",($mc ? html_select('Engine', ['' => '('.lang(175).')'] + $mc, $M['Engine']).on_help('event.target.value', 1).script("qsl('select').onchange = helpClose;")."\n" : '');
        if ($b) {
            echo "<datalist id='collations'>".optionlist($b)."</datalist>\n",(preg_match('~sqlite|mssql~', JUSH) ? '' : "<input list='collations' name='Collation' value='".h($M['Collation'])."' placeholder='(".lang(101).")'>\n");
        }echo "<input type='submit' value='".lang(16)."'>\n";
    }if (support('columns')) {
        echo "<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";
        edit_fields($M['fields'], $b, 'TABLE', $Vc);
        echo "</table>\n",script('editFields();'),"</div>\n<p>\n",lang(42).": <input type='number' name='Auto_increment' class='size' value='".h($M['Auto_increment'])."'>\n",checkbox('defaults', 1, ($_POST ? $_POST['defaults'] : get_setting('defaults')), lang(176), 'columnShow(this.checked, 5)', 'jsonly');
        $jb = ($_POST ? $_POST['comments'] : get_setting('comments'));
        echo (support('comment') ? checkbox('comments', 1, $jb, lang(41), 'editingCommentsClick(this, true);', 'jsonly').' '.(preg_match('~\n~', $M['Comment']) ? "<textarea name='Comment' rows='2' cols='20'".($jb ? '' : " class='hidden'").'>'.h($M['Comment']).'</textarea>' : '<input name="Comment" value="'.h($M['Comment']).'" data-maxlength="'.(min_version(5.5) ? 2048 : 60).'"'.($jb ? '' : " class='hidden'").'>') : ''),'<p>
<input type="submit" value="',lang(16),'">
';
    }echo '
';
    if ($a != '') {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, $a));
    }if ($Sf && (JUSH == 'sql' || $a == '')) {
        $Tf = preg_match('~RANGE|LIST~', $M['partition_by']);
        print_fieldset('partition', lang(178), $M['partition_by']);
        echo '<p>'.html_select('partition_by', array_merge([''], $Sf), $M['partition_by']).on_help("event.target.value.replace(/./, 'PARTITION BY \$&')", 1).script("qsl('select').onchange = partitionByChange;"),"(<input name='partition' value='".h($M['partition'])."'>)\n",lang(179).": <input type='number' name='partitions' class='size".($Tf || ! $M['partition_by'] ? ' hidden' : '')."' value='".h($M['partitions'])."'>\n","<table id='partition-table'".($Tf ? '' : " class='hidden'").">\n",'<thead><tr><th>'.lang(180).'<th>'.lang(181)."</thead>\n";
        foreach ($M['partition_names'] as $z => $X) {
            echo '<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off">',($z == count($M['partition_names']) - 1 ? script("qsl('input').oninput = partitionNameChange;") : ''),'<td><input name="partition_values[]" value="'.h(idx($M['partition_values'], $z)).'">';
        }echo "</table>\n</div></fieldset>\n";
    }echo input_token(),'</form>
';
} elseif (isset($_GET['indexes'])) {
    $a = $_GET['indexes'];
    $Jd = ['PRIMARY', 'UNIQUE', 'INDEX'];
    $S = table_status1($a, true);
    $Hd = driver()->indexAlgorithms($S);
    if (preg_match('~MyISAM|M?aria'.(min_version(5.6, '10.0.5') ? '|InnoDB' : '').'~i', $S['Engine'])) {
        $Jd[] = 'FULLTEXT';
    }if (preg_match('~MyISAM|M?aria'.(min_version(5.7, '10.2.2') ? '|InnoDB' : '').'~i', $S['Engine'])) {
        $Jd[] = 'SPATIAL';
    }$x = indexes($a);
    $n = fields($a);
    $ng = [];
    if (JUSH == 'mongo') {
        $ng = $x['_id_'];
        unset($Jd[0]);
        unset($x['_id_']);
    }$M = $_POST;
    if ($M) {
        save_settings(['index_options' => $M['options']]);
    }if ($_POST && ! $l && ! $_POST['add'] && ! $_POST['drop_col']) {
        $qa = [];
        foreach ($M['indexes'] as $w) {
            $E = $w['name'];
            if (in_array($w['type'], $Jd)) {
                $d = [];
                $se = [];
                $Qb = [];
                $Id = (support('partial_indexes') ? $w['partial'] : '');
                $Gd = (in_array($w['algorithm'], $Hd) ? $w['algorithm'] : '');
                $Q = [];
                ksort($w['columns']);
                foreach ($w['columns'] as $z => $c) {
                    if ($c != '') {
                        $re = idx($w['lengths'], $z);
                        $Ob = idx($w['descs'], $z);
                        $Q[] = ($n[$c] ? idf_escape($c) : $c).($re ? '('.(+$re).')' : '').($Ob ? ' DESC' : '');
                        $d[] = $c;
                        $se[] = ($re ?: null);
                        $Qb[] = $Ob;
                    }
                }$zc = $x[$E];
                if ($zc) {
                    ksort($zc['columns']);
                    ksort($zc['lengths']);
                    ksort($zc['descs']);
                    if ($w['type'] == $zc['type'] && array_values($zc['columns']) === $d && (! $zc['lengths'] || array_values($zc['lengths']) === $se) && array_values($zc['descs']) === $Qb && $zc['partial'] == $Id && (! $Hd || $zc['algorithm'] == $Gd)) {
                        unset($x[$E]);

                        continue;
                    }
                }if ($d) {
                    $qa[] = [$w['type'], $E, $Q, $Gd, $Id];
                }
            }
        }foreach ($x as $E => $zc) {
            $qa[] = [$zc['type'], $E, 'DROP'];
        }if (! $qa) {
            redirect(ME.'table='.urlencode($a));
        }queries_redirect(ME.'table='.urlencode($a), lang(182), alter_indexes($a, $qa));
    }page_header(lang(134), $l, ['table' => $a], h($a));
    $Lc = array_keys($n);
    if ($_POST['add']) {
        foreach ($M['indexes'] as $z => $w) {
            if ($w['columns'][count($w['columns'])] != '') {
                $M['indexes'][$z]['columns'][] = '';
            }
        }$w = end($M['indexes']);
        if ($w['type'] || array_filter($w['columns'], 'strlen')) {
            $M['indexes'][] = ['columns' => [1 => '']];
        }
    }if (! $M) {
        foreach ($x as $z => $w) {
            $x[$z]['name'] = $z;
            $x[$z]['columns'][] = '';
        }$x[] = ['columns' => [1 => '']];
        $M['indexes'] = $x;
    }$se = (JUSH == 'sql' || JUSH == 'mssql');
    $jh = ($_POST ? $_POST['options'] : get_setting('index_options'));
    echo '
<form action="" method="post">
<div class="scrollable">
<table class="nowrap">
<thead><tr>
<th id="label-type">',lang(183);
    $Ad = " class='idxopts".($jh ? '' : ' hidden')."'";
    if ($Hd) {
        echo "<th id='label-algorithm'$Ad>".lang(184).doc_link(['sql' => 'create-index.html#create-index-storage-engine-index-types', 'mariadb' => 'storage-engine-index-types/']);
    }echo '<th><input type="submit" class="wayoff">',lang(185).($se ? "<span$Ad> (".lang(186).')</span>' : '');
    if ($se || support('descidx')) {
        echo checkbox('options', 1, $jh, lang(107), 'indexOptionsShow(this.checked)', 'jsonly')."\n";
    }echo '<th id="label-name">',lang(187);
    if (support('partial_indexes')) {
        echo "<th id='label-condition'$Ad>".lang(188);
    }echo '<th><noscript>',icon('plus', 'add[0]', '+', lang(108)),'</noscript>
</thead>
';
    if ($ng) {
        echo '<tr><td>PRIMARY<td>';
        foreach ($ng['columns'] as $z => $c) {
            echo select_input(' disabled', $Lc, $c),"<label><input disabled type='checkbox'>".lang(50).'</label> ';
        }echo "<td><td>\n";
    }$y = 1;
    foreach ($M['indexes'] as $w) {
        if (! $_POST['drop_col'] || $y != key($_POST['drop_col'])) {
            echo '<tr><td>'.html_select("indexes[$y][type]", [-1 => ''] + $Jd, $w['type'], ($y == count($M['indexes']) ? 'indexesAddRow.call(this);' : ''), 'label-type');
            if ($Hd) {
                echo "<td$Ad>".html_select("indexes[$y][algorithm]", array_merge([''], $Hd), $w['algorithm'], 'label-algorithm');
            }echo '<td>';
            ksort($w['columns']);
            $t = 1;
            foreach ($w['columns'] as $z => $c) {
                echo '<span>'.select_input(" name='indexes[$y][columns][$t]' title='".lang(39)."'", ($n && ($c == '' || $n[$c]) ? array_combine($Lc, $Lc) : []), $c, 'partial('.($t == count($w['columns']) ? 'indexesAddColumn' : 'indexesChangeColumn').", '".js_escape(JUSH == 'sql' ? '' : $_GET['indexes'].'_')."')"),"<span$Ad>",($se ? "<input type='number' name='indexes[$y][lengths][$t]' class='size' value='".h(idx($w['lengths'], $z))."' title='".lang(106)."'>" : ''),(support('descidx') ? checkbox("indexes[$y][descs][$t]", 1, idx($w['descs'], $z), lang(50)) : ''),'</span> </span>';
                $t++;
            }echo "<td><input name='indexes[$y][name]' value='".h($w['name'])."' autocapitalize='off' aria-labelledby='label-name'>\n";
            if (support('partial_indexes')) {
                echo "<td$Ad><input name='indexes[$y][partial]' value='".h($w['partial'])."' autocapitalize='off' aria-labelledby='label-condition'>\n";
            }echo '<td>'.icon('cross', "drop_col[$y]", 'x', lang(111)).script("qsl('button').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");
        }$y++;
    }echo '</table>
</div>
<p>
<input type="submit" value="',lang(16),'">
',input_token(),'</form>
';
} elseif (isset($_GET['database'])) {
    $M = $_POST;
    if ($_POST && ! $l && ! $_POST['add']) {
        $E = trim($M['name']);
        if ($_POST['drop']) {
            $_GET['db'] = '';
            queries_redirect(remove_from_uri('db|database'), lang(189), drop_databases([DB]));
        } elseif ($E !== DB) {
            if (DB != '') {
                $_GET['db'] = $E;
                queries_redirect(preg_replace('~\bdb=[^&]*&~', '', ME).'db='.urlencode($E), lang(190), rename_database($E, $M['collation']));
            } else {
                $i = explode("\n", str_replace("\r", '', $E));
                $Bh = true;
                $le = '';
                foreach ($i as $j) {
                    if (count($i) == 1 || $j != '') {
                        if (! create_database($j, $M['collation'])) {
                            $Bh = false;
                        }$le = $j;
                    }
                }restart_session();
                set_session('dbs', null);
                queries_redirect(ME.'db='.urlencode($le), lang(191), $Bh);
            }
        } else {
            if (! $M['collation']) {
                redirect(substr(ME, 0, -1));
            }query_redirect('ALTER DATABASE '.idf_escape($E).(preg_match('~^[a-z0-9_]+$~i', $M['collation']) ? " COLLATE $M[collation]" : ''), substr(ME, 0, -1), lang(192));
        }
    }page_header(DB != '' ? lang(58) : lang(115), $l, [], h(DB));
    $b = collations();
    $E = DB;
    if ($_POST) {
        $E = $M['name'];
    } elseif (DB != '') {
        $M['collation'] = db_collation(DB, $b);
    } elseif (JUSH == 'sql') {
        foreach (get_vals('SHOW GRANTS') as $ed) {
            if (preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~', $ed, $C) && $C[1]) {
                $E = stripcslashes(idf_unescape("`$C[2]`"));
                break;
            }
        }
    }echo '
<form action="" method="post">
<p>
',($_POST['add'] || strpos($E, "\n") ? '<textarea autofocus name="name" rows="10" cols="40">'.h($E).'</textarea><br>' : '<input name="name" autofocus value="'.h($E).'" data-maxlength="64" autocapitalize="off">')."\n".($b ? html_select('collation', ['' => '('.lang(101).')'] + $b, $M['collation']).doc_link(['sql' => 'charset-charsets.html', 'mariadb' => 'supported-character-sets-and-collations/']) : ''),'<input type="submit" value="',lang(16),'">
';
    if (DB != '') {
        echo "<input type='submit' name='drop' value='".lang(127)."'>".confirm(lang(177, DB))."\n";
    } elseif (! $_POST['add'] && $_GET['db'] == '') {
        echo icon('plus', 'add[0]', '+', lang(108))."\n";
    }echo input_token(),'</form>
';
} elseif (isset($_GET['call'])) {
    $ca = ($_GET['name'] ?: $_GET['call']);
    page_header(lang(193).': '.h($ca), $l);
    $Og = routine($_GET['call'], (isset($_GET['callf']) ? 'FUNCTION' : 'PROCEDURE'));
    $Dd = [];
    $If = [];
    foreach ($Og['fields'] as $t => $m) {
        if (substr($m['inout'], -3) == 'OUT' && JUSH == 'sql') {
            $If[$t] = '@'.idf_escape($m['field']).' AS '.idf_escape($m['field']);
        }if (! $m['inout'] || substr($m['inout'], 0, 2) == 'IN') {
            $Dd[] = $t;
        }
    }if (! $l && $_POST) {
        $Oa = [];
        foreach ($Og['fields'] as $z => $m) {
            $X = '';
            if (in_array($z, $Dd)) {
                $X = process_input($m);
                if ($X === false) {
                    $X = "''";
                }if (isset($If[$z])) {
                    connection()->query('SET @'.idf_escape($m['field'])." = $X");
                }
            }if (isset($If[$z])) {
                $Oa[] = '@'.idf_escape($m['field']);
            } elseif (in_array($z, $Dd)) {
                $Oa[] = $X;
            }
        }$J = (isset($_GET['callf']) ? 'SELECT ' : 'CALL ').($Og['returns']['type'] == 'record' ? '* FROM ' : '').table($ca).'('.implode(', ', $Oa).')';
        $vh = microtime(true);
        $K = connection()->multi_query($J);
        $la = connection()->affected_rows;
        echo adminer()->selectQuery($J, $vh, ! $K);
        if (! $K) {
            echo "<p class='error'>".error()."\n";
        } else {
            $g = connect();
            if ($g) {
                $g->select_db(DB);
            }do {
                $K = connection()->store_result();
                if (is_object($K)) {
                    print_select_result($K, $g);
                } else {
                    echo "<p class='message'>".lang(194, $la)." <span class='time'>".@date('H:i:s')."</span>\n";
                }
            } while (connection()->next_result());
            if ($If) {
                print_select_result(connection()->query('SELECT '.implode(', ', $If)));
            }
        }
    }echo '
<form action="" method="post">
';
    if ($Dd) {
        echo "<table class='layout'>\n";
        foreach ($Dd as $z) {
            $m = $Og['fields'][$z];
            $E = $m['field'];
            echo '<tr><th>'.adminer()->fieldName($m);
            $Y = idx($_POST['fields'], $E);
            if ($Y != '') {
                if ($m['type'] == 'set') {
                    $Y = implode(',', $Y);
                }
            }input($m, $Y, idx($_POST['function'], $E, ''));
            echo "\n";
        }echo "</table>\n";
    }echo '<p>
<input type="submit" value="',lang(193),'">
',input_token(),'</form>

<pre>
';
    function pre_tr($Rg)
    {
        return preg_replace('~^~m', '<tr>', preg_replace('~\|~', '<td>', preg_replace('~\|$~m', '', rtrim($Rg))));
    }$R = '(\+--[-+]+\+\n)';
    $M = '(\| .* \|\n)';
    echo preg_replace_callback("~^$R?$M$R?($M*)$R?~m", function ($C) {
        $Pc = pre_tr($C[2]);

        return "<table>\n".($C[1] ? "<thead>$Pc</thead>\n" : $Pc).pre_tr($C[4])."\n</table>";
    }, preg_replace('~(\n(    -|mysql)&gt; )(.+)~', "\\1<code class='jush-sql'>\\3</code>", preg_replace('~(.+)\n---+\n~', "<b>\\1</b>\n", h($Og['comment']))));
    echo '</pre>
';
} elseif (isset($_GET['foreign'])) {
    $a = $_GET['foreign'];
    $E = $_GET['name'];
    $M = $_POST;
    if ($_POST && ! $l && ! $_POST['add'] && ! $_POST['change'] && ! $_POST['change-js']) {
        if (! $_POST['drop']) {
            $M['source'] = array_filter($M['source'], 'strlen');
            ksort($M['source']);
            $Ph = [];
            foreach ($M['source'] as $z => $X) {
                $Ph[$z] = $M['target'][$z];
            }$M['target'] = $Ph;
        }if (JUSH == 'sqlite') {
            $K = recreate_table($a, $a, [], [], [" $E" => ($M['drop'] ? '' : ' '.format_foreign_key($M))]);
        } else {
            $qa = 'ALTER TABLE '.table($a);
            $K = ($E == '' || queries("$qa DROP ".(JUSH == 'sql' ? 'FOREIGN KEY ' : 'CONSTRAINT ').idf_escape($E)));
            if (! $M['drop']) {
                $K = queries("$qa ADD".format_foreign_key($M));
            }
        }queries_redirect(ME.'table='.urlencode($a), ($M['drop'] ? lang(195) : ($E != '' ? lang(196) : lang(197))), $K);
        if (! $M['drop']) {
            $l = lang(198);
        }
    }page_header(lang(199), $l, ['table' => $a], h($a));
    if ($_POST) {
        ksort($M['source']);
        if ($_POST['add']) {
            $M['source'][] = '';
        } elseif ($_POST['change'] || $_POST['change-js']) {
            $M['target'] = [];
        }
    } elseif ($E != '') {
        $Vc = foreign_keys($a);
        $M = $Vc[$E];
        $M['source'][] = '';
    } else {
        $M['table'] = $a;
        $M['source'] = [''];
    }echo '
<form action="" method="post">
';
    $oh = array_keys(fields($a));
    if ($M['db'] != '') {
        connection()->select_db($M['db']);
    }if ($M['ns'] != '') {
        $Ff = get_schema();
        set_schema($M['ns']);
    }$Bg = array_keys(array_filter(table_status('', true), 'Adminer\fk_support'));
    $Ph = array_keys(fields(in_array($M['table'], $Bg) ? $M['table'] : reset($Bg)));
    $rf = "this.form['change-js'].value = '1'; this.form.submit();";
    echo '<p><label>'.lang(200).': '.html_select('table', $Bg, $M['table'], $rf)."</label>\n";
    if (JUSH != 'sqlite') {
        $Gb = [];
        foreach (adminer()->databases() as $j) {
            if (! information_schema($j)) {
                $Gb[] = $j;
            }
        }echo '<label>'.lang(69).': '.html_select('db', $Gb, $M['db'] != '' ? $M['db'] : $_GET['db'], $rf).'</label>';
    }echo input_hidden('change-js'),'<noscript><p><input type="submit" name="change" value="',lang(201),'"></noscript>
<table>
<thead><tr><th id="label-source">',lang(136),'<th id="label-target">',lang(137),'</thead>
';
    $y = 0;
    foreach ($M['source'] as $z => $X) {
        echo '<tr>','<td>'.html_select('source['.(+$z).']', [-1 => ''] + $oh, $X, ($y == count($M['source']) - 1 ? 'foreignAddRow.call(this);' : ''), 'label-source'),'<td>'.html_select('target['.(+$z).']', $Ph, idx($M['target'], $z), '', 'label-target');
        $y++;
    }echo '</table>
<p>
<label>',lang(103),': ',html_select('on_delete', [-1 => ''] + explode('|', driver()->onActions), $M['on_delete']),'</label>
<label>',lang(102),': ',html_select('on_update', [-1 => ''] + explode('|', driver()->onActions), $M['on_update']),'</label>
',doc_link(['sql' => 'innodb-foreign-key-constraints.html', 'mariadb' => 'foreign-keys/']),'<p>
<input type="submit" value="',lang(16),'">
<noscript><p><input type="submit" name="add" value="',lang(202),'"></noscript>
';
    if ($E != '') {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, $E));
    }echo input_token(),'</form>
';
} elseif (isset($_GET['view'])) {
    $a = $_GET['view'];
    $M = $_POST;
    $Gf = 'VIEW';
    if (JUSH == 'pgsql' && $a != '') {
        $wh = table_status1($a);
        $Gf = strtoupper($wh['Engine']);
    }if ($_POST && ! $l) {
        $E = trim($M['name']);
        $ua = " AS\n$M[select]";
        $B = ME.'table='.urlencode($E);
        $D = lang(203);
        $U = ($_POST['materialized'] ? 'MATERIALIZED VIEW' : 'VIEW');
        if (! $_POST['drop'] && $a == $E && JUSH != 'sqlite' && $U == 'VIEW' && $Gf == 'VIEW') {
            query_redirect((JUSH == 'mssql' ? 'ALTER' : 'CREATE OR REPLACE').' VIEW '.table($E).$ua, $B, $D);
        } else {
            $Rh = $E.'_adminer_'.uniqid();
            drop_create("DROP $Gf ".table($a), "CREATE $U ".table($E).$ua, "DROP $U ".table($E), "CREATE $U ".table($Rh).$ua, "DROP $U ".table($Rh), ($_POST['drop'] ? substr(ME, 0, -1) : $B), lang(204), $D, lang(205), $a, $E);
        }
    }if (! $_POST && $a != '') {
        $M = view($a);
        $M['name'] = $a;
        $M['materialized'] = ($Gf != 'VIEW');
        if (! $l) {
            $l = error();
        }
    }page_header(($a != '' ? lang(35) : lang(206)), $l, ['table' => $a], h($a));
    echo '
<form action="" method="post">
<p>',lang(187),': <input name="name" value="',h($M['name']),'" data-maxlength="64" autocapitalize="off">
',(support('materializedview') ? ' '.checkbox('materialized', 1, $M['materialized'], lang(130)) : ''),'<p>';
    textarea('select', $M['select']);
    echo '<p>
<input type="submit" value="',lang(16),'">
';
    if ($a != '') {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, $a));
    }echo input_token(),'</form>
';
} elseif (isset($_GET['event'])) {
    $aa = $_GET['event'];
    $Td = ['YEAR', 'QUARTER', 'MONTH', 'DAY', 'HOUR', 'MINUTE', 'WEEK', 'SECOND', 'YEAR_MONTH', 'DAY_HOUR', 'DAY_MINUTE', 'DAY_SECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'MINUTE_SECOND'];
    $xh = ['ENABLED' => 'ENABLE', 'DISABLED' => 'DISABLE', 'SLAVESIDE_DISABLED' => 'DISABLE ON SLAVE'];
    $M = $_POST;
    if ($_POST && ! $l) {
        if ($_POST['drop']) {
            query_redirect('DROP EVENT '.idf_escape($aa), substr(ME, 0, -1), lang(207));
        } elseif (in_array($M['INTERVAL_FIELD'], $Td) && isset($xh[$M['STATUS']])) {
            $Sg = "\nON SCHEDULE ".($M['INTERVAL_VALUE'] ? 'EVERY '.q($M['INTERVAL_VALUE'])." $M[INTERVAL_FIELD]".($M['STARTS'] ? ' STARTS '.q($M['STARTS']) : '').($M['ENDS'] ? ' ENDS '.q($M['ENDS']) : '') : 'AT '.q($M['STARTS'])).' ON COMPLETION'.($M['ON_COMPLETION'] ? '' : ' NOT').' PRESERVE';
            queries_redirect(substr(ME, 0, -1), ($aa != '' ? lang(208) : lang(209)), queries(($aa != '' ? 'ALTER EVENT '.idf_escape($aa).$Sg.($aa != $M['EVENT_NAME'] ? "\nRENAME TO ".idf_escape($M['EVENT_NAME']) : '') : 'CREATE EVENT '.idf_escape($M['EVENT_NAME']).$Sg)."\n".$xh[$M['STATUS']].' COMMENT '.q($M['EVENT_COMMENT']).rtrim(" DO\n$M[EVENT_DEFINITION]", ';').';'));
        }
    }page_header(($aa != '' ? lang(210).': '.h($aa) : lang(211)), $l);
    if (! $M && $aa != '') {
        $N = get_rows('SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = '.q(DB).' AND EVENT_NAME = '.q($aa));
        $M = reset($N);
    }echo '
<form action="" method="post">
<table class="layout">
<tr><th>',lang(187),'<td><input name="EVENT_NAME" value="',h($M['EVENT_NAME']),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">',lang(212),'<td><input name="STARTS" value="',h("$M[EXECUTE_AT]$M[STARTS]"),'">
<tr><th title="datetime">',lang(213),'<td><input name="ENDS" value="',h($M['ENDS']),'">
<tr><th>',lang(214),'<td><input type="number" name="INTERVAL_VALUE" value="',h($M['INTERVAL_VALUE']),'" class="size"> ',html_select('INTERVAL_FIELD', $Td, $M['INTERVAL_FIELD']),'<tr><th>',lang(118),'<td>',html_select('STATUS', $xh, $M['STATUS']),'<tr><th>',lang(41),'<td><input name="EVENT_COMMENT" value="',h($M['EVENT_COMMENT']),'" data-maxlength="64">
<tr><th><td>',checkbox('ON_COMPLETION', 'PRESERVE', $M['ON_COMPLETION'] == 'PRESERVE', lang(215)),'</table>
<p>';
    textarea('EVENT_DEFINITION', $M['EVENT_DEFINITION']);
    echo '<p>
<input type="submit" value="',lang(16),'">
';
    if ($aa != '') {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, $aa));
    }echo input_token(),'</form>
';
} elseif (isset($_GET['procedure'])) {
    $ca = ($_GET['name'] ?: $_GET['procedure']);
    $Og = (isset($_GET['function']) ? 'FUNCTION' : 'PROCEDURE');
    $M = $_POST;
    $M['fields'] = (array) $M['fields'];
    if ($_POST && ! process_fields($M['fields']) && ! $l) {
        $Cf = routine($_GET['procedure'], $Og);
        $Rh = "$M[name]_adminer_".uniqid();
        foreach ($M['fields'] as $z => $m) {
            if ($m['field'] == '') {
                unset($M['fields'][$z]);
            }
        }drop_create("DROP $Og ".routine_id($ca, $Cf), create_routine($Og, $M), "DROP $Og ".routine_id($M['name'], $M), create_routine($Og, ['name' => $Rh] + $M), "DROP $Og ".routine_id($Rh, $M), substr(ME, 0, -1), lang(216), lang(217), lang(218), $ca, $M['name']);
    }page_header(($ca != '' ? (isset($_GET['function']) ? lang(219) : lang(220)).': '.h($ca) : (isset($_GET['function']) ? lang(221) : lang(222))), $l);
    if (! $_POST) {
        if ($ca == '') {
            $M['language'] = 'sql';
        } else {
            $M = routine($_GET['procedure'], $Og);
            $M['name'] = $ca;
        }
    }$b = get_vals('SHOW CHARACTER SET');
    sort($b);
    $Pg = routine_languages();
    echo ($b ? "<datalist id='collations'>".optionlist($b).'</datalist>' : ''),'
<form action="" method="post" id="form">
<p>',lang(187),': <input name="name" value="',h($M['name']),'" data-maxlength="64" autocapitalize="off">
',($Pg ? '<label>'.lang(21).': '.html_select('language', $Pg, $M['language'])."</label>\n" : ''),'<input type="submit" value="',lang(16),'">
<div class="scrollable">
<table class="nowrap">
';
    edit_fields($M['fields'], $b, $Og);
    if (isset($_GET['function'])) {
        echo '<tr><td>'.lang(223);
        edit_type('returns', (array) $M['returns'], $b, [], (JUSH == 'pgsql' ? ['void', 'trigger'] : []));
    }echo '</table>
',script('editFields();'),'</div>
<p>';
    textarea('definition', $M['definition'], 20);
    echo '<p>
<input type="submit" value="',lang(16),'">
';
    if ($ca != '') {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, $ca));
    }echo input_token(),'</form>
';
} elseif (isset($_GET['check'])) {
    $a = $_GET['check'];
    $E = $_GET['name'];
    $M = $_POST;
    if ($M && ! $l) {
        if (JUSH == 'sqlite') {
            $K = recreate_table($a, $a, [], [], [], '', [], "$E", ($M['drop'] ? '' : $M['clause']));
        } else {
            $K = ($E == '' || queries('ALTER TABLE '.table($a).' DROP CONSTRAINT '.idf_escape($E)));
            if (! $M['drop']) {
                $K = queries('ALTER TABLE '.table($a).' ADD'.($M['name'] != '' ? ' CONSTRAINT '.idf_escape($M['name']) : '')." CHECK ($M[clause])");
            }
        }queries_redirect(ME.'table='.urlencode($a), ($M['drop'] ? lang(224) : ($E != '' ? lang(225) : lang(226))), $K);
    }page_header(($E != '' ? lang(227).': '.h($E) : lang(141)), $l, ['table' => $a]);
    if (! $M) {
        $Va = driver()->checkConstraints($a);
        $M = ['name' => $E, 'clause' => $Va[$E]];
    }echo '
<form action="" method="post">
<p>';
    if (JUSH != 'sqlite') {
        echo lang(187).': <input name="name" value="'.h($M['name']).'" data-maxlength="64" autocapitalize="off"> ';
    }echo doc_link(['sql' => 'create-table-check-constraints.html', 'mariadb' => 'constraint/'], '?'),'<p>';
    textarea('clause', $M['clause']);
    echo '<p><input type="submit" value="',lang(16),'">
';
    if ($E != '') {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, $E));
    }echo input_token(),'</form>
';
} elseif (isset($_GET['trigger'])) {
    $a = $_GET['trigger'];
    $E = "$_GET[name]";
    $mi = trigger_options();
    $M = (array) trigger($E, $a) + ['Trigger' => $a.'_bi'];
    if ($_POST) {
        if (! $l && in_array($_POST['Timing'], $mi['Timing']) && in_array($_POST['Event'], $mi['Event']) && in_array($_POST['Type'], $mi['Type'])) {
            $pf = ' ON '.table($a);
            $Yb = 'DROP TRIGGER '.idf_escape($E).(JUSH == 'pgsql' ? $pf : '');
            $B = ME.'table='.urlencode($a);
            if ($_POST['drop']) {
                query_redirect($Yb, $B, lang(228));
            } else {
                if ($E != '') {
                    queries($Yb);
                }queries_redirect($B, ($E != '' ? lang(229) : lang(230)), queries(create_trigger($pf, $_POST)));
                if ($E != '') {
                    queries(create_trigger($pf, $M + ['Type' => reset($mi['Type'])]));
                }
            }
        }$M = $_POST;
    }page_header(($E != '' ? lang(231).': '.h($E) : lang(232)), $l, ['table' => $a]);
    echo '
<form action="" method="post" id="form">
<table class="layout">
<tr><th>',lang(233),'<td>',html_select('Timing', $mi['Timing'], $M['Timing'], 'triggerChange(/^'.preg_quote($a, '/')."_[ba][iud]$/, '".js_escape($a)."', this.form);"),'<tr><th>',lang(234),'<td>',html_select('Event', $mi['Event'], $M['Event'], "this.form['Timing'].onchange();"),(in_array('UPDATE OF', $mi['Event']) ? " <input name='Of' value='".h($M['Of'])."' class='hidden'>" : ''),'<tr><th>',lang(40),'<td>',html_select('Type', $mi['Type'], $M['Type']),'</table>
<p>',lang(187),': <input name="Trigger" value="',h($M['Trigger']),'" data-maxlength="64" autocapitalize="off">
',script("qs('#form')['Timing'].onchange();"),'<p>';
    textarea('Statement', $M['Statement']);
    echo '<p>
<input type="submit" value="',lang(16),'">
';
    if ($E != '') {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, $E));
    }echo input_token(),'</form>
';
} elseif (isset($_GET['user'])) {
    $ea = $_GET['user'];
    $rg = ['' => ['All privileges' => '']];
    foreach (get_rows('SHOW PRIVILEGES') as $M) {
        foreach (explode(',', ($M['Privilege'] == 'Grant option' ? '' : $M['Context'])) as $qb) {
            $rg[$qb][$M['Privilege']] = $M['Comment'];
        }
    }$rg['Server Admin'] += $rg['File access on server'];
    $rg['Databases']['Create routine'] = $rg['Procedures']['Create routine'];
    unset($rg['Procedures']['Create routine']);
    $rg['Columns'] = [];
    foreach (['Select', 'Insert', 'Update', 'References'] as $X) {
        $rg['Columns'][$X] = $rg['Tables'][$X];
    }unset($rg['Server Admin']['Usage']);
    foreach ($rg['Tables'] as $z => $X) {
        unset($rg['Databases'][$z]);
    }$af = [];
    if ($_POST) {
        foreach ($_POST['objects'] as $z => $X) {
            $af[$X] = (array) $af[$X] + idx($_POST['grants'], $z, []);
        }
    }$fd = [];
    $nf = '';
    if (isset($_GET['host']) && ($K = connection()->query('SHOW GRANTS FOR '.q($ea).'@'.q($_GET['host'])))) {
        while ($M = $K->fetch_row()) {
            if (preg_match('~GRANT (.*) ON (.*) TO ~', $M[0], $C) && preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~', $C[1], $Ae, PREG_SET_ORDER)) {
                foreach ($Ae as $X) {
                    if ($X[1] != 'USAGE') {
                        $fd["$C[2]$X[2]"][$X[1]] = true;
                    }if (preg_match('~ WITH GRANT OPTION~', $M[0])) {
                        $fd["$C[2]$X[2]"]['GRANT OPTION'] = true;
                    }
                }
            }if (preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~", $M[0], $C)) {
                $nf = $C[1];
            }
        }
    }if ($_POST && ! $l) {
        $of = (isset($_GET['host']) ? q($ea).'@'.q($_GET['host']) : "''");
        if ($_POST['drop']) {
            query_redirect("DROP USER $of", ME.'privileges=', lang(235));
        } else {
            $cf = q($_POST['user']).'@'.q($_POST['host']);
            $Xf = $_POST['pass'];
            if ($Xf != '' && ! $_POST['hashed'] && ! min_version(8)) {
                $Xf = get_val('SELECT PASSWORD('.q($Xf).')');
                $l = ! $Xf;
            }$vb = false;
            if (! $l) {
                if ($of != $cf) {
                    $vb = queries((min_version(5) ? 'CREATE USER' : 'GRANT USAGE ON *.* TO')." $cf IDENTIFIED BY ".(min_version(8) ? '' : 'PASSWORD ').q($Xf));
                    $l = ! $vb;
                } elseif ($Xf != $nf) {
                    queries("SET PASSWORD FOR $cf = ".q($Xf));
                }
            }if (! $l) {
                $Lg = [];
                foreach ($af as $if => $ed) {
                    if (isset($_GET['grant'])) {
                        $ed = array_filter($ed);
                    }$ed = array_keys($ed);
                    if (isset($_GET['grant'])) {
                        $Lg = array_diff(array_keys(array_filter($af[$if], 'strlen')), $ed);
                    } elseif ($of == $cf) {
                        $lf = array_keys((array) $fd[$if]);
                        $Lg = array_diff($lf, $ed);
                        $ed = array_diff($ed, $lf);
                        unset($fd[$if]);
                    }if (preg_match('~^(.+)\s*(\(.*\))?$~U', $if, $C) && (! grant('REVOKE', $Lg, $C[2], " ON $C[1] FROM $cf") || ! grant('GRANT', $ed, $C[2], " ON $C[1] TO $cf"))) {
                        $l = true;
                        break;
                    }
                }
            }if (! $l && isset($_GET['host'])) {
                if ($of != $cf) {
                    queries("DROP USER $of");
                } elseif (! isset($_GET['grant'])) {
                    foreach ($fd as $if => $Lg) {
                        if (preg_match('~^(.+)(\(.*\))?$~U', $if, $C)) {
                            grant('REVOKE', array_keys($Lg), $C[2], " ON $C[1] FROM $cf");
                        }
                    }
                }
            }queries_redirect(ME.'privileges=', (isset($_GET['host']) ? lang(236) : lang(237)), ! $l);
            if ($vb) {
                connection()->query("DROP USER $cf");
            }
        }
    }page_header((isset($_GET['host']) ? lang(26).': '.h("$ea@$_GET[host]") : lang(149)), $l, ['privileges' => ['', lang(62)]]);
    $M = $_POST;
    if ($M) {
        $fd = $af;
    } else {
        $M = $_GET + ['host' => get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];
        $M['pass'] = $nf;
        if ($nf != '') {
            $M['hashed'] = true;
        }$fd[(DB == '' || $fd ? '' : idf_escape(addcslashes(DB, '%_\\'))).'.*'] = [];
    }echo '<form action="" method="post">
<table class="layout">
<tr><th>',lang(25),'<td><input name="host" data-maxlength="60" value="',h($M['host']),'" autocapitalize="off">
<tr><th>',lang(26),'<td><input name="user" data-maxlength="80" value="',h($M['user']),'" autocapitalize="off">
<tr><th>',lang(27),'<td><input name="pass" id="pass" value="',h($M['pass']),'" autocomplete="new-password">
',($M['hashed'] ? '' : script("typePassword(qs('#pass'));")),(min_version(8) ? '' : checkbox('hashed', 1, $M['hashed'], lang(238), "typePassword(this.form['pass'], this.checked);")),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".lang(62).doc_link(['sql' => 'grant.html#priv_level']);
    $t = 0;
    foreach ($fd as $if => $ed) {
        echo '<th>'.($if != '*.*' ? "<input name='objects[$t]' value='".h($if)."' size='10' autocapitalize='off'>" : input_hidden("objects[$t]", '*.*').'*.*');
        $t++;
    }echo "</thead>\n";
    foreach (['' => '', 'Server Admin' => lang(25), 'Databases' => lang(28), 'Tables' => lang(132), 'Columns' => lang(39), 'Procedures' => lang(239)] as $qb => $Ob) {
        foreach ((array) $rg[$qb] as $qg => $hb) {
            echo '<tr><td'.($Ob ? ">$Ob<td" : " colspan='2'").' lang="en" title="'.h($hb).'">'.h($qg);
            $t = 0;
            foreach ($fd as $if => $ed) {
                $E = "'grants[$t][".h(strtoupper($qg))."]'";
                $Y = $ed[strtoupper($qg)];
                if ($qb == 'Server Admin' && $if != (isset($fd['*.*']) ? '*.*' : '.*')) {
                    echo '<td>';
                } elseif (isset($_GET['grant'])) {
                    echo "<td><select name=$E><option><option value='1'".($Y ? ' selected' : '').'>'.lang(240)."<option value='0'".($Y == '0' ? ' selected' : '').'>'.lang(241).'</select>';
                } else {
                    echo "<td align='center'><label class='block'>","<input type='checkbox' name=$E value='1'".($Y ? ' checked' : '').($qg == 'All privileges' ? " id='grants-$t-all'>" : '>'.($qg == 'Grant option' ? '' : script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$t-all'); };"))),'</label>';
                }$t++;
            }
        }
    }echo "</table>\n",'<p>
<input type="submit" value="',lang(16),'">
';
    if (isset($_GET['host'])) {
        echo '<input type="submit" name="drop" value="',lang(127),'">',confirm(lang(177, "$ea@$_GET[host]"));
    }echo input_token(),'</form>
';
} elseif (isset($_GET['processlist'])) {
    if (support('kill')) {
        if ($_POST && ! $l) {
            $ge = 0;
            foreach ((array) $_POST['kill'] as $X) {
                if (adminer()->killProcess($X)) {
                    $ge++;
                }
            }queries_redirect(ME.'processlist=', lang(242, $ge), $ge || ! $_POST['kill']);
        }
    }page_header(lang(116), $l);
    echo '
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds">
',script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");
    $t = -1;
    foreach (adminer()->processList() as $t => $M) {
        if (! $t) {
            echo "<thead><tr lang='en'>".(support('kill') ? '<th>' : '');
            foreach ($M as $z => $X) {
                echo "<th>$z".doc_link(['sql' => 'show-processlist.html#processlist_'.strtolower($z)]);
            }echo "</thead>\n";
        }echo '<tr>'.(support('kill') ? '<td>'.checkbox('kill[]', $M[JUSH == 'sql' ? 'Id' : 'pid'], 0) : '');
        foreach ($M as $z => $X) {
            echo '<td>'.((JUSH == 'sql' && $z == 'Info' && preg_match('~Query|Killed~', $M['Command']) && $X != '') || (JUSH == 'pgsql' && $z == 'current_query' && $X != '<IDLE>') || (JUSH == 'oracle' && $z == 'sql_text' && $X != '') ? "<code class='jush-".JUSH."'>".shorten_utf8($X, 100, '</code>').' <a href="'.h(ME.($M['db'] != '' ? 'db='.urlencode($M['db']).'&' : '').'sql='.urlencode($X)).'">'.lang(243).'</a>' : h($X));
        }echo "\n";
    }echo '</table>
</div>
<p>
';
    if (support('kill')) {
        echo ($t + 1).'/'.lang(244, max_connections()),"<p><input type='submit' value='".lang(245)."'>\n";
    }echo input_token(),'</form>
',script('tableCheck();');
} elseif (isset($_GET['select'])) {
    $a = $_GET['select'];
    $S = table_status1($a);
    $x = indexes($a);
    $n = fields($a);
    $Vc = column_foreign_keys($a);
    $kf = $S['Oid'];
    $ka = get_settings('adminer_import');
    $Mg = [];
    $d = [];
    $Xg = [];
    $zf = [];
    $Uh = '';
    foreach ($n as $z => $m) {
        $E = adminer()->fieldName($m);
        $Ye = html_entity_decode(strip_tags($E), ENT_QUOTES);
        if (isset($m['privileges']['select']) && $E != '') {
            $d[$z] = $Ye;
            if (is_shortable($m)) {
                $Uh = adminer()->selectLengthProcess();
            }
        }if (isset($m['privileges']['where']) && $E != '') {
            $Xg[$z] = $Ye;
        }if (isset($m['privileges']['order']) && $E != '') {
            $zf[$z] = $Ye;
        }$Mg += $m['privileges'];
    }[$O, $s] = adminer()->selectColumnsProcess($d, $x);
    $O = array_unique($O);
    $s = array_unique($s);
    $Xd = count($s) < count($O);
    $Z = adminer()->selectSearchProcess($n, $x);
    $yf = adminer()->selectOrderProcess($n, $x);
    $_ = adminer()->selectLimitProcess();
    if ($_GET['val'] && is_ajax()) {
        header('Content-Type: text/plain; charset=utf-8');
        foreach ($_GET['val'] as $ui => $M) {
            $ua = convert_field($n[key($M)]);
            $O = [$ua ?: idf_escape(key($M))];
            $Z[] = where_check($ui, $n);
            $L = driver()->select($a, $O, $Z, $O);
            if ($L) {
                echo first($L->fetch_row());
            }
        }exit;
    }$ng = $wi = [];
    foreach ($x as $w) {
        if ($w['type'] == 'PRIMARY') {
            $ng = array_flip($w['columns']);
            $wi = ($O ? $ng : []);
            foreach ($wi as $z => $X) {
                if (in_array(idf_escape($z), $O)) {
                    unset($wi[$z]);
                }
            }break;
        }
    }if ($kf && ! $ng) {
        $ng = $wi = [$kf => 0];
        $x[] = ['type' => 'PRIMARY', 'columns' => [$kf]];
    }if ($_POST && ! $l) {
        $Ti = $Z;
        if (! $_POST['all'] && is_array($_POST['check'])) {
            $Va = [];
            foreach ($_POST['check'] as $Sa) {
                $Va[] = where_check($Sa, $n);
            }$Ti[] = '(('.implode(') OR (', $Va).'))';
        }$Ti = ($Ti ? "\nWHERE ".implode(' AND ', $Ti) : '');
        if ($_POST['export']) {
            save_settings(['output' => $_POST['output'], 'format' => $_POST['format']], 'adminer_import');
            dump_headers($a);
            adminer()->dumpTable($a, '');
            $Zc = ($O ? implode(', ', $O) : '*').convert_fields($d, $n, $O)."\nFROM ".table($a);
            $hd = ($s && $Xd ? "\nGROUP BY ".implode(', ', $s) : '').($yf ? "\nORDER BY ".implode(', ', $yf) : '');
            $J = "SELECT $Zc$Ti$hd";
            if (is_array($_POST['check']) && ! $ng) {
                $si = [];
                foreach ($_POST['check'] as $X) {
                    $si[] = '(SELECT'.limit($Zc, "\nWHERE ".($Z ? implode(' AND ', $Z).' AND ' : '').where_check($X, $n).$hd, 1).')';
                }$J = implode(' UNION ALL ', $si);
            }adminer()->dumpData($a, 'table', $J);
            adminer()->dumpFooter();
            exit;
        }if (! adminer()->selectEmailProcess($Z, $Vc)) {
            if ($_POST['save'] || $_POST['delete']) {
                $K = true;
                $la = 0;
                $Q = [];
                if (! $_POST['delete']) {
                    foreach ($_POST['fields'] as $E => $X) {
                        $X = process_input($n[$E]);
                        if ($X !== null && ($_POST['clone'] || $X !== false)) {
                            $Q[idf_escape($E)] = ($X !== false ? $X : idf_escape($E));
                        }
                    }
                }if ($_POST['delete'] || $Q) {
                    $J = ($_POST['clone'] ? 'INTO '.table($a).' ('.implode(', ', array_keys($Q)).")\nSELECT ".implode(', ', $Q)."\nFROM ".table($a) : '');
                    if ($_POST['all'] || ($ng && is_array($_POST['check'])) || $Xd) {
                        $K = ($_POST['delete'] ? driver()->delete($a, $Ti) : ($_POST['clone'] ? queries("INSERT $J$Ti".driver()->insertReturning($a)) : driver()->update($a, $Q, $Ti)));
                        $la = connection()->affected_rows;
                        if (is_object($K)) {
                            $la += $K->num_rows;
                        }
                    } else {
                        foreach ((array) $_POST['check'] as $X) {
                            $Si = "\nWHERE ".($Z ? implode(' AND ', $Z).' AND ' : '').where_check($X, $n);
                            $K = ($_POST['delete'] ? driver()->delete($a, $Si, 1) : ($_POST['clone'] ? queries('INSERT'.limit1($a, $J, $Si)) : driver()->update($a, $Q, $Si, 1)));
                            if (! $K) {
                                break;
                            }$la += connection()->affected_rows;
                        }
                    }
                }$D = lang(246, $la);
                if ($_POST['clone'] && $K && $la == 1) {
                    $me = last_id($K);
                    if ($me) {
                        $D = lang(170, " $me");
                    }
                }queries_redirect(remove_from_uri($_POST['all'] && $_POST['delete'] ? 'page' : ''), $D, $K);
                if (! $_POST['delete']) {
                    $jg = (array) $_POST['fields'];
                    edit_form($a, array_intersect_key($n, $jg), $jg, ! $_POST['clone'], $l);
                    page_footer();
                    exit;
                }
            } elseif (! $_POST['import']) {
                if (! $_POST['val']) {
                    $l = lang(247);
                } else {
                    $K = true;
                    $la = 0;
                    foreach ($_POST['val'] as $ui => $M) {
                        $Q = [];
                        foreach ($M as $z => $X) {
                            $z = bracket_escape($z, true);
                            $Q[idf_escape($z)] = (preg_match('~char|text~', $n[$z]['type']) || $X != '' ? adminer()->processInput($n[$z], $X) : 'NULL');
                        }$K = driver()->update($a, $Q, ' WHERE '.($Z ? implode(' AND ', $Z).' AND ' : '').where_check($ui, $n), ($Xd || $ng ? 0 : 1), ' ');
                        if (! $K) {
                            break;
                        }$la += connection()->affected_rows;
                    }queries_redirect(remove_from_uri(), lang(246, $la), $K);
                }
            } elseif (! is_string($Mc = get_file('csv_file', true))) {
                $l = upload_error($Mc);
            } elseif (! preg_match('~~u', $Mc)) {
                $l = lang(248);
            } else {
                save_settings(['output' => $ka['output'], 'format' => $_POST['separator']], 'adminer_import');
                $K = true;
                $eb = array_keys($n);
                preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~', $Mc, $Ae);
                $la = count($Ae[0]);
                driver()->begin();
                $dh = ($_POST['separator'] == 'csv' ? ',' : ($_POST['separator'] == 'tsv' ? "\t" : ';'));
                $N = [];
                foreach ($Ae[0] as $z => $X) {
                    preg_match_all("~((?>\"[^\"]*\")+|[^$dh]*)$dh~", $X.$dh, $Be);
                    if (! $z && ! array_diff($Be[1], $eb)) {
                        $eb = $Be[1];
                        $la--;
                    } else {
                        $Q = [];
                        foreach ($Be[1] as $t => $bb) {
                            $Q[idf_escape($eb[$t])] = ($bb == '' && $n[$eb[$t]]['null'] ? 'NULL' : q(preg_match('~^".*"$~s', $bb) ? str_replace('""', '"', substr($bb, 1, -1)) : $bb));
                        }$N[] = $Q;
                    }
                }$K = (! $N || driver()->insertUpdate($a, $N, $ng));
                if ($K) {
                    driver()->commit();
                }queries_redirect(remove_from_uri('page'), lang(249, $la), $K);
                driver()->rollback();
            }
        }
    }$Hh = adminer()->tableName($S);
    if (is_ajax()) {
        page_headers();
        ob_start();
    } else {
        page_header(lang(44).": $Hh", $l);
    }$Q = null;
    if (isset($Mg['insert']) || ! support('table')) {
        $Nf = [];
        foreach ((array) $_GET['where'] as $X) {
            if (isset($Vc[$X['col']]) && count($Vc[$X['col']]) == 1 && ($X['op'] == '=' || (! $X['op'] && (is_array($X['val']) || ! preg_match('~[_%]~', $X['val']))))) {
                $Nf['set'.'['.bracket_escape($X['col']).']'] = $X['val'];
            }
        }$Q = $Nf ? '&'.http_build_query($Nf) : '';
    }adminer()->selectLinks($S, $Q);
    if (! $d && support('table')) {
        echo "<p class='error'>".lang(250).($n ? '.' : ': '.error())."\n";
    } else {
        echo "<form action='' id='form'>\n","<div style='display: none;'>";
        hidden_fields_get();
        echo (DB != '' ? input_hidden('db', DB).(isset($_GET['ns']) ? input_hidden('ns', $_GET['ns']) : '') : ''),input_hidden('select', $a),"</div>\n";
        adminer()->selectColumnsPrint($O, $d);
        adminer()->selectSearchPrint($Z, $Xg, $x);
        adminer()->selectOrderPrint($yf, $zf, $x);
        adminer()->selectLimitPrint($_);
        adminer()->selectLengthPrint($Uh);
        adminer()->selectActionPrint($x);
        echo "</form>\n";
        $G = $_GET['page'];
        $Yc = null;
        if ($G == 'last') {
            $Yc = get_val(count_rows($a, $Z, $Xd, $s));
            $G = floor(max(0, intval($Yc) - 1) / $_);
        }$Yg = $O;
        $gd = $s;
        if (! $Yg) {
            $Yg[] = '*';
            $rb = convert_fields($d, $n, $O);
            if ($rb) {
                $Yg[] = substr($rb, 2);
            }
        }foreach ($O as $z => $X) {
            $m = $n[idf_unescape($X)];
            if ($m && ($ua = convert_field($m))) {
                $Yg[$z] = "$ua AS $X";
            }
        }if (! $Xd && $wi) {
            foreach ($wi as $z => $X) {
                $Yg[] = idf_escape($z);
                if ($gd) {
                    $gd[] = idf_escape($z);
                }
            }
        }$K = driver()->select($a, $Yg, $Z, $gd, $yf, $_, $G, true);
        if (! $K) {
            echo "<p class='error'>".error()."\n";
        } else {
            if (JUSH == 'mssql' && $G) {
                $K->seek($_ * $G);
            }$jc = [];
            echo "<form action='' method='post' enctype='multipart/form-data'>\n";
            $N = [];
            while ($M = $K->fetch_assoc()) {
                if ($G && JUSH == 'oracle') {
                    unset($M['RNUM']);
                }$N[] = $M;
            }if ($_GET['page'] != 'last' && $_ && $s && $Xd && JUSH == 'sql') {
                $Yc = get_val(' SELECT FOUND_ROWS()');
            }if (! $N) {
                echo "<p class='message'>".lang(14)."\n";
            } else {
                $Ca = adminer()->backwardKeys($a, $Hh);
                echo "<div class='scrollable'>","<table id='table' class='nowrap checkable odds'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),'<thead><tr>'.(! $s && $O ? '' : "<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);", '')." <a href='".h($_GET['modify'] ? remove_from_uri('modify') : $_SERVER['REQUEST_URI'].'&modify=1')."'>".lang(251).'</a>');
                $Ze = [];
                $bd = [];
                reset($O);
                $zg = 1;
                foreach ($N[0] as $z => $X) {
                    if (! isset($wi[$z])) {
                        $X = idx($_GET['columns'], key($O)) ?: [];
                        $m = $n[$O ? ($X ? $X['col'] : current($O)) : $z];
                        $E = ($m ? adminer()->fieldName($m, $zg) : ($X['fun'] ? '*' : h($z)));
                        if ($E != '') {
                            $zg++;
                            $Ze[$z] = $E;
                            $c = idf_escape($z);
                            $wd = remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($z);
                            $Ob = '&desc%5B0%5D=1';
                            echo "<th id='th[".h(bracket_escape($z))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});", '');
                            $ad = apply_sql_function($X['fun'], $E);
                            $nh = isset($m['privileges']['order']) || $ad;
                            echo ($nh ? "<a href='".h($wd.($yf[0] == $c || $yf[0] == $z ? $Ob : ''))."'>$ad</a>" : $ad),"<span class='column hidden'>";
                            if ($nh) {
                                echo "<a href='".h($wd.$Ob)."' title='".lang(50)."' class='text'> â</a>";
                            }if (! $X['fun'] && isset($m['privileges']['where'])) {
                                echo '<a href="#fieldset-search" title="'.lang(47).'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($z)."');");
                            }echo '</span>';
                        }$bd[$z] = $X['fun'];
                        next($O);
                    }
                }$se = [];
                if ($_GET['modify']) {
                    foreach ($N as $M) {
                        foreach ($M as $z => $X) {
                            $se[$z] = max($se[$z], min(40, strlen(utf8_decode($X))));
                        }
                    }
                }echo ($Ca ? '<th>'.lang(252) : '')."</thead>\n";
                if (is_ajax()) {
                    ob_end_clean();
                }foreach (adminer()->rowDescriptions($N, $Vc) as $Xe => $M) {
                    $ti = unique_array($N[$Xe], $x);
                    if (! $ti) {
                        $ti = [];
                        reset($O);
                        foreach ($N[$Xe] as $z => $X) {
                            if (! preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~', current($O))) {
                                $ti[$z] = $X;
                            }next($O);
                        }
                    }$ui = '';
                    foreach ($ti as $z => $X) {
                        $m = (array) $n[$z];
                        if ((JUSH == 'sql' || JUSH == 'pgsql') && preg_match('~char|text|enum|set~', $m['type']) && strlen($X) > 64) {
                            $z = (strpos($z, '(') ? $z : idf_escape($z));
                            $z = 'MD5('.(JUSH != 'sql' || preg_match('~^utf8~', $m['collation']) ? $z : "CONVERT($z USING ".charset(connection()).')').')';
                            $X = md5($X);
                        }$ui
                            .= '&'.($X !== null ? urlencode('where['.bracket_escape($z).']').'='.urlencode($X === false ? 'f' : $X) : 'null%5B%5D='.urlencode($z));
                    }echo '<tr>'.(! $s && $O ? '' : '<td>'.checkbox('check[]', substr($ui, 1), in_array(substr($ui, 1), (array) $_POST['check'])).($Xd || information_schema(DB) ? '' : " <a href='".h(ME.'edit='.urlencode($a).$ui)."' class='edit'>".lang(253).'</a>'));
                    reset($O);
                    foreach ($M as $z => $X) {
                        if (isset($Ze[$z])) {
                            $c = current($O);
                            $m = (array) $n[$z];
                            $X = driver()->value($X, $m);
                            if ($X != '' && (! isset($jc[$z]) || $jc[$z] != '')) {
                                $jc[$z] = (is_mail($X) ? $Ze[$z] : '');
                            }$A = '';
                            if (is_blob($m) && $X != '') {
                                $A = ME.'download='.urlencode($a).'&field='.urlencode($z).$ui;
                            }if (! $A && $X !== null) {
                                foreach ((array) $Vc[$z] as $p) {
                                    if (count($Vc[$z]) == 1 || end($p['source']) == $z) {
                                        $A = '';
                                        foreach ($p['source'] as $t => $oh) {
                                            $A
                                                .= where_link($t, $p['target'][$t], $N[$Xe][$oh]);
                                        }$A = ($p['db'] != '' ? preg_replace('~([?&]db=)[^&]+~', '\1'.urlencode($p['db']), ME) : ME).'select='.urlencode($p['table']).$A;
                                        if ($p['ns']) {
                                            $A = preg_replace('~([?&]ns=)[^&]+~', '\1'.urlencode($p['ns']), $A);
                                        }if (count($p['source']) == 1) {
                                            break;
                                        }
                                    }
                                }
                            }if ($c == 'COUNT(*)') {
                                $A = ME.'select='.urlencode($a);
                                $t = 0;
                                foreach ((array) $_GET['where'] as $W) {
                                    if (! array_key_exists($W['col'], $ti)) {
                                        $A
                                            .= where_link($t++, $W['col'], $W['val'], $W['op']);
                                    }
                                }foreach ($ti as $de => $W) {
                                    $A
                                        .= where_link($t++, $de, $W);
                                }
                            }$xd = select_value($X, $A, $m, $Uh);
                            $u = h("val[$ui][".bracket_escape($z).']');
                            $kg = idx(idx($_POST['val'], $ui), bracket_escape($z));
                            $ec = ! is_array($M[$z]) && is_utf8($xd) && $N[$Xe][$z] == $M[$z] && ! $bd[$z] && ! $m['generated'];
                            $U = (preg_match('~^(AVG|MIN|MAX)\((.+)\)~', $c, $C) ? $n[idf_unescape($C[2])]['type'] : $m['type']);
                            $Th = preg_match('~text|json|lob~', $U);
                            $Yd = preg_match(number_type(), $U) || preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~', $c);
                            echo "<td id='$u'".($Yd && ($X === null || is_numeric(strip_tags($xd)) || $U == 'money') ? " class='number'" : '');
                            if (($_GET['modify'] && $ec && $X !== null) || $kg !== null) {
                                $kd = h($kg !== null ? $kg : $M[$z]);
                                echo '>'.($Th ? "<textarea name='$u' cols='30' rows='".(substr_count($M[$z], "\n") + 1)."'>$kd</textarea>" : "<input name='$u' value='$kd' size='$se[$z]'>");
                            } else {
                                $xe = strpos($xd, '<i>â¦</i>');
                                echo " data-text='".($xe ? 2 : ($Th ? 1 : 0))."'".($ec ? '' : " data-warning='".h(lang(254))."'").">$xd";
                            }
                        }next($O);
                    }if ($Ca) {
                        echo '<td>';
                    }adminer()->backwardKeysPrint($Ca, $N[$Xe]);
                    echo "</tr>\n";
                }if (is_ajax()) {
                    exit;
                }echo "</table>\n","</div>\n";
            }if (! is_ajax()) {
                if ($N || $G) {
                    $xc = true;
                    if ($_GET['page'] != 'last') {
                        if (! $_ || (count($N) < $_ && ($N || ! $G))) {
                            $Yc = ($G ? $G * $_ : 0) + count($N);
                        } elseif (JUSH != 'sql' || ! $Xd) {
                            $Yc = ($Xd ? false : found_rows($S, $Z));
                            if (intval($Yc) < max(1e4, 2 * ($G + 1) * $_)) {
                                $Yc = first(slow_query(count_rows($a, $Z, $Xd, $s)));
                            } else {
                                $xc = false;
                            }
                        }
                    }$Lf = ($_ && ($Yc === false || $Yc > $_ || $G));
                    if ($Lf) {
                        echo (($Yc === false ? count($N) + 1 : $Yc - $G * $_) > $_ ? '<p><a href="'.h(remove_from_uri('page').'&page='.($G + 1)).'" class="loadmore">'.lang(255).'</a>'.script("qsl('a').onclick = partial(selectLoadMore, $_, '".lang(256)."â¦');", '') : ''),"\n";
                    }echo "<div class='footer'><div>\n";
                    if ($Lf) {
                        $Fe = ($Yc === false ? $G + (count($N) >= $_ ? 2 : 1) : floor(($Yc - 1) / $_));
                        echo '<fieldset>';
                        if (JUSH != 'simpledb') {
                            echo "<legend><a href='".h(remove_from_uri('page'))."'>".lang(257).'</a></legend>',script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".lang(257)."', '".($G + 1)."')); return false; };"),pagination(0, $G).($G > 5 ? ' â¦' : '');
                            for ($t = max(1, $G - 4); $t < min($Fe, $G + 5); $t++) {
                                echo pagination($t, $G);
                            }if ($Fe > 0) {
                                echo ($G + 5 < $Fe ? ' â¦' : ''),($xc && $Yc !== false ? pagination($Fe, $G) : " <a href='".h(remove_from_uri('page').'&page=last')."' title='~$Fe'>".lang(258).'</a>');
                            }
                        } else {
                            echo '<legend>'.lang(257).'</legend>',pagination(0, $G).($G > 1 ? ' â¦' : ''),($G ? pagination($G, $G) : ''),($Fe > $G ? pagination($G + 1, $G).($Fe > $G + 1 ? ' â¦' : '') : '');
                        }echo "</fieldset>\n";
                    }echo '<fieldset>','<legend>'.lang(259).'</legend>';
                    $Vb = ($xc ? '' : '~ ').$Yc;
                    $sf = "const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Vb' : checked); selectCount('selected2', this.checked || !checked ? '$Vb' : checked);";
                    echo checkbox('all', 1, 0, ($Yc !== false ? ($xc ? '' : '~ ').lang(153, $Yc) : ''), $sf)."\n","</fieldset>\n";
                    if (adminer()->selectCommandPrint()) {
                        echo '<fieldset',($_GET['modify'] ? '' : ' class="jsonly"'),'><legend>',lang(251),'</legend><div>
<input type="submit" value="',lang(16),'"',($_GET['modify'] ? '' : ' title="'.lang(247).'"'),'>
</div></fieldset>
<fieldset><legend>',lang(126),' <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="',lang(12),'">
<input type="submit" name="clone" value="',lang(243),'">
<input type="submit" name="delete" value="',lang(20),'">',confirm(),'</div></fieldset>
';
                    }$Wc = adminer()->dumpFormat();
                    foreach ((array) $_GET['columns'] as $c) {
                        if ($c['fun']) {
                            unset($Wc['sql']);
                            break;
                        }
                    }if ($Wc) {
                        print_fieldset('export', lang(67)." <span id='selected2'></span>");
                        $Jf = adminer()->dumpOutput();
                        echo ($Jf ? html_select('output', $Jf, $ka['output']).' ' : ''),html_select('format', $Wc, $ka['format'])," <input type='submit' name='export' value='".lang(67)."'>\n","</div></fieldset>\n";
                    }adminer()->selectEmailPrint(array_filter($jc, 'strlen'), $d);
                    echo "</div></div>\n";
                }if (adminer()->selectImportPrint()) {
                    echo '<p>',"<a href='#import'>".lang(66).'</a>',script("qsl('a').onclick = partial(toggle, 'import');", ''),"<span id='import'".($_POST['import'] ? '' : " class='hidden'").'>: ',file_input("<input type='file' name='csv_file'> ".html_select('separator', ['csv' => 'CSV,', 'csv;' => 'CSV;', 'tsv' => 'TSV'], $ka['format'])." <input type='submit' name='import' value='".lang(66)."'>"),'</span>';
                }echo input_token(),"</form>\n",(! $s && $O ? '' : script('tableCheck();'));
            }
        }
    }if (is_ajax()) {
        ob_end_clean();
        exit;
    }
} elseif (isset($_GET['variables'])) {
    $wh = isset($_GET['status']);
    page_header($wh ? lang(118) : lang(117));
    $Ji = ($wh ? show_status() : show_variables());
    if (! $Ji) {
        echo "<p class='message'>".lang(14)."\n";
    } else {
        echo "<table>\n";
        foreach ($Ji as $M) {
            echo '<tr>';
            $z = array_shift($M);
            echo "<th><code class='jush-".JUSH.($wh ? 'status' : 'set')."'>".h($z).'</code>';
            foreach ($M as $X) {
                echo '<td>'.nl_br(h($X));
            }
        }echo "</table>\n";
    }
} elseif (isset($_GET['script'])) {
    header('Content-Type: text/javascript; charset=utf-8');
    if ($_GET['script'] == 'db') {
        $Eh = ['Data_length' => 0, 'Index_length' => 0, 'Data_free' => 0];
        foreach (table_status() as $E => $S) {
            json_row("Comment-$E", h($S['Comment']));
            if (! is_view($S) || preg_match('~materialized~i', $S['Engine'])) {
                foreach (['Engine', 'Collation'] as $z) {
                    json_row("$z-$E", h($S[$z]));
                }foreach ($Eh + ['Auto_increment' => 0, 'Rows' => 0] as $z => $X) {
                    if ($S[$z] != '') {
                        $X = format_number($S[$z]);
                        if ($X >= 0) {
                            json_row("$z-$E", ($z == 'Rows' && $X && $S['Engine'] == (JUSH == 'pgsql' ? 'table' : 'InnoDB') ? "~ $X" : $X));
                        }if (isset($Eh[$z])) {
                            $Eh[$z] += ($S['Engine'] != 'InnoDB' || $z != 'Data_free' ? $S[$z] : 0);
                        }
                    } elseif (array_key_exists($z, $S)) {
                        json_row("$z-$E", '?');
                    }
                }
            }
        }foreach ($Eh as $z => $X) {
            json_row("sum-$z", format_number($X));
        }json_row('');
    } elseif ($_GET['script'] == 'kill') {
        connection()->query('KILL '.number($_POST['kill']));
    } else {
        foreach (count_tables(adminer()->databases()) as $j => $X) {
            json_row("tables-$j", $X);
            json_row("size-$j", db_size($j));
        }json_row('');
    }exit;
} else {
    $Nh = array_merge((array) $_POST['tables'], (array) $_POST['views']);
    if ($Nh && ! $l && ! $_POST['search']) {
        $K = true;
        $D = '';
        if (JUSH == 'sql' && $_POST['tables'] && count($_POST['tables']) > 1 && ($_POST['drop'] || $_POST['truncate'] || $_POST['copy'])) {
            queries('SET foreign_key_checks = 0');
        }if ($_POST['truncate']) {
            if ($_POST['tables']) {
                $K = truncate_tables($_POST['tables']);
            }$D = lang(260);
        } elseif ($_POST['move']) {
            $K = move_tables((array) $_POST['tables'], (array) $_POST['views'], $_POST['target']);
            $D = lang(261);
        } elseif ($_POST['copy']) {
            $K = copy_tables((array) $_POST['tables'], (array) $_POST['views'], $_POST['target']);
            $D = lang(262);
        } elseif ($_POST['drop']) {
            if ($_POST['views']) {
                $K = drop_views($_POST['views']);
            }if ($K && $_POST['tables']) {
                $K = drop_tables($_POST['tables']);
            }$D = lang(263);
        } elseif (JUSH == 'sqlite' && $_POST['check']) {
            foreach ((array) $_POST['tables'] as $R) {
                foreach (get_rows('PRAGMA integrity_check('.q($R).')') as $M) {
                    $D
                        .= '<b>'.h($R).'</b>: '.h($M['integrity_check']).'<br>';
                }
            }
        } elseif (JUSH != 'sql') {
            $K = (JUSH == 'sqlite' ? queries('VACUUM') : apply_queries('VACUUM'.($_POST['optimize'] ? '' : ' ANALYZE'), $_POST['tables']));
            $D = lang(264);
        } elseif (! $_POST['tables']) {
            $D = lang(11);
        } elseif ($K = queries(($_POST['optimize'] ? 'OPTIMIZE' : ($_POST['check'] ? 'CHECK' : ($_POST['repair'] ? 'REPAIR' : 'ANALYZE'))).' TABLE '.implode(', ', array_map('Adminer\idf_escape', $_POST['tables'])))) {
            while ($M = $K->fetch_assoc()) {
                $D
                    .= '<b>'.h($M['Table']).'</b>: '.h($M['Msg_text']).'<br>';
            }
        }queries_redirect(substr(ME, 0, -1), $D, $K);
    }page_header(($_GET['ns'] == '' ? lang(28).': '.h(DB) : lang(265).': '.h($_GET['ns'])), $l, true);
    if (adminer()->homepage()) {
        if ($_GET['ns'] !== '') {
            echo "<h3 id='tables-views'>".lang(266)."</h3>\n";
            $Mh = tables_list();
            if (! $Mh) {
                echo "<p class='message'>".lang(11)."\n";
            } else {
                echo "<form action='' method='post'>\n";
                if (support('table')) {
                    echo '<fieldset><legend>'.lang(267)." <span id='selected2'></span></legend><div>",html_select('op', adminer()->operators(), idx($_POST, 'op', JUSH == 'elastic' ? 'should' : 'LIKE %%'))," <input type='search' name='query' value='".h($_POST['query'])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');", '')," <input type='submit' name='search' value='".lang(47)."'>\n","</div></fieldset>\n";
                    if ($_POST['search'] && $_POST['query'] != '') {
                        $_GET['where'][0]['op'] = $_POST['op'];
                        search_tables();
                    }
                }echo "<div class='scrollable'>\n","<table class='nowrap checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);", ''),'<th>'.lang(132),'<td>'.lang(268).doc_link(['sql' => 'storage-engines.html']),'<td>'.lang(122).doc_link(['sql' => 'charset-charsets.html', 'mariadb' => 'supported-character-sets-and-collations/']),'<td>'.lang(269).doc_link(['sql' => 'show-table-status.html']),'<td>'.lang(270).doc_link(['sql' => 'show-table-status.html']),'<td>'.lang(271).doc_link(['sql' => 'show-table-status.html']),'<td>'.lang(42).doc_link(['sql' => 'example-auto-increment.html', 'mariadb' => 'auto_increment/']),'<td>'.lang(272).doc_link(['sql' => 'show-table-status.html']),(support('comment') ? '<td>'.lang(41).doc_link(['sql' => 'show-table-status.html']) : ''),"</thead>\n";
                $T = 0;
                foreach ($Mh as $E => $U) {
                    $Mi = ($U !== null && ! preg_match('~table|sequence~i', $U));
                    $u = h('Table-'.$E);
                    echo '<tr><td>'.checkbox(($Mi ? 'views[]' : 'tables[]'), $E, in_array("$E", $Nh, true), '', '', '', $u),'<th>'.(support('table') || support('indexes') ? "<a href='".h(ME).'table='.urlencode($E)."' title='".lang(33)."' id='$u'>".h($E).'</a>' : h($E));
                    if ($Mi && ! preg_match('~materialized~i', $U)) {
                        $Yh = lang(131);
                        echo '<td colspan="6">'.(support('view') ? "<a href='".h(ME).'view='.urlencode($E)."' title='".lang(35)."'>$Yh</a>" : $Yh),'<td align="right"><a href="'.h(ME).'select='.urlencode($E).'" title="'.lang(32).'">?</a>';
                    } else {
                        foreach (['Engine' => [], 'Collation' => [], 'Data_length' => ['create', lang(34)], 'Index_length' => ['indexes', lang(135)], 'Data_free' => ['edit', lang(36)], 'Auto_increment' => ['auto_increment=1&create', lang(34)], 'Rows' => ['select', lang(32)]] as $z => $A) {
                            $u = " id='$z-".h($E)."'";
                            echo $A ? "<td align='right'>".(support('table') || $z == 'Rows' || (support('indexes') && $z != 'Data_length') ? "<a href='".h(ME."$A[0]=").urlencode($E)."'$u title='$A[1]'>?</a>" : "<span$u>?</span>") : "<td id='$z-".h($E)."'>";
                        }$T++;
                    }echo (support('comment') ? "<td id='Comment-".h($E)."'>" : ''),"\n";
                }echo '<tr><td><th>'.lang(244, count($Mh)),'<td>'.h(JUSH == 'sql' ? get_val('SELECT @@default_storage_engine') : ''),'<td>'.h(db_collation(DB, collations()));
                foreach (['Data_length', 'Index_length', 'Data_free'] as $z) {
                    echo "<td align='right' id='sum-$z'>";
                }echo "\n","</table>\n",script("ajaxSetHtml('".js_escape(ME)."script=db');"),"</div>\n";
                if (! information_schema(DB)) {
                    echo "<div class='footer'><div>\n";
                    $Hi = "<input type='submit' value='".lang(273)."'> ".on_help("'VACUUM'");
                    $vf = "<input type='submit' name='optimize' value='".lang(274)."'> ".on_help(JUSH == 'sql' ? "'OPTIMIZE TABLE'" : "'VACUUM OPTIMIZE'");
                    echo '<fieldset><legend>'.lang(126)." <span id='selected'></span></legend><div>".(JUSH == 'sqlite' ? $Hi."<input type='submit' name='check' value='".lang(275)."'> ".on_help("'PRAGMA integrity_check'") : (JUSH == 'pgsql' ? $Hi.$vf : (JUSH == 'sql' ? "<input type='submit' value='".lang(276)."'> ".on_help("'ANALYZE TABLE'").$vf."<input type='submit' name='check' value='".lang(275)."'> ".on_help("'CHECK TABLE'")."<input type='submit' name='repair' value='".lang(277)."'> ".on_help("'REPAIR TABLE'") : '')))."<input type='submit' name='truncate' value='".lang(278)."'> ".on_help(JUSH == 'sqlite' ? "'DELETE'" : "'TRUNCATE".(JUSH == 'pgsql' ? "'" : " TABLE'")).confirm()."<input type='submit' name='drop' value='".lang(127)."'>".on_help("'DROP TABLE'").confirm()."\n";
                    $i = (support('scheme') ? adminer()->schemas() : adminer()->databases());
                    echo "</div></fieldset>\n";
                    $Wg = '';
                    if (count($i) != 1 && JUSH != 'sqlite') {
                        echo '<fieldset><legend>'.lang(279)." <span id='selected3'></span></legend><div>";
                        $j = (isset($_POST['target']) ? $_POST['target'] : (support('scheme') ? $_GET['ns'] : DB));
                        echo ($i ? html_select('target', $i, $j) : '<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".lang(280)."'>",(support('copy') ? " <input type='submit' name='copy' value='".lang(281)."'> ".checkbox('overwrite', 1, $_POST['overwrite'], lang(282)) : ''),"</div></fieldset>\n";
                        $Wg = " selectCount('selected3', formChecked(this, /^(tables|views)\[/));";
                    }echo "<input type='hidden' name='all' value=''>",script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support('table') ? " selectCount('selected2', formChecked(this, /^tables\[/) || $T);" : '')."$Wg }"),input_token(),"</div></div>\n";
                }echo "</form>\n",script('tableCheck();');
            }echo "<p class='links'><a href='".h(ME)."create='>".lang(68)."</a>\n",(support('view') ? "<a href='".h(ME)."view='>".lang(206)."</a>\n" : '');
            if (support('routine')) {
                echo "<h3 id='routines'>".lang(63)."</h3>\n";
                $Qg = routines();
                if ($Qg) {
                    echo "<table class='odds'>\n",'<thead><tr><th>'.lang(187).'<td>'.lang(40).'<td>'.lang(223)."<td></thead>\n";
                    foreach ($Qg as $M) {
                        $E = ($M['SPECIFIC_NAME'] == $M['ROUTINE_NAME'] ? '' : '&name='.urlencode($M['ROUTINE_NAME']));
                        echo '<tr>','<th><a href="'.h(ME.($M['ROUTINE_TYPE'] != 'PROCEDURE' ? 'callf=' : 'call=').urlencode($M['SPECIFIC_NAME']).$E).'">'.h($M['ROUTINE_NAME']).'</a>','<td>'.h($M['ROUTINE_TYPE']),'<td>'.h($M['DTD_IDENTIFIER']),'<td><a href="'.h(ME.($M['ROUTINE_TYPE'] != 'PROCEDURE' ? 'function=' : 'procedure=').urlencode($M['SPECIFIC_NAME']).$E).'">'.lang(138).'</a>';
                    }echo "</table>\n";
                }echo '<p class="links">'.(support('procedure') ? '<a href="'.h(ME).'procedure=">'.lang(222).'</a>' : '').'<a href="'.h(ME).'function=">'.lang(221)."</a>\n";
            }if (support('event')) {
                echo "<h3 id='events'>".lang(65)."</h3>\n";
                $N = get_rows('SHOW EVENTS');
                if ($N) {
                    echo "<table>\n",'<thead><tr><th>'.lang(187).'<td>'.lang(283).'<td>'.lang(212).'<td>'.lang(213)."<td></thead>\n";
                    foreach ($N as $M) {
                        echo '<tr>','<th>'.h($M['Name']),'<td>'.($M['Execute at'] ? lang(284).'<td>'.$M['Execute at'] : lang(214).' '.$M['Interval value'].' '.$M['Interval field']."<td>$M[Starts]"),"<td>$M[Ends]",'<td><a href="'.h(ME).'event='.urlencode($M['Name']).'">'.lang(138).'</a>';
                    }echo "</table>\n";
                    $vc = get_val('SELECT @@event_scheduler');
                    if ($vc && $vc != 'ON') {
                        echo "<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($vc)."\n";
                    }
                }echo '<p class="links"><a href="'.h(ME).'event=">'.lang(211)."</a>\n";
            }
        }
    }
}page_footer();
