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

    public function urlRatingModal()
    {
        return url('rating/' . $this->id());
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
        $questionSteps = (ASTERION_LANGUAGE_ID == 'pt') ? 'Escreva estes passos de forma mais clara, organizada e em terceira pessoa. Não use títulos, nem a lista de ingredientes, nem enumere os passos. Não use linhas como "Bom apetite" ou "Pronto para desfrutar". A preparação é: "' . $steps . '"' : $questionSteps;
        return str_replace('. ', ".\n\n", ChatGPT::answer($questionSteps));
    }

    public function getContentChatGPT()
    {
        $response = [];
        $titleTest = $this->titleTest($this->getBasicInfo());
        $questionTitle = 'Corrige la sintaxis y ortografia de esta frase, sin usar comillas, sin poner punto final: "' . $titleTest . '"';
        $questionTitle = (ASTERION_LANGUAGE_ID == 'pt') ? 'Corrija a sintaxe e a ortografia desta frase, sem usar aspas, sem colocar ponto final: "' . $titleTest . '"' : $questionTitle;
        $response['title_page'] = str_replace('"', '', str_replace('.', '', ChatGPT::answer($questionTitle)));
        $questionDescription = 'Escribe tres lineas sobre la receta ' . $this->getBasicInfo() . ', sin decir como se prepara. No usar signos de admiracion y frases repetitivas. Dirigete a la tercera persona en plural. Puedes usar como inspiracion la preparación de la receta: " ' . $this->showUi('PreparationParagraph') . ' "';
        $questionDescription = (ASTERION_LANGUAGE_ID == 'pt') ? 'Escreva três linhas sobre a receita ' . $this->getBasicInfo() . ', sem dizer como preparar. Não use sinais de exclamação e frases repetitivas. Dirija-se à terceira pessoa no plural. Você pode se inspirar na preparação da receita: " ' . $this->showUi('PreparationParagraph') . ' "' : $questionDescription;
        $response['description'] = str_replace('¡', '', str_replace('!', '.', '<p>' . str_replace('. ', '.</p><p>', ChatGPT::answer($questionDescription)) . '</p>'));
        $questionMetaDescription = 'Resume el siguiente texto a 140 caracteres, sin usar signos de admiracion: ' . strip_tags($response['description']);
        $questionMetaDescription = (ASTERION_LANGUAGE_ID == 'pt') ? 'Resuma o seguinte texto em 140 caracteres, sem usar sinais de exclamação: ' . strip_tags($response['description']) : $questionMetaDescription;
        $response['meta_description'] = str_replace(':', ',', str_replace('"', '', str_replace('¡', '', str_replace('!', '.', ChatGPT::answer($questionMetaDescription)))));
        $questionShortDescription = 'Escribe no mas de 250 caracteres sobre la receta ' . $this->getBasicInfo() . '. Evita invitaciones a probarla o prepararla. Puedes usar como inspiracion el texto "' . $this->get('short_description') . '" o tambien la preparacion de la misma "' . $this->showUi('PreparationParagraph') . '"';
        $questionShortDescription = (ASTERION_LANGUAGE_ID == 'pt') ? 'Escreva não mais de 250 caracteres sobre a receita ' . $this->getBasicInfo() . '. Evite convites para experimentar ou preparar. Você pode se inspirar no texto "' . $this->get('short_description') . '" ou também na preparação da mesma "' . $this->showUi('PreparationParagraph') . '"' : $questionShortDescription;
        $response['short_description'] = str_replace(':', ',', str_replace('"', '', str_replace('¡', '', str_replace('!', '.', ChatGPT::answer($questionShortDescription)))));
        return $response;
    }

    public function titleTest($title) {
        $firstWord = explode(' ', $title)[0];
        $lastTwoCharacters = substr($firstWord, -2);
        $lastCharacter = substr($firstWord, -1);
        $genre = 'm';
        $genre = ($lastCharacter == 's') ? 'mp' : $genre;
        $genre = ($lastTwoCharacters == 'as') ? 'fp' : $genre;
        $genre = ($lastCharacter == 'a') ? 'f' : $genre;
        switch ($genre) {
            case 'm':
                $titleTestOptions = [
                    'Como se prepara el ' . $title,
                    'Como se prepara un sabroso ' . $title,
                    'Como se prepara un rico ' . $title,
                    'Como preparar un exquisito ' . $title,
                    'Como cocinar un rico ' . $title,
                    'Como se hace el ' . $title,
                    'Como se hace un sabroso ' . $title,
                    'Como se hace un rico ' . $title,
                    'Receta tradicional de ' . $title,
                ];
                $titleTestOptions = (ASTERION_LANGUAGE_ID == 'pt') ? [
                    'Como se prepara o ' . $title,
                    'Como se prepara um saboroso ' . $title,
                    'Como se prepara um rico ' . $title,
                    'Como preparar um requintado ' . $title,
                    'Como cozinhar um rico ' . $title,
                    'Como se faz o ' . $title,
                    'Como se faz um saboroso ' . $title,
                    'Como se faz um rico ' . $title,
                    'Receita tradicional de ' . $title,
                ] : $titleTestOptions;     
            break;
            case 'mp':
                $titleTestOptions = [
                    'Como se preparan los ' . $title,
                    'Como se preparan unos sabrosos ' . $title,
                    'Como se prepara unos ricos ' . $title,
                    'Como preparar unos exquisitos ' . $title,
                    'Como cocinar unos ricos ' . $title,
                    'Como se hacen unos ' . $title,
                    'Como se hacen unos sabrosos ' . $title,
                    'Como se hacen unos ricos ' . $title,
                    'Receta tradicional de los ' . $title,
                ];
                $titleTestOptions = (ASTERION_LANGUAGE_ID == 'pt') ? [
                    'Como se preparan os ' . $title,
                    'Como se preparan uns saborosos ' . $title,
                    'Como se prepara uns ricos ' . $title,
                    'Como preparar uns requintados ' . $title,
                    'Como cozinhar uns ricos ' . $title,
                    'Como se fazem os ' . $title,
                    'Como se fazem uns saborosos ' . $title,
                    'Como se fazem uns ricos ' . $title,
                    'Receita tradicional dos ' . $title,
                ] : $titleTestOptions;      
            break;
            case 'f':
                $titleTestOptions = [
                    'Como se prepara la ' . $title,
                    'Como se prepara una sabrosa ' . $title,
                    'Como se prepara una rica ' . $title,
                    'Como preparar una exquisita ' . $title,
                    'Como cocinar una rica ' . $title,
                    'Como se hace la ' . $title,
                    'Como se hace una sabrosa ' . $title,
                    'Como se hace una rica ' . $title,
                    'Receta tradicional de la ' . $title,
                ];
                $titleTestOptions = (ASTERION_LANGUAGE_ID == 'pt') ? [
                    'Como se prepara a ' . $title,
                    'Como se prepara uma saborosa ' . $title,
                    'Como se prepara uma rica ' . $title,
                    'Como preparar uma requintada ' . $title,
                    'Como cozinhar uma rica ' . $title,
                    'Como se faz a ' . $title,
                    'Como se faz uma saborosa ' . $title,
                    'Como se faz uma rica ' . $title,
                    'Receita tradicional da ' . $title,
                ] : $titleTestOptions;   
            break;
            case 'fp':
                $titleTestOptions = [
                    'Como se preparan las ' . $title,
                    'Como se preparan unas sabrosas ' . $title,
                    'Como se preparan unas ricas ' . $title,
                    'Como preparar unas exquisitas ' . $title,
                    'Como cocinar unas ricas ' . $title,
                    'Como se hacen las ' . $title,
                    'Como se hacen unas sabrosas ' . $title,
                    'Como se hacen unas ricas ' . $title,
                    'Receta tradicional de las ' . $title,
                ];
                $titleTestOptions = (ASTERION_LANGUAGE_ID == 'pt') ? [
                    'Como se preparan as ' . $title,
                    'Como se preparan umas saborosas ' . $title,
                    'Como se prepara umas ricas ' . $title,
                    'Como preparar umas requintadas ' . $title,
                    'Como cozinhar umas ricas ' . $title,
                    'Como se fazem as ' . $title,
                ] : $titleTestOptions;    
            break;
        }
        return $titleTestOptions[array_rand($titleTestOptions)];
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

    public function toJson()
    {
        $categories = Category::allToJson();
        $countryCode = Parameter::code('country_code');
        $infoIns = (array)$this->values;
        $infoIns['image'] = $this->getImageUrl('image', 'web');
        $infoIns['image_small'] = $this->getImageUrl('image', 'small');
        unset($infoIns['created']);
        unset($infoIns['modified']);
        unset($infoIns['title_url']);
        unset($infoIns['active']);
        unset($infoIns['ord']);
        unset($infoIns['preparation_old']);
        unset($infoIns['id_user']);
        unset($infoIns['friend_links']);
        unset($infoIns['description_bottom']);
        
        $infoIns['country'] = $countryCode;
        $infoIns['url'] = $this->url();
        $infoIns['cook_time'] = $infoIns['cook_time'];
        $infoIns['cook_time_label'] = __($infoIns['cook_time']);
        $infoIns['cooking_method'] = $infoIns['cooking_method'];
        $infoIns['cooking_method_label'] = __($infoIns['cooking_method']);
        $infoIns['diet'] = $infoIns['diet'];
        $infoIns['diet_label'] = __($infoIns['diet']);
        $infoIns['id_category_name'] = $categories[$infoIns['id_category']];

        $ingredients = (new RecipeIngredient)->readList(['where' => 'id_recipe=:id_recipe', 'order'=>'ord'], ['id_recipe' => $infoIns['id']]);
        $infoIns['ingredients'] = [];
        foreach ($ingredients as $ingredient) {
            $infoIngredient = (array)$ingredient->values;
            unset($infoIngredient['id']);
            unset($infoIngredient['created']);
            unset($infoIngredient['modified']);
            unset($infoIngredient['id_recipe']);
            unset($infoIngredient['ord']);
            unset($infoIngredient['ingredient_old']);
            $infoIngredient['label'] = $ingredient->labelSimple();
            $infoIns['ingredients'][] = $infoIngredient;
        }

        $preparation = (new RecipePreparation)->readList(['where' => 'id_recipe=:id_recipe', 'order'=>'ord'], ['id_recipe' => $infoIns['id']]);
        $infoIns['preparation'] = [];
        foreach ($preparation as $step) {
            $infoStep = (array)$step->values;
            unset($infoStep['id']);
            unset($infoStep['created']);
            unset($infoStep['modified']);
            unset($infoStep['id_recipe']);
            unset($infoStep['ord']);
            unset($infoStep['image']);
            unset($infoStep['description']);
            unset($infoStep['description_old']);
            unset($infoStep['image_old']);
            $infoIns['preparation'][] = $infoStep;
        }

        return $infoIns;
    }

    public function loadTranslation()
    {
        if (!isset($this->translation_url)) {
            $translations = Translate_Controller::loadTranslations();
            $this->translation_url = (isset($translations['recipe_' . $this->id()])) ? $translations['recipe_' . $this->id()] : '';
        }
        return $this->translation_url;
    }

}
