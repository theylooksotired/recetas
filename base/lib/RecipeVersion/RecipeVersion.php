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

    public function getPrepTime()
    {
        $cookTime = $this->get('cook_time');
        $values = [
            '5_minutes' => 'PT5M',
            '15_minutes' => 'PT5M',
            '30_minutes' => 'PT10M',
            '45_minutes' => 'PT10M',
            '1_hour' => 'PT30M',
            '2_hours' => 'PT30M',
            '3_hours' => 'PT30M',
            '4_hours' => 'PT30M',
            '5_hours' => 'PT30M',
            '1_day' => 'PT30M',
            '2_days' => 'PT30M',
        ];
        return (isset($values[$cookTime])) ? $values[$cookTime] : 'PT10M';
    }

    public function getCookTime()
    {
        $default = 'PT1H';
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
        return (isset($values[$this->get('cook_time')])) ? $values[$this->get('cook_time')] : $default;
    }

    public function getTotalTime()
    {
        $default = 'PT1H10M';
        $values = [
            '5_minutes' => 'PT10M',
            '15_minutes' => 'PT20M',
            '30_minutes' => 'PT40M',
            '45_minutes' => 'PT55M',
            '1_hour' => 'PT1H10M',
            '2_hours' => 'PT2H10M',
            '3_hours' => 'PT3H10M',
            '4_hours' => 'PT4H10M',
            '5_hours' => 'PT5H10M',
            '1_day' => 'P1DT10M',
            '2_days' => 'P2DT10M',
        ];
        return (isset($values[$this->get('cook_time')])) ? $values[$this->get('cook_time')] : $default;        
    }

    public function getTotalTimeLabel()
    {
        $cookTime = $this->get('cook_time');
        return (isset($values[$cookTime])) ? __[$cookTime] : __('1_hour');
    }

    public function getDiet()
    {
        $default = 'low_fat';
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
        return (isset($values[$this->get('diet')])) ? $values[$this->get('diet')] : $default;
    }

    public function getServings()
    {
        $servings = ($this->get('servings') > 0) ? $this->get('servings') : 4;
        return $servings . ' ' . __('servings');
    }

    public function getPreparationChatGPT()
    {
        $steps = $this->showUi('PreparationParagraph');
        $questionSteps = 'Escribe estos pasos de forma mas clara, ordenada y en tercera persona. No uses titulos, ni la lista de ingredientes, ni ennumeres los pasos. No uses lineas como "Buen provecho" o "Listo para disfrutar". La preparacion es: "' . $steps . '"';
        return str_replace('. ', ".\n\n", ChatGPT::answer($questionSteps));
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

    public function toJson()
    {
        $infoIns = (array)$this->values;
        $infoIns['image'] = $this->getImageUrl('image', 'web');
        $infoIns['image_small'] = $this->getImageUrl('image', 'small');
        $infoIns['cook_time'] = $infoIns['cook_time'];
        $infoIns['cook_time_label'] = __($infoIns['cook_time']);
        $infoIns['cooking_method'] = $infoIns['cooking_method'];
        $infoIns['cooking_method_label'] = __($infoIns['cooking_method']);
        $infoIns['diet'] = $infoIns['diet'];
        $infoIns['diet_label'] = __($infoIns['diet']);

        $ingredients = (new RecipeVersionIngredient)->readList(['where' => 'id_recipe_version=:id_recipe_version', 'order'=>'ord'], ['id_recipe_version' => $infoIns['id']]);
        $infoIns['ingredients'] = [];
        foreach ($ingredients as $ingredient) {
            $infoIngredient = (array)$ingredient->values;
            unset($infoIngredient['id']);
            unset($infoIngredient['created']);
            unset($infoIngredient['modified']);
            unset($infoIngredient['id_recipe_version']);
            unset($infoIngredient['ord']);
            unset($infoIngredient['ingredient_old']);
            $infoIns['ingredients'][] = $infoIngredient;
        }

        $preparation = (new RecipeVersionPreparation)->readList(['where' => 'id_recipe_version=:id_recipe_version', 'order'=>'ord'], ['id_recipe_version' => $infoIns['id']]);
        $infoIns['preparation'] = [];
        foreach ($preparation as $step) {
            $infoStep = (array)$step->values;
            unset($infoStep['id']);
            unset($infoStep['created']);
            unset($infoStep['modified']);
            unset($infoStep['id_recipe_version']);
            unset($infoStep['ord']);
            unset($infoStep['image']);
            unset($infoStep['description']);
            unset($infoStep['description_old']);
            unset($infoStep['image_old']);
            $infoIns['preparation'][] = $infoStep;
        }

        return $infoIns;
    }

}
