const fs = require('fs');
const path = require('path');

// Pages to verify
const pages = [
    'pages/nasabah/index.php',
    'pages/pinjaman/index.php',
    'pages/angsuran/index.php',
    'pages/users/index.php',
    'pages/cabang/index.php',
    'pages/pembayaran/index.php',
    'pages/kas_bon/index.php',
    'pages/pengeluaran/index.php',
    'pages/kas_petugas/index.php',
    'pages/setting_bunga/index.php',
    'pages/family_risk/index.php'
];

// Required patterns
const patterns = {
    dataTableCSS: /dataTables\.bootstrap5\.min\.css/,
    dataTableJS: /jquery\.dataTables\.min\.js/,
    sweetAlert2: /sweetalert2@11/,
    select2CSS: /select2.*\.min\.css/,
    select2JS: /select2.*\.min\.js/,
    flatpickrCSS: /flatpickr.*\.min\.css/,
    flatpickrJS: /flatpickr.*\.min\.js/,
    jquery: /jquery-.*\.min\.js/,
    dataTableInit: /DataTable\(/,
    select2Init: /select2\(/,
    flatpickrInit: /flatpickr\(/,
    swalInit: /Swal\.fire/
};

const results = [];

pages.forEach(page => {
    const filePath = path.join(__dirname, page);
    const result = {
        page: page,
        dataTableCSS: false,
        dataTableJS: false,
        sweetAlert2: false,
        select2CSS: false,
        select2JS: false,
        flatpickrCSS: false,
        flatpickrJS: false,
        jquery: false,
        dataTableInit: false,
        select2Init: false,
        flatpickrInit: false,
        swalInit: false
    };

    if (fs.existsSync(filePath)) {
        const content = fs.readFileSync(filePath, 'utf8');
        
        // Check each pattern
        for (const [key, pattern] of Object.entries(patterns)) {
            result[key] = pattern.test(content);
        }
    } else {
        console.log(`❌ File not found: ${page}`);
    }

    results.push(result);
});

// Generate report
console.log('\n=== INTEGRATION VERIFICATION REPORT ===\n');

results.forEach(result => {
    console.log(`\n${result.page}:`);
    console.log(`  DataTable CSS: ${result.dataTableCSS ? '✓' : '✗'}`);
    console.log(`  DataTable JS: ${result.dataTableJS ? '✓' : '✗'}`);
    console.log(`  SweetAlert2: ${result.sweetAlert2 ? '✓' : '✗'}`);
    console.log(`  Select2 CSS: ${result.select2CSS ? '✓' : '✗'}`);
    console.log(`  Select2 JS: ${result.select2JS ? '✓' : '✗'}`);
    console.log(`  Flatpickr CSS: ${result.flatpickrCSS ? '✓' : '✗'}`);
    console.log(`  Flatpickr JS: ${result.flatpickrJS ? '✓' : '✗'}`);
    console.log(`  jQuery: ${result.jquery ? '✓' : '✗'}`);
    console.log(`  DataTable Init: ${result.dataTableInit ? '✓' : '✗'}`);
    console.log(`  Select2 Init: ${result.select2Init ? '✓' : '✗'}`);
    console.log(`  Flatpickr Init: ${result.flatpickrInit ? '✓' : '✗'}`);
    console.log(`  Swal Init: ${result.swalInit ? '✓' : '✗'}`);
});

// Summary
let totalChecks = results.length * Object.keys(patterns).length;
let passedChecks = 0;

results.forEach(result => {
    for (const key of Object.keys(patterns)) {
        if (result[key]) passedChecks++;
    }
});

console.log(`\n\n=== SUMMARY ===`);
console.log(`Total Checks: ${passedChecks}/${totalChecks}`);
console.log(`Completion: ${((passedChecks / totalChecks) * 100).toFixed(2)}%`);

// Save results
fs.writeFileSync('verification_results.json', JSON.stringify(results, null, 2));
console.log('\nResults saved to verification_results.json');
