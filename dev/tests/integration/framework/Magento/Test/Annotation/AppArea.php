<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Test_Annotation_AppArea
{
    const ANNOTATION_NAME = 'magentoAppArea';

    /**
     * @var Magento_Test_Application
     */
    private $_application;

    /**
     * List of allowed areas
     *
     * @var array
     */
    private $_allowedAreas = array(
        Mage_Core_Model_App_Area::AREA_GLOBAL,
        Mage_Core_Model_App_Area::AREA_ADMINHTML,
        Mage_Core_Model_App_Area::AREA_FRONTEND,
        'install',
        'webapi',
        'cron',
    );

    /**
     * @param Magento_Test_Application $application
     */
    public function __construct(Magento_Test_Application $application)
    {
        $this->_application = $application;
    }

    /**
     * Get current application area
     *
     * @param array $annotations
     * @return string
     * @throws Magento_Exception
     */
    protected function _getTestAppArea($annotations)
    {
        $area = isset($annotations['method'][self::ANNOTATION_NAME])
                    ? current($annotations['method'][self::ANNOTATION_NAME])
                    : (isset($annotations['class'][self::ANNOTATION_NAME])
                        ? current($annotations['class'][self::ANNOTATION_NAME])
                        : Magento_Test_Application::DEFAULT_APP_AREA);

        if (false == in_array($area, $this->_allowedAreas)) {
            throw new Magento_Exception(
                'Invalid "@magentoAppArea" annotation, can be "' . implode('", "', $this->_allowedAreas) . '" only.'
            );
        }

        return $area;
    }

    /**
     * Start test case event observer
     *
     * @param PHPUnit_Framework_TestCase $test
     */
    public function startTest(PHPUnit_Framework_TestCase $test)
    {
        $area = $this->_getTestAppArea($test->getAnnotations());
        if ($this->_application->getArea() !== $area) {
            $this->_application->reinitialize();

            if ($this->_application->getArea() !== $area) {
                $this->_application->loadArea($area);
            }
        }
    }
}