<?php
/**
 * @version   $Id: Converter.php,v 2ff1149344e5 2011/02/10 13:36:02 likemandrake $
 * @author    Piotr Minkina <likemandrake@o2.pl>
 * @copyright Copyright (C) 2011 Piotr Minkina. All rights reserved.
 * @license   GNU/GPL version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Converter for plugin plgSystemJomSocialKunenaSync, converts data between
 * JomSocial and Kunena
 *
 * @author Piotr Minkina <likemandrake@o2.pl>
 */
class plgSystemJomSocialKunenaSync_Converter
{
    /**
     * Instance of plgSystemJomSocialKunenaSync, but uses methods from JPlugin
     * @var JPlugin
     */
    protected $_plugin;
    
    /**
     * This is map from Kunena profile form value name to Kunena db field name
     *
     * Saying "profile form value name" I mean names of form fields, which are
     * sent when user saves his profile in Kunena. This names are also used
     * in plugin configuration, to reduce the amount of mapping.
     *
     * @var array
     */
    protected $_kunenaMap;
    
    /**
     * This is map from Kunena profile form value name to JomSocial field name
     * @see plgSystemJomSocialKunenaSync_Mapper::$_kunenaMap
     * @var array
     */
    protected $_jomsocialMap;
    
    /**
     * Temporary variable with user data, which should be converted
     * @var array
     */
    protected $_userdata;
    
    /**
     * Temporary variable with converted user data
     * @var array
     */
    protected $_converted;
    
    /**
     * You know... This is CONSTRUCTOR!
     *
     * Additionally prepares array mappings from Kunena form field to Kunena
     * db field and from Kunena form field to JomSocial field
     *
     * @see plgSystemJomSocialKunenaSync_Mapper::$_kunenaMap
     * @see plgSystemJomSocialKunenaSync_Mapper::$_jomsocialMap
     * @param JPlugin $plugin
     */
    public function __construct(JPlugin $plugin)
    {
        $this->_plugin       = $plugin;
        $this->_jomsocialMap = array();
        $this->_kunenaMap    = array
        (
        //  Kunena form field => Kunena db field
            'personaltext'    => 'personalText',
            'birthdate'       => 'birthdate',
            'location'        => 'location',
            'gender'          => 'gender',
            'websiteurl'      => 'websiteurl',
            'twitter'         => 'TWITTER',
            'facebook'        => 'FACEBOOK',
            'myspace'         => 'MYSPACE',
            'skype'           => 'SKYPE',
            'linkedin'        => 'LINKEDIN',
            'delicious'       => 'DELICIOUS',
            'friendfeed'      => 'FRIENDFEED',
            'digg'            => 'DIGG',
            'yim'             => 'YIM',
            'aim'             => 'AIM',
            'gtalk'           => 'GTALK',
            'icq'             => 'ICQ',
            'msn'             => 'MSN',
            'blogspot'        => 'BLOGSPOT',
            'flickr'          => 'FLICKR',
            'bebo'            => 'BEBO',
            'signature'       => 'signature',
            'messageordering' => 'ordering',
            'hidemail'        => 'hideEmail',
            'showonline'      => 'showOnline'
        );
        
        // Prepares: Kunena form field => JomSocial field
        foreach (array_keys($this->_kunenaMap) as $param)
        {
            // Configuration of the plugin has the same names of params as
            // Kunena form fields names, so we use it to get JomSocisl field
            $jomsocial = $this->_plugin->params->get($param, 0);
            echo $jomsocial.' ';
            if (!empty($jomsocial))
            {
                $this->_jomsocialMap[$param] = $jomsocial;
            }
        }
    }
    
    /**
     * Converts values between Kunena and JomSocial user profile data
     *
     * @param array $userdata
     * @param bool  $tokunena
     * @return array
     */
    public function &convert(array &$userdata, $tokunena = true)
    {
        $this->_userdata  = $userdata;
        $this->_converted = array();
        
        foreach ($this->_jomsocialMap as $kunena => $jomsocial)
        {
            if ($tokunena)
            {
                // From JomSocial field name
                $from = $jomsocial;
                // To Kunena db field name
                $to   = $this->_kunenaMap[$kunena];
            }
            else
            {
                // From Kunena form field name
                $from = $kunena;
                // To JomSocial field name
                $to   = $jomsocial;
            }
            $method = '_' . strtolower($kunena) . 'Convert';
            if (method_exists($this, $method))
            {
                $this->$method($from, $to, $tokunena);
            }
            else
            {
                $this->_defaultConvert($from, $to);
            }
        }
        var_dump($this->_converted);
        return $this->_converted;
    }
    
    /**
     * Default conversion, simple rewriting of value
     *
     * @param string $from
     * @param string $to
     */
    protected function _defaultConvert($from, $to)
    {
        $this->_converted[$to] = (string) $this->_userdata[$from];
    }
    
    /**
     * One to one conversion for Gender
     *
     * @param string $from
     * @param string $to
     * @param bool   $tokunena
     */
    protected function _genderConvert($from, $to, $tokunena)
    {
        $male   = $this->_plugin->params->get('conv_gender_male', 'Male');
        $female = $this->_plugin->params->get('conv_gender_female', 'Female');
        
        $map    = array(0 => '', 1 => $male, 2 => $female);
        
        $this->_converted[$to] = $this->_mappedConversion($map, $from, $tokunena);
    }
    
    /**
     * Conversion for Birthdate
     *
     * @param string $from
     * @param string $to
     * @param bool   $tokunena
     */
    protected function _birthdateConvert($from, $to, $tokunena)
    {
        var_dump($this->_userdata);
        if ($tokunena)
        {
            $date = explode(' ', $this->_userdata[$from]);
            $date = array_shift($date);
            
            $this->_converted[$to] = $date;
        }
        else
        {
            $this->_converted[$to] = $this->_userdata[$from . '1']
                             . '-' . $this->_userdata[$from . '2']
                             . '-' . $this->_userdata[$from . '3']
                             . ' 23:59:59';
        }
    }
    
    /**
     * Conversion for Website URL, optionally with Website Name
     *
     * @param string $from
     * @param string $to
     * @param bool   $tokunena
     */
    protected function _websiteurlConvert($from, $to, $tokunena)
    {
        $websitename = $this->_plugin->params->get('conv_websitename', 0);
        
        if ($tokunena)
        {
            if (!empty($websitename))
            {
                $this->_converted['websitename'] = $this->_userdata[$websitename];
            }
            $this->_converted[$to] = substr($this->_userdata[$from], strpos($this->_userdata[$from], '://') + 3);
        }
        else
        {
            if (!empty($websitename) && $websitename != $to)
            {
                $this->_converted[$websitename] = $this->_userdata['websitename'];
            }
            $this->_converted[$to] = 'http://' . $this->_userdata[$from];
        }
    }
    
    /**
     * One to one conversion for Message Ordering forum setting
     *
     * @param string $from
     * @param string $to
     * @param bool   $tokunena
     */
    protected function _messageorderingConvert($from, $to, $tokunena)
    {
        $asc  = $this->_plugin->params->get('conv_ordering_asc', 'Ascending');
        $desc = $this->_plugin->params->get('conv_ordering_desc', 'Descending');
        
        $map  = array(0 => $asc, 1 => $desc);
        
        $this->_converted[$to] = $this->_mappedConversion($map, $from, $tokunena);
    }
    
    /**
     * One to one conversion for Hide E-mail forum setting
     *
     * @param string $from
     * @param string $to
     * @param bool   $tokunena
     */
    protected function _hidemailConvert($from, $to, $tokunena)
    {
        $no  = $this->_plugin->params->get('conv_no', 'No');
        $yes = $this->_plugin->params->get('conv_yes', 'Yes');
        
        $map = array(0 => $no, 1 => $yes);
        
        $this->_converted[$to] = $this->_mappedConversion($map, $from, $tokunena);
    }
    
    /**
     * One to one conversion for Show Online forum setting
     *
     * @param string $from
     * @param string $to
     * @param bool   $tokunena
     */
    protected function _showonlineConvert($from, $to, $tokunena)
    {
        $this->_hidemailConvert($from, $to, $tokunena);
    }
    
    /**
     * Helper for one to one conversions (mapped rewriting)
     *
     * @param array  $map
     * @param string $from
     * @param bool   $tokunena
     * @return mixed
     */
    protected function _mappedConversion(array &$map, $from, $tokunena)
    {
        $to = $this->_userdata[$from];
        if ($tokunena)
        {
            $map = array_flip($map);
        }
        if (!isset($map[$to]))
        {
            reset($map);
            return current($map);
        }
        return $map[$to];
    }
}
