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

    public function getTitlePage()
    {
        return $this->getBasicInfo();
    }

    public function getBasicInfoTitle()
    {
        return str_replace('"', '', $this->getBasicInfo());
    }

    public function getBasicInfoTitlePage()
    {
        return ($this->get('title_page') != '') ? $this->get('title_page') : $this->getBasicInfoTitle();
    }

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
        if (!is_object($this->get('id_category_object'))) {
            $this->loadMultipleValuesSingleAttribute('id_category');
        }
        return url('recetas/' . $this->get('id_category_object')->get('name_url') . '/' . $this->get('title_url'));
    }

    public function urlFixSteps()
    {
        return url('recipes-fix-steps/' . $this->id());
    }

    public function urlFixContent()
    {
        return url('recipes-fix-content/' . $this->id());
    }

    public function loadCategoryManually($category)
    {
        $this->set('id_category_object', $category);
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
                        $preparation = new RecipePreparation([
                            'id_recipe' => $this->id(),
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
                        $ingredients = new RecipeIngredient([
                            'id_recipe' => $this->id(),
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

    public function getPreparationChatGPT()
    {
        $steps = $this->showUi('PreparationParagraph');
        $questionSteps = 'Escribe estos pasos de forma mas clara, ordenada y en tercera persona. No uses titulos, ni la lista de ingredientes, ni ennumeres los pasos. No uses lineas como "Buen provecho" o "Listo para disfrutar". La preparacion es: "' . $steps . '"';
        return str_replace('. ', ".\n\n", ChatGPT::answer($questionSteps));
    }

    public function getContentChatGPT()
    {
        $response = [];
        $firstWord = explode(' ', $this->getBasicInfo())[0];
        $lastTwoCharacters = substr($firstWord, -2);
        $lastCharacter = substr($firstWord, -1);
        $genre = 'm';
        $genre = ($lastCharacter == 's') ? 'mp' : $genre;
        $genre = ($lastTwoCharacters == 'as') ? 'fp' : $genre;
        $genre = ($lastCharacter == 'a') ? 'f' : $genre;
        switch ($genre) {
            case 'm':
                $titleTestOptions = [
                    'Como se prepara el ' . $this->getBasicInfo(),
                    'Como se prepara un sabroso ' . $this->getBasicInfo(),
                    'Como se prepara un rico ' . $this->getBasicInfo(),
                    'Como preparar un exquisito ' . $this->getBasicInfo(),
                    'Como cocinar un rico ' . $this->getBasicInfo(),
                    'Como se hace el ' . $this->getBasicInfo(),
                    'Como se hace un sabroso ' . $this->getBasicInfo(),
                    'Como se hace un rico ' . $this->getBasicInfo(),
                    'Receta tradicional de ' . $this->getBasicInfo(),
                ];        
            break;
            case 'mp':
                $titleTestOptions = [
                    'Como se preparan los ' . $this->getBasicInfo(),
                    'Como se preparan unos sabrosos ' . $this->getBasicInfo(),
                    'Como se prepara unos ricos ' . $this->getBasicInfo(),
                    'Como preparar unos exquisitos ' . $this->getBasicInfo(),
                    'Como cocinar unos ricos ' . $this->getBasicInfo(),
                    'Como se hacen unos ' . $this->getBasicInfo(),
                    'Como se hacen unos sabrosos ' . $this->getBasicInfo(),
                    'Como se hacen unos ricos ' . $this->getBasicInfo(),
                    'Receta tradicional de los ' . $this->getBasicInfo(),
                ];        
            break;
            case 'f':
                $titleTestOptions = [
                    'Como se prepara la ' . $this->getBasicInfo(),
                    'Como se prepara una sabrosa ' . $this->getBasicInfo(),
                    'Como se prepara una rica ' . $this->getBasicInfo(),
                    'Como preparar una exquisita ' . $this->getBasicInfo(),
                    'Como cocinar una rica ' . $this->getBasicInfo(),
                    'Como se hace la ' . $this->getBasicInfo(),
                    'Como se hace una sabrosa ' . $this->getBasicInfo(),
                    'Como se hace una rica ' . $this->getBasicInfo(),
                    'Receta tradicional de la ' . $this->getBasicInfo(),
                ];        
            break;
            case 'fp':
                $titleTestOptions = [
                    'Como se preparan las ' . $this->getBasicInfo(),
                    'Como se preparan unas sabrosas ' . $this->getBasicInfo(),
                    'Como se preparan unas ricas ' . $this->getBasicInfo(),
                    'Como preparar unas exquisitas ' . $this->getBasicInfo(),
                    'Como cocinar unas ricas ' . $this->getBasicInfo(),
                    'Como se hacen las ' . $this->getBasicInfo(),
                    'Como se hacen unas sabrosas ' . $this->getBasicInfo(),
                    'Como se hacen unas ricas ' . $this->getBasicInfo(),
                    'Receta tradicional de las ' . $this->getBasicInfo(),
                ];        
            break;
        }
        $questionTitle = 'Corrige la sintaxis y ortografia de esta frase, sin usar comillas, sin poner punto final: "' . $titleTestOptions[array_rand($titleTestOptions)] . '"';
        $response['title_page'] = str_replace('"', '', str_replace('.', '', ChatGPT::answer($questionTitle)));
        $questionDescription = 'Escribe tres lineas sobre la receta ' . $this->getBasicInfo() . ', sin decir como se prepara. No usar signos de admiracion y frases repetitivas. Dirigete a la tercera persona en plural. Puedes usar como inspiracion la preparación de la receta: " ' . $this->showUi('PreparationParagraph') . ' "';
        $response['description'] = str_replace('¡', '', str_replace('!', '.', '<p>' . str_replace('. ', '.</p><p>', ChatGPT::answer($questionDescription)) . '</p>'));
        $questionMetaDescription = 'Resume el siguiente texto a 140 caracteres, sin usar signos de admiracion: ' . strip_tags($response['description']);
        $response['meta_description'] = str_replace(':', ',', str_replace('"', '', str_replace('¡', '', str_replace('!', '.', ChatGPT::answer($questionMetaDescription)))));
        $questionShortDescription = 'Escribe no mas de 250 caracteres sobre la receta ' . $this->getBasicInfo() . '. Evita invitaciones a probarla o prepararla. Puedes usar como inspiracion el texto "' . $this->get('short_description') . '" o tambien la preparacion de la misma "' . $this->showUi('PreparationParagraph') . '"';
        $response['short_description'] = str_replace(':', ',', str_replace('"', '', str_replace('¡', '', str_replace('!', '.', ChatGPT::answer($questionShortDescription)))));
        return $response;
    }

    public function fixSteps()
    {
        $preparationLines = explode("\n", $this->getPreparationChatGPT());
        $query = 'DELETE FROM ' . (new RecipePreparation)->tableName . ' WHERE id_recipe = ' . $this->id();
        Db::execute($query);
        foreach ($preparationLines as $line) {
            $line = trim($line);
            if ($line != '') {
                $preparation = new RecipePreparation([
                    'id_recipe' => $this->id(),
                    'step' => $line
                ]);
                $preparation->persist();
            }
        }
    }

    public function fixContent()
    {
        $content = $this->getContentChatGPT();
        $this->persistSimple('title_page', $content['title_page']);
        $this->persistSimple('description', $content['description']);
        $this->persistSimple('meta_description', $content['meta_description']);
        $this->persistSimple('short_description', $content['short_description']);
    }

}
