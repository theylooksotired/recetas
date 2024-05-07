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
                    $questionDescription = 'Escribe una descripcion sobre el origen, historia y significado de la receta ' . $recipe->getBasicInfo() . ' de ' . Parameter::code('meta_title_header_bottom') . ', sin decir como se prepara. En el primer parrafo escribe que vas a explicar como se prepara la receta, cuales son los pasos a seguir y las instrucciones. Evita usar signos de admiracion y frases repetitivas. Dirigete a la tercera persona en plural.';
                    $response['description'] = $this->callChatGPT($questionDescription);
                    $questionMetaDescription = 'Resume el siguiente texto a 140 caracteres, sin usar signos de admiracion: ' . $response['description'];
                    $response['meta_description'] = $this->callChatGPT($questionMetaDescription);
                    $questionShortDescription = 'Haz este texto mas lindo, sin usar signos de admiracion: ' . $recipe->getBasicInfo() . ' ' . $recipe->get('short_description');
                    $response['short_description'] = $this->callChatGPT($questionShortDescription);
                }
                return json_encode($response);
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
            case 'ingredient-ajax':
                dd($this->values);
                // $tag = (new Tag)->read($this->values['id']);
                // if ($tag->id() != '') {
                //     $category = (new Category)->readFirst(['where' => 'name=:name'], ['name' => $this->values['category']]);
                //     if ($category->id() != '') {
                //         $tag->persistSimple('id_category', $category->id());
                //         echo 1;
                //     }
                // }
                // echo 0;
                exit();
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

    public function callChatGPT($question)
    {
        
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . Parameter::code('openai_api')
        ];
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres un escritor latinoamericano de un sitio de recetas, tu publico es amigable y te gusta escribir de forma calmada.'
                ],
                [
                    'role' => 'user',
                    'content' => $question
                ]
            ],
            'n' => 1
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        return (isset($response['choices'][0]['message']['content'])) ? $response['choices'][0]['message']['content'] : '';
    }

}
