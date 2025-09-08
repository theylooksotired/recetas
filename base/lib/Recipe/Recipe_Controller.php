<?php
/**
 * @class RecipeController
 *
 * This class is the controller for the Recipe objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Recipe_Controller extends Controller
{

    public function getContent()
    {
        $this->mode = 'admin';
        $this->object = new $this->objectType;
        $this->title_page = __((string) $this->object->info->info->form->title);
        $this->layout = (string) $this->object->info->info->form->layout;
        $this->layout_page = '';
        $this->menu_inside = $this->menuInside();
        $this->login = UserAdmin_Login::getInstance();
        $this->ui = new NavigationAdmin_Ui($this);
        LogAdmin::log($this->action . '_' . $this->objectType, $this->values);
        switch ($this->action) {
            default:
                return parent::getContent();
                break;
            case 'load-ai-data':
                $this->mode = 'json';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '' && $recipe->get('description') == '') {
                    $response = $recipe->getContentChatGPT();
                }
                return json_encode($response);
                break;
            case 'prompts-images':
                $this->mode = 'json';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $recipe->loadMultipleValues();
                    $newSteps = (count($recipe->get('preparation')) > 4) ? 4 : 3;
                    $question = 'Haz un archivo JSON que tenga el "titulo", "descripcion", "ingredientes" y "pasos" que resume los pasos a tan solo ' . $newSteps . ' de la receta: ' . $recipe->showUi('Text');
                    $answer = ChatGPT::answerJson($question);
                    if (isset($answer['pasos'])) {
                        echo '<html><body>';
                        echo "PROMPT 0<br/><br/>Haz una imagen solamente con los ingredientes de esta receta, no uses textos ni leyendas, la imagen debe contener solamente los ingredientes. La receta es: " . $recipe->showUi('Text');
                        echo "<br/><br/><br/><br/>===========<br/><br/><br/><br/>";
                        $ingredients = [];
                        foreach ($recipe->get('ingredients') as $ingredient) {
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
                        $recipeSimple = $recipe->getBasicInfo('') . '<br/>Ingredientes:<br/>' . implode('<br/>', $ingredients) . '<br/>Preparacion:<br/>' . $pasosSimple;
                        for ($i = 0; $i < count($answer['pasos']); $i++) {
                            echo "PROMPT " . ($i + 1) . "<br/><br/>Haz una imagen solamente con el paso " . ($i + 1) . " de esta receta, no uses textos ni leyendas. La receta es: " . $recipeSimple;
                            echo "<br/><br/><br/><br/>===========<br/><br/><br/><br/>";
                        }
                        echo '</body></html>';
                    }
                }
                return '';
                break;
            case 'steps-ai-data':
                $this->mode = 'json';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $steps = $recipe->showUi('PreparationParagraph');
                    $questionSteps = 'Escribe estos pasos de forma mas clara, ordenada y en tercera persona. No uses titulos, ni la lista de ingredientes, ni ennumeres los pasos. La preparacion es: "' . $steps . '"';
                    $questionSteps = (ASTERION_LANGUAGE_ID == 'pt') ? 'Escreva esses passos de forma mais clara, organizada e em terceira pessoa. Não use títulos, nem a lista de ingredientes, nem enumere os passos. A preparação é: "' . $steps . '"' : $questionSteps;
                    $response['steps'] = str_replace('. ', ".\n\n", ChatGPT::answer($questionSteps));
                }
                return json_encode($response);
                break;
            case 'full-ai-recipe':
                $this->mode = 'json';
                $response = [];
                $content = (isset($this->values['content'])) ? $this->values['content'] : '';
                if ($content != '') {
                    $categories = (new Category)->readList();
                    $categoriesInfo = [];
                    foreach ($categories as $category) {
                        $categoriesInfo[] = $category->id() . ':' . $category->get('name');
                    }
                    $questionRecipe = 'Escribe un archivo JSON, sin ningun texto adicional, con los campos titulo, metaDescripcion (140 caracteres), descripcion (350 caracteres), descripcionHtml (descripcion sencilla del plato, debe ser distinta a la descripcion y debe contener mejor informacion sobre la receta, de maximo 1000 caracteres en codigo HTML), idCategory (' . implode($categoriesInfo, ',') . '), tiempoPreparacion (5_minutes,15_minutes,30_minutes,45_minutes,1_hour,2_hours,3_hours,4_hours,5_hours,1_day,2_days), numeroPorciones, ingredientes (lista, separar cada ingrediente si una linea tiene varios), pasos (lista, que incluya a todos los ingredientes, siempre en tercera persona y con un lenguaje muy amigable, se debe tener una sola linea por paso). La receta es: "' . $content . '"';
                    $questionRecipes = (ASTERION_LANGUAGE_ID == 'pt') ? 'Escreva um arquivo JSON, sem texto adicional, com os campos titulo, metaDescripcion (140 caracteres), descripcion (350 caracteres), descripcionHtml (descrição simples do prato, deve ser diferente da descrição e deve conter informações melhores sobre a receita, com no máximo 1000 caracteres em HTML), ingredientes (lista, separar cada ingrediente se uma linha tiver vários), pasos (lista, que inclua todos os ingredientes, sempre em terceira pessoa e com uma linguagem muito amigável, deve haver apenas uma linha por passo). A receita é: "' . $content . '"' : $questionRecipe;
                    $answerChatGPT = ChatGPT::answer($questionRecipe);
                    preg_match('/\{(?:[^{}]|(?R))*\}/', $answerChatGPT, $matches);
                    $jsonString = (isset($matches[0])) ? $matches[0] : '';
                    if ($jsonString != '') {
                        $response = json_decode($jsonString, true);
                        if (isset($response['titulo'])) {
                            $questionTitle = 'Corrige la sintaxis y ortografia de esta frase, sin usar comillas, sin poner punto final: "' . (new Recipe)->titleTest($response['titulo']) . '"';
                            $questionTitle = (ASTERION_LANGUAGE_ID == 'pt') ? 'Corrija a sintaxe e a ortografia desta frase, sem usar aspas, sem colocar ponto final: "' . (new Recipe)->titleTest($response['titulo']) . '"' : $questionTitle;
                            $response['tituloPagina'] = str_replace('"', '', str_replace('.', '', ChatGPT::answer($questionTitle)));
                        }
                        if (isset($response['descripcionHtml'])) {
                            $response['descripcionHtml'] = str_replace('. ', '.</p><p>', $response['descripcionHtml']);
                        }
                        return json_encode($response);
                    }
                }
                return '';
                break;
            case 'preparation':
                $table = '';
                foreach ((new Recipe)->readList(['order' => 'title']) as $item) {
                    Db::execute('DELETE FROM ' . (new RecipePreparation)->tableName . ' WHERE id_recipe="' . $item->id() . '"');
                    $dom = new DOMDocument();
                    $dom->loadHTML(html_entity_decode($item->get('preparation_old')));
                    $steps = $dom->getElementsByTagName('li');
                    if ($steps->length > 1) {
                        foreach ($steps as $key => $step) {
                            $nodeValue = (string) $step->nodeValue;
                            if ($nodeValue != '') {
                                $preparation = new RecipePreparation([
                                    'id_recipe' => $item->id(),
                                    'step' => utf8_decode($nodeValue),
                                ]);
                                $preparation->persist();
                            }
                        }
                    } else {
                        dump('PROBLEM : ' . $item->getBasicInfo());
                    }
                }
                dd('DONE');
                break;
            case 'ingredients-clean-spaces':
                $table = '';
                foreach ((new RecipeIngredient)->readList(['order' => 'ingredient']) as $item) {
                    $item->persistSimple('ingredient', trim(str_replace('  ', ' ', $item->get('ingredient'))));
                }
                echo 'DONE';
                break;
            case 'ingredients-clean-salt':
                $table = '';
                foreach ((new RecipeIngredient)->readList(['order' => 'ingredient']) as $item) {
                    $ingredient = trim(str_replace('  ', ' ', $item->get('ingredient_old')));
                    if ($ingredient == 'Sal e pimenta a gosto' || $ingredient == 'Sal e pimenta-do-reino a gosto' || $ingredient == 'Sal e pimenta do reino a gosto') {
                        $item->persistSimple('ingredient_old', 'Sal');
                        $newIngredient = new RecipeIngredient([
                            'id_recipe' => $item->get('id_recipe'),
                            'ingredient_old' => 'Pimienta',
                            'ord' => 99,
                        ]);
                        $newIngredient->persist();
                    }
                }
                echo 'DONE';
                break;
            case 'ingredients-clean':
                $table = '';
                foreach ((new RecipeIngredient)->readList(['order' => 'ingredient']) as $item) {
                    $ingredient_new = $item->get('ingredient');
                    $recipe = (new Recipe)->read($item->get('id_recipe'));
                    $table .= '
                            <tr class="form" data-id="' . $item->id() . '">
                                <td><a href="' . $recipe->urlAdmin() . '" target="_blank">Receta</a></td>
                                <td><input name="ingredient_new" value="' . $ingredient_new . '"  style="width:100%"/></td>
                                <td><button>Guardar</button></td>
                            </tr>';
                }
                $this->content = '<table class="table_admin table_categories small" style="width:100%" data-action="' . url('recipe/ingredient-clean-ajax', true) . '">' . $table . '</table>';
                $this->head = $this->loadFormAjax();
                return $this->ui->render();
                break;
            case 'ingredient-clean-ajax':
                $item = (new RecipeIngredient)->read($this->values['id']);
                if ($item->id() != '') {
                    $item->persistSimple('ingredient', $this->values['ingredient_new']);
                    $item->persistSimple('ingredient_old', $this->values['ingredient_new']);
                    echo 1;
                }
                echo 0;
                exit();
                break;
            case 'ingredients':
                $table = '';
                // foreach ((new RecipeIngredient)->readList(['where'=>'type IS NULL OR type=""', 'order' => 'ingredient']) as $item) {
                // $items = (new RecipeIngredient)->readList(['order' => 'ingredient']) ;
                $items = (new RecipeIngredient)->readList(['where'=>'type IS NULL OR type=""', 'order' => 'ingredient']) ;
                $query = 'SELECT DISTINCT i.* FROM ' . (new RecipeIngredient)->tableName . ' i
                    LEFT JOIN ' . (new Recipe)->tableName . ' r ON i.id_recipe=r.id WHERE r.active != 1 OR r.active IS NULL
                    ORDER BY i.ingredient';
                $items = (new RecipeIngredient)->readListQuery($query) ;
                foreach ($items as $item) {
                    $ingredient = ($item->get('ingredient_old') == 'Sal y pimienta' || $item->get('ingredient_old') == '') ? $item->get('ingredient') : $item->get('ingredient_old');
                    $ingredient = str_replace('0g','0 g', $ingredient);
                    $ingredient = str_replace('5g','5 g', $ingredient);
                    $ingredient = str_replace('0m','0 m', $ingredient);
                    $ingredient = str_replace('5m','5 m', $ingredient);
                    $ingredient = str_replace('1k','1 k', $ingredient);
                    $ingredient = str_replace('5k','5 k', $ingredient);
                    $ingredient = str_replace('2k','2 k', $ingredient);
                    $ingredient = str_replace('½k','½ k', $ingredient);
                    $ingredient = str_replace('1l','1 l', $ingredient);
                    $ingredient = str_replace('2l','2 l', $ingredient);
                    $ingredient = str_replace('3l','3 l', $ingredient);
                    $ingredient = str_replace('4l','4 l', $ingredient);
                    $ingredient = str_replace('5l','5 l', $ingredient);
                    $ingredient = str_replace('5d','5 d', $ingredient);
                    $amount = $item->get('amount');
                    if ($amount == '') {
                        $amount = ($amount == '' && strpos($ingredient, '1.5') !== false) ? '1.5' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1,5') !== false) ? '1,5' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1 1/2') !== false) ? '1 1/2' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '2 1/2') !== false) ? '2 1/2' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1/2') !== false) ? '1/2' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1/3') !== false) ? '1/3' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1/4') !== false) ? '1/4' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1/8') !== false) ? '1/8' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '3/4') !== false) ? '3/4' : $amount; 
                        $amount = ($amount == '' && strpos($ingredient, '1 1/2') !== false) ? '1 1/2' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1 1/3') !== false) ? '1 1/3' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1 3/4') !== false) ? '1 3/4' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1 ½') !== false) ? '1 ½' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1½') !== false) ? '1 ½' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '2 ½') !== false) ? '2 ½' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1 ¼') !== false) ? '1 ¼' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1¼') !== false) ? '1 ¼' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '2 ¼') !== false) ? '2 ¼' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1,5') !== false) ? '1,5' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '2,5') !== false) ? '2,5' : $amount;
                        $amount = ($amount == '' && floatval($ingredient) > 0) ? intval($ingredient) : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '½') !== false) ? '½' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '⅓') !== false) ? '⅓' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '¼') !== false) ? '¼' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '⅛') !== false) ? '⅛' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '¾') !== false) ? '¾' : $amount;
                    }
                    $type = $item->get('type');
                    if ($type == '') {
                        $type = (strpos($ingredient, ' cda ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cda. ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cdta ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cdta. ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cdas ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cuchara ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cucharas ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cucharada ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cucharadas ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' colher (chá) ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' colheres (sopa) ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' colher (sopa) ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' xícaras ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cdta ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cdtas ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cdita ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cdita. ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cditas ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharadita ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharaditas ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharilla ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharillas ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' xícara (chá) ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' xícaras (chá) ') !== false) ? 'teaspoon' : $type; 
                        $type = (strpos($ingredient, ' colher (café) ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' colheres (chá) ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' vaso ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' vasos ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' vasitos ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' copa ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' copas ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' unidad ') !== false) ? 'unit' : $type;
                        $type = (strpos($ingredient, ' unidades ') !== false) ? 'unit' : $type;
                        $type = (strpos($ingredient, ' l ') !== false) ? 'liter' : $type;
                        $type = (strpos($ingredient, ' lt ') !== false) ? 'liter' : $type;
                        $type = (strpos($ingredient, ' lts ') !== false) ? 'liter' : $type;
                        $type = (strpos($ingredient, ' litro ') !== false) ? 'liter' : $type;
                        $type = (strpos($ingredient, ' litros ') !== false) ? 'liter' : $type;
                        $type = (strpos($ingredient, ' tza ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' taza ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' tazas ') !== false) ? 'cup' : $type;
                        $type = (strpos($ingredient, ' kg ') !== false) ? 'kilogram' : $type;
                        $type = (strpos($ingredient, ' kilo ') !== false) ? 'kilogram' : $type;
                        $type = (strpos($ingredient, ' kilos ') !== false) ? 'kilogram' : $type;
                        $type = (strpos($ingredient, ' lb ') !== false) ? 'pound' : $type;
                        $type = (strpos($ingredient, ' lb. ') !== false) ? 'pound' : $type;
                        $type = (strpos($ingredient, ' libra ') !== false) ? 'pound' : $type;
                        $type = (strpos($ingredient, ' libras ') !== false) ? 'pound' : $type;
                        $type = (strpos($ingredient, ' g ') !== false) ? 'gram' : $type;
                        $type = (strpos($ingredient, ' gr ') !== false) ? 'gram' : $type;
                        $type = (strpos($ingredient, ' gr. ') !== false) ? 'gram' : $type;
                        $type = (strpos($ingredient, ' gramo ') !== false) ? 'gram' : $type;
                        $type = (strpos($ingredient, ' gramos ') !== false) ? 'gram' : $type;
                        $type = (strpos($ingredient, ' pizca ') !== false) ? 'pinch' : $type;
                        $type = (strpos($ingredient, ' pizcas ') !== false) ? 'pinch' : $type;
                        $type = (strpos($ingredient, ' cc ') !== false) ? 'centiliter' : $type;
                        $type = (strpos($ingredient, ' ml ') !== false) ? 'milliliter' : $type;
                        $type = (strpos($ingredient, ' lata ') !== false) ? 'can' : $type;
                        $type = (strpos($ingredient, ' latas ') !== false) ? 'can' : $type;
                        $type = (strpos($ingredient, ' oz. ') !== false) ? 'ounce' : $type;
                        $type = (strpos($ingredient, ' onza ') !== false) ? 'ounce' : $type;
                        $type = (strpos($ingredient, ' onzas ') !== false) ? 'ounce' : $type;
                        $type = (strpos($ingredient, ' onz. ') !== false) ? 'ounce' : $type;
                    }
                    $typeEmpty = ($type == '') ? true : false;
                    $type = ($typeEmpty) ? '' : $type;
                    $remove = [' cda ', ' cda. ', ' cdas ', ' cuchara ', ' cucharas ', ' cucharada ', ' cucharadas ', ' cdta ', ' cdta. ', ' cdtas ', ' cdita ', ' cditas ', ' cucharadita ', ' cucharaditas ', ' cucharilla ', ' cucharillas ', ' vaso ', ' vasos ', ' vasitos ', ' copa ', ' copas ', ' unidad ', ' unidades ', ' l ', ' lt ', ' lts ', ' litro ', ' litros ', ' tza ', ' taza ', ' tazas ', ' kg ', ' kilo ', ' kilos ', ' lb ', ' lb. ', ' libra ', ' libras ', ' g ', ' gr ', ' gr. ', ' gramo ', ' gramos ', ' pizca ', ' pizcas ', ' cc ', ' ml ', ' lata ', ' latas ', ' colher (chá) ', ' xícara (chá) ', ' xícaras (chá) ', ' colheres (sopa) ', ' colher (café) ', ' colher (sopa) ', ' xícaras ', ' colheres (chá) ', ' oz. ', ' onza ', ' onzas ', ' onz. ', ' cdita. '];
                    $ingredient_new = str_replace($remove, '', $ingredient);
                    $ingredient_new = str_replace($amount, '', $ingredient_new);
                    $ingredient_new = trim($ingredient_new);
                    if (substr($ingredient_new, 0, 3) == 'de ') {
                        $ingredient_new = substr($ingredient_new, 3);
                    }
                    $ingredient_new = trim($ingredient_new);
                    if ($ingredient == 'Sal' || $ingredient == 'sal' || $ingredient == 'Sal al gusto' || $ingredient == 'Sal a gusto' || $ingredient == 'sal a gusto' || $ingredient == 'Sal a gosto') {
                        $typeEmpty = false;
                        $type = 'pinch';
                        $amount = '1';
                        $ingredient_new = 'Sal';
                    }
                    if ($ingredient == 'Pimienta' || $ingredient == 'Pimienta al gusto' || $ingredient == 'Pimenta a gosto') {
                        $typeEmpty = false;
                        $type = 'pinch';
                        $amount = '1';
                        $ingredient_new = 'Pimienta';
                    }
                    $ingredient_new = ucfirst(strtolower($ingredient_new));
                    //$ingredient_new = ($item->get('ingredient') != '') ? $item->get('ingredient') : $ingredient_new;
                    $table .= '
                            <tr class="form" data-id="' . $item->id() . '">
                                <td><input name="amount" value="' . $amount . '"  style="width:100%;"/></td>
                                <td><input name="type" value="' . $type . '"  style="width:100%;' . (($typeEmpty) ? 'border: 1px solid red;' : '') . '"/></td>
                                <td><input name="ingredient_new" value="' . $ingredient_new . '"  style="width:100%"/></td>
                                <td><input name="ingredient_old" value="' . $ingredient . '"  style="width:100%"/></td>
                                <td><button>Guardar</button></td>
                            </tr>';
                    if (isset($this->parameters['save']) && $this->parameters['save']=='all') {
                        $item->persistSimple('amount', $amount);
                        $item->persistSimple('type', $type);
                        $item->persistSimple('ingredient', $ingredient_new);
                    }
                }
                $this->content = '<p>' . count($items) . '</p><table class="table_admin table_categories small" style="width:100%" data-action="' . url('recipe/ingredient-ajax', true) . '">' . $table . '</table>';
                $this->head = $this->loadFormAjax();
                return $this->ui->render();
                break;
            case 'ingredient-ajax':
                $item = (new RecipeIngredient)->read($this->values['id']);
                if ($item->id() != '') {
                    $item->persistSimple('amount', $this->values['amount']);
                    $item->persistSimple('type', $this->values['type']);
                    dd($item->persistSimple('ingredient', $this->values['ingredient_new']));
                    $item->persistSimple('ingredient_old', $this->values['ingredient_old']);
                    echo 1;
                }
                echo 0;
                exit();
                break;
            case 'others':
                $recipe = (new Recipe)->read($this->id);
                $searchText = 'Receta de ' . $recipe->getBasicInfo();
                $response = file_get_contents('https://www.googleapis.com/customsearch/v1?key=AIzaSyAnP86N1FY-FGhWtVY0AT8x0WInS-4tReU&cx=57be6ac7afb0e4453&q=' . urlencode($searchText));
                $data = json_decode($response, true);
                if (isset($data['items']) && count($data['items'] > 3)) {
                    foreach ($data['items'] as $item) {
                        if ($item['link'] != $recipe->url() && !strpos($item['link'], 'youtube') && !strpos($item['link'], 'facebook')) {
                            $content = @file_get_contents($item['link']);
                            if ($content !== false) {
                                $doc = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $doc->loadHTML($content);
                                libxml_clear_errors();
                                $body = $doc->getElementsByTagName('body')->item(0);
                                $htmlBody = $doc->saveHTML($body);
                                $htmlBody = preg_replace('/<!--(.*?)-->/', '', $htmlBody);
                                $htmlBody = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $htmlBody);
                                $htmlBody = preg_replace('/<noscript\b[^>]*>(.*?)<\/noscript>/is', '', $htmlBody);
                                $htmlBody = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $htmlBody);
                                $htmlBody = preg_replace('/<header\b[^>]*>(.*?)<\/header>/is', '', $htmlBody);
                                $htmlBody = preg_replace('/<footer\b[^>]*>(.*?)<\/footer>/is', '', $htmlBody);
                                $htmlBody = preg_replace('/<side\b[^>]*>(.*?)<\/side>/is', '', $htmlBody);
                                $htmlBody = preg_replace('/<([a-zA-Z0-9]+)\b[^>]*>/', '<$1>', $htmlBody);
                                $htmlBody = preg_replace('/<([a-zA-Z0-9]+)\b[^>]*>(.*?)<\/\1>/', '', $htmlBody);
                                $htmlBody = preg_replace('~>\s+<~', '><', $htmlBody);
                                $htmlBody = preg_replace('/\s\s+/', ' ', $htmlBody);
                                // dump(ChatGPT::answer('Encuentra la titulo de la receta en : ' . $htmlBody));
                                echo '=============<br/>';
                                echo $item['link'];
                                echo '<br/><br/>';
                                echo '<pre>' . ChatGPT::answer('Encuentra los ingredientes con sus cantidades de la receta en el siguiente codigo HTML: ' . $htmlBody) . '</pre>';
                                echo '<br/><br/>';
                                $preparacion = ChatGPT::answer('Encuentra los pasos de preparacion de la receta en el siguiente codigo HTML: ' . $htmlBody);
                                echo '<pre>' . ChatGPT::answer('Reformula los pasos y escribe de forma mas clara: ' . $preparacion) . '</pre>';
                                echo '<br/><br/><br/><br/><br/><br/><br/><br/>';
                            }
                        }
                    }
                }
                dd('END');
                break;
            case 'facebook-post':
                $this->mode = 'ajax';
                $item = (new Recipe)->read($this->id);
                $response = ['status' => 'NOK'];
                if ($item->id() != '') {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v22.0/" . Parameter::code('facebook_page_id') . "/feed");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . Parameter::code('facebook_access_token')
                    ]);
                    if (isset($this->parameters['time'])) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                            'message' => $item->showUi('TextSocial'),
                            'image_url' => $item->getImageUrl('image', 'web'),
                            'link' => $item->url(),
                            'published' => 'false',
                            'scheduled_publish_time' => $this->parameters['time']
                        ]));
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                            'message' => $item->showUi('TextSocial'),
                            'image_url' => $item->getImageUrl('image', 'web'),
                            'link' => $item->url(),
                        ]));
                    }
                    $response = curl_exec($ch);
                    curl_close($ch);
                }
                return json_encode($response);
            break;
            case 'instagram-post':
                $this->mode = 'ajax';
                $item = (new Recipe)->read($this->id);
                $response = ['status' => 'NOK'];
                if ($item->id() != '') {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v22.0/" . Parameter::code('facebook_instagram_id') . "/media");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . Parameter::code('facebook_access_token')
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                        'caption' => $item->showUi('TextSocial'),
                        'image_url' => $item->getImageUrl('image', 'web'),
                    ]));
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $responseData = json_decode($response, true);
                    $responseId = (isset($responseData['id'])) ? $responseData['id'] : null;
                    if ($responseId) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v22.0/" . Parameter::code('facebook_instagram_id') . "/media_publish");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . Parameter::code('facebook_access_token')
                        ]);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                            'creation_id' => $responseId,
                        ]));
                        $response = curl_exec($ch);
                        curl_close($ch);
                    }
                }
                return json_encode($response);
                break;
        }
    }

    public function loadFormAjax()
    {
        return '
            <script type="text/javascript">
                $(function() {
                    $(document).on(\'click\', \'.form button\', function(evt){
                        var form = $(this).parents(\'.form\');
                        var data = {
                            "id":form.data(\'id\'),
                            "amount":form.find(\'input[name="amount"]\').val(),
                            "type":form.find(\'input[name="type"]\').val(),
                            "ingredient_new":form.find(\'input[name="ingredient_new"]\').val(),
                            "ingredient_old":form.find(\'input[name="ingredient_old"]\').val()
                        };
                        $.ajax({
                            type: "POST",
                            url: $(\'.table_admin\').data(\'action\'),
                            data: data,
                            success: function(data) {
                                if (data == "10") {
                                    form.find(\'button\').css(\'background\', \'green\');
                                }
                            }
                        });
                    });
                });
            </script>';
    }

}
