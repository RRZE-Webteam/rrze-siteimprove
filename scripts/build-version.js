/* eslint-disable no-console */
'use strict';

var fs = require('fs');
var path = require('path');

function readJson(filePath) {
    var raw = fs.readFileSync(filePath, 'utf8');
    return JSON.parse(raw);
}

function writeJson(filePath, obj) {
    var out = JSON.stringify(obj, null, 2) + '\n';
    fs.writeFileSync(filePath, out, 'utf8');
}

function parseSemver(version) {
    var m = version.match(/^(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z.-]+))?$/);
    if (!m) {
        throw new Error('Invalid semver: ' + version);
    }

    return {
        major: parseInt(m[1], 10),
        minor: parseInt(m[2], 10),
        patch: parseInt(m[3], 10),
        prerelease: m[4] || ''
    };
}

function formatSemver(v) {
    var base = String(v.major) + '.' + String(v.minor) + '.' + String(v.patch);
    if (v.prerelease) {
        return base + '-' + v.prerelease;
    }
    return base;
}

function bumpDev(version) {
    var v = parseSemver(version);
    var m;
    var n;

    if (!v.prerelease) {
        v.prerelease = '1';
        return formatSemver(v);
    }

    m = v.prerelease.match(/^(\d+)$/);
    if (!m) {
        v.prerelease = '1';
        return formatSemver(v);
    }

    n = parseInt(m[1], 10);
    v.prerelease = String(n + 1);

    return formatSemver(v);
}

function bumpProd(version) {
    var v = parseSemver(version);

    v.prerelease = '';
    v.patch = v.patch + 1;

    return formatSemver(v);
}

function bumpRelease(version) {
    var v = parseSemver(version);

    v.prerelease = '';
    v.minor = v.minor + 1;
    v.patch = 0;

    return formatSemver(v);
}

function replaceInFile(filePath, replacer) {
    var content = fs.readFileSync(filePath, 'utf8');
    var updated = replacer(content);

    if (updated !== content) {
        fs.writeFileSync(filePath, updated, 'utf8');
    }
}

function setReadmeTxtVersion(pluginRoot, newVersion) {
    var filePath = path.join(pluginRoot, 'readme.txt');
    var replacements = 0;

    if (!fs.existsSync(filePath)) {
        return;
    }

    replaceInFile(filePath, function replaceReadmeVersion(content) {
        content = content.replace(
            /^(Stable tag:\s*)(.+)$/m,
            function replaceStableTag(match, p1) {
                replacements++;
                return p1 + newVersion;
            }
        );

        content = content.replace(
            /^(Version:\s*)(.+)$/m,
            function replaceVersion(match, p1) {
                replacements++;
                return p1 + newVersion;
            }
        );

        return content;
    });

    if (replacements === 0) {
        throw new Error('No version field found in readme.txt');
    }
}

function setPluginVersion(pluginRoot, pkg, newVersion) {
    var filePath = path.join(pluginRoot, pkg.main);
    var replacements = 0;

    if (!pkg.main || typeof pkg.main !== 'string') {
        throw new Error('package.json has no valid "main" entry');
    }

    if (!fs.existsSync(filePath)) {
        throw new Error('Plugin main file not found: ' + filePath);
    }

    replaceInFile(filePath, function replacePluginVersion(content) {
        content = content.replace(
            /^(\s*(?:\*\s*)?Version:\s*)(.+)$/m,
            function replaceHeaderVersion(match, p1) {
                replacements++;
                return p1 + newVersion;
            }
        );

        return content;
    });

    if (replacements === 0) {
        throw new Error('No plugin header version field found in ' + pkg.main);
    }
}

function setConfigVersion(pluginRoot, newVersion) {
    var filePath = path.join(pluginRoot, 'includes', 'Config.php');
    var replacements = 0;

    if (!fs.existsSync(filePath)) {
        throw new Error('Config file not found: ' + filePath);
    }

    replaceInFile(filePath, function replaceConfigVersion(content) {
        return content.replace(
            /(\s*'version'\s*=>\s*')[^']*(')/,
            function replaceVersion(match, p1, p2) {
                replacements++;
                return p1 + newVersion + p2;
            }
        );
    });

    if (replacements === 0) {
        throw new Error('No version field found in ' + filePath);
    }
}

function replaceConfigString(content, key, value) {
    var pattern = new RegExp("(\\s*'" + key + "'\\s*=>\\s*')[^']*(')");
    var replacements = 0;
    var updated;

    updated = content.replace(pattern, function replaceConfigValue(match, p1, p2) {
        replacements++;
        return p1 + value + p2;
    });

    if (replacements === 0) {
        throw new Error('No config field found: ' + key);
    }

    return updated;
}

function setConfigCompatibility(pluginRoot, pkg) {
    var filePath = path.join(pluginRoot, 'includes', 'Config.php');
    var compatibility = pkg.compatibility;

    if (!compatibility || typeof compatibility !== 'object') {
        return;
    }

    if (!fs.existsSync(filePath)) {
        throw new Error('Config file not found: ' + filePath);
    }

    replaceInFile(filePath, function replaceConfigCompatibility(content) {
        var updated = content;

        if (typeof compatibility.wprequires === 'string' && compatibility.wprequires.trim() !== '') {
            updated = replaceConfigString(updated, 'wprequires', compatibility.wprequires.trim());
        }

        if (typeof compatibility.wptestedup === 'string' && compatibility.wptestedup.trim() !== '') {
            updated = replaceConfigString(updated, 'wptestedup', compatibility.wptestedup.trim());
        }

        if (typeof compatibility.phprequires === 'string' && compatibility.phprequires.trim() !== '') {
            updated = replaceConfigString(updated, 'phprequires', compatibility.phprequires.trim());
        }

        return updated;
    });
}

function setPluginCompatibility(pluginRoot, pkg) {
    var compatibility = pkg.compatibility;
    var filePath;

    if (!pkg.main || typeof pkg.main !== 'string') {
        throw new Error('package.json has no valid "main" entry');
    }

    filePath = path.join(pluginRoot, pkg.main);

    if (!compatibility || typeof compatibility !== 'object') {
        return;
    }

    if (!fs.existsSync(filePath)) {
        throw new Error('Plugin main file not found: ' + filePath);
    }

    replaceInFile(filePath, function replacePluginCompatibility(content) {
        var updated = content;

        if (typeof compatibility.phprequires === 'string' && compatibility.phprequires.trim() !== '') {
            updated = updated.replace(
                /^(\s*(?:\*\s*)?Requires PHP:\s*)(.+)$/m,
                function replacePhpRequirement(match, p1) {
                    return p1 + compatibility.phprequires.trim();
                }
            );
        }

        if (typeof compatibility.wprequires === 'string' && compatibility.wprequires.trim() !== '') {
            updated = updated.replace(
                /^(\s*(?:\*\s*)?Requires at least:\s*)(.+)$/m,
                function replaceWpRequirement(match, p1) {
                    return p1 + compatibility.wprequires.trim();
                }
            );
        }

        return updated;
    });
}

function setPackageLockVersion(pluginRoot, newVersion) {
    var filePath = path.join(pluginRoot, 'package-lock.json');
    var lock;

    if (!fs.existsSync(filePath)) {
        return;
    }

    lock = readJson(filePath);
    lock.version = newVersion;

    if (
        lock.packages
        && typeof lock.packages === 'object'
        && lock.packages['']
        && typeof lock.packages[''] === 'object'
    ) {
        lock.packages[''].version = newVersion;
    }

    writeJson(filePath, lock);
}

function getPackageAuthor(pkg) {
    if (!pkg.author || typeof pkg.author !== 'object') {
        return '';
    }

    return pkg.author.name || '';
}

function getPackageSupportEmail(pkg) {
    if (!pkg.supports || typeof pkg.supports !== 'object') {
        return '';
    }

    return pkg.supports.email || '';
}

function getPackageIssueUrl(pkg) {
    if (!pkg.repository || typeof pkg.repository !== 'object') {
        return '';
    }

    return pkg.repository.issues || pkg.repository.url || '';
}

function replacePotHeader(content, key, value) {
    var pattern = new RegExp('^"' + key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ': .*\\\\n"$', 'm');

    if (!value) {
        return content;
    }

    return content.replace(pattern, '"' + key + ': ' + value.replace(/"/g, '\\"') + '\\n"');
}

function setPotHeaders(pluginRoot, pkg, newVersion) {
    var domainPath = pkg.domainPath || pkg.domainpath || 'languages';
    var potPath = path.join(pluginRoot, domainPath.replace(/^\/+/, ''), (pkg.textdomain || pkg.name) + '.pot');
    var author = getPackageAuthor(pkg);
    var supportEmail = getPackageSupportEmail(pkg);
    var contact = author && supportEmail ? author + ' <' + supportEmail + '>' : author;
    var content;
    var updated;

    if (!fs.existsSync(potPath)) {
        return;
    }

    content = fs.readFileSync(potPath, 'utf8');
    updated = content;
    updated = replacePotHeader(updated, 'Project-Id-Version', (pkg.title || pkg.name) + ' ' + newVersion);
    updated = replacePotHeader(updated, 'Report-Msgid-Bugs-To', getPackageIssueUrl(pkg));
    updated = replacePotHeader(updated, 'Last-Translator', contact);
    updated = replacePotHeader(updated, 'Language-Team', contact);

    if (updated !== content) {
        fs.writeFileSync(potPath, updated, 'utf8');
    }
}

function getNextVersion(mode, currentVersion) {
    if (mode === 'dev') {
        return bumpDev(currentVersion);
    }

    if (mode === 'prod') {
        return bumpProd(currentVersion);
    }

    if (mode === 'release') {
        return bumpRelease(currentVersion);
    }

    throw new Error('Unsupported mode: ' + mode);
}

function main() {
    var mode = process.argv[2];
    var pluginRoot = process.cwd();
    var packagePath = path.join(pluginRoot, 'package.json');
    var pkg;
    var current;
    var next;

    if (mode !== 'dev' && mode !== 'prod' && mode !== 'release') {
        console.error('Usage: node scripts/build-version.js dev|prod|release');
        process.exit(1);
    }

    pkg = readJson(packagePath);

    if (!pkg.version || typeof pkg.version !== 'string') {
        throw new Error('package.json has no valid version');
    }

    current = pkg.version;
    next = getNextVersion(mode, current);

    pkg.version = next;
    writeJson(packagePath, pkg);
    setPackageLockVersion(pluginRoot, next);

    setReadmeTxtVersion(pluginRoot, next);
    setPluginVersion(pluginRoot, pkg, next);
    setConfigVersion(pluginRoot, next);
    setConfigCompatibility(pluginRoot, pkg);
    setPluginCompatibility(pluginRoot, pkg);
    setPotHeaders(pluginRoot, pkg, next);

    console.log('Version bumped (' + mode + '): ' + current + ' -> ' + next);
}

main();
