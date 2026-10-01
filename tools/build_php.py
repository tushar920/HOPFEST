#!/usr/bin/env python3
"""Build the PHP upload package for HOP FEST.

    python3 tools/build_php.py [output.zip]

Takes index.html + server/*.php + only the assets the page uses, and writes
a zip whose contents go straight into the website's root folder.
"""
import os, re, shutil, sys, tempfile, zipfile

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.abspath(sys.argv[1]) if len(sys.argv) > 1 else os.path.join(ROOT, 'hopfest-website-php.zip')
CONTACT_RE = r'window\.HOPFEST_CONTACT=\{[^<]*?\};'
CONTACT_PHP = ('window.HOPFEST_CONTACT=<?= json_encode((array)($hfcfg["contact"] ?? []), '
               'JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES) ?>;')

html = open(os.path.join(ROOT, 'index.html'), encoding='utf-8').read()
assert '<?' not in html, 'index.html must not contain "<?"'
assert len(re.findall(CONTACT_RE, html)) == 1, 'contact line missing from index.html'
page = "<?php $hfcfg = require __DIR__ . '/config.php'; ?>" + re.sub(CONTACT_RE, lambda m: CONTACT_PHP, html)

files = set()
for ref in re.findall(r'\./((?:assets/|favicon|apple-touch)[^`"\'\s)]*)', html):
    if '${' in ref:
        files.update(os.path.join(os.path.dirname(ref), f) for f in os.listdir(os.path.join(ROOT, os.path.dirname(ref))))
    else:
        files.add(ref)
files.update(['favicon.ico', 'assets/images/rewards/medal.svg'])
missing = [f for f in files if not os.path.isfile(os.path.join(ROOT, f))]
assert not missing, f'missing files: {missing}'

with tempfile.TemporaryDirectory() as tmp:
    open(os.path.join(tmp, 'index.php'), 'w', encoding='utf-8').write(page)
    for f in os.listdir(os.path.join(ROOT, 'server')):
        if f.endswith('.php'):
            shutil.copy(os.path.join(ROOT, 'server', f), tmp)
    for f in sorted(files):
        os.makedirs(os.path.join(tmp, os.path.dirname(f)), exist_ok=True)
        shutil.copy(os.path.join(ROOT, f), os.path.join(tmp, f))
    if os.path.exists(OUT):
        os.remove(OUT)
    with zipfile.ZipFile(OUT, 'w', zipfile.ZIP_DEFLATED) as z:
        for d, _, fs in os.walk(tmp):
            for f in fs:
                p = os.path.join(d, f)
                z.write(p, os.path.relpath(p, tmp))
    n = sum(len(fs) for _, _, fs in os.walk(tmp))
print(f'{OUT}  ({n} files, {os.path.getsize(OUT) / 1e6:.1f} MB)')
