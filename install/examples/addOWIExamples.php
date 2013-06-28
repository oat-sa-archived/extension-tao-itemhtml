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
?>
<?php
$itemClass	= taoItems_models_classes_ItemsService::singleton()->getRootClass();
$files		= array(	dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Googlemaps.zip',
					 	dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Mammals.zip',
						dirname(__FILE__) . DIRECTORY_SEPARATOR . 'MammalsRTL.zip');

$service = taoItems_models_classes_XHTML_ImportService::singleton();

foreach ($files as $file) {
	$item = $service->importXhtmlFile($file, $itemClass, false);
	$path_parts = pathinfo($file);
	$label = $path_parts['filename'];
	$item->setLabel($label);
}