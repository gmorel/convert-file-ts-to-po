<?php

include_once( 'kernel/common/template.php' );

$cli = eZCLI::instance();

if (!$isQuiet) {
    $cli->output("*********** Starting EXTRACT TS FILE DATA INTO PO FILE ***********");
}


$I18nTsFile = new I18nTsFile();
$I18nPoFile = new I18nPoFile();
// Get all ts file from the application
$ts_files = $I18nTsFile->getTsFiles();

foreach ($ts_files as $ts_file) {

    if (empty($ts_file) || !file_exists($ts_file)) { 
    } else {
        $ret = $I18nTsFile->generateTsFileFollowingDatas($ts_file, I18nFile::CONTEXT_NAME);
        if ($ret) {
            if (!$isQuiet) {
                $cli->output("**************************************************************");
            }
            
            
            // Get file_path names
            $po_file_path = $I18nPoFile->generateExtractedPoFileURL($ts_file);
            $ts_file_path = $I18nTsFile->generateExtractedTsFileURL($ts_file);

            if (!$isQuiet) {
                $cli->output(" - File $ts_file extracted successfully to $ts_file_path");
            }
            if (empty($ts_file_path) || !file_exists($ts_file_path)) {
                if (!$isQuiet) {
                    $cli->output(" - ERROR Can't find file $ts_file_path");
                }
            } else {
                // Convert .ts file into .po             
                $exec = $I18nTsFile->execTs2Po($ts_file_path, $po_file_path);
                $removed_lines = $I18nPoFile->cleanPoFile($po_file_path, $po_file_path);
        
                if (!$isQuiet) {
                    $cli->output(" - File $po_file_path converted successfully");
                }
            }
        }
    }
}
if (!$isQuiet) {
    $cli->output("*********** End EXTRACT TS FILE DATA INTO PO FILE ***********");
}
?>


