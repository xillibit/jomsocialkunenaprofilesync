<?php
/**
 * @version   $Id: jomsocialkunenasync.php,v c7a388095e27 2011/02/10 11:39:01 likemandrake $
 * @author    Piotr Minkina <likemandrake@o2.pl>
 * @copyright Copyright (C) 2011 Piotr Minkina. All rights reserved.
 * @license   GNU/GPL version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Provides user profile synchronization between JomSocial and Kunena
 *
 * @author Piotr Minkina <likemandrake@o2.pl>
 */
class plgSystemJomSocialKunenaProfileSync extends JPlugin {
    /**
     * Instance of plgSystemJomSocialKunenaSync_Helper
     * @var plgSystemJomSocialKunenaSync_Helper
     */
    protected $_helper;
    
    /**
     * Instance of plgSystemJomSocialKunenaSync_Converter
     * @var plgSystemJomSocialKunenaSync_Converter
     */
    protected $_converter;
    
    /**
     * To prevent infinite loop
     * @var bool
     */
    protected $_sync;
    
    /**
     * Temporary variable with user data (helpful with JomSocial events)
     * @var array
     */
    protected $_userdata;
    
    /**
     * You know... This is CONSTRUCTOR!
     *
     * @param JObservable $subject
     * @param array       $config
     */
    public function __construct(&$subject, $config = array())  {
        parent::__construct($subject, $config);
        $this->_type = 'community';
        $this->_sync = true;
    }
    
    /**
     * Getter for plgSystemJomSocialKunenaSync::$_helper
     *
     * Lazy loads library with plgSystemJomSocialKunenaSync_Helper and creates
     * the instance of
     *
     * @return plgSystemJomSocialKunenaSync_Helper
     */
    public function getHelper() {
        if (null === $this->_helper)
        {
            require_once JPATH_PLUGINS . '/system/jomsocialkunenaprofilesync/helper/Helper.php';
            $this->_helper = new plgSystemJomSocialKunenaSync_Helper($this);
        }
        return $this->_helper;
    }
    
    /**
     * Getter for plgSystemJomSocialKunenaSync::$_converter
     *
     * Lazy loads library with plgSystemJomSocialKunenaSync_Converter
     * and creates the instance of
     *
     * @return plgSystemJomSocialKunenaSync_Converter
     */
    public function getConverter()  {
        if (null === $this->_converter)
        {
            require_once JPATH_PLUGINS . '/system/jomsocialkunenaprofilesync/helper/Converter.php';
            $this->_converter = new plgSystemJomSocialKunenaSync_Converter($this);
        }
        return $this->_converter;
    }
    
    /**
     * This event is triggered after an update of a user record, or when a new
     * user has been stored in the database
     *
     * @param array  $user
     * @param bool   $isnew
     * @param bool   $success
     * @param string $msg
     */
     public function onUserAfterSave($user, $isNew, $result, $error) {      
      if ($result && $this->_sync && isset($user['option']) && 'com_kunena' == $user['option'] ) {
            $this->_sync = false;
            $data =& $this->getConverter()->convert($user, false);
            $this->getHelper()->toJomSocial($user['id'], $data);
        }
    }
   
       
    /**
     * This event trigger before an update JomSocial user profile data is made
     *
     * @param int   $userid
     * @param array $values
     * @return bool
     */
    public function onBeforeProfileUpdate($userid, array $values) {
        $this->_userdata = $values;
        return true;
    }
    
    /**
     * This event trigger after JomSocial user profile data is saved
     *
     * @param int  $userid
     * @param bool $success
     */
    public function onAfterProfileUpdate($userid, $success) {
        if ($success && true == $this->_sync)
        {
            $this->_sync = false;
            $data =& $this->getConverter()->convert($this->_userdata, true);
            $this->getHelper()->toKunena($userid, $data);
        }
    }
}
