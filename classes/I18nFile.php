<?php

/**
 * Manage .po files 
 * Convert .ts into .po with linguist
 * Convert .po to utf-8 with notepad++
 */
class I18nFile {

    CONST EXTRACTED_PO_FILE_NAME = 'translation_extracted.po';
    CONST EXTRACTED_TS_FILE_NAME = 'translation_extracted.ts';
    CONST ORIGINAL_TS_FILE_NAME = 'translation.ts';
    CONST ORIGINAL_PO_FILE_NAME = 'translation.po';
    CONST CONTEXT_NAME = 'project_context';

    /**
     * Return active extensions from site.ini
     * @return array active extension  
     * @author gmorel
     */
    protected function _getActiveExtensions() {
        $ini = eZINI::instance('site.ini');
        $active_extensions = $ini->variable('ExtensionSettings', 'ActiveExtensions');
        return $active_extensions;
    }

    /**
     * Scan directory for files po/tsrecursively
     * @param string $Directory directory where we suppose there are po/ts files
     * @param array $result search result reference with translation.po/translation.ts file url
     * @param string $file_type translation.po or translation.ts
     * @author gmorel
     * @tested gmorel
     */
    public function scanDirectoryForFiles($Directory, &$result, $file_type) {
        if (is_dir($Directory)) {
            $MyDirectory = opendir($Directory);
            while ($Entry = @readdir($MyDirectory)) {
                if (is_dir($Directory . '/' . $Entry) && $Entry != '.' && $Entry != '..') {
                    $this->scanDirectoryForFiles($Directory . '/' . $Entry, $result, $file_type);
                } else {
                    if ($Entry == $file_type) {
                        $result[] = $Directory . '/' . $Entry;
                    }
                }
            }
            closedir($MyDirectory);
        }
    }

    /**
     * Return extracted .po file name url
     * @param string $sourceFileURL source url
     * @return type 
     * @author gmorel
     * @tested gmorel
     */
    public function generateExtractedPoFileURL($sourceFileURL) {
        $path_parts = pathinfo($sourceFileURL);
        return $path_parts['dirname'] . '/' . self::EXTRACTED_PO_FILE_NAME;
    }

    /**
     * Return extracted .ts file name url
     * @param string $sourceFileURL source url
     * @return type 
     * @author gmorel
     * @tested gmorel
     */
    public function generateExtractedTsFileURL($sourceFileURL) {
        $path_parts = pathinfo($sourceFileURL);
        return $path_parts['dirname'] . '/' . self::EXTRACTED_TS_FILE_NAME;
    }

    /**
     * Indent a raw xml into a readable xml
     * @param string $xml source
     * @param int $indent_space nb space for one indent
     * @return string 
     */
    protected function _formatXmlString($xml, $indent_space = 4) {
        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

        // now indent the tags
        $token = strtok($xml, "\n");
        $result = ''; // holds formatted version as it is built
        $pad = 0; // initial indent
        $matches = array(); // returns from preg_matches()
        // scan each line and adjust indent based on opening/closing tags
        while ($token !== false) :

            // test for the various tag states
            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent = 0;
            // 2. closing tag - outdent now
            elseif (preg_match('/^<\/\w/', $token, $matches)) :
                $pad = $pad - $indent_space;
            // 3. opening tag - don't pad this one, only subsequent tags
            elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
                $indent = $indent_space;
            // 4. no indentation needed
            else :
                $indent = 0;
            endif;

            // pad the line with the required number of leading spaces
            $line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token = strtok("\n"); // get the next token
            $pad += $indent; // update the pad size for subsequent lines    
        endwhile;

        return $result;
    }

}
