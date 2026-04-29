<?php
/**
 * Validation script for Museum Railway Timetable plugin
 * Run this before deploying: php scripts/validate.php
 */

$errors = [];
$warnings = [];
$checks = 0;

echo "🔍 Validating Museum Railway Timetable Plugin...\n\n";

// 1. Check required files exist
echo "1. Checking required files...\n";
$required_files = [
    'museum-railway-timetable.php',
    'uninstall.php',
    'inc/assets.php',
    'inc/admin-page.php',
    'inc/admin-page/admin-list.php',
    'inc/import-lennakatten/loader.php',
    'inc/shortcodes.php',
    'inc/cpt.php',
    'inc/functions/helpers.php',
    'inc/functions/services.php',
    'inc/functions/journey-loader.php',
    'assets/admin-base.css',
    'assets/admin-timetable.css',
    'assets/admin-timetable-overview.css',
    'assets/admin-meta-boxes.css',
    'assets/admin-dashboard.css',
    'assets/admin-ui.css',
    'assets/admin-responsive.css',
    'assets/admin-utils.js',
    'assets/admin-route-ui.js',
    'assets/admin-stoptimes-ui.js',
    'assets/admin-timetable-services-ui.js',
    'assets/admin.js',
    'languages/museum-railway-timetable.pot',
    'languages/museum-railway-timetable-sv_SE.po',
];

foreach ($required_files as $file) {
    $checks++;
    if (!file_exists($file)) {
        $errors[] = "Missing required file: $file";
        echo "  ❌ Missing: $file\n";
    } else {
        echo "  ✅ $file\n";
    }
}

// 2. Check PHP syntax
echo "\n2. Checking PHP syntax...\n";
$php_files = array_merge(
    glob('*.php') ?: [],
    glob('inc/*.php') ?: [],
    glob('inc/*/*.php') ?: [],
    glob('inc/*/*/*.php') ?: [],
    glob('inc/*/*/*/*.php') ?: []
);
$php_files = array_unique(array_filter($php_files));

foreach ($php_files as $file) {
    if (strpos($file, 'scripts/validate.php') !== false) continue;
    $checks++;
    $output = [];
    $return_var = 0;
    exec("php -l \"$file\" 2>&1", $output, $return_var);
    if ($return_var !== 0) {
        $errors[] = "Syntax error in $file: " . implode("\n", $output);
        echo "  ❌ $file\n";
        echo "     " . implode("\n     ", $output) . "\n";
    } else {
        echo "  ✅ $file\n";
    }
}

// 3. Check ABSPATH protection
echo "\n3. Checking ABSPATH protection...\n";
foreach ($php_files as $file) {
    if (strpos($file, 'scripts/validate.php') !== false) continue;
    if (strpos($file, 'uninstall.php') !== false) continue; // uninstall.php has different check
    $checks++;
    $content = file_get_contents($file);
    if (!preg_match('/if\s*\(\s*!\s*defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)\s*\)/', $content)) {
        $warnings[] = "Missing ABSPATH check in $file";
        echo "  ⚠️  $file (missing ABSPATH check)\n";
    } else {
        echo "  ✅ $file\n";
    }
}

// 4. Check for inline styles (CSS custom properties like --service-count are allowed)
echo "\n4. Checking for inline styles...\n";
foreach ($php_files as $file) {
    if (strpos($file, 'scripts/validate.php') !== false) continue;
    $checks++;
    $content = file_get_contents($file);
    if (preg_match('/style\s*=\s*["\']/', $content)) {
        // Allow style="--var: value" (CSS custom properties for dynamic values)
        if (preg_match('/style\s*=\s*[^>]*--[a-zA-Z-]+\s*:/', $content)) {
            echo "  ✅ $file (CSS custom property only)\n";
        } else {
            $warnings[] = "Inline style found in $file";
            echo "  ⚠️  $file (contains inline styles)\n";
        }
    } else {
        echo "  ✅ $file\n";
    }
}

// 5. Check plugin header
echo "\n5. Checking plugin header...\n";
$checks++;
$main_file = file_get_contents('museum-railway-timetable.php');
$required_headers = [
    'Plugin Name',
    'Description',
    'Version',
    'Text Domain',
];
foreach ($required_headers as $header) {
    if (strpos($main_file, $header . ':') === false) {
        $errors[] = "Missing plugin header: $header";
        echo "  ❌ Missing header: $header\n";
    } else {
        echo "  ✅ Header: $header\n";
    }
}

// 6. Check text domain consistency
echo "\n6. Checking text domain consistency...\n";
$checks++;
$text_domain = 'museum-railway-timetable';
$domain_matches = 0;
$domain_issues = 0;
foreach ($php_files as $file) {
    if (strpos($file, 'scripts/validate.php') !== false) continue;
    $content = file_get_contents($file);
    preg_match_all('/__(\(|[\'"])[^\'"\)]*[\'"],\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
    if (!empty($matches[2])) {
        foreach ($matches[2] as $domain) {
            if ($domain === $text_domain) {
                $domain_matches++;
            } else {
                $domain_issues++;
                $warnings[] = "Inconsistent text domain in $file: found '$domain', expected '$text_domain'";
            }
        }
    }
}
if ($domain_issues === 0) {
    echo "  ✅ Text domain consistent ($domain_matches matches)\n";
} else {
    echo "  ⚠️  Found $domain_issues inconsistent text domain(s)\n";
}

// 7. Check CSS files exist and are valid
echo "\n7. Checking CSS files...\n";
$css_files = [
    'assets/admin-base.css',
    'assets/admin-timetable.css',
    'assets/admin-timetable-overview.css',
    'assets/admin-meta-boxes.css',
    'assets/admin-dashboard.css',
    'assets/admin-ui.css',
    'assets/admin-responsive.css',
];
foreach ($css_files as $css_file) {
    $checks++;
    if (file_exists($css_file)) {
        $css = file_get_contents($css_file);
        if (strlen($css) > 0) {
            echo "  ✅ $css_file\n";
        } else {
            $errors[] = "CSS file is empty: $css_file";
            echo "  ❌ $css_file is empty\n";
        }
    } else {
        $errors[] = "CSS file missing: $css_file";
        echo "  ❌ $css_file missing\n";
    }
}

// 8. Check JS files exist and are valid
echo "\n8. Checking JavaScript files...\n";
$admin_js_files = [
    'assets/admin-utils.js',
    'assets/admin-route-ui.js',
    'assets/admin-stoptimes-ui.js',
    'assets/admin-timetable-services-ui.js',
    'assets/admin.js',
];
foreach ($admin_js_files as $js_file) {
    $checks++;
    if (file_exists($js_file)) {
        $js = file_get_contents($js_file);
        if (strlen($js) > 0) {
            echo "  ✅ $js_file\n";
        } else {
            $errors[] = "JS file is empty: $js_file";
            echo "  ❌ $js_file is empty\n";
        }
    } else {
        $errors[] = "JS file missing: $js_file";
        echo "  ❌ $js_file missing\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 Validation Summary\n";
echo str_repeat("=", 60) . "\n";
echo "Total checks: $checks\n";
echo "Errors: " . count($errors) . "\n";
echo "Warnings: " . count($warnings) . "\n\n";

if (!empty($errors)) {
    echo "❌ ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  - $warning\n";
    }
    echo "\n";
}

if (empty($errors) && empty($warnings)) {
    echo "✅ All validations passed! Project is ready to deploy.\n";
    exit(0);
} elseif (empty($errors)) {
    echo "⚠️  Project has warnings but no errors. Review warnings before deploying.\n";
    exit(0);
} else {
    echo "❌ Validation failed! Please fix errors before deploying.\n";
    exit(1);
}
