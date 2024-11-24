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

    public function persist($persistMultiple = true)
    {
        $this->values['ingredient'] = str_replace('a gusto', '', $this->values['ingredient']);
        $this->values['ingredient'] = ucfirst(strtolower(trim($this->values['ingredient'])));
        $amount = trim($this->values['amount']);
        $amount = str_replace('1/2', '½', $amount);
        $amount = str_replace('1/4', '¼', $amount);
        $amount = str_replace('3/4', '¾', $amount);
        $amount = str_replace('1/3', '⅓', $amount);
        $amount = str_replace('1/8', '⅛', $amount);
        $amount = str_replace('2/3', '⅔', $amount);
        $this->values['amount'] = $amount;
        return parent::persist($persistMultiple);
    }

}
