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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

namespace oat\taoOpenWebItem\model\export;

use \tao_models_classes_export_ExportHandler;
use \core_kernel_classes_Resource;
use \core_kernel_classes_Class;
use \taoItems_models_classes_ItemsService;
use \tao_helpers_File;
use \Exception;
use \ZipArchive;
use \common_Exception;
use \common_Logger;
use oat\taoOpenWebItem\model\OwiItemModel;

/**
 * Exporter of taoOpenWebItem
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoOpenWebItem
 
 */
class OwiExportHandler implements tao_models_classes_export_ExportHandler
{

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_export_ExportHandler::getLabel()
     */
    public function getLabel() {
    	return __('Open Web Item');
    }
    
    /**
     * (non-PHPdoc)
     * @see tao_models_classes_export_ExportHandler::getExportForm()
     */
    public function getExportForm(core_kernel_classes_Resource $resource) {
        if ($resource instanceof core_kernel_classes_Class) {
            $formData= array('class' => $resource);
        } else {
            $formData= array('instance' => $resource);
        }
    	$form = new ExportForm($formData);
    	return $form->getForm();
    }
    
    /**
     * (non-PHPdoc)
     * @see tao_models_classes_export_ExportHandler::export()
     */
    public function export($formValues, $destination) {
    	$file = null;
    	if(isset($formValues['filename'])) {
			$uri = $formValues['exportInstance'];
				
			$itemService = taoItems_models_classes_ItemsService::singleton();
			
			$fileName = $formValues['filename'].'_'.time().'.zip';
			$path = tao_helpers_File::concat(array($destination, $fileName));
			if(!tao_helpers_File::securityCheck($path, true)){
				throw new Exception('Unauthorized file name');
			}
			
			$zipArchive = new ZipArchive();
			if($zipArchive->open($path, ZipArchive::CREATE) !== true){
				throw new Exception('Unable to create archive at '.$path);
			}
				
			$item = new core_kernel_classes_Resource($uri);
			if($itemService->hasItemModel($item, array(OwiItemModel::ITEMMODEL_URI))){
				$exporter = new OwiExporter($item, $zipArchive);
				$exporter->export();
			} else {
				throw new common_Exception('Tried to export non OWI ('.$item->getUri().') as OWI ');
			}
			$zipArchive->close();
			$file = $path;
		} else {
			common_Logger::w('Missing filename for export using '.__CLASS__);
		}
		return $file;
    }

}