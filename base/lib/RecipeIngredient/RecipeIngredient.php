<?php
/**
 * @class RecipeIngredient
 *
 * This class defines the relation between a project and its users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeIngredient extends Db_Object
{

    public function labelSimple()
    {
        if ($this->get('amount') != '') {
            $type = (($this->get('type') != 'unit' && $this->get('type') != '') ? strtolower((intval($this->get('amount')) > 1) ? __($this->get('type') . '_plural') : __($this->get('type'))) . ' ' . __('of') : '');
            return $this->get('amount') . ' ' .  (($type != '') ? $type . ' ' : '') . $this->get('ingredient');
        } else {
            return $this->get('ingredient');
        }
    }

}
