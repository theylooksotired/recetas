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
            case 'steps-ai-data':
                $this->mode = 'json';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $steps = $recipe->showUi('PreparationParagraph');
                    $questionSteps = 'Escribe estos pasos de forma mas clara, ordenada y en tercera persona. No uses titulos, ni la lista de ingredientes, ni ennumeres los pasos. La preparacion es: "' . $steps . '"';
                    $response['steps'] = str_replace('. ', ".\n\n", ChatGPT::answer($questionSteps));
                }
                return json_encode($response);
                break;
            case 'full-ai-recipe':
                $this->mode = 'json';
                $response = [];
                $content = (isset($this->values['content'])) ? $this->values['content'] : '';
                if ($content != '') {
                    $questionRecipe = 'Escribe un archivo JSON, sin ningun texto adicional, con los campos titulo, metaDescripcion (140 caracteres), descripcion (350 caracteres), descripcionHtml (descripcion sencilla del plato, debe ser distinta a la descripcion y debe contener mejor informacion sobre la receta, de maximo 1000 caracteres en codigo HTML), ingredientes (lista, separar cada ingrediente si una linea tiene varios), pasos (lista, que incluya a todos los ingredientes, siempre en tercera persona y con un lenguaje muy amigable, se debe tener una sola linea por paso). La receta es: "' . $content . '"';
                    $answerChatGPT = ChatGPT::answer($questionRecipe);
                    preg_match('/\{(?:[^{}]|(?R))*\}/', $answerChatGPT, $matches);
                    $jsonString = (isset($matches[0])) ? $matches[0] : '';
                    if ($jsonString != '') {
                        $response = json_decode($jsonString, true);
                        if (isset($response['titulo'])) {
                            $questionTitle = 'Corrige la sintaxis y ortografia de esta frase, sin usar comillas, sin poner punto final: "' . (new Recipe)->titleTest($response['titulo']) . '"';
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
                    $ingredient = trim(str_replace('  ', ' ', $item->get('ingredient')));
                    if ($ingredient == 'Sal y pimienta') {
                        $item->persistSimple('ingredient', 'Sal');
                        $newIngredient = new RecipeIngredient([
                            'id_recipe' => $item->get('id_recipe'),
                            'ingredient' => 'Pimienta',
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
                foreach ((new RecipeIngredient)->readList(['order' => 'ingredient']) as $item) {
                    $ingredient = ($item->get('ingredient_old') == 'Sal y pimienta' || $item->get('ingredient_old') == '') ? $item->get('ingredient') : $item->get('ingredient_old');
                    $amount = $item->get('amount');
                    if ($amount == '') {
                        $amount = ($amount == '' && strpos($ingredient, '1 ½') !== false) ? '1 ½' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1½') !== false) ? '1 ½' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '2 ½') !== false) ? '2 ½' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1 ¼') !== false) ? '1 ¼' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '1¼') !== false) ? '1 ¼' : $amount;
                        $amount = ($amount == '' && strpos($ingredient, '2 ¼') !== false) ? '2 ¼' : $amount;
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
                        $type = (strpos($ingredient, ' cdas ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cuchara ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cucharas ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cucharada ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cucharadas ') !== false) ? 'tablespoon' : $type;
                        $type = (strpos($ingredient, ' cdta ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cdtas ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cdita ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cditas ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharadita ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharaditas ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharilla ') !== false) ? 'teaspoon' : $type;
                        $type = (strpos($ingredient, ' cucharillas ') !== false) ? 'teaspoon' : $type;
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
                        $type = (strpos($ingredient, ' libra ') !== false) ? 'pound' : $type;
                        $type = (strpos($ingredient, ' libras ') !== false) ? 'pound' : $type;
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
                    }
                    $typeEmpty = ($type == '') ? true : false;
                    $type = ($typeEmpty) ? 'unit' : $type;
                    $remove = [' cda ', ' cdas ', ' cuchara ', ' cucharas ', ' cucharada ', ' cucharadas ', ' cdta ', ' cdtas ', ' cdita ', ' cditas ', ' cucharadita ', ' cucharaditas ', ' cucharilla ', ' cucharillas ', ' vaso ', ' vasos ', ' vasitos ', ' copa ', ' copas ', ' unidad ', ' unidades ', ' l ', ' lt ', ' lts ', ' litro ', ' litros ', ' tza ', ' taza ', ' tazas ', ' kg ', ' kilo ', ' kilos ', ' lb ', ' libra ', ' libras ', ' gr ', ' gr. ', ' gramo ', ' gramos ', ' pizca ', ' pizcas ', ' cc ', ' ml ', ' lata ', ' latas '];
                    $ingredient_new = str_replace($remove, '', $ingredient);
                    $ingredient_new = str_replace($amount, '', $ingredient_new);
                    $ingredient_new = trim($ingredient_new);
                    if (substr($ingredient_new, 0, 3) == 'de ') {
                        $ingredient_new = substr($ingredient_new, 3);
                    }
                    $ingredient_new = trim($ingredient_new);
                    if ($ingredient == 'Sal' || $ingredient == 'sal' || $ingredient == 'Sal al gusto' || $ingredient == 'Sal a gusto' || $ingredient == 'sal a gusto') {
                        $typeEmpty = false;
                        $type = 'pinch';
                        $amount = '1';
                        $ingredient_new = 'Sal';
                    }
                    if ($ingredient == 'Pimienta' || $ingredient == 'Pimienta al gusto') {
                        $typeEmpty = false;
                        $type = 'pinch';
                        $amount = '1';
                        $ingredient_new = 'Pimienta';
                    }
                    $ingredient_new = ucfirst(strtolower($ingredient_new));
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
                $this->content = '<table class="table_admin table_categories small" style="width:100%" data-action="' . url('recipe/ingredient-ajax', true) . '">' . $table . '</table>';
                $this->head = $this->loadFormAjax();
                return $this->ui->render();
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
