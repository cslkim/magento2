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
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wysiwyg Config for Editor HTML Element
 *
 * @category    Mage
 * @package     Mage_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Cms_Model_Wysiwyg_Config extends Varien_Object
{
    /**
     * Wysiwyg behaviour
     */
    const WYSIWYG_ENABLED = 'enabled';
    const WYSIWYG_HIDDEN = 'hidden';
    const WYSIWYG_DISABLED = 'disabled';
    const IMAGE_DIRECTORY = 'wysiwyg';

    /**
     * @var Magento_AuthorizationInterface
     */
    protected $_authorization;
    
    /**
     * @var Mage_Core_Model_View_Url
     */
    protected $_viewUrl;

    /**
     * @param Magento_AuthorizationInterface $authorization
     * @param Mage_Core_Model_View_Url $viewUrl
     * @param array $data
     */
    public function __construct(
        Magento_AuthorizationInterface $authorization,
        Mage_Core_Model_View_Url $viewUrl,
        array $data = array()
    ) {
        $this->_authorization = $authorization;
        $this->_viewUrl = $viewUrl;
        parent::__construct($data);
    }

    /**
     * Return Wysiwyg config as Varien_Object
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param array|Varien_Object $data Varien_Object constructor params to override default config values
     * @return Varien_Object
     */
    public function getConfig($data = array())
    {
        $config = new Varien_Object();
        $viewUrl = $this->_viewUrl;

        $config->setData(array(
            'enabled'                       => $this->isEnabled(),
            'hidden'                        => $this->isHidden(),
            'use_container'                 => false,
            'add_variables'                 => true,
            'add_widgets'                   => true,
            'no_display'                    => false,
            'translator'                    => Mage::helper('Mage_Cms_Helper_Data'),
            'encode_directives'             => true,
            'directives_url'                =>
                Mage::getSingleton('Mage_Backend_Model_Url')->getUrl('*/cms_wysiwyg/directive'),
            'popup_css'                     =>
                $viewUrl->getViewFileUrl('mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'),
            'content_css'                   =>
                $viewUrl->getViewFileUrl('mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'),
            'width'                         => '100%',
            'plugins'                       => array()
        ));

        $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));

        if ($this->_authorization->isAllowed('Mage_Cms::media_gallery')) {
            $config->addData(array(
                'add_images' => true,
                'files_browser_window_url' => Mage::getSingleton('Mage_Backend_Model_Url')
                    ->getUrl('*/cms_wysiwyg_images/index'),
                'files_browser_window_width' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
                'files_browser_window_height'=> (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height'),
            ));
        }

        if (is_array($data)) {
            $config->addData($data);
        }

        Mage::dispatchEvent('cms_wysiwyg_config_prepare', array('config' => $config));

        return $config;
    }

    /**
     * Return URL for skin images placeholder
     *
     * @return string
     */
    public function getSkinImagePlaceholderUrl()
    {
        return $this->_viewUrl->getViewFileUrl('Mage_Cms::images/wysiwyg_skin_image.png');
    }

    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        $wysiwygState = Mage::getStoreConfig('cms/wysiwyg/enabled', $this->getStoreId());
        return in_array($wysiwygState, array(self::WYSIWYG_ENABLED, self::WYSIWYG_HIDDEN));
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return Mage::getStoreConfig('cms/wysiwyg/enabled') == self::WYSIWYG_HIDDEN;
    }
}
