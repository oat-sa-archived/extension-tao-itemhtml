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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOpenWebItem\model;

use common_Logger;
use common_report_Report;
use core_kernel_classes_Resource;
use tao_models_classes_service_ServiceCall;
use taoItems_models_classes_ItemCompiler;
use taoItems_models_classes_ItemsService;
use oat\taoItems\model\media\ItemMediaResolver;
use oat\oatbox\filesystem\Directory;
use oat\tao\model\media\sourceStrategy\HttpSource;

/**
 * The QTI Item Compiler
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package taoItems
 */
class OwiItemCompiler extends taoItems_models_classes_ItemCompiler
{
    /**
     * Compile qti item
     *
     * @throws taoItems_models_classes_CompilationFailedException
     * @return tao_models_classes_service_ServiceCall
     */
    public function compile()
    {
        $publicDirectory = $this->spawnPublicDirectory();
        $item = $this->getResource();

        $report = new common_report_Report(common_report_Report::TYPE_SUCCESS, __('Published %s', $item->getLabel()));
        if (!taoItems_models_classes_ItemsService::singleton()->isItemModelDefined($item)) {
            return $this->fail(__('Item \'%s\' has no model', $item->getLabel()));
        }

        $langs = $this->getContentUsedLanguages();
        if (empty($langs)) {
            $report->setType(common_report_Report::TYPE_ERROR);
            $report->setMessage(__('Item "%s" is not available in any language', $item->getLabel()));
        }
        foreach ($langs as $compilationLanguage) {
            $langReport = $this->deployItem($item, $compilationLanguage, $publicDirectory->getDirectory($compilationLanguage));
            if ($langReport->getType() == common_report_Report::TYPE_ERROR) {
                $report->setType(common_report_Report::TYPE_ERROR);
                $report->setMessage(__('Failed to publish %1$s', $item->getLabel()));
                $report->add($langReport);
                break;
            }
        }
        if ($report->getType() == common_report_Report::TYPE_SUCCESS) {
            $report->setData($this->createService($item, $publicDirectory));
        }

        return $report;
    }

    protected function deployItem(core_kernel_classes_Resource $item, $languageCode, $compiledDirectory)
    {
        $itemService = taoItems_models_classes_ItemsService::singleton();
        // copy local files
        $source = $itemService->getItemDirectory($item, $languageCode);
        $files = $source->getFlyIterator(Directory::ITERATOR_FILE | Directory::ITERATOR_RECURSIVE);
        foreach ($files as $file) {
            $relPath = ltrim($source->getRelPath($file), '/');
            if ($relPath != 'index.html') {
                $compiledDirectory->getFile($relPath)->write($file->readStream(), $file->getMimeType());
            }
        }
        $xhtml = $itemService->render($item, $languageCode);
        $subreport = $this->retrieveExternalResources($xhtml, $compiledDirectory);
        if ($subreport->getType() == \common_report_Report::TYPE_SUCCESS) {
            $xhtml = $subreport->getData();
            $compiledDirectory->getFile('index.html')->write($xhtml);
            return new common_report_Report(
                common_report_Report::TYPE_SUCCESS,
                __('Published "%1$s" in language "%2$s"', $item->getLabel(), $languageCode)
            );
        } else {
            return $subreport;
        }
    }

    /**
     *
     * @param unknown $xhtml
     * @param unknown $destination
     * @return common_report_Report
     */
    protected function retrieveExternalResources($xhtml, $compiledDirectory){

        $authorizedMedia = array("jpg", "jpeg", "png", "gif", "mp3", 'mp4', 'webm', 'swf', 'wma', 'wav', 'css', 'js');
        $mediaList = array();
        $expr = "/http[s]?:(\\\\)?\/(\\\\)?\/[^<'\"&?]+\.(".implode('|', $authorizedMedia).")/mi";//take into account json encoded url
        preg_match_all($expr, $xhtml, $mediaList, PREG_PATTERN_ORDER);

        $uniqueMediaList = array_unique($mediaList[0]);

        $report = new common_report_Report(common_report_Report::TYPE_SUCCESS);
        $source = new HttpSource();

        foreach($uniqueMediaList as $mediaUrl){
            // This is a file that has to be stored in the item compilation folder itself...
            // I do not get why they are all copied. They are all there they were copied from the item module...
            // But I agree that remote resources (somewhere on the Internet) should be copied via curl.
            // So if the URL does not matches a place where the TAO server is, we curl the resource and store it.
            // FileManager files should be considered as remote resources to avoid 404 issues. Indeed, a backoffice
            // user might delete an image in the filemanager during a delivery campain. This is dangerous.
            $decodedMediaUrl = str_replace('\/', '/', $mediaUrl);
            if (substr($decodedMediaUrl, 0, strlen(ROOT_URL)) != ROOT_URL) {
                try {
                    $stream = $source->getFileStream($decodedMediaUrl);
                    $localPath = basename($decodedMediaUrl);
                    $compiledDirectory->getFile($localPath)->write($stream);
                    $xhtml = str_replace($mediaUrl, $localPath, $xhtml, $replaced); //replace only when copyFile is successful
                } catch (\Exception $e) {
                    $report = new common_report_Report(common_report_Report::TYPE_ERROR, __('Failed retrieving %s', $decodedMediaUrl));
                    break;
                }
            }
        }
        if ($report->getType() == common_report_Report::TYPE_SUCCESS) {
            $report->setData($xhtml);
        }
        return $report;
    }
}
