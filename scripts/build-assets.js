#!/usr/bin/env node

'use strict';

var crypto = require('crypto');
var fs = require('fs');
var path = require('path');
var terser = require('terser');
var packageConfig = require('../package.json');

function getConfiguredDirectory(group, key) {
    var value = packageConfig[group] && packageConfig[group][key];

    if (typeof value !== 'string' || value.trim() === '') {
        throw new Error('Missing package.json path: ' + group + '.' + key);
    }

    return path.resolve(__dirname, '..', value);
}

var sourceJsDirectory = getConfiguredDirectory('source', 'js');
var targetJsDirectory = getConfiguredDirectory('target', 'js');

var scriptEntries = [
    {
        source: path.join(sourceJsDirectory, 'rrze-siteimprove.js'),
        output: path.join(targetJsDirectory, 'rrze-siteimprove.js'),
        dependencies: ['jquery']
    }
];

var legacyFiles = [
    'analytics.asset.php',
    'analytics.js',
    'settings.asset.php',
    'settings.css',
    'settings-rtl.css',
    'settings.js',
    'siteimprove.asset.php',
    'siteimprove.js',
    'rrze-siteimprove-admin-rtl.css',
    'rrze-siteimprove-admin.asset.php',
    'rrze-siteimprove-admin.css',
    'rrze-siteimprove-admin.css.map',
    'rrze-siteimprove-admin.js',
    'rrze-siteimprove-admin.js.map',
    'rrze-siteimprove-rtl.css',
    'rrze-siteimprove.css',
    'rrze-siteimprove.css.map'
];

function ensureDir(directory) {
    if (!fs.existsSync(directory)) {
        fs.mkdirSync(directory, { recursive: true });
    }
}

function parseArgs(argv) {
    var mode = 'dev';
    var watch = false;
    var i;

    for (i = 2; i < argv.length; i++) {
        if (argv[i] === 'dev' || argv[i] === 'prod') {
            mode = argv[i];
        } else if (argv[i] === '--watch') {
            watch = true;
        }
    }

    return {
        mode: mode,
        watch: watch
    };
}

function contentHash(content) {
    return crypto.createHash('md5').update(content).digest('hex').slice(0, 20);
}

function formatPhpArray(values) {
    var out = [];
    var i;

    for (i = 0; i < values.length; i++) {
        out.push("'" + values[i].replace(/'/g, "\\'") + "'");
    }

    return 'array(' + out.join(', ') + ')';
}

function writeAssetFile(outputFile, dependencies, version) {
    var assetPath = outputFile.replace(/\.(js|css)$/, '.asset.php');
    var content = "<?php return array('dependencies' => "
        + formatPhpArray(dependencies)
        + ", 'version' => '"
        + version.replace(/'/g, "\\'")
        + "');\n";

    fs.writeFileSync(assetPath, content, 'utf8');
}

function removeFile(filePath) {
    if (fs.existsSync(filePath)) {
        fs.unlinkSync(filePath);
    }
}

function removeSourceMapFiles(directory) {
    var entries;
    var removed = 0;
    var i;
    var filePath;

    if (!fs.existsSync(directory)) {
        return removed;
    }

    entries = fs.readdirSync(directory, { withFileTypes: true });
    for (i = 0; i < entries.length; i++) {
        filePath = path.join(directory, entries[i].name);

        if (entries[i].isDirectory()) {
            removed += removeSourceMapFiles(filePath);
        } else if (entries[i].isFile() && path.extname(entries[i].name) === '.map') {
            fs.unlinkSync(filePath);
            removed++;
        }
    }

    return removed;
}

function removeLegacyFiles() {
    var i;

    for (i = 0; i < legacyFiles.length; i++) {
        removeFile(path.join(targetJsDirectory, legacyFiles[i]));
    }
}

async function buildScript(entry, mode) {
    var isProduction = mode === 'prod';
    var source = fs.readFileSync(entry.source, 'utf8');
    var result;
    var code;

    ensureDir(path.dirname(entry.output));

    result = await terser.minify(
        {
            [path.basename(entry.source)]: source
        },
        {
            compress: isProduction,
            mangle: isProduction,
            format: {
                beautify: !isProduction,
                comments: !isProduction
            },
            sourceMap: !isProduction
                ? {
                    filename: path.basename(entry.output),
                    url: path.basename(entry.output) + '.map'
                }
                : false
        }
    );

    if (result.error) {
        throw result.error;
    }

    code = result.code || '';
    fs.writeFileSync(entry.output, code, 'utf8');

    if (!isProduction && result.map) {
        fs.writeFileSync(entry.output + '.map', result.map, 'utf8');
    } else {
        removeFile(entry.output + '.map');
    }

    writeAssetFile(entry.output, entry.dependencies, contentHash(code));
}

async function buildAssets(mode) {
    var i;

    for (i = 0; i < scriptEntries.length; i++) {
        await buildScript(scriptEntries[i], mode);
    }

    removeLegacyFiles();

    if (mode === 'prod') {
        removeSourceMapFiles(targetJsDirectory);
    }
}

function watchDirectory(directory, callback) {
    var entries;
    var i;
    var entryPath;

    if (!fs.existsSync(directory)) {
        return;
    }

    fs.watch(directory, callback);
    entries = fs.readdirSync(directory, { withFileTypes: true });

    for (i = 0; i < entries.length; i++) {
        if (!entries[i].isDirectory()) {
            continue;
        }

        entryPath = path.join(directory, entries[i].name);
        watchDirectory(entryPath, callback);
    }
}

function watchAssets(mode) {
    var timeout;

    function rebuildAssets() {
        clearTimeout(timeout);
        timeout = setTimeout(function rebuildChangedAssets() {
            buildAssets(mode)
                .then(function onBuildDone() {
                    console.log('Assets built (' + mode + ').');
                })
                .catch(function onBuildError(error) {
                    console.error(error);
                });
        }, 100);
    }

    watchDirectory(sourceJsDirectory, rebuildAssets);
    rebuildAssets();

    console.log('Asset watch active (' + mode + ').');
}

function main() {
    var args = parseArgs(process.argv);

    if (args.watch) {
        watchAssets(args.mode);
        return;
    }

    buildAssets(args.mode)
        .then(function onBuildDone() {
            console.log('Assets built (' + args.mode + ').');
        })
        .catch(function onBuildError(error) {
            console.error(error);
            process.exitCode = 1;
        });
}

main();
