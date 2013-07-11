<?php
/*
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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 **/

/**
 * Unit Tests fuer Unterformular fuer Personen in einer Rolle im Metadaten-Formular.
 */
class Admin_Form_DocumentPersonRoleTest extends ControllerTestCase {
    
    public function testCreateForm() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $this->assertEquals(1, count($form->getElements()));
        $this->assertEquals(0, count($form->getSubForms()));
        $this->assertEquals('author', $form->getRoleName());
        $this->assertNotNull($form->getElement('Add'));
    }
    
    public function testPopulateFromModel() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $document = new Opus_Document(21); // hat zwei Authoren

        $this->assertEquals(0, count($form->getSubForms()));
        
        $form->populateFromModel($document);
        
        $this->assertEquals(2, count($form->getSubForms()));
    }
    
    public function testProcessPostAdd() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $post = array(
            'Add' => 'Hinzufügen'
        );
        
        $result = $form->processPost($post, null);
        
        $this->assertNotNull($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document::RESULT_SWITCH_TO, $result['result']);
        $this->assertArrayHasKey('target', $result);
        
        $target = $result['target'];
        
        $this->assertArrayHasKey('role', $target);
        $this->assertEquals('author', $target['role']);
    }
    
    public function testProcessPostRemove() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $post = array(
            'PersonAuthor0' => array(
                'Remove' => 'Entfernen'
            )
        );
        
        $document = new Opus_Document(21); // hat zwei Authoren
        
        $form->populateFromModel($document);
        
        $this->assertEquals(2, count($form->getSubForms()), 'Ungenügend Unterformulare.');
        
        $form->processPost($post, null);
        
        $this->assertEquals(1, count($form->getSubForms()), 'Unterformular wurde nicht entfernt.');
        
        // TODO prüfe Namen von Unterformularen
    }
    
    public function testProcessPostEdit() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $post = array(
            'PersonAuthor0' => array(
                'Edit' => 'Editieren'
            )
        );
        
        $document = new Opus_Document(21); // hat zwei Authoren
        
        $form->populateFromModel($document);
        
        $this->assertEquals(2, count($form->getSubForms()));
        
        $result = $form->processPost($post, null);
        
        $this->assertNotNull($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document::RESULT_SWITCH_TO, $result['result']);
        $this->assertArrayHasKey('target', $result);
        
        $target = $result['target'];
        
        $this->assertArrayHasKey('role', $target);
        $this->assertEquals('author', $target['role']);
    }
    
    public function testProcessPost() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $post = array();
        
        $this->assertNull($form->processPost($post, null));
    }
    
    public function testProcessPostMoveFirst() {
        $form = $this->getFormForSorting();
        
        $post = array(
            'PersonAuthor2' => array(
                'Moves' => array(
                    'First' => 'First'
                )
            )
        );
        
        $form->processPost($post, null);
        
        $this->verifyExpectedOrder($form, array(312, 310, 311));
    }
    
    /**
     * Wenn nur nach den SortOrder Werten sortiert würde, müsste PersonAuthor1 auf Position 3 landen. Da aber auf den 
     * First-Button für PersonAuthor1 geklickt wurde, muss das Ergebnis die Reihenfolge (Author1, Author0, Author2) 
     * sein.
     */
    public function testProcessPostMoveFirstAndSortBySortOrder() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);
        
        $post = array(
            'PersonAuthor1' => array(
                'Moves' => array(
                    'First' => 'First'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(311, 312, 310));
    }
    
    public function testProcessPostMoveLast() {
        $form = $this->getFormForSorting();
        
        $post = array(
            'PersonAuthor0' => array(
                'Moves' => array(
                    'Last' => 'Last'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(311, 312, 310));
    }
    
    public function testProcessPostMoveLastAndSortBySortOrder() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(2);
        
        $post = array(
            'PersonAuthor1' => array(
                'Moves' => array(
                    'Last' => 'Last'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(310, 312, 311));
    }
    
    public function testProcessPostMoveLastAndSortBySortOrderCase2() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(3);
        
        $post = array(
            'PersonAuthor0' => array(
                'Moves' => array(
                    'Last' => 'Last'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(311, 312, 310));
    }
    
    public function testProcessPostMoveLastAndSortBySortOrderCase3() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(2);
        
        $post = array(
            'PersonAuthor0' => array(
                'Moves' => array(
                    'Last' => 'Last'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(312, 311, 310));
    }
    
    public function testProcessPostMoveUp() {
        $form = $this->getFormForSorting();
                
        $post = array(
            'PersonAuthor2' => array(
                'Moves' => array(
                    'Up' => 'Hoch'
                )
            )
        );
        
        $form->processPost($post, null);
        
        $this->verifyExpectedOrder($form, array(310, 312, 311));
    }
    
    public function testProcessPostMoveUpAndSortBySortOrder() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);
        
        $post = array(
            'PersonAuthor2' => array(
                'Moves' => array(
                    'Up' => 'Hoch'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(310, 312, 311));
    }
    
    public function testProcessPostMoveDown() {
        $form = $this->getFormForSorting();
        
        $post = array(
            'PersonAuthor1' => array(
                'Moves' => array(
                    'Down' => 'Runter'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(310, 312, 311));
    }
            
    public function testProcessPostMoveDownAndSortBySortOrder() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);
        
        $post = array(
            'PersonAuthor1' => array(
                'Moves' => array(
                    'Down' => 'Runter'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(312, 310, 311));
    }
    
    public function testProcessPostMoveDownAndSortBySortOrderCase2() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(3);
        
        $post = array(
            'PersonAuthor0' => array(
                'Moves' => array(
                    'Down' => 'Runter'
                )
            )
        );
        
        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, array(310, 311, 312));
        // $this->verifyExpectedOrder($form, array(312, 310, 311)); // TODO was wäre die sinnvollste Erwartung?
    }
        
    public function testContinueEdit() {
        $this->markTestIncomplete();
    }
    
    public function testCreateSubForm() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $subform = $form->createSubForm();
        
        $this->assertNotNull($subform);
        $this->assertTrue($subform instanceof Admin_Form_DocumentPerson);
        $this->assertNotNull($subform->getSubForm('Roles'));
        $this->assertNull($subform->getSubForm('Roles')->getElement('RoleAuthor')); // Unterformular richtig
        $this->assertNotNull($subform->getSubForm('Moves'));
    }
    
    /**
     * Prüft, ob Unterformulare von einer anderen Rolle eingefügt werden können.
     */
    public function testAddSubFormForPerson() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $form->createSubForm();
    }
    
    /**
     * Erster und letzter Autor werden ausgetauscht.
     */
    public function testSortSubFormsBySortOrder() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);
        
        $form->sortSubFormsBySortOrder();
        
        $this->verifyExpectedOrder($form, array(312, 311, 310));
    }
    
    /**
     * 
     */
    public function testSortSubFormsBySortOrderRepeatedValuesRespectOldOrderAndModified() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);
        
        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, array(311, 312, 310));
    }
    
    /**
     * 
     */
    public function testSortSubFormsBySortOrderRepeatedValuesRespectOldOrder() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(2);
        
        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, array(311, 310, 312));
    }
    
    /**
     * Für Autor 3 wurde explizit SortOrder = 1 gesetzt. Das heißt dieser Autor muss auf Position 1 und Author 1 muss
     * auf Position 2 rutschen. Author 2 landet auf Position 3.
     */
    public function testSortSubFormsBySortOrderRepeatedValuesRespectModified() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);
        
        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, array(312, 310, 311));
    }
        
    public function testSortSubFormsBySortOrderEmptyValues() {
        $form = $this->getFormForSorting();
        
        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(null);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(null);
        
        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, array(311, 310, 312));
    }
    
    protected function verifyExpectedOrder($form, $expected) {
        foreach ($expected as $index => $personId) {
            $this->assertEquals($personId, $form->getSubForm('PersonAuthor' . $index)->getElement(
                    'PersonId')->getValue(), "Person $personId ist nicht an $index. Stelle.");
        }
    }
        
    protected function getFormForSorting() {
        $form = new Admin_Form_DocumentPersonRole('author');
        
        $document = new Opus_Document(250);
        
        $authors = $document->getPersonAuthor();
        $authorId0 = $authors[0]->getModel()->getId(); // 310
        $authorId1 = $authors[1]->getModel()->getId(); // 311
        $authorId2 = $authors[2]->getModel()->getId(); // 312
        
        $form->populateFromModel($document);
        
        $this->verifyExpectedOrder($form, array(310, 311, 312));
        
        return $form;
    }
        
}
