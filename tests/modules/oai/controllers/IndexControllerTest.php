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
 * @package     Tests
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_IndexControllerTest extends ControllerTestCase {


    public function testInvalidVerb() {
    try{
        $this->dispatch('/oai?verb=InvalidVerb');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(),
           "Response must contain 'badVerb'");
    }
    catch (Exception $e) {
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

    public function testNoVerb() {
        $this->dispatch('/oai');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(),
           "Response must contain 'badVerb'");
    }

    public function testIdentify() {
        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    public function testListMetadataFormats() {
    try{
        $this->dispatch('/oai?verb=ListSets');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }
    catch (Exception $e) {
        echo $this->getResponse()->getBody() . "\n";
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

    public function testSets() {
    try{
        $this->dispatch('/oai?verb=ListMetadataFormats');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }
    catch (Exception $e) {
        echo $this->getResponse()->getBody() . "\n";
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

    public function testGetRecordxMetaDiss() {
    try{
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDiss&identifier=oai::80');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());

        $this->assertContains('oai::80', $response->getBody(),
           "Response must contain 'oai::80'");
           
    }
    catch (Exception $e) {
        echo $this->getResponse()->getBody() . "\n";
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

    public function testGetRecordOaiDc() {
    try{
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::35');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }
    catch (Exception $e) {
        echo $this->getResponse()->getBody() . "\n";
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

    public function testGetRecordxMetaDissPlus() {
    try{
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
//        $this->checkForBadStringsInHtml($response->getBody());

        $this->assertContains('oai::41', $response->getBody(),
           "Response must contain 'oai::80'");

        $this->assertContains('xMetaDiss', $response->getBody(),
           "Response must contain 'xMetaDiss'");
    }
    catch (Exception $e) {
        echo $this->getResponse()->getBody() . "\n";
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

    public function testListIdentifiers() {
    try{
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }
    catch (Exception $e) {
        echo $this->getResponse()->getBody() . "\n";
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

    public function testListRecords() {
    try{
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&from=2006-01-01');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
//        $this->checkForBadStringsInHtml($response->getBody());

        $this->assertContains('<ListRecords>', $response->getBody(),
           "Response must contain '<ListRecords>'");
        $this->assertContains('<record>', $response->getBody(),
           "Response must contain '<record>'");
    }
    catch (Exception $e) {
        echo $this->getResponse()->getBody() . "\n";
        $this->fail($e->getMessage() . "\n--\n" . $this->getResponse() . "\n--\n");
    }
    }

}

?>
