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

namespace oat\taoOpenWebItem\model\import;

use oat\tao\model\import\ImportHandlerHelperTrait;
use oat\tao\model\import\TaskParameterProviderInterface;
use \tao_models_classes_import_ImportHandler;
use \helpers_TimeOutHelper;
use \Exception;
use \common_report_Report;
use \common_exception_UserReadableException;
use \common_exception_Error;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Short description of class taoOpenWebItem_models_classes_ItemModel
 *
 * @access  public
 * @author  Joel Bout, <joel@taotesting.com>
 * @package taoOpenWebItem
 */
class OwiImportHandler implements tao_models_classes_import_ImportHandler, ServiceLocatorAwareInterface, TaskParameterProviderInterface
{
    use ImportHandlerHelperTrait {
        getTaskParameters as getDefaultTaskParameters;
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::getLabel()
     */
    public function getLabel()
    {
        return __('Open Web Item');
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::getForm()
     */
    public function getForm()
    {
        $form = new OwiImportForm();

        return $form->getForm();
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::import()
     * @param \core_kernel_classes_Class $class
     * @param \tao_helpers_form_Form     $form
     * @return common_report_Report
     * @throws \oat\oatbox\service\ServiceNotFoundException
     * @throws \common_Exception
     * @throws common_exception_Error
     */
    public function import($class, $form)
    {
        $uploadedFile = $this->fetchUploadedFile($form);

        if (isset($uploadedFile)) {

            helpers_TimeOutHelper::setTimeOutLimit(helpers_TimeOutHelper::LONG);

            // for backward compatibility
            $disable_validation = $form instanceof \tao_helpers_form_Form
                ? (array)$form->getValue('disable_validation')
                : (array)$form['disable_validation'];

            $validate = count($disable_validation) == 0 ? true : false;

            $importService = new ImportService();
            try {
                $report = $importService->importXhtmlFile($uploadedFile, $class, $validate);
            } catch (Exception $e) {
                $report = common_report_Report::createFailure(
                    __('An unexpected error occured during the OWI Item import.')
                );

                if ($e instanceof common_exception_UserReadableException) {
                    $report->add($e);
                }
            }
            helpers_TimeOutHelper::reset();

            $this->getUploadService()->remove($uploadedFile);
        } else {
            throw new common_exception_Error('No file provided as parameter \'source\' for OWI import');
        }

        return $report;
    }

    /**
     * Defines the task parameters to be stored for later use.
     *
     * @param \tao_helpers_form_Form $form
     * @return array
     */
    public function getTaskParameters(\tao_helpers_form_Form $form)
    {
        return array_merge(
            [
                'disable_validation' => $form->getValue('disable_validation'),
            ],
            $this->getDefaultTaskParameters($form)
        );
    }

}