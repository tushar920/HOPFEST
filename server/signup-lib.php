<?php
declare(strict_types=1);

const HF_COLUMNS = ['Submitted (IST)', 'Name', 'Mobile (+91)', 'Email', 'City', 'Number of kids', 'Kids’ ages'];

/**
 * Where sign-ups are saved. Prefers a folder one level above the website
 * (not reachable from the web). Falls back to ./data, locked with .htaccess.
 */
function hf_storage_dir(): string
{
    $outside = dirname(__DIR__) . '/hopfest-signups';
    if ((is_dir($outside) || @mkdir($outside, 0750, true)) && is_writable($outside)) {
        return $outside;
    }
    $inside = __DIR__ . '/data';
    if (!is_dir($inside)) {
        @mkdir($inside, 0750, true);
    }
    if (!is_file($inside . '/.htaccess')) {
        @file_put_contents($inside . '/.htaccess', "Require all denied\nDeny from all\n");
    }
    if (!is_file($inside . '/index.html')) {
        @file_put_contents($inside . '/index.html', '');
    }
    return $inside;
}

const HF_GUARD = "<?php http_response_code(404); exit; ?>\n";

/** A .php file that shows nothing if opened in a browser, on any kind of server. */
function hf_csv_path(): string
{
    return hf_storage_dir() . '/signups.csv.php';
}

/** Stops spreadsheet apps from treating a value as a formula. */
function hf_cell(string $v): string
{
    return ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) ? "'" . $v : $v;
}

function hf_save(array $row): bool
{
    $path = hf_csv_path();
    $fh = @fopen($path, 'ab');
    if (!$fh) {
        return false;
    }
    flock($fh, LOCK_EX);
    if (filesize($path) === 0) {
        fwrite($fh, HF_GUARD);
        fputcsv($fh, HF_COLUMNS, ',', '"', '\\');
    }
    $ok = fputcsv($fh, array_map('hf_cell', $row), ',', '"', '\\') !== false;
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $ok;
}

function hf_read_all(): array
{
    $path = hf_csv_path();
    if (!is_file($path)) {
        return [];
    }
    $rows = [];
    $fh = fopen($path, 'rb');
    fgets($fh); // guard line
    $first = true;
    while (($r = fgetcsv($fh, 0, ',', '"', '\\')) !== false) {
        if ($first) { $first = false; continue; }
        if ($r !== [null]) { $rows[] = $r; }
    }
    fclose($fh);
    return $rows;
}

/** The saved sign-ups as plain CSV text (without the guard line). */
function hf_csv_text(): string
{
    $path = hf_csv_path();
    if (!is_file($path)) {
        return implode(',', HF_COLUMNS) . "\n";
    }
    $all = (string)file_get_contents($path);
    return str_starts_with($all, HF_GUARD) ? substr($all, strlen(HF_GUARD)) : $all;
}
