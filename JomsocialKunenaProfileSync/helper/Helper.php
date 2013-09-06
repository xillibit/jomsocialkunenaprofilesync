<?php
/**
 * @version   $Id: Helper.php,v 15069a9c2544 2011/02/10 12:10:07 likemandrake $
 * @author    Piotr Minkina <likemandrake@o2.pl>
 * @copyright Copyright (C) 2011 Piotr Minkina. All rights reserved.
 * @license   GNU/GPL version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for plugin plgSystemJomSocialKunenaSync, stories converted data to database
 *
 * @author Piotr Minkina <likemandrake@o2.pl>
 */
class plgSystemJomSocialKunenaSync_Helper
{
    /**
     * Instance of plgSystemJomSocialKunenaSync, but uses methods from JPlugin
     * @var JPlugin
     */
    protected $_plugin;
    
    /**
     * Instance of CommunityModelProfile
     * @var CommunityModelProfile
     */
    protected $_model;
    
    /**
     * Instance of KunenaUser
     * @var KunenaUser
     */
    protected $_user;
    
    /**
     * You know... This is CONSTRUCTOR!
     *
     * @param JPlugin $plugin
     */
    public function __construct(JPlugin $plugin)
    {
        $this->_plugin = $plugin;
    }
    
    /**
     * Getter for plgSystemJomSocialKunenaSync_Helper::$_model
     *
     * Lazy loads library with CommunityModelProfile and creates the instance of
     *
     * @param int  $userid
     * @param bool $force
     * @return CommunityModelProfile
     */
    public function getJomSocialModel($userid, $force = false)
    {
        if (null === $this->_model || true == $force)
        {
            require_once JPATH_ROOT . '/components/com_community/libraries/core.php';
            $this->_model = CFactory::getModel('profile');            
        }
        return $this->_model;
    }
    
    /**
     * Getter for plgSystemJomSocialKunenaSync_Helper::$_user
     *
     * Lazy loads library with KunenaUser and creates the instance of
     *
     * @param int $userid
     * @param bool $force
     * @return KunenaUser
     */
    public function getKunenaUser($userid, $force = false)
    {
        if (null === $this->_user || true == $force)
        {
            require_once JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
            $this->_user = KunenaFactory::getUser($userid);
        }
        return $this->_user;
    }
    
    /**
     * Stories mapped user data to JomSocial user profile
     *
     * @param int   $userid
     * @param array $values
     */
    public function toJomSocial($userid, array &$values)
    {
        echo 'tojomsocial';
        
        $model = $this->getJomSocialModel($userid);
        
        foreach ($values as $field => $value)
        {
            echo $field;
            $model->updateUserData($field, $userid, $value);
        }
    }
    
    /**
     * Stories mapped user data to Kunena user profile
     *
     * @param int   $userid
     * @param array $values
     */
    public function toKunena($userid, array &$values)
    {
        echo 'toKunena';
    
        $user = $this->getKunenaUser($userid);
        
        $user->setProperties($values);
        $user->save();
    }
}
