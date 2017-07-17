<?php
/**
 * @title            Kernel Class
 * @desc             Kernel Class of pH7CMS.
 *
 * @author           Pierre-Henry Soria <hello@ph7cms.com>
 * @copyright        (c) 2011-2017, Pierre-Henry Soria. All Rights Reserved.
 * @license          CC-BY License - http://creativecommons.org/licenses/by/3.0/
 * @link             http://ph7cms.com
 * @package          PH7 / Framework / Core
 * @version          1.5
 */

namespace PH7\Framework\Core;

defined('PH7') or exit('Restricted access');

use PH7\Framework\Config\Config;
use PH7\Framework\File\File;
use PH7\Framework\Mvc\Request\Http;
use PH7\Framework\Mvc\Router\Uri;
use PH7\Framework\Navigation\Browser;
use PH7\Framework\Page\Page;
use PH7\Framework\Registry\Registry;
use PH7\Framework\Security\Version;
use PH7\Framework\Str\Str;
use PH7\Framework\Url\Header;

abstract class Kernel
{
    const SOFTWARE_NAME = 'pH7CMS';
    const SOFTWARE_DESCRIPTION = 'pH7CMS Software allows to build next generation Social Dating app/website for your business!';
    const SOFTWARE_WEBSITE = 'http://ph7cms.com';
    const SOFTWARE_LICENSE_KEY_URL = 'http://ph7cms.com/memberships';
    const SOFTWARE_DOC_URL = 'http://ph7cms.com/doc';
    const SOFTWARE_FAQ_URL = 'http://ph7cms.com/faq';
    const SOFTWARE_FORUM_URL = 'http://ph7cms.com/forum';
    const SOFTWARE_EMAIL = 'hello@ph7cms.com';
    const SOFTWARE_AUTHOR = 'Pierre-Henry Soria';
    const SOFTWARE_COMPANY = 'Social Dating CMS | By Pierre-Henry Soria';
    const SOFTWARE_LICENSE = 'GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.';
    const SOFTWARE_COPYRIGHT = '(c) 2011-2017, Pierre-Henry Soria. All Rights Reserved.';
    const SOFTWARE_VERSION_NAME = Version::KERNEL_VERSION_NAME;
    const SOFTWARE_VERSION = Version::KERNEL_VERSION;
    const SOFTWARE_BUILD = Version::KERNEL_BUILD;
    const SOFTWARE_TECHNOLOGY_NAME = Version::KERNEL_TECHNOLOGY_NAME;
    const SOFTWARE_SERVER_NAME = Version::KERNEL_SERVER_NAME;
    const SOFTWARE_USER_AGENT = 'pH7 Web Simulator/1.1.2'; // USER AGENT NAME of Web Simulator
    const SOFTWARE_CRAWLER_NAME = 'ph7hizupcrawler'; // CRAWLER BOT NAME

    /** @var Config */
    protected $config;

    /** @var Str */
    protected $str;

    /** @var File */
    protected $file;

    /** @var Http */
    protected $httpRequest;

    /** @var Browser */
    protected $browser;

    /** @var Registry */
    protected $registry;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->str = new Str;
        $this->file = new File;
        $this->httpRequest = new Http;
        $this->browser = new Browser;
        $this->registry = Registry::getInstance();

        /**
         * @internal The "_checkLicense" method cannot be declare more than one time. The "Kernel.class.php" file is included many times in the software, so we need to check that with a constant.
         */
        if (!defined( 'PH7_CHECKED_LIC' )) {
            define( 'PH7_CHECKED_LIC', 1 ); // OK, now we have checked the license key
            $this->_checkLicense();
        }
    }

    /**
     * Check License key.
     *
     * @return integer Returns '1' if the license key is invalid and stops the script with the exit() function.
     */
    final private function _checkLicense()
    {
        define('PH7_SOFTWARE_STATUS', true);
        define('PH7_LICENSE_STATUS', 'active');
        define('PH7_LICENSE_NAME', 'pH7Builder, Open License');
        define('PH7_VALID_LICENSE', (PH7_LICENSE_STATUS === 'active'));

        if (!PH7_SOFTWARE_STATUS) {
            $sLicenseMsg = t('You need to buy a <strong>valid <a href="%0%">pH7CMS</a> License Key</strong> to use the features requiring a license key!', self::SOFTWARE_WEBSITE);
            Page::message($sLicenseMsg);
        }

        if (!PH7_VALID_LICENSE) {
            if ($this->_isLicenseFeature()) {
                $this->licenseErrMsg();
            }
        }
    }

    /**
     * Checks if there's a feature that requires a license key.
     *
     * @return boolean Returns TRUE if the feature requires a license, FALSE otherwise.
     */
    final private function _isLicenseFeature()
    {
        return (
            ($this->registry->module === PH7_ADMIN_MOD && ($this->registry->action === 'ads' || $this->registry->action === 'addfakeprofiles' || $this->registry->action === 'import')) ||
            ($this->registry->module === 'payment' || $this->httpRequest->getExists('mobapp') || false !== stripos($this->httpRequest->currentUrl(), 'upgrade'))
        );
    }

    /**
     * Displays a error message when there is a feature that requires a license key.
     *
     * @return void
     */
    final private function licenseErrMsg()
    {
        if (\PH7\AdminCore::auth()) {
            // Message for admins
            Header::redirect(Uri::get(PH7_ADMIN_MOD, 'setting', 'license'), t('You are still using the Free Version. It\'s time now to buy a Pro License and get all amazing features and be able to use this module.'), 'error');
        } else {
            // Message for guests
            exit(t('#LICENSE ERROR# The owner of this website needs to pay a <a href="%0%">pH7CMS License</a> to use this feature.', self::SOFTWARE_WEBSITE));
        }
    }

    public function __destruct()
    {
        unset(
            $this->config,
            $this->str,
            $this->file,
            $this->httpRequest,
            $this->browser,
            $this->registry
        );
    }

    /**
     * Clone is set to private to stop cloning.
     *
     * @access private
     */
    private function __clone() {}
}