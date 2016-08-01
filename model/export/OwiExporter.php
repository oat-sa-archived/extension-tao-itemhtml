<?php
/**
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
 * 				 2012-2016 (modification) Open Assessment Technologies SA;
 * 
 */
namespace oat\taoOpenWebItem\model\export;

use oat\oatbox\filesystem\Directory;
use \taoItems_models_classes_ItemExporter;

class OwiExporter extends taoItems_models_classes_ItemExporter
{
	/**
	 * Overriden export for OWIs
	 *
	 * @see taoItems_models_classes_ItemExporter::export()
	 */
	public function export($options = array())
	{
		$location = $this->getItemDirectory();
		$this->exportDirectory($location);
	}

	/**
	 * Export to zip the given directory recursively
	 *
	 * @param Directory $directory
	 * @throws \common_Exception
	 * @throws \tao_models_classes_FileNotFoundException
	 */
	protected function exportDirectory(Directory $directory)
	{
		$iterator = $directory->getFlyIterator(Directory::ITERATOR_FILE | Directory::ITERATOR_DIRECTORY);
		while($iterator->valid()) {
			\common_Logger::i(print_r($iterator->current(), true));
			$iterator->next();
		}
//		if (! $directory->isEmpty()) {
//			foreach($directory->getDirectoryIterator() as $content) {
//				if ($directory->hasDirectory($content)) {
//					$this->exportDirectory($directory->getDirectory($content));
//				} else {
//					$this->addFile($directory->readPsrStream($content), $directory->getRelativePath() . '/' . $content);
//				}
//			}
//		}
	}
}