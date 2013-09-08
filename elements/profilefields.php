<?php
/**
 * @version   $Id: profilefields.php,v bc8f018bf117 2011/02/10 11:45:57 likemandrake $
 * @author    Piotr Minkina <likemandrake@o2.pl>
 * @copyright Copyright (C) 2011 Piotr Minkina. All rights reserved.
 * @license   GNU/GPL version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Displays JomSocial fields with grouping
 *
 * @author Piotr Minkina <likemandrake@o2.pl>
 */
class JFormFieldProfileFields extends JFormField {
    protected $type = 'Profile Fields';

     /**
     * Prepares select list of JomSocial fields
     *
     * @see JElement::fetchTooltip()
     */
      protected function getInput() {
      	jimport('joomla.filesystem.folder');

      	if ( JFolder::exists(JPATH_ADMINISTRATOR .  '/components/com_community/models') ) {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR .  '/components/com_community/models');

        $profiles = JModelLegacy::getInstance('Profiles', 'CommunityModel');
        $fields   = $profiles->getFields(false);
        $attribs  = array('class' => 'inputbox');
        $options  = array();


        $none = $this->element['none'];

		    $size = $this->element['size'];
		    $class = $this->element['class'];


        $attribs = ' ';
		if ($size) {
			$attribs .= 'size="' . $size . '"';
		}
		if ($class) {
			$attribs .= 'class="' . $class . '"';
		} else {
			$attribs .= 'class="inputbox"';
		}
		if (!empty($this->element['multiple'])) {
			$attribs .= ' multiple="multiple"';
		}

           $options[] = JHTML::_('select.option', '0', ' ----- ' . JText::_('Disabled') . ' ----- ');
        foreach ($fields as $field)
        {
            if (!$field->published)
            {
                continue;
            }
            if ('group' == $field->type)
            {
                $options[] = JHTML::_('select.optgroup', $field->name);
            }
            else
            {
                $options[] = JHTML::_('select.option', $field->fieldcode, $field->name);
            }
        }
        return JHTML::_('select.genericlist', $options,
                        $this->name,
                        JArrayHelper::toString($attribs),
                        'value', 'text',  $this->value);
       }
    }
}
