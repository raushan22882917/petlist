<?php
/**
 * Auto Theme Packager
 * Packs wp-content/themes/petslist-raushan into petslist-update.zip
 */

$source_dir = __DIR__ . '/wp-content/themes/petslist-raushan';
$zip_file   = __DIR__ . '/petslist-update.zip';
$zip_alt    = __DIR__ . '/petslist-raushan.zip';

if ( ! class_exists( 'ZipArchive' ) ) {
    die( "Error: ZipArchive PHP extension is not installed.\n" );
}

if ( ! is_dir( $source_dir ) ) {
    die( "Error: Source directory $source_dir not found.\n" );
}

// Remove old zip if present
if ( file_exists( $zip_file ) ) {
    @unlink( $zip_file );
}
if ( file_exists( $zip_alt ) ) {
    @unlink( $zip_alt );
}

$zip = new ZipArchive();
if ( $zip->open( $zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
    die( "Error: Cannot create zip file at $zip_file\n" );
}

$source_dir_real = realpath( $source_dir );
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator( $source_dir_real, RecursiveDirectoryIterator::SKIP_DOTS ),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;
foreach ( $files as $name => $file ) {
    if ( ! $file->isDir() ) {
        $filePath = $file->getRealPath();
        // Skip .git, node_modules, DS_Store
        if ( strpos( $filePath, '/.git/' ) !== false || strpos( $filePath, '/node_modules/' ) !== false || basename( $filePath ) === '.DS_Store' ) {
            continue;
        }
        $relativePath = 'petslist-raushan/' . substr( $filePath, strlen( $source_dir_real ) + 1 );
        $zip->addFile( $filePath, $relativePath );
        $count++;
    }
}

$zip->close();

// Also copy to petslist-raushan.zip
@copy( $zip_file, $zip_alt );

$size_mb = round( filesize( $zip_file ) / 1024 / 1024, 2 );

echo "SUCCESS: Packaged $count files into petslist-update.zip ({$size_mb} MB)!\n";
echo "Output files:\n";
echo "1. $zip_file\n";
echo "2. $zip_alt\n";

