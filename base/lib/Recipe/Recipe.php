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

    public function url()
    {
        $this->loadCategory();
        return url('recetas/' . $this->category->get('name_url') . '/' . $this->get('title_url'));
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

    public function loadCategory()
    {
        if (!isset($this->category)) {
            $this->category = (new Category)->read($this->get('id_category'));
        }
        return $this->category;
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
        $question = 'Crea un archivo JSON con los campos "title_page" (que debe ser una correccion de la frase: ' . $titleTest . ' , "description" (que son tres lineas sobre la receta, puede ser historia o anectodas, debe ser diferente de la short_description y de la meta_description), "meta_description" (que son 140 caracteres para la pagina de la receta) y "short_description" (que deben ser 250 caracteres con una descripcion corta) para una receta de cocina llamada "' . $this->getBasicInfo() . '". Usa un lenguaje atractivo y persuasivo para invitar a los lectores a probar la receta. No uses signos de admiracion ni emoticons. El contenido debe estar en español. La receta es: "' . $this->showUi('PreparationParagraph') . '"';
        $question = (ASTERION_LANGUAGE_ID == 'pt') ? 'Crie um arquivo JSON com os campos "title_page" (que deve ser uma correção da frase: ' . $titleTest . ' , "description" (que são cinco linhas sobre a receita, pode ser história ou anedotas), "meta_description" (que são 140 caracteres para a página da receita) e "short_description" (que devem ser 250 caracteres com uma descrição curta) para uma receita de cozinha chamada "' . $this->getBasicInfo() . '". Use uma linguagem atraente e persuasiva para convidar os leitores a experimentar a receita. Não use sinais de exclamação ou emoticons. O conteúdo deve estar em português. A receita é: "' . $this->showUi('PreparationParagraph') . '"' : $question;
        return ChatGPT::answerJson($question);
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
        if (isset($content['title_page']) && $content['title_page'] != '') {
            $this->persistSimple('title_page', $content['title_page']);
            $this->persistSimple('description', $content['description']);
            $this->persistSimple('meta_description', $content['meta_description']);
            $this->persistSimple('short_description', $content['short_description']);
        }
    }

    public function toJson()
    {
        $categories = Category::allToJson();
        $countryCode = Parameter::code('country_code');
        $infoIns = (array)$this->values;
        $infoIns['image'] = $this->getImageUrl('image', 'web');
        $infoIns['image_small'] = $this->getImageUrl('image', 'small');
        $infoIns['image_ingredients'] = $this->getImageUrl('image_ingredients', 'web');
        unset($infoIns['created']);
        unset($infoIns['modified']);
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

        $images = (new RecipeImage)->readList(['where' => 'id_recipe=:id_recipe', 'order'=>'ord'], ['id_recipe' => $infoIns['id']]);
        $infoIns['images'] = [];
        foreach ($images as $image) {
            $infoImage = (array)$image->values;
            unset($infoImage['id']);
            unset($infoImage['created']);
            unset($infoImage['modified']);
            unset($infoImage['id_recipe']);
            unset($infoImage['ord']);
            $infoImage['image'] = $image->getImageUrl('image', 'web');
            $infoImage['image_small'] = $image->getImageUrl('image', 'small');
            $infoIns['images'][] = $infoImage;
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

    public function promptImages()
    {
        $this->loadMultipleValues();
        $newSteps = (count($this->get('preparation')) > 4) ? 4 : 3;
        $question = 'Haz un archivo JSON que tenga el "titulo", "descripcion", "ingredientes" y "pasos" que resume los pasos a tan solo ' . $newSteps . ' de la receta: ' . $this->showUi('Text');
        $answer = ChatGPT::answerJson($question);
        $response = [];
        if (isset($answer['pasos'])) {
            $response['prompt_ingredientes'] = "Haz una imagen solamente con los ingredientes de esta receta, no uses textos ni leyendas, la imagen debe contener solamente los ingredientes. La receta es: " . $this->showUi('Text');
            $ingredients = [];
            foreach ($this->get('ingredients') as $ingredient) {
                if ($ingredient->get('amount') != '') {
                    $ingredientType = (($ingredient->get('type') != 'unit' && $ingredient->get('type') != '') ? strtolower((intval($ingredient->get('amount')) > 1) ? __($ingredient->get('type') . '_plural') : __($ingredient->get('type'))) . ' ' . __('of') : '');
                    $ingredients[] = $ingredient->get('amount') . ' ' . $ingredientType . ' ' . $ingredient->get('ingredient');
                } else {
                    $ingredients[] = $ingredient->get('ingredient');
                }
            }
            $pasosSimple = '';
            for ($i = 0; $i < count($answer['pasos']); $i++) {
                $pasosSimple .= 'Paso ' . ($i + 1) . ': ' . $answer['pasos'][$i] . '<br/>';
            }
            $recipeSimple = $this->getBasicInfo('') . '<br/>Ingredientes:<br/>' . implode('<br/>', $ingredients) . '<br/>Preparacion:<br/>' . $pasosSimple;
            for ($i = 0; $i < count($answer['pasos']); $i++) {
                $response['prompt_paso_' . ($i + 1)] = "Haz una imagen diferente y separada, para el paso " . ($i + 1) . " de esta receta, no uses textos ni leyendas. La receta es: " . $recipeSimple;
            }
        }
        return $response;
    }

}
