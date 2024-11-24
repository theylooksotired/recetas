<?php
/**
 * @class RecipeVersionIngredient
 *
 * This class defines the relation between a project and its users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeVersionIngredient extends Db_Object
{

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
