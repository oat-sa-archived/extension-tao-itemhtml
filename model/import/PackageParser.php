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
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2017 Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOpenWebItem\model\import;

use oat\oatbox\filesystem\File;
use \tao_models_classes_Parser;
use \Exception;
use \tao_helpers_File;
use \ZipArchive;

/**
 * The XHTML parser enable you to validate the format and extract an XHTML
 * An XHTML package is valide when it's a zip archive containing at it's root a
 * file with the XHTML Transitional doctype, validate and well formed.
 * The external resources (media, css, scripts, images, etc.) in the package are
 * too.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package taoItems
 */
class PackageParser extends tao_models_classes_Parser
{
    /**
     * Validate the zip archive. Local and remote file are allowed.
     * - Check if file is found
     * - Check content is not empty
     * - Check extension as zip
     * - In case of local, check path security
     * - Then validate archive
     *
     * @param string $schema
     * @return bool
     * @throws Exception
     */
    public function validate($schema = '')
    {
        $forced = $this->valid;
        $this->valid = true;

        try {
            switch ($this->sourceType) {
                case self::SOURCE_FILE:
                if (!file_exists($this->source)) {
                        throw new Exception("File " . $this->source . " not found.");
                    }
                    if (!is_readable($this->source)) {
                        throw new Exception("Unable to read file " . $this->source);
                    }
                    if (!preg_match("/\.zip$/", basename($this->source))) {
                        throw new Exception("Wrong file extension in " . $this->source . ", zip extension is expected");
                    }
                    if (!tao_helpers_File::securityCheck($this->source)) {
                        throw new Exception($this->source . " seems to contain some security issues");
                    }
                    break;

                case self::SOURCE_FLYFILE:
                    /** @var File $this->source */
                    if (! $this->source->exists()) {
                        throw new Exception('Source file does not exists ("' . $this->source->getBasename() . '").');
                    }
                    if (!preg_match("/\.zip$/", $this->source->getBasename())) {
                        throw new Exception('Wrong file extension in ' . $this->source->getBasename() . ', zip extension is expected.');
                    }
                    if (! $this->content = $this->source->read()) {
                        throw new Exception('Unable to read file ("' . $this->source->getBasename() . '").');
                    }
                    break;

                default:
                    throw new Exception("File cannot be handled by package parser.");
                    break;
            }
        } catch (Exception $e) {
            if ($forced) {
                throw $e;
            } else {
                $this->addError($e);
            }
        }

        if ($this->valid && !$forced) {
            $this->valid = false;
            $this->valid = $this->isValidArchive();
        }

        return $this->valid;
    }

    /**
     * Extract the zip archive and return the path to extracted package
     *
     * @return string
     */
    public function extract()
    {
        $tmpDirectory = $tmpArchive = $archiveLocation = null;

        switch ($this->sourceType) {
            case self::SOURCE_FILE:
                $archiveLocation = $this->source;
                break;
            case self::SOURCE_FLYFILE:
                $tmpDirectory = tao_helpers_File::createTempDir();
                $tmpArchive = uniqid('owiArchive') . 'zip';
                $archiveLocation = $tmpDirectory . $tmpArchive;
                $sourceResource = $this->source->readStream();
                $destinationResource = fopen($archiveLocation, 'w');
                stream_copy_to_stream($sourceResource, $destinationResource);
                fclose($sourceResource);
                fclose($destinationResource);
                break;
        }

        $content = '';
        $folder = tao_helpers_File::createTempDir();
        $zip = new ZipArchive();
        if ($zip->open($archiveLocation) === true) {
            if ($zip->extractTo($folder)) {
                $content = $folder;
            }
            $zip->close();
        }

        if (file_exists($tmpDirectory . $tmpArchive)) {
            unlink($tmpDirectory . $tmpArchive);
            unlink($tmpDirectory);
        }

        return $content;
    }

    /**
     * Check if the archive is valid by checking if an index.html exists at package root
     *
     * @return bool
     */
    protected function isValidArchive()
    {
        $tmpDirectory = $tmpArchive = $archiveLocation = null;

        switch ($this->sourceType) {
            case self::SOURCE_FILE:
                $archiveLocation = $this->source;
                break;
            case self::SOURCE_FLYFILE:
                $tmpDirectory = tao_helpers_File::createTempDir();
                $tmpArchive = uniqid('owiArchive') . 'zip';
                $archiveLocation = $tmpDirectory . $tmpArchive;
                $sourceResource = $this->source->readStream();
                $destinationResource = fopen($archiveLocation, 'w');
                stream_copy_to_stream($sourceResource, $destinationResource);
                fclose($sourceResource);
                fclose($destinationResource);
                break;
        }

        $isValid = false;
        $zip = new ZipArchive();
        //check the archive opening and the consistency
        if ($zip->open($archiveLocation, ZIPARCHIVE::CHECKCONS) !== false) {
            //check if the manifest is there
            if ($zip->locateName("index.html") !== false) {
                $isValid = true;
            } else {
                $this->addError(__("An Open Web Item package must contain an index.html file at the root of the archive."));
            }
        } else {
            $this->addError(__("The ZIP archive containing the Open Web Item could not be open."));
        }

        if (file_exists($tmpDirectory . $tmpArchive)) {
            unlink($tmpDirectory . $tmpArchive);
            unlink($tmpDirectory);
        }

        return $isValid;
    }
}