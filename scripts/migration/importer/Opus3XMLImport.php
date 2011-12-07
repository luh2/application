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
 * @package     Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

require_once 'Opus3ImportLogger.php';

class Opus3XMLImport {
    
   /**
    * Holds Zend-Configurationfile
    *
    * @var file.
    */
    protected $config = null;

   /**
    * Holds Logger
    *
    * @var file
    */
    protected $logger = null;

    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xml = null;
    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xslt = null;
    /**
     * Holds the xslt processor.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_proc = null;
    /**
     * Holds the document that should get imported
     *
     * @var DomNode  XML-Representation of the document to import
     */
    protected $document = null;
     /**
     * Holds the Mappings forCollections
     *
     * @var Array
     */
    protected $mappings = array();

    /**
     * Holds the Collections the document should be added
     *
     * @var Array
     */
    protected $collections = array();

    /**
     * Holds the Series the document should be added
     *
     * @var Array
     */
    protected $series = array();

    /**
     * Holds Values for Grantor, Licence and PublisherUniversity
     *
     * @var Array
     */
    protected $values = array();

    /**
     * Holds SortOrder of Authors
     *
     * @var Array
     */
    protected $personSortOrder = array();

    /**
     * Holds Doctypes for Thesis
     *
     * @var Array
     */
    protected $thesistypes = array();

    /**
     * Holds the complete XML-Representation of the Importfile
     *
     * @var DomDocument  XML-Representation of the importfile
     */
    protected $completeXML = null;


    /**
     * Do some initialization on startup of every action
     *
     * @param string $xslt Filename of the stylesheet to be used
     * @param string $stylesheetPath Path to the stylesheet
     * @return void
     */
    public function __construct($xslt, $stylesheetPath) {
        // Initialize member variables.
        $this->config = Zend_Registry::get('Zend_Config');
        $this->logger = new Opus3ImportLogger();

        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheetPath . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);

        $this->mapping['language'] =  array('old' => 'OldLanguage', 'new' => 'Language', 'config' => $this->config->import->language);
        $this->mapping['type'] =  array('old' => 'OldType', 'new' => 'Type', 'config' => $this->config->import->doctype);

        $this->mapping['collection'] = array('name' => 'OldCollection', 'mapping' => $this->config->import->mapping->collections);
        $this->mapping['institute'] = array('name' => 'OldInstitute',  'mapping' => $this->config->import->mapping->institutes);
        $this->mapping['series'] = array('name' => 'OldSeries',  'mapping' => $this->config->import->mapping->series);
        $this->mapping['grantor'] = array('name' => 'OldGrantor', 'mapping' => $this->config->import->mapping->grantors);
        $this->mapping['licence'] = array('name' => 'OldLicence',  'mapping' => $this->config->import->mapping->licences);
        $this->mapping['publisherUniversity'] = array('name' => 'OldPublisherUniversity', 'mapping' => $this->config->import->mapping->universities);
        $this->mapping['role'] = array('name' => 'OldRole', 'mapping' => $this->config->import->mapping->roles);

        array_push($this->thesistypes, 'bachelorthesis');
        array_push($this->thesistypes, 'doctoralthesis');
        array_push($this->thesistypes, 'habilitation');
        array_push($this->thesistypes, 'masterthesis');
        array_push($this->thesistypes, 'studythesis');
    }

    public function finalize() {
        $this->logger->finalize();
    }

    public function initImportFile($data) {
        $this->completeXML = new DOMDocument;
        $this->completeXML->loadXML($this->_proc->transformToXml($data));
        $doclist = $this->completeXML->getElementsByTagName('Opus_Document');
        return $doclist;
    }

    /**
     * Imports metadata from an XML-Document
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array information about the document that has been imported
     */
    public function import($document) {
        $this->collections = array();
        $this->values = array();
        $this->series = array();
        $this->personSortOrder = array();
        $this->document = $document;
     
        $oldid = null;
        $oldid = $this->document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');

        $this->skipEmptyFields();
	$this->validateEmails();
        $this->checkTitle();

        $this->mapDocumentTypeAndLanguage();
        $this->mapElementLanguage();
        $this->mapClassifications();
        $this->mapCollections();
        $this->mapValues();

        $this->getSortOrder();

        $imported = array();
        $doc = null;

        try {
            $doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');

            // ThesisGrantor and ThesisPublisher only for Thesis-Documents
            if(in_array($doc->getType(), $this->thesistypes)) {
                if (array_key_exists('grantor', $this->values)) {
                    $dnbGrantor = new Opus_DnbInstitute($this->values['grantor']);
                    $doc->setThesisGrantor($dnbGrantor);
                }
                if (array_key_exists('publisherUniversity', $this->values)) {
                    $dnbPublisher = new Opus_DnbInstitute($this->values['publisherUniversity']);
                    $doc->setThesisPublisher($dnbPublisher);
                }
            }

            if (array_key_exists('licence', $this->values)) {
                $doc->addLicence(new Opus_Licence($this->values['licence']));
            }

            // TODO Opus4.x : Handle SortOrder via Opus_Document_Model
             foreach ($doc->getPersonAuthor() as $a) {
                $lastname = $a->getLastName();
                $firstname = $a->getFirstName();
                $sortorder = $this->personSortOrder[$lastname.",".$firstname];
                $a->setSortOrder($sortorder);
            }



            foreach ($this->collections as $c) {
                $coll = new Opus_Collection($c);
                $coll->setVisible(1);
                $coll->store();
                $doc->addCollection($coll);
            }

            foreach ($this->series as $s) {
                $coll = new Opus_Collection($s[0]);
                $coll->setVisible(1);
                $coll->store();
                $identifierSerial = new Opus_Identifier();
                $identifierSerial->setValue($s[1]);
                $doc->addIdentifierSerial($identifierSerial);
                $doc->addCollection($coll);
            }

            //$this->logger->log_debug("Opus3XMLImport", "(3):".$this->completeXML->saveXML($this->document));
            $doc->store();

            $imported['result'] = 'success';
            $imported['oldid'] = $oldid;
            $imported['newid'] = $doc->getId();
       
            if (array_key_exists('role', $this->values)) {
                $imported['roleid'] = $this->values['role'];
                //$this->logger->log_debug("Opus3XMLImport", "ROLE_ID'" . $this->values['roleid'] . "');
            }
        } catch (Exception $e) {
            $imported['result'] = 'failure';
            $imported['message'] = $e->getMessage();
            $imported['entry'] = $this->completeXML->saveXML($this->document);
            $imported['oldid'] = $oldid;
        }

        unset($this->collections);
        unset($this->series);
        unset($this->values);
        unset($this->document);

        return $imported;
    }

    private function skipEmptyFields() {
       // BUGFIX:OPUSVIER-938: Fehler beim Import von Dokumenten mit Autoren ohne Vornamen
        $roles = array();
        array_push($roles, array('PersonAdvisor', 'FirstName'));
        array_push($roles, array('PersonAuthor', 'FirstName'));
        array_push($roles, array('PersonContributor', 'FirstName'));
        array_push($roles, array('PersonEditor', 'FirstName'));
        array_push($roles, array('PersonReferee', 'FirstName'));
        array_push($roles, array('PersonOther', 'FirstName'));
        array_push($roles, array('PersonTranslator', 'FirstName'));
        array_push($roles, array('PersonSubmitter', 'FirstName'));

        array_push($roles, array('TitleMain', 'Value'));
        array_push($roles, array('TitleAbstract', 'Value'));
        array_push($roles, array('TitleParent', 'Value'));
        array_push($roles, array('TitleSub', 'Value'));
        array_push($roles, array('TitleAdditional', 'Value'));

        array_push($roles, array('SubjectUncontrolled', 'Value'));
        array_push($roles, array('IdentifierIsbn', 'Value'));
	array_push($roles, array('Enrichment', 'Value'));

        array_push($roles, array('Note', 'Message'));
 
        foreach ($roles as $r) {
            $elements = $this->document->getElementsByTagName($r[0]);
            foreach ($elements as $e) {
                if (trim($e->getAttribute($r[1])) == "") {
                    $this->logger->log_error("Opus3XMLImport", $r[0] . "' with empty '" . $r[1] . "' will not be imported");
                    $this->document->removeChild($e);
                }
            }
        }
    }


    private function checkTitle() {
        $tagnames = array('TitleMain', 'TitleAbstract', 'TitleAdditional');
        $oa = $this->mapping['language'];
        foreach ($tagnames as $tag) {
            $language = array();
            $elements = $this->document->getElementsByTagName($tag);
            foreach ($elements as $e) {
                $old_value = $e->getAttribute($oa['old']);
                $new_value = $oa['config']->$old_value;
                /* Check for TitleElements with duplicated Languages */
                if (in_array($new_value, $language)) {
                    $this->logger->log_error("Opus3XMLImport", $tag . "' with language '" . $new_value . "' already exists . Document will not be indexed.");
                } else {
                    array_push($language, $new_value);
                }
            }
        }
    }
    
    private function validateEmails() {
    
        $roles = array();
        array_push($roles, 'PersonAdvisor');
        array_push($roles, 'PersonAuthor');
        array_push($roles, 'PersonContributor');
        array_push($roles, 'PersonEditor');
        array_push($roles, 'PersonReferee');
        array_push($roles, 'PersonOther');
        array_push($roles, 'PersonTranslator');
        array_push($roles, 'PersonSubmitter');    
    
	$validator = new Zend_Validate_EmailAddress();
	
        foreach ($roles as $r) {
            $elements = $this->document->getElementsByTagName($r);
            foreach ($elements as $e) {	
                if (trim($e->getAttribute('Email')) != "") {
			if (!($validator->isValid($e->getAttribute('Email')))) {
				$this->logger->log_error("Opus3XMLImport", "Invalid Email-Address '" . $e->getAttribute('Email') . "' will not be imported.");
				$e->removeAttribute('Email');
			}
                }
            }
        }
    }	    

 
    private function mapDocumentTypeAndLanguage() {
        $mapping = array('language', 'type');
        foreach ($mapping as $m) {
            $oa = $this->mapping[$m];
            $old_value = $this->document->getAttribute($oa['old']);
            $new_value = $oa['config']->$old_value;
            $this->document->removeAttribute($oa['old']);
            $this->document->setAttribute($oa['new'], $new_value);
        }
    }

    private function mapElementLanguage() {
        $tagnames = array('TitleMain', 'TitleAbstract', 'TitleAdditional', 'SubjectSwd', 'SubjectUncontrolled');
        $oa = $this->mapping['language'];
        foreach ($tagnames as $tag) {
            $elements = $this->document->getElementsByTagName($tag);
            foreach ($elements as $e) {
                $old_value = $e->getAttribute($oa['old']);
                $new_value = $oa['config']->$old_value;
                $e->removeAttribute($oa['old']);
                $e->setAttribute($oa['new'], $new_value);
            }
        }
    }

    private function mapClassifications() {
        $old_bkl = array('name' => 'OldBkl', 'role' => 'bkl');
        $old_ccs = array('name' => 'OldCcs', 'role' => 'ccs');
        $old_ddc = array('name' => 'OldDdc', 'role' => 'ddc');
        $old_jel = array('name' => 'OldJel', 'role' => 'jel');
        $old_msc = array('name' => 'OldMsc', 'role' => 'msc');
        $old_pacs = array('name' => 'OldPacs', 'role' => 'pacs');
        $old_array = array($old_bkl, $old_ccs, $old_ddc, $old_jel, $old_msc, $old_pacs);

        foreach ($old_array as $oa) {
            $elements = $this->document->getElementsByTagName($oa['name']);

            while ($elements->length > 0) {
                $e = $elements->Item(0);
                $value = $e->getAttribute('Value');
                $role = Opus_CollectionRole::fetchByName($oa['role']);
                $colls = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $value);

                if (count($colls) > 0) {
                    foreach ($colls as $c) {
                        /* TODO: DDC-Hack */
                        if (($oa['role'] === 'ddc') and (count($c->getChildren()) > 0)) { continue; }
                        array_push($this->collections, $c->getId());
                    }
                }
                else {
                    $this->logger->log_error("Opus3XMLImport", "Document not added to '" . $oa['role'] . "' '" . $value . "'");
                }
                $this->document->removeChild($e);
            }
        }
    }

    private function mapCollections() {
        $mapping = array('collection', 'institute', 'series');

        foreach ($mapping as $m) {
            $oa = $this->mapping[$m];
            $elements = $this->document->getElementsByTagName($oa['name']);
            while ($elements->length > 0) {
                $e = $elements->Item(0);
                $old_value = $e->getAttribute('Value');

                if (!is_null ($this->getMapping($oa['mapping'], $old_value))) {
                    $new_value = $this->getMapping($oa['mapping'], $old_value);

                    if ($m === 'series') {

                        $old_issue = $e->getAttribute('Issue');
                        $new_series = array($new_value, $old_issue);
                        //$this->logger->log_debug("Opus3XMLImport", "Found Mapping in ".$oa['mapping'].": '".$old_value."' --> '".$new_value."' with Issue '".$old_issue."'");
                        array_push($this->series,  $new_series);

                    } else {
                        array_push($this->collections,  $new_value);
                    }

                }
                else {
                    $this->logger->log_error("Opus3XMLImport", "('$m'): No valid Mapping in '" . $oa['mapping'] . "' for '" . $old_value . "'");
                }

                $this->document->removeChild($e);
            }
        }
    }

     private function mapValues() {
        $mapping = array('grantor', 'licence', 'publisherUniversity', 'role');
        foreach ($mapping as $m) {
            $oa = $this->mapping[$m];
            //$this->logger->log_debug("Opus3XMLImport", "($m): Mapping  '" . $oa['mapping'] . "' for '" . $oa['name'] . "'");
            $elements = $this->document->getElementsByTagName($oa['name']);
            while ($elements->length > 0) {
                $e = $elements->Item(0);
                $old_value = $e->getAttribute('Value');


                if ($m === 'publisherUniversity') {
                    $old_value = str_replace(" ", "_", $old_value);
                }

                if (!is_null ($this->getMapping($oa['mapping'], $old_value))) {
                    $new_value = $this->getMapping($oa['mapping'], $old_value);
                    $this->values[$m] = $new_value;
                    //$this->logger->log_debug("Opus3XMLImport", "Found Mapping in " . $oa['mapping'] . ": '" .$old_value . "' --> '" .$new_value . "'");
                }
                else {
                    $this->logger->log_error("Opus3XMLImport", "('$m'): No valid Mapping in '" . $oa['mapping'] . "' for '" . $old_value . "'");
                }

                $this->document->removeChild($e);
            }
        }
    }

    private function getSortorder() {

        $roles = array();
        array_push($roles, 'PersonAuthor');

        foreach ($roles as $r) {
            $elements = $this->document->getElementsByTagName($r);
            foreach ($elements as $e) {
                $firstname = $e->getAttribute('FirstName');
                $lastname = $e->getAttribute('LastName');
                $sortorder = $e->getAttribute('SortOrder');
                $this->personSortOrder[$lastname.",".$firstname] = $sortorder;
                $e->removeAttribute('SortOrder');
            }
        }
    }


    /**
     * Get mapped Values for a document and add it
     *
     * @mappingFile: name of the Mapping-File
     * @id: original id
     * @return new id
     */
     private function getMapping($mappingFile, $id) {
        /* TODO: CHECK if File exists , echo ERROR and return null if not*/
        if (!is_readable($mappingFile)) {
            $this->logger->log_error("Opus3XMLImport", "MappingFile '" . $mappingFile . "' is not readable");
            return null;
        }
        $fp = file($mappingFile);
        $mapping = array();
        foreach ($fp as $line) {
            $values = explode(" ", $line);
            $mapping[$values[0]] = trim($values[1]);
        }
        if (array_key_exists($id, $mapping) === false) {
            unset($fp);
            return null;
        }
        unset($fp);
        return $mapping[$id];
    }

}