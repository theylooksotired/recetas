<?php
/**
 * @class Recipe
 *
 * This class defines the users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Recipe extends Db_Object
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

    public function getServings()
    {
        return ($this->get('servings') > 0) ? $this->get('servings') . ' ' . __('servings') : '';
    }

    public function url()
    {
        $this->loadMultipleValuesSingleAttribute('id_category');
        return $this->get('id_category_object')->url() . '/' . $this->get('title_url');
    }

    public function urlUploadTempImagePublic()
    {
        return url('cuenta/subir-imagen-temporal');
    }

    public function urlDeleteImagePublic($valueFile = '')
    {
        return url('cuenta/borrar-imagen-receta/' . $this->id());
    }

    public function deleteImagePublic()
    {
        $status = StatusCode::NOK;
        try {
            Image_File::deleteImage($this->className, $this->get('image'));
            $this->persistSimple('image', '');
            $status = StatusCode::OK;
        } catch (Exception $e) {};
        return ['status' => $status];
    }

    public function createPublic($values)
    {
        $status = StatusCode::NOK;
        $content = (new Recipe_Form())->create();
        if (count($values) > 0) {
            $login = User_Login::getInstance();
            $values['active'] = 0;
            $values['id_user'] = $login->id();
            $recipe = new Recipe($values);
            $persist = $recipe->persist();
            if ($persist['status'] == StatusCode::OK) {
                Session::flashInfo(__('saved_recipe_message'));
                $status = StatusCode::OK;
                $form = Recipe_Form::fromObject($recipe);
                $content = $form->edit();
                return ['status' => $status, 'content' => $content, 'recipe' => $recipe];
            } else {
                Session::flashError(__('errors_form'));
                $content = (new Recipe_Form($persist['values'], $persist['errors']))->create();
            }
        }
        return ['status' => $status, 'content' => $content];
    }

    public function editPublic($values)
    {
        $status = StatusCode::NOK;
        $form = Recipe_Form::fromObject($this);
        $content = $form->edit();
        if (count($values) > 0) {
            $login = User_Login::getInstance();
            $values['active'] = 0;
            $values['id'] = $this->id();
            $values['id_user'] = $login->id();
            $comic = new Recipe($values);
            $persist = $comic->persist();
            if ($persist['status'] == StatusCode::OK) {
                Session::flashInfo(__('saved_recipe_message'));
                $form = Recipe_Form::fromObject($comic);
                $content = $form->edit();
            } else {
                Session::flashError(__('errors_form'));
                $content = (new Recipe_Form($persist['values'], $persist['errors']))->edit();
            }
        }
        return ['status' => $status, 'content' => $content];
    }

}
