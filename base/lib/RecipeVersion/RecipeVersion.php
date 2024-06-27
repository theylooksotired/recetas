<?php
/**
 * @class RecipeVersion
 *
 * This class defines the users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeVersion extends Db_Object
{

    public function getCookTime()
    {
        $values = [
            '5_minutes' => 'PT5M',
            '15_minutes' => 'PT15M',
            '30_minutes' => 'PT30M',
            '45_minutes' => 'PT45M',
            '1_hour' => 'PT1H',
            '2_hours' => 'PT2H',
            '3_hours' => 'PT3H',
            '4_hours' => 'PT4H',
            '5_hours' => 'PT5H',
            '1_day' => 'P1D',
            '2_days' => 'P2D',
        ];
        return (isset($values[$this->get('cook_time')])) ? $values[$this->get('cook_time')] : '';
    }

    public function getDiet()
    {
        $values = [
            'diabetic' => 'DiabeticDiet',
            'gluten_free' => 'GlutenFreeDiet',
            'halal' => 'HalalDiet',
            'hindu' => 'HinduDiet',
            'kosher' => 'KosherDiet',
            'low_calorie' => 'LowCalorieDiet',
            'low_fat' => 'LowFatDiet',
            'low_lactose' => 'LowLactoseDiet',
            'low_salt' => 'LowSaltDiet',
            'vegan' => 'VeganDiet',
            'vegetarian' => 'VegetarianDiet',
        ];
        return (isset($values[$this->get('diet')])) ? $values[$this->get('diet')] : '';
    }

    public function getPreparationChatGPT()
    {
        $steps = $this->showUi('PreparationParagraph');
        $questionSteps = 'Escribe estos pasos de forma mas clara, ordenada y en tercera persona. No uses titulos, ni la lista de ingredientes, ni ennumeres los pasos. No uses lineas como "Buen provecho" o "Listo para disfrutar". La preparacion es: "' . $steps . '"';
        return str_replace('. ', ".\n\n", ChatGPT::answer($questionSteps));
    }

    public function getServings()
    {
        return ($this->get('servings') > 0) ? $this->get('servings') . ' ' . __('servings') : '';
    }

    public function persist($persistMultiple = true)
    {
        $persist = parent::persist($persistMultiple);
        if ($persist['status'] == StatusCode::OK) {
            if ($this->get('preparation_raw') != '') {
                $preparationLines = explode("\n", $this->get('preparation_raw'));
                foreach ($preparationLines as $line) {
                    $line = trim($line);
                    if ($line != '') {
                        $preparation = new RecipeVersionPreparation([
                            'id_recipe_version' => $this->id(),
                            'step' => $line
                        ]);
                        $preparation->persist();
                    }
                }
            }
            $this->persistSimple('preparation_raw', '');
            if ($this->get('ingredients_raw') != '') {
                $ingredientsLines = explode("\n", $this->get('ingredients_raw'));
                foreach ($ingredientsLines as $line) {
                    $line = trim($line);
                    if ($line != '') {
                        $ingredients = new RecipeVersionIngredient([
                            'id_recipe_version' => $this->id(),
                            'ingredient' => $line
                        ]);
                        $ingredients->persist();
                    }
                }
            }
            $this->persistSimple('ingredients_raw', '');
        }
        return $persist;
    }

}
