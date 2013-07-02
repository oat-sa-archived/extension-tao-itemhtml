<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * Export form for QTI packages
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoItems
 * @subpackage actions_form
 */
class taoOpenWebItem_model_import_OwiImportForm
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---
    /**
     * Short description of method initForm
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED5 begin

    	$this->form = new tao_helpers_form_xhtml_Form('export');

		$this->form->setDecorators(array(
			'element'			=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')),
			'group'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')),
			'error'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all')),
			'actions-bottom'	=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar')),
			'actions-top'		=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar'))
		));

    	$exportElt = tao_helpers_form_FormFactory::getElement('export', 'Free');
		$exportElt->setValue( "<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/export.png' /> ".__('Export')."</a>");

		$this->form->setActions(array($exportElt), 'bottom');
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED5 end
    }
    
    /**
     * overriden
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {

    	$descElt = tao_helpers_form_FormFactory::getElement('xhtml_desc', 'Label');
		$descElt->setValue(__('An Open Web Item package is a Zip archive containing an index.html file with the XHTML 1.0 Transitional Doctype and resources (images, scripts, css, video, etc.).'));
		$this->form->addElement($descElt);
    	
    	//create file upload form box
		$fileElt = tao_helpers_form_FormFactory::getElement('source', 'AsyncFile');
		$fileElt->setDescription(__("Add the source file"));
    	if(isset($_POST['import_sent_xhtml'])){
			$fileElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		}
		else{
			$fileElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty', array('message' => '')));
		}
		$fileElt->addValidators(array(
			tao_helpers_form_FormFactory::getValidator('FileMimeType', array('mimetype' => array('application/zip', 'application/x-zip', 'application/x-zip-compressed', 'application/octet-stream'), 'extension' => array('zip'))),
			tao_helpers_form_FormFactory::getValidator('FileSize', array('max' => tao_helpers_Environment::getFileUploadLimit()))
		));
    	
		$this->form->addElement($fileElt);
		
		$disableValidationElt = tao_helpers_form_FormFactory::getElement("disable_validation", 'Checkbox');
		$disableValidationElt->setDescription("Disable validation");
		$disableValidationElt->setOptions(array("on" => ""));
		$this->form->addElement($disableValidationElt);
		
		$this->form->createGroup('file', __('Upload an Open Web Item Package File'), array('xhtml_desc', 'source', 'disable_validation'));
		
		$xhtmlSentElt = tao_helpers_form_FormFactory::getElement('import_sent_xhtml', 'Hidden');
		$xhtmlSentElt->setValue(1);
		$this->form->addElement($xhtmlSentElt);
    }

} /* end of class taoItems_actions_form_Export */

?>