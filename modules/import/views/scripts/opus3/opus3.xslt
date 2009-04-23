<?xml version="1.0" encoding="utf-8"?>
<!--
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
-->

<!--
/**
 * @category    Application
 * @package     Module_Import
 */
-->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl">

    <xsl:output method="xml" indent="no" />

    <!--
    Suppress output for all elements that don't have an explicit template.
    -->
    <xsl:template match="*" />

    <xsl:template match="/">
        <xsl:element name="Documents">
            <xsl:apply-templates select="/mysqldump/database/table_data[@name='opus']/row" />
        </xsl:element>
    </xsl:template>

    <!--
    Suppress fields with nil value
    -->
    <xsl:template match="table_data/row/field[@xsi:nil='true']" priority="1" />

    <xsl:template match="table_data[@name='opus']/row">
        <xsl:element name="Opus_Document">            
            <xsl:variable name="OriginalID"><xsl:value-of select="field[@name='source_opus']" /></xsl:variable>
            <xsl:attribute name="Type">
                <xsl:choose>
                    <xsl:when test="field[@name='type']='1'">
                        <xsl:text>manual</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='2'">
                        <xsl:text>article</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='4'">
                        <xsl:text>monograph</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='5'">
                        <xsl:text>book section</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='7'">
                        <xsl:text>master thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='8'">
                        <xsl:text>doctoral thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='9'">
                        <xsl:text>honour thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='11'">
                        <xsl:text>journal</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='15'">
                        <xsl:text>conference</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='16'">
                        <xsl:text>conference item</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='17'">
                        <xsl:text>paper</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='19'">
                        <xsl:text>study paper</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='20'">
                        <xsl:text>report</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='22'">
                        <xsl:text>preprint</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='23'">
                        <xsl:text>other</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='24'">
                        <xsl:text>habil thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='25'">
                        <xsl:text>bachelor thesis</xsl:text>
                    </xsl:when>
                    <xsl:when test="field[@name='type']='26'">
                        <xsl:text>lecture</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>other</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            
            <!-- All publications in the OPUS table are published, so we can set this statically -->
            <!--<xsl:attribute name="PublicationState">
                <xsl:text>published</xsl:text>
            </xsl:attribute>-->
            
            <!-- Language might be a multivalue field in Opus4; in Opus3 there can be only one language -->
            <!-- if the document type defines it as multivalue, there will be problems importing -->
            <xsl:attribute name="Language">
                <xsl:call-template name="mapLanguage"><xsl:with-param name="lang"><xsl:value-of select="field[@name='language']" /></xsl:with-param></xsl:call-template>
            </xsl:attribute>
            <xsl:if test="string-length(field[@name='creator_corporate'])>0">
                <xsl:attribute name="CreatingCorporation">
                    <xsl:value-of select="field[@name='creator_corporate']" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="string-length(field[@name='contributors_corporate'])>0">
                <xsl:attribute name="ContributingCorporation">
                    <xsl:value-of select="field[@name='contributors_corporate']" />
                </xsl:attribute>
            </xsl:if>
            <!-- Source holds source_title? -->
            <xsl:if test="string-length(field[@name='source_title'])>0">
                <xsl:attribute name="Source">
                    <xsl:value-of select="field[@name='source_title']" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="string-length(field[@name='source_swb'])>0">
                <xsl:attribute name="IdentifierOpac">
                    <xsl:value-of select="field[@name='source_swb']" />
                </xsl:attribute>
            </xsl:if>
            
            
            <!-- Take date_creation from OPUS3 as ServerDatePublished -->
            <!-- date_creation is stored as a unix timestamp -->
            <!-- its transferred by date function in PHP -->
            <xsl:variable name="date_creation"><xsl:value-of select="field[@name='date_creation']" /></xsl:variable>
            <xsl:variable name="date_modified"><xsl:value-of select="field[@name='date_modified']" /></xsl:variable>
            <xsl:variable name="date_valid"><xsl:value-of select="field[@name='date_valid']" /></xsl:variable>
            <!--PublishedYear und PublishedDate are not stored by Opus 3, so we leave it out -->
            <xsl:attribute name="CompletedYear">
                <xsl:value-of select="field[@name='date_year']" />
            </xsl:attribute>
            <!--CompletedDate is left out, because Opus3 only stores the year -->
            <xsl:if test="$date_creation>0">
                <xsl:attribute name="ServerDatePublished">
                    <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_creation)" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="$date_modified>0">
                <xsl:attribute name="ServerDateModified">
                    <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_modified)" />
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="$date_valid>0">
                <xsl:attribute name="ServerDateValid">
                    <xsl:value-of select="php:function('date', 'Y-m-d H:i:s', $date_valid)" />
                </xsl:attribute>
            </xsl:if>
            <xsl:variable name="dateaccepted"><xsl:value-of select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]/field[@name='date_accepted']" /></xsl:variable>
            <xsl:if test="$dateaccepted>0">
                <xsl:attribute name="DateAccepted">
                    <xsl:value-of select="php:function('date', 'Y-m-d', $dateaccepted)" />
                </xsl:attribute>
            </xsl:if>
            <xsl:attribute name="PublisherUniversity">
                <xsl:value-of select="field[@name='publisher_university']" />
            </xsl:attribute>           
            
            <!-- Fields not used yet -->
            <!--<xsl:attribute name="RangeId">
                <xsl:value-of select="field[@name='bereich_id']" />
            </xsl:attribute>-->
            
            <!-- Find persons associated with the document -->
            <xsl:call-template name="getAuthors"><xsl:with-param name="OriginalID"><xsl:value-of select="$OriginalID" /></xsl:with-param></xsl:call-template>
            <xsl:if test="string-length(field[@name='contributors_name'])>0">
                <xsl:call-template name="getContributors"><xsl:with-param name="contributors"><xsl:value-of select="field[@name='contributors_name']" /></xsl:with-param></xsl:call-template>
            </xsl:if>
            <xsl:call-template name="getAdvisors"><xsl:with-param name="OriginalID"><xsl:value-of select="$OriginalID" /></xsl:with-param></xsl:call-template>
            
            <!-- Classifications and Subjects -->
            <!--<xsl:call-template name="getSubjects"><xsl:with-param name="source_id"><xsl:value-of select="$OriginalID" /></xsl:with-param><xsl:with-param name="subject"><xsl:value-of select="field[@name='subject_type']" /></xsl:with-param></xsl:call-template>-->            
                      
            <!--
            Prepared, but commented out: 
            Subjects from opus_ccs etc.
            PublicationState
            RangeId
            
            Missing fields in other opus3 tables:
            opus_coll <field name="Collection" />
            opus_inst (+ institutes + faculties) <field name="Institute" />
            opus_schriftenreihe (+ schriftenreihe) <field name="TitleParent" mandatory="yes" multiplicity="4" />
	        -->
            
            <xsl:apply-templates select="field" />
        </xsl:element>
    </xsl:template>

    <xsl:template name="mapLanguage">
        <xsl:param name="lang" required="yes" />
        <xsl:if test="$lang='ger'">
            <xsl:text>deu</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='eng'">
            <xsl:text>eng</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='fre'">
            <xsl:text>fra</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='rus'">
            <xsl:text>rus</xsl:text>
        </xsl:if>
        <xsl:if test="$lang='mul'">
            <xsl:text></xsl:text>
        </xsl:if>
        <xsl:if test="$lang='mis'">
            <xsl:text></xsl:text>
        </xsl:if>
    </xsl:template> 

    <!-- temporary licence information -->
    <xsl:template match="table_data[@name='opus']/row/field[@name='lic']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="OldLicence">
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <!-- temporary DDC information -->
    <xsl:template match="table_data[@name='opus']/row/field[@name='sachgruppe_ddc']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="OldDdc">
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>

    <!-- Notes -->
    <xsl:template match="table_data[@name='opus']/row/field[@name='bem_intern']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="Note">
                <xsl:attribute name="Scope">
                    <xsl:text>private</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Message">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='bem_extern']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="Note">
                <xsl:attribute name="Scope">
                    <xsl:text>public</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Message">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>

    <!-- Subjects and Classifications -->
    <xsl:template match="table_data[@name='opus']/row/field[@name='subject_swd']">
        <!-- Split values from field by <Space>,<Space> -->
        <!-- each value gets its own element -->
        <xsl:if test="string-length(.)>0">
            <xsl:element name="SubjectSwd">
                <xsl:attribute name="Language">
                    <xsl:text>deu</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='subject_uncontrolled_english']">
        <!-- WARNING: the uncontrolled subjects have no standardized format -->
        <!-- take them as one value in one field ??? -->
        <xsl:if test="string-length(.)>0">
            <xsl:element name="SubjectUncontrolled">
                <xsl:attribute name="Language">
                    <xsl:text>eng</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='subject_uncontrolled_german']">
        <!-- WARNING: the uncontrolled subjects have no standardized format -->
        <!-- take them as one value in one field ??? -->
        <xsl:if test="string-length(.)>0">
            <xsl:element name="SubjectUncontrolled">
                <xsl:attribute name="Language">
                    <xsl:text>deu</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template name="getSubjects">
        <xsl:param name="subject" required="yes" />
        <xsl:param name="source_id" required="yes" />
        <xsl:variable name="subject_table">opus_<xsl:value-of select="$subject" /></xsl:variable>
        <xsl:variable name="subject_object">Subject<xsl:value-of select="php:function('strtoupper', substring($subject, 1, 1))" /><xsl:value-of select="substring($subject, 2)" /></xsl:variable>
        <xsl:if test="string-length(/mysqldump/database/table_data[@name=$subject_table]/row[field[@name='source_opus']=$source_id]/field[@name='class'])>0">
            <xsl:element name="{$subject_object}">
                <xsl:attribute name="Language">
                    <xsl:text>deu</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="/mysqldump/database/table_data[@name=$subject_table]/row[field[@name='source_opus']=$source_id]/field[@name='class']" />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    
    <!-- Titles and abstracts -->
    <xsl:template match="table_data[@name='opus']/row/field[@name='title']">
        <xsl:element name="TitleMain">
            <xsl:attribute name="Language">
                <xsl:call-template name="mapLanguage"><xsl:with-param name="lang"><xsl:value-of select="../field[@name='language']" /></xsl:with-param></xsl:call-template>
            </xsl:attribute>
            <xsl:attribute name="Value">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='title_en']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="TitleMain">
                <xsl:attribute name="Language">
                    <xsl:text>eng</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='description']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="TitleAbstract">
                <xsl:attribute name="Language">
                    <xsl:call-template name="mapLanguage"><xsl:with-param name="lang"><xsl:value-of select="../field[@name='description_lang']" /></xsl:with-param></xsl:call-template>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='description2']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="TitleAbstract">
                <xsl:attribute name="Language">
                    <xsl:call-template name="mapLanguage"><xsl:with-param name="lang"><xsl:value-of select="../field[@name='description2_lang']" /></xsl:with-param></xsl:call-template>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="." />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template name="getGermanTitle">
        <xsl:if test="string-length(field[@name='title_de'])>0">
            <xsl:element name="TitleMain">
                <xsl:attribute name="Language">
                    <xsl:text>deu</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="Value">
                    <xsl:value-of select="field[@name='title_de']" />
                </xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    
    <!-- Identifiers -->
    <xsl:template match="table_data[@name='opus']/row/field[@name='source_opus']">
        <xsl:element name="IdentifierOpus3">
            <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='urn']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="IdentifierUrn">
                <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='isbn']">
        <xsl:if test="string-length(.)>0">
            <xsl:element name="IdentifierIsbn">
                <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
            </xsl:element>
        </xsl:if>
    </xsl:template>
    <xsl:template match="table_data[@name='opus']/row/field[@name='url']">
        <xsl:element name="IdentifierUrl">
            <xsl:attribute name="Value"><xsl:value-of select="." /></xsl:attribute>
        </xsl:element>
    </xsl:template>
    
    <!-- Person templates, called in main template -->
    <xsl:template name="getAuthors">
        <xsl:param name="OriginalID" required="yes" />
        <xsl:for-each select="/mysqldump/database/table_data[@name='opus_autor']/row[field[@name='source_opus']=$OriginalID]">
            <xsl:call-template name="getAuthor" />
        </xsl:for-each>
    </xsl:template>
    <xsl:template name="getAuthor">
        <xsl:element name="PersonAuthor">
            <xsl:attribute name="AcademicTitle"></xsl:attribute>
            <xsl:attribute name="DateOfBirth"></xsl:attribute>
            <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
            <xsl:attribute name="Email"></xsl:attribute>
            <xsl:attribute name="FirstName">
                <xsl:value-of select="substring-after(field[@name='creator_name'],', ')" />
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:value-of select="substring-before(field[@name='creator_name'],', ')" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template name="getContributors">
        <!-- WARNING: no unique format for persons in Opus3! -->
        <!-- there may occur problems -->
        <xsl:param name="contributor" required="yes" />
        <xsl:element name="PersonContributor">
            <xsl:attribute name="AcademicTitle"></xsl:attribute>
            <xsl:attribute name="DateOfBirth"></xsl:attribute>
            <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
            <xsl:attribute name="Email"></xsl:attribute>
            <xsl:attribute name="FirstName">
                <xsl:value-of select="substring-after($contributor,', ')" />
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:value-of select="substring-before($contributor,', ')" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template name="getAdvisors">
        <xsl:param name="OriginalID" required="yes" />
        <xsl:for-each select="/mysqldump/database/table_data[@name='opus_diss']/row[field[@name='source_opus']=$OriginalID]">
            <xsl:call-template name="getAdvisor" />
            <xsl:call-template name="getGermanTitle" />
        </xsl:for-each>
     </xsl:template>
    <xsl:template name="getAdvisor">
        <xsl:element name="PersonAdvisor">
            <xsl:attribute name="AcademicTitle">
                <xsl:value-of select="substring-after(substring-before(field[@name='advisor'],')'),'(')" />
            </xsl:attribute>
            <xsl:attribute name="DateOfBirth"></xsl:attribute>
            <xsl:attribute name="PlaceOfBirth"></xsl:attribute>
            <xsl:attribute name="Email"></xsl:attribute>
            <xsl:attribute name="FirstName">
                <xsl:value-of select="substring-after(substring-before(field[@name='advisor'],' ('),', ')" />
            </xsl:attribute>
            <xsl:attribute name="LastName">
                <xsl:value-of select="substring-before(field[@name='advisor'],', ')" />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
