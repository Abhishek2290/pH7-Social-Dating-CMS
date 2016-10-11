<?php
/**
 * @title          Add Fake Profiles; Process Class
 * @desc           Generate Fake Profiles from Web API.
 *
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2014-2016, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Admin / From / Processing
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use
PH7\Framework\Url\Url,
PH7\Framework\Geo\Misc\Country,
PH7\Framework\Security\Validate\Validate,
PH7\Framework\Ip\Ip;

/** Reset the time limit and increase the memory **/
@set_time_limit(0);
@ini_set('memory_limit', '528M');

class AddFakeProfilesFormProcess extends Form
{
    const API_URL = 'http://api.randomuser.me';
    const API_VER = '1.1';

    public function __construct()
    {
        parent::__construct();

        $oUser = new UserCore;
        $oUserModel = new UserCoreModel;
        $oExistsModel = new ExistsCoreModel;
        $oValidate = new Validate;

        foreach ($this->getApiClient()['results'] as $aUser)
        {
            $sEmail = trim($aUser['email']);
            $sUsername = trim($aUser['login']['username']);
            if ($oValidate->email($sEmail) && !$oExistsModel->email($sEmail) && $oValidate->username($sUsername))
            {
                $aData['username'] = $sUsername;
                $aData['email'] = $sEmail;
                $aData['first_name'] = $aUser['name']['first'];
                $aData['last_name'] = $aUser['name']['last'];
                $aData['password'] = $aUser['login']['password'];
                $aData['sex'] = $aUser['gender'];
                $aData['match_sex'] = array($oUser->getMatchSex($aData['sex']));
                $aData['country'] = Country::fixCode($aUser['nat']);
                $aData['city'] = $aUser['location']['city'];
                $aData['state'] = $aUser['location']['state'];
                $aData['zip_code'] = $aUser['location']['postcode'];
                $aData['birth_date'] = $this->dateTime->get($aUser['dob'])->date('Y-m-d');
                $aData['avatar'] = $aUser['picture']['large'];
                $aData['ip'] = Ip::get();

                $aData['profile_id'] = $oUserModel->add(escape($aData, true));

                $this->addAvatar($aData, $oUser);
            }
        }

        unset($oUser, $oUserModel, $oExistsModel, $oValidate, $aUser, $aData, $aUserData);

        \PFBC\Form::setSuccess('form_add_fake_profiles', nt('%n% user has successfully been added.', '%n% users have successfully been added.', $this->getUserNumber()));
    }

    protected function getUserNumber()
    {
        return $this->httpRequest->post('num');
    }

    protected function getApiClient()
    {
        $sApiUrl = static::API_URL;
        $sApiParms = '?' . Url::httpBuildQuery($this->getApiParameters(), null, '&');
        $sApiVer = static::API_VER;
        $rUserData = $this->getApiResults($sApiUrl, $sApiParms, $sApiVer);
        return json_decode($rUserData, true);
    }

    private function getApiParameters()
    {
        return [
            'results' => $this->getUserNumber(),
            'gender' => $this->httpRequest->post('sex'),
            'nat' => $this->httpRequest->post('nat'),
            'noinfo' => 1
        ];
    }

    /**
     * Get Data from the third-party API.
     *
     * @param string $sApiUrl API URL.
     * @param string $sApiParms Parameters to send to the API.
     * @param string $sApiVersion Version of the API it will use. If fails from the API server, it will ignore it.
     *
     * @return void
     */
    private function getApiResults($sApiUrl, $sApiParms, $sApiVersion)
    {
        if ($rData = $this->file->getFile($sApiUrl . PH7_SH . $sApiVersion . PH7_SH . $sApiParms)) {
            return $rData;
        } else {
            return $this->file->getFile($sApiUrl . PH7_SH . $sApiParms);
        }
    }

    /**
     * Add User's Avatar.
     *
     * @param array $aData
     * @param object \PH7\UserCore $oUser
     *
     * @return void
     */
    private function addAvatar(array $aData, UserCore $oUser)
    {
        // Sometime, cURL returns FALSE and doesn't work at all under Windowns server or some other specific server config, so use file_get_contents() instead as it will work.
        if (!$rFile = $this->file->getUrlContents($aData['avatar'])) {
            $rFile = $this->file->getFile($aData['avatar']);
        }

        // Create a temporary file before creating the avatar images
        $sTmpFile = PH7_PATH_TMP . PH7_DS . uniqid() . sha1($aData['avatar']) . '.tmp';
        $this->file->putFile($sTmpFile, $rFile);

        $oUser->setAvatar($aData['profile_id'], $aData['username'], $sTmpFile, 1); // Create the different avatar sizes and set the avatar
        $this->file->deleteFile($sTmpFile);// Remove the temporary file as we don't need it anymore
    }
}
