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

    public $category;
    public $translation_url;

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

    public function urlEs()
    {
        $this->loadCategory();
        return url('recetas/' . $this->category->values['name_url'] . '/' . $this->values['title_url']);
    }
    
    public function urlEn()
    {
        $this->loadCategory();
        return url('recetas/' . $this->category->values['name_url_en'] . '/' . $this->values['title_url_en']);
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

    public function loadTranslation()
    {
        if (!isset($this->translation_url)) {
            $this->translation_url = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('//en.', '//www.', $this->urlEs()) : str_replace('//www.', '//en.', $this->urlEn());
        }
        return $this->translation_url;
    }

    public function loadTranslated($simple = false)
    {
        if (ASTERION_LANGUAGE_ID == 'en') {
            $translation = @json_decode($this->get('translation'), true);
            if (isset($translation['title'])) {
                $keys = ['title', 'title_page', 'short_description', 'description', 'meta_description'];
                foreach ($keys as $key) {
                    $this->values[$key] = $translation[$key];
                }
                if (!$simple) {
                    if (isset($translation['ingredients'])) {
                        foreach ($this->values['ingredients'] as $valueIngredient) {
                            $valueIngredient->values['ingredient'] = (isset($translation['ingredients'][$valueIngredient->id()])) ? $translation['ingredients'][$valueIngredient->id()] : '';
                        }
                    }
                    if (isset($translation['preparation'])) {
                        foreach ($this->values['preparation'] as $valuePreparation) {
                            $valuePreparation->values['step'] = (isset($translation['preparation'][$valuePreparation->id()])) ? $translation['preparation'][$valuePreparation->id()] : '';
                        }
                    }
                    if (isset($translation['images'])) {
                        foreach ($this->values['images'] as $valueImage) {
                            $valueImage->values['label'] = (isset($translation['images'][$valueImage->id()])) ? $translation['images'][$valueImage->id()] : '';
                        }
                    }
                }
            }
        }
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

    public function toJson($simple = false)
    {
        $categories = Category::allToJson();
        $countryCode = Parameter::code('country_code');
        $infoIns = (array)$this->values;
        $infoIns['image'] = $this->getImageUrl('image', 'web');
        $infoIns['image_small'] = $this->getImageUrl('image', 'small');
        $infoIns['image_ingredients'] = $this->getImageUrl('image_ingredients', 'web');
        $unsetItems = ['created', 'modified', 'id_user', 'ord', 'preparation_old', 'friend_links', 'check_versions', 'ingredients_raw', 'preparation_raw', 'link_es', 'link_en', 'preparation', 'images', 'questions', 'reviews', 'adsense_dates', 'adsense_earnings', 'adsense_visits', 'adsense_info'];
        foreach ($unsetItems as $item) {
            unset($infoIns[$item]);
        }

        $infoIns['country'] = $countryCode;
        $infoIns['url'] = $this->url();
        $infoIns['cook_time'] = $infoIns['cook_time'];
        $infoIns['cook_time_label'] = __($infoIns['cook_time']);
        $infoIns['cooking_method'] = $infoIns['cooking_method'];
        $infoIns['cooking_method_label'] = __($infoIns['cooking_method']);
        $infoIns['diet'] = $infoIns['diet'];
        $infoIns['diet_label'] = __($infoIns['diet']);
        $infoIns['id_category_name'] = $categories[$infoIns['id_category']];

        if ($simple) {
            $ingredients = (new RecipeIngredient)->readList(['where' => 'id_recipe=:id_recipe', 'order'=>'ord'], ['id_recipe' => $infoIns['id']]);
            $ingredientsLabels = [];
            foreach ($ingredients as $ingredient) {
                $ingredientsLabels[] = $ingredient->labelSimple();
            }
            $infoIns['ingredients'] = $ingredientsLabels;
            return $infoIns;
        }

        $ingredients = (new RecipeIngredient)->readList(['where' => 'id_recipe=:id_recipe', 'order'=>'ord'], ['id_recipe' => $infoIns['id']]);
        $infoIns['ingredients'] = [];
        foreach ($ingredients as $ingredient) {
            $infoIngredient = (array)$ingredient->values;
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

    public function promptImages()
    {
        $this->loadMultipleValues();
        $newSteps = (isset($_GET['steps'])) ? intval($_GET['steps']) : 4;
        $question = 'Haz un archivo JSON que tenga el "titulo", "descripcion", "ingredientes" y "pasos" que resume los pasos a tan solo ' . $newSteps . ' de la receta: ' . $this->showUi('Text');
        $answer = ChatGPT::answerJson($question);
        $response = [];
        if (isset($answer['pasos'])) {
            $response['prompt_receta'] = "Haz una imagen realista de esta receta, debe ser un plato central, de preferencia circular y cenital, con mucho aire al borde y un fondo tradicional, no uses textos ni leyendas. La receta es: " . $this->showUi('Text');
            $response['prompt_final'] = "Haz una imagen realista de una persona que esta comiendo o bebiendo el plato de esta receta, debe ser un plato central. De preferencia usa elementos de " . Parameter::code('country_code') . " y dale mucho aire a los bordes de la imagen. La receta es: " . $this->showUi('Text');
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

    public function translate()
    {
        $recipeInfo = $this->toJson();
        $itemsToUnset = ['ingredients_raw', 'preparation_raw', 'id', 'id_category', 'image', 'title_url', 'rating', 'rating_count', 'cook_time', 'cooking_method', 'servings', 'diet', 'image_ingredients', 'views', 'youtube_url', 'redirect_force_url', 'title_url_en', 'translation', 'translated', 'most_searched', 'adsense_dates', 'adsense_earnings', 'adsense_visits', 'adsense_info', 'image_small', 'country', 'url', 'cook_time_label', 'cooking_method_label', 'diet_label', 'id_category_name', 'title_url_en', 'translation', 'active'];
        foreach ($itemsToUnset as $item) {
            unset($recipeInfo[$item]);
        }

        $cleanIngredients = [];
        foreach ($recipeInfo['ingredients'] as $key => $ingredient) {
            $cleanIngredients[$ingredient['id']] = $ingredient['ingredient'];
        }
        $recipeInfo['ingredients'] = $cleanIngredients;

        $cleanPreparation = [];
        foreach ($recipeInfo['preparation'] as $key => $step) {
            $cleanPreparation[$step['id']] = $step['step'];
        }
        $recipeInfo['preparation'] = $cleanPreparation;

        $cleanImages = [];
        foreach ($recipeInfo['images'] as $key => $image) {
            $cleanImages[$image['id']] = $image['label'];
        }
        $recipeInfo['images'] = $cleanImages;

        $recipeJson = json_encode($recipeInfo);
        $questionVersion = 'Translate this JSON file to english respecting all the key names and the same order, just translate the values: "' . $recipeJson . '"';
        $response = [];
        $maxAttempts = 3;
        $attempts = 0;
        while (empty($response['title']) && $attempts < $maxAttempts) {
            $response = ChatGPT::answerJson($questionVersion);
            $attempts++;
        }
        if (isset($response['title'])) {
            $titleUrl = Text::simpleUrl($response['title']);
            $oldRecipe = (new Recipe)->readFirst(['where' => 'title_url_en=:title_url_en AND id != :id'], ['title_url_en' => $titleUrl, 'id' => $this->id()]);
            $titleUrl .= ($oldRecipe->id() != '') ? '-' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 6)  : '';
            $this->persistSimple('title_url_en', $titleUrl);
            $this->persistSimple('translation', json_encode($response));
        }
    }

    public function getImageUrl($attributeName, $version = '', $modified = false)
    {
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/enrechaiti', '', ASTERION_STOCK_FILE) : ASTERION_STOCK_FILE;
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/enrecvegetariana', '', ASTERION_STOCK_FILE) : $stockFile;
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/enrec', '/rec', $stockFile) : $stockFile;
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/rechaiti', '', $stockFile) : $stockFile;
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/recvegetariana', '', $stockFile) : $stockFile;
        $stockUrl = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('//en.', '//www.', ASTERION_STOCK_URL) : ASTERION_STOCK_URL;
        $version = ($version != '') ? '_' . strtolower($version) : '';
        $file = $stockFile . $this->className . '/' . $this->get($attributeName) . '/' . $this->get($attributeName) . $version . '.jpg';
        if (is_file($file)) {
            return str_replace($stockFile, $stockUrl, $file) . ($modified && ($this->get('modified') != '') ? '?v=' . Date::sqlInt($this->get('modified')) : '');
        }
    }

}
