<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Builds the fist page of an upload form for one file
 *
 */

class Publish_Form_PublishingFirst extends Zend_Form {

    public $config;

    /**
     * First publishing form of two forms
     * Here: Doctype + Upload-File
     *
     * @return void
     */
    public function init() {        

        $documentInSession = new Zend_Session_Namespace('document');
        $this->config = Zend_Registry::get('Zend_Config');

        //Select with different document types given by the used function
        $listOptions = $this->getXmlDocTypeFiles();

        if (count($listOptions)<=1) {
            $value = (array_keys($listOptions));
            $doctypes = $this->createElement('text', 'type');
            $doctypes->setLabel('selecttype')
                    ->setValue($value[0]);
        }
        else {
            $doctypes = $this->createElement('select', 'type');
            $doctypes->setLabel('selecttype')
                    ->setMultiOptions(array_merge(array('' => 'choose_valid_doctype'), $listOptions))
                    ->setRequired(true);
        }

        // get path to store files
        $tempPath = $this->config->path->workspace->temp;
        if (true === empty($tempPath)) 
            $tempPath = '../workspace/tmp/';        

        // get allowed filetypes
        $filetypes = $this->config->publish->filetypes->allowed;
        if (true === empty($filetypes)) 
            $filetypes = 'pdf,txt,html,htm';
        
        //get allowed file size
        $maxFileSize = (int) $this->config->publish->maxfilesize;
        if (true === empty($maxFileSize))
            $maxFileSize = 1024000; //1MB

        //get the initial number of file fields, toto: aus der config holen
        $number_of_files = (int) $this->config->form->first->numberoffiles;
        if (true === empty($number_of_files))
            $number_of_files = 1;

        //show Bibliographie?
        $bib = $this->config->form->first->bibliographie == 1;
        if (true === empty($bib))
            $bib = 0;

        $fileupload = $this->createElement('File', 'fileupload');
        $fileupload->setLabel('fileupload')
                ->setMultiFile($number_of_files)
                ->setDestination($tempPath)
                ->addValidator('Count', false, $number_of_files)     // ensure only 1 file
                ->addValidator('Size', false, $maxFileSize) // limit to value given in application.ini
                ->addValidator('Extension', false, $filetypes); // allowed filetypes by extension


        if ($bib === 1) {
            $bibliographie = $this->createElement('checkbox', 'bibliographie');
            $bibliographie->setLabel('bibliographie');
        }
        

        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('Send');

        $documentId = new Zend_Form_Element_Hidden('DocumentId');
        $documentId->addValidator('NotEmpty')
            ->addValidator('Int');

        $this->addElements(array($doctypes, $fileupload));
        //show Bibliographie?
        if ($this->config->form->first->bibliographie == 1) {
            $this->addElement($bibliographie);
        }

        $this->addElements(array($documentId, $submit));
        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);        
    }

    /**
     * OLD function getXmlDocFiles, TODO: really needed? or other way of getting the types?
     * @return array() of found docTypes
     */
     protected function getXmlDocTypeFiles() {
        $xml_path = $this->config->publish->path->documenttypes;
        if (true === empty($xml_path)) {
            $filetypes = "../config/xmldoctypes/";
        }
        $result = array();
        if ($dirhandle = opendir($xml_path)) {
            while (false !== ($file = readdir($dirhandle))) {
                if (preg_match("/.xml$/", $file) === 0) {
                    continue;
                }

                $path_parts = pathinfo($file);
                $filename = $path_parts['filename'];
                $basename = $path_parts['basename'];
                $extension = $path_parts['extension'];
                if (($basename === '.') or ($basename === '..') or ($extension !== 'xml')) {
                    continue;
                }
                $result[$filename] = $filename;
            }
            closedir($dirhandle);
            asort($result);
        }
        return $result;
    }
}


