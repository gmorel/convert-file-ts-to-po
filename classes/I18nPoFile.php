<?php

/**
 * Manage .po files 
 * Convert .ts into .po with linguist
 * Convert .po to utf-8 with notepad++
 */
class I18nPoFile extends I18nFile {

    /**
     * Return PoFiles
     * @return array .po files 
     * @author gmorel
     */
    public function getPoFiles() {
        $result = array();

        // Search .po in /share folder
        $this - scanDirectoryForPoFiles('./share/translations', $result);

        // Search .po in each extensions
        $active_extensions = $this->_getActiveExtensions();
        foreach ($active_extensions as $active_extension) {
            $this->scanDirectoryForPoFiles('./extension/' . $active_extension . '/translations', $result);
        }
        return $result;
    }

    /**
     * Scan directory for .po files recursively
     * @param string $Directory directory where we suppose there are po files
     * @param array $result search result reference with translation.po file url
     * @author gmorel
     * @tested gmorel
     */
    public function scanDirectoryForPoFiles($Directory, &$result) {
        $this->scanDirectoryForFiles($Directory, $result, parent::EXTRACTED_PO_FILE_NAME);
    }

    /**
     * Exec function po2ts
     * @param string $ts_file_path source
     * @param string $po_file_path target
     * @return type 
     * @author gmorel
     * @tested gmorel
     */
    public function execPo2Ts($po_file_path, $ts_file_path) {
        $exec = exec('po2ts ' . escapeshellarg($po_file_path) . ' ' . escapeshellarg($ts_file_path));
        return $exec;
    }

    /**
     * Clean a .po file by removing the 2 first lines 
     * msgid ""
     * msgstr ""
     * preventing PoEdit to save file
     * @param string $file_path file path
     * @param string $new_file_path file path of the cleaned file
     * @return array removed lines
     */
    public function cleanPoFile($file_path, $new_file_path) {
        if (empty($file_path) || !file_exists($file_path)) {
            return false;
        }
        if ( 0 == filesize( $file_path ) )
        {
            return false;
        }
        $file = file($file_path);
        
        $array_line_to_remove = array();
        $first_line = "msgid \"\"\n";
        $second_line = "msgstr \"\"\n";
        $third_line = '"Project-Id-Version: PACKAGE VERSION\n"'."\n";
        $fourth_line = '"Report-Msgid-Bugs-To: \n"'."\n";
        $fifth_line = '"POT-Creation-Date: 2012-06-18 09:44+0200\n"'."\n";
        $sixth_line = '"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"'."\n";
        $seventh_line = '"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"'."\n";
        $eight_line = '"Language-Team: LANGUAGE <LL@li.org>\n"'."\n";
        
        $array_line_to_remove[] = $third_line;
        $array_line_to_remove[] = $fourth_line;
        $array_line_to_remove[] = $sixth_line;
        $array_line_to_remove[] = $seventh_line;
        $array_line_to_remove[] = $eight_line;
        
//        $lines_removed = array();
//        if ($file[2] == $third_line) {
//            $lines_removed[] = $file[2];
//            unset($file[2]);
//        }
        $array_cleaned_file = $this->_removeLines($array_line_to_remove, $file);

        file_put_contents($new_file_path, $array_cleaned_file);
    }
    
    protected function _removeLines($array_line_to_remove, $array_file)
    {
        $array_new_file = array();
        foreach ($array_file as $file_line) {
            $keep = true;
            foreach ($array_line_to_remove as $line_to_remove) {
                if($line_to_remove == $file_line)
                {
                    $keep = false;
                }
            }
            if($keep)
            {
                $array_new_file[] = $file_line;
            }
        }
        return $array_new_file;
    }

}