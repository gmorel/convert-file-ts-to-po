<?php

include_once( 'kernel/common/template.php' );

$cli = eZCLI::instance();

if (!$isQuiet) {
    $cli->output("*********** Starting INCLUDE PO FILE DATA INTO TS FILE ***********");
}


$I18nTsFile = new I18nTsFile();
$I18nPoFile = new I18nPoFile();

// Get all ts file from the application
$ts_files = $I18nTsFile->getTsFiles();

foreach ($ts_files as $ts_file) {
    if (empty($ts_file) || !file_exists($ts_file)){
    } else {
        // Get file_path names
        $po_file_path = $I18nPoFile->generateExtractedPoFileURL($ts_file);
        $ts_file_path = $I18nTsFile->generateExtractedTsFileURL($ts_file);

        if (empty($po_file_path) || !file_exists($po_file_path)) {
            if (!$isQuiet) {
                $cli->output(" - ERROR Can't find file $po_file_path");
            }
        } else {
            if (!$isQuiet) {
                $cli->output("**************************************************************");
            }
            // Convert .po file into .ts
            
            $exec = $I18nPoFile->execPo2Ts($po_file_path, $ts_file_path);
            if (!$isQuiet) {
                $cli->output(" - File $po_file_path converted successfully into $ts_file_path");
            }
            $ret = $I18nTsFile->insertExtractedTs($ts_file);
            if ($ret) {
                if (!$isQuiet) {
                    $cli->output(" - File $ts_file_path inserted successfully into $ts_file");
                }
            } else {
                if (!$isQuiet) {
                    $cli->output(" - ERROR Can't insert file $ts_file_path into $ts_file");
                }
            }
        }
    }
}
if (!$isQuiet) {
    $cli->output("*********** Ending INCLUDE PO FILE DATA INTO TS FILE ***********");
}
?>


