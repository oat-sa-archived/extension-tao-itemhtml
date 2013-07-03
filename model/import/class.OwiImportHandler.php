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
 * Copyright (c) 2013 (original work) Open Assessment Techonologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

/**
 * Short description of class taoQTI_models_classes_ItemModel
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes_Export
 */
class taoOpenWebItem_model_import_OwiImportHandler implements tao_models_classes_import_ImportHandler
{

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::getLabel()
     */
    public function getLabel() {
    	return __('Open Web Item');
    }
    
    /**
     * (non-PHPdoc)
     * @see tao_models_classes_export_ExportHandler::getForm()
     */
    public function getForm() {
    	$form = new taoOpenWebItem_model_import_OwiImportForm();
    	return $form->getForm();
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::import()
     */
    public function import($class, $formValues) {
		
        //import for CSV
        if(isset($formValues['source'])){
			
			set_time_limit(200);	//the zip extraction is a long process that can exced the 30s timeout
			
			//get the services instances we will need
			$itemService	= taoItems_models_classes_ItemsService::singleton();
			
			$uploadedFile = $formValues['source']['uploaded_file'];
			$uploadedFileBaseName = basename($uploadedFile);
			// uploaded file name contains an extra prefix that we have to remove.
			$uploadedFileBaseName = preg_replace('/^([0-9a-z])+_/', '', $uploadedFileBaseName, 1);
			$uploadedFileBaseName = preg_replace('/.zip|.ZIP$/', '', $uploadedFileBaseName);
			
			$validate = true;
			if(isset($formValues['disable_validation'])){
				if(is_array($formValues['disable_validation'])){
					$validate = false;
				}
			}
			
			$importService = new taoOpenWebItem_model_import_ImportService();
			try {
				$report = $importService->importXhtmlFile($uploadedFile, $class, $validate);
			} catch (taoItems_models_classes_Import_ExtractException $e) {
			    $error = new common_report_ErrorElement( __('unable to extract archive content, please check your tmp dir'));
			    $report = common_report_Report::createFailure( __('An error occured during the import'), $error);
			} catch (common_Exception $e) {
			    $report = common_report_Report::createFailure(__('An error occured during the import'));
			    if ($e instanceof common_exception_UserReadableException) {
			        $report->add($e);
			    }
			}
			
			tao_helpers_File::remove($uploadedFile);
			
		} else {
		    throw new common_exception_Error('No file provided as parameter \'source\' for OWI import');
		}
		return $report;
    }


}

?>