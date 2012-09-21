convert-file-ts-to-po
=====================

Description
-------------
+   Allow to convert all .ts translation files in a eZ Publish project into .po files readable by PoEdit.
+   Can also extract a part of a .ts file from a context.

Goal 
-------------
If you are using the framework php eZ Publish which managed i18n via .ts files. And if your customer only wants to do the translation via the PoEdit software instead of using linguit. This tool is for you.

It will generate .po file, then translate them via PoEdit and insert the new translation inside the original .ts file.

If some of your .tpl are directly on the design folder instead of inside an extension. You might want to translate only the sentences used in you .tpl and not the default eZ ones. You will need to give a particular prefix in your context/name element to allow the extension to extract only the relevant sentences.
For example add the context 'your_project':

	{'Last name'|i18n("design/your_project/contact")}
    ezpI18n::tr( "design/your_project/contact", 'Last name') ;

    <context>
        <name>design/your_project/contact</name>
        <message>
              <source>Last name</source>
              <translation>Nom</translation>
          </message>
    </context>

Then on the file classes/I18nFile.php, modify the const CONTEXT_NAME
CONST CONTEXT_NAME = 'your_project';

This will then extract only the translation having 'your_project' as context int the .po geerated file

How to use
-------------
Get Translate Toolkit from their site
http://sourceforge.net/projects/translate/files/Translate%20Toolkit/
Or if you prefer the file "translate-toolkit_1.9.0-1_all.deb" is available in the "extension/convert_file_ts_to_po/info/" folder too.
sudo dpkg -i translate-toolkit_1.9.0-1_all.deb to install

This extension will use these functions
http://translate.sourceforge.net/wiki/toolkit/ts2po?redirect=1
    ts2po translation_extracted.ts translation_extracted.po
    po2ts translation_extracted.po translation_extracted.ts


php runcronjobs.php -d -c extract_ts_file_from_original_ts_file
This CRON used as a CLI extract data from all translation.ts having the specified context into a file translation_extracted.ts.
So as to allow PoEdit to used key specfied several times, we temporary add the context to the .po key using this format (CONTEXT)KEY

You then can send all translation_extracted.po files to translation using PoEdit

Once .po file are completed, you can put back the .po file where they were generated first and launch the CRON bellow
php runcronjobs.php -d -c include_ts_file_into_original_ts_file
It will insert all data from the .po file to the translation.ts file in the same folder. And this for each translation.ts file you can have in your extensions and share files.


Don't forget to add your extension in your site.ini settings 

    [ExtensionSettings]
    ActiveExtensions[]
    ActiveExtensions[]=convert_file_ts_to_po

And to change the context in extension/convert_file_ts_to_po/classes/I18nFile.php
    CONST CONTEXT_NAME = 'xxx';
