<?php

/**
 * Manage .ts files  
 * Convert .ts into .po with linguist
 * Convert .po to utf-8 with notepad++
 */
class I18nTsFile extends I18nFile {

    CONST XML_HEADER = '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE TS><TS version="2.0"></TS>';

    /**
     * Return TsFiles
     * @return array .ts files 
     * @author gmorel
     */
    public function getTsFiles() {
        $result = array();

        // Search .ts in /share folder
        $this->scanDirectoryForTsFiles('./share/translations', $result);

        // Search .ts in each extensions
        $active_extensions = $this->_getActiveExtensions();
        foreach ($active_extensions as $active_extension) {
            $this->scanDirectoryForTsFiles('./extension/' . $active_extension . '/translations', $result);
        }
        return $result;
    }

    /**
     * Scan directory for .ts files recursively
     * @param string $Directory directory where we suppose there are ts files
     * @param array $result search result reference with translation.ts file url
     * @author gmorel
     * @tested gmorel
     */
    public function scanDirectoryForTsFiles($Directory, &$result) {
        $this->scanDirectoryForFiles($Directory, $result, parent::ORIGINAL_TS_FILE_NAME);
    }

    /**
     * Generate extracted .ts file from a full .ts file
     *
     * elements looks like this : 
     * <message>
     *      <source>Current user</source>
     *      <translation type="unfinished"></translation>
     * </message>
     * 
     * @param string $extracted_file_name source .ts file name
     * @author gmorel
     * @tested gmorel
     */
    public function generateTsFileFollowingDatas($source_file_name, $context_name) {

        if (empty($source_file_name) || !file_exists($source_file_name)) {
            return false;
        }

        // Get new file url
        $extrated_file_name = $this->generateExtractedTsFileURL($source_file_name);

        // Clear file
        file_put_contents($extrated_file_name, '');
        $sourceSimpleXMLElement = simplexml_load_file($source_file_name);
        $extractedSimpleXMLElement = new ExSimpleXMLElement(self::XML_HEADER);
        $language = $sourceSimpleXMLElement['language'];
        $extractedSimpleXMLElement->addAttribute('language', (string) $language);
        foreach ($sourceSimpleXMLElement->context as $value) {
            if (strpos((string) $value->name, $context_name) === false) {
                // Context name not found
            } else {
                foreach ($value->message as $message) {
                    $message->source = '(' . (string) $value->name . ')' . (string) $message->source;
                }
                $extractedSimpleXMLElement->appendXML($value);
            }
        }

        $indented_xml = $this->_formatXmlString($extractedSimpleXMLElement->asXML());

        file_put_contents($extrated_file_name, $indented_xml);

        return true;
    }

    /**
     * Insert an extracted ts file into a full ts file
     * @param string $original_ts_file_path original ts file path
     * @author gmorel
     * @tested gmorel
     */
    public function insertExtractedTs($original_ts_file_path) {
        $extracted_ts_file_path = $this->generateExtractedTsFileURL($original_ts_file_path);
        $originalSimpleXMLElement = simplexml_load_file($original_ts_file_path);
        $extractedSimpleXMLElement = simplexml_load_file($extracted_ts_file_path);

        foreach ($extractedSimpleXMLElement->context as $extractedSimpleXMLValue) {
            // Avoid ts to po conversion issue 
            $extracted_context_name = (string) $extractedSimpleXMLValue->name;
            if (!empty($extracted_context_name)) {
                // Look for the context name in the original file
                foreach ($originalSimpleXMLElement->context as $originalSimpleXMLValue) {
                    if ($extracted_context_name == (string) $originalSimpleXMLValue->name) {
                        // Remove the context from the key
                        foreach ($extractedSimpleXMLValue->message as $extractedSimpleXMLMessage) {
                            $extractedSimpleXMLMessage->source = str_replace('(' . $extracted_context_name . ')', '', (string) $extractedSimpleXMLMessage->source);
                            
                            // If keys are the same
                            foreach ($originalSimpleXMLValue->message as $originalSimpleXMLMessage) {
                                if ((string) $originalSimpleXMLMessage->source == (string) $extractedSimpleXMLMessage->source) {
                                    if ((string) $originalSimpleXMLMessage->translation != (string) $extractedSimpleXMLMessage->translation) {
                                        // Replace the original value by the extracted value 
                                        $originalSimpleXMLMessage->translation = (string) $extractedSimpleXMLMessage->translation;
                                        unset($originalSimpleXMLMessage->translation['type']);
                                    }
                                    
                                }

                                // Set empty element as unfinished
                                $originalSimpleXMLMessage->translation = trim((string) $originalSimpleXMLMessage->translation);
                                if(empty($originalSimpleXMLMessage->translation))
                                {
                                    $originalSimpleXMLMessage->translation['type'] = 'unfinished';
                                }
                            }  
                        }
                    }
                }
            }
        }
        
        //Convert object into string
        $result = $originalSimpleXMLElement->asXML();
        
        // Clean <location line="0"/> from .ts file
        $result = str_replace('<location line="0"/>', '', $result);
        
        file_put_contents($original_ts_file_path, $result);
        return true;
    }

    /**
     * Exec function ts2po
     * @param string $ts_file_path source
     * @param string $po_file_path target
     * @return type 
     * @author gmorel
     * @tested gmorel
     */
    public function execTs2Po($ts_file_path, $po_file_path) {
        $exec = exec('ts2po ' . escapeshellarg($ts_file_path) . ' ' . escapeshellarg($po_file_path));
        return $exec;
    }

}