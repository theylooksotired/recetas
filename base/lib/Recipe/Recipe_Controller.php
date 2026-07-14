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
                $this->mode = 'ajax';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $response = $recipe->promptImages();
                }
                foreach ($response as $key => $prompt) {
                    echo '<strong>' . $key . '</strong><br/>' . $prompt . '<br/><br/>';
                }
                return '';
                break;
            case 'script-video':
                $this->mode = 'ajax';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    echo $recipe->scriptVideo();
                }
                return '';
                break;
            case 'social-media':
                $this->mode = 'ajax';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $recipe->loadMultipleValues();
                    $font = ASTERION_BASE_FILE . 'visual/css/fonts/AlfaSlabOne-Regular.ttf';
                    // Portada
                    $pngMaskCover = ASTERION_BASE_FILE . 'visual/img/pngs/en4pasos.png';
                    $imageCover = str_replace(ASTERION_LOCAL_URL, ASTERION_LOCAL_FILE, $recipe->getImageUrl('image', 'web'));
                    $imageCoverOut = ASTERION_STOCK_FILE . 'recipe_out_cover.jpg';
                    $imageCoverBigOut = ASTERION_STOCK_FILE . 'recipe_out_big_cover.jpg';
                    $imageCover = new Image($imageCover);
                    $imageCover->resizeSquare($imageCoverOut, 1024, 'image/jpg');
                    $imageCover->resizeSquare($imageCoverBigOut, 1920, 'image/jpg');
                    $imageCover = new Image($imageCoverOut);
                    $imageCover->addPngOverImage($pngMaskCover, $imageCoverOut, 'image/jpg');
                    $imageCover = new Image($imageCoverOut);
                    $imageCover->addTextOverImage($recipe->get('title'), $imageCoverOut, 'image/jpg', 100, $font);
                    // Ingredientes
                    $pngMaskIngredientes = ASTERION_BASE_FILE . 'visual/img/pngs/ingredientes.png';
                    $imageIngredients = str_replace(ASTERION_LOCAL_URL, ASTERION_LOCAL_FILE, $recipe->getImageUrl('image_ingredients', 'web'));
                    $imageIngredientsOut = ASTERION_STOCK_FILE . 'recipe_out_ingredientes.jpg';
                    $imageIngredientsBigOut = ASTERION_STOCK_FILE . 'recipe_out_big_ingredientes.jpg';
                    $imageIngredients = new Image($imageIngredients);
                    $imageIngredients->resizeSquare($imageIngredientsOut, 1024, 'image/jpg');
                    $imageIngredients->resizeSquare($imageIngredientsBigOut, 1920, 'image/jpg');
                    $imageIngredients = new Image($imageIngredientsOut);
                    $imageIngredients->addPngOverImage($pngMaskIngredientes, $imageIngredientsOut, 'image/jpg');
                    // Pasos
                    $pasosText = '';
                    for ($i=1; $i<=4; $i++) {
                        $pngMaskStep = ASTERION_BASE_FILE . 'visual/img/pngs/paso' . $i . '.png';
                        $imageStepOut = ASTERION_STOCK_FILE . 'recipe_out_step' . $i . '.jpg';
                        $imageStepBigOut = ASTERION_STOCK_FILE . 'recipe_out_big_step' . $i . '.jpg';
                        $imageStep = str_replace(ASTERION_LOCAL_URL, ASTERION_LOCAL_FILE, $recipe->get('images')[$i - 1]->getImageUrl('image', 'web'));
                        $imageStep = new Image($imageStep);
                        $imageStep->resizeSquare($imageStepOut, 1024, 'image/jpg');
                        $imageStep->resizeSquare($imageStepBigOut, 1920, 'image/jpg');
                        $imageStep = new Image($imageStepOut);
                        $imageStep->addPngOverImage($pngMaskStep, $imageStepOut, 'image/jpg');
                        $pasosText .= 'Paso ' . $i . ': ' . $recipe->get('images')[$i - 1]->get('label') . "\n";
                    }
                    $zipImages = new ZipArchive();
                    $zipFileName = ASTERION_STOCK_FILE . 'recipe_social_media_images_' . $recipe->id() . '.zip';
                    if ($zipImages->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                        $zipImages->addFile($imageCoverOut, 'cover.jpg');
                        $zipImages->addFile($imageIngredientsOut, 'ingredientes.jpg');
                        $zipImages->addFile(ASTERION_STOCK_FILE . 'recipe_out_step1.jpg', 'paso1.jpg');
                        $zipImages->addFile(ASTERION_STOCK_FILE . 'recipe_out_step2.jpg', 'paso2.jpg');
                        $zipImages->addFile(ASTERION_STOCK_FILE . 'recipe_out_step3.jpg', 'paso3.jpg');
                        $zipImages->addFile(ASTERION_STOCK_FILE . 'recipe_out_step4.jpg', 'paso4.jpg');
                        $zipImages->close();
                    }
                    $zipImagesBig = new ZipArchive();
                    $zipFileNameBig = ASTERION_STOCK_FILE . 'recipe_social_media_images_big_' . $recipe->id() . '.zip';
                    if ($zipImagesBig->open($zipFileNameBig, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                        $zipImagesBig->addFile($imageCoverBigOut, 'cover.jpg');
                        $zipImagesBig->addFile($imageIngredientsBigOut, 'ingredientes.jpg');
                        $zipImagesBig->addFile(ASTERION_STOCK_FILE . 'recipe_out_big_step1.jpg', 'paso1.jpg');
                        $zipImagesBig->addFile(ASTERION_STOCK_FILE . 'recipe_out_big_step2.jpg', 'paso2.jpg');
                        $zipImagesBig->addFile(ASTERION_STOCK_FILE . 'recipe_out_big_step3.jpg', 'paso3.jpg');
                        $zipImagesBig->addFile(ASTERION_STOCK_FILE . 'recipe_out_big_step4.jpg', 'paso4.jpg');
                        $zipImagesBig->close();
                    }
                    $question = 'Escribe un archivo JSON con los siguientes campos: "introduccion" que son dos lineas para introducir la receta en una publicacion de redes sociales, "despedida" que es una linea para despedir la publicacion. La receta es: "' . $recipe->showUi('Text') . '"';
                    $info = ChatGPT::answerJson($question, $options = []);
                    $socialText .= (isset($info['introduccion'])) ? "\n\n" . $info['introduccion'] : '';
                    $socialText .= "\n\n-- Ingredientes --\n\n";
                    foreach ($recipe->get('ingredients') as $ingredient) {
                        if ($ingredient->get('amount') != '') {
                            $ingredientType = (($ingredient->get('type') != 'unit' && $ingredient->get('type') != '') ? strtolower((intval($ingredient->get('amount')) > 1) ? __($ingredient->get('type') . '_plural') : __($ingredient->get('type'))) . ' ' . __('of') : '');
                            $socialText .= $ingredient->get('amount') . ' ' . $ingredientType . ' ' . $ingredient->get('ingredient') . "\n";
                        } else {
                            $socialText .= $ingredient->get('ingredient') . "\n";
                        }
                    }
                    $socialText .= "\n-- Preparación --\n\n";
                    $socialText .= $pasosText;
                    $socialText .= (isset($info['despedida'])) ? "\n" . $info['despedida'] : '';
                    $socialText .= "\n\nMás información:\n" . $recipe->url() . "\n\n#receta #cocina";
                    $content = '<a href="' . str_replace(ASTERION_LOCAL_FILE, ASTERION_LOCAL_URL, $zipFileName) . '" target="_blank">Descargar para Post FB IG</a><br/><br/>';
                    $content .= '<a href="' . str_replace(ASTERION_LOCAL_FILE, ASTERION_LOCAL_URL, $zipFileNameBig) . '" target="_blank">Descargar para video</a><br/><br/>';
                    $question = 'Escribe solamente un titulo que sea bien SEO para nuestro canal de youtube, tiktok y reels. El contenido es: "' . $socialText . '"';
                    $content .= '<pre>' . ChatGPT::answer($question) . "\n\n" . $socialText . '</pre>';
                    echo $content;
                }
                break;
            case 'seo-advice':
                $this->mode = 'ajax';
                $recipe = (new Recipe)->read($this->id);
                if ($recipe->id() != '') {
                    $url = $recipe->url();
                    // $url = 'https://www.cocina-cubana.com/recetas/plato-principal/pescado-empanizado';
                    $result = GoogleSearchConsole::loadRows($url);
                    if (isset($result['error'])) {
                        echo '<div class="message message_error">' . htmlspecialchars($result['error'], ENT_QUOTES, 'UTF-8') . '</div>';
                        break;
                    }
                    $rows = $result['rows'];
                    if (empty($rows)) {
                        echo '<div class="message message_info">No hay datos de queries para los ultimos 30 dias en esta URL.</div>';
                        break;
                    }
                    $table = GoogleSearchConsole::renderTable($rows, $url);
                    if ($table != '') {
                        $jsonRecipe = json_encode([
                            'title_page' => $recipe->get('title_page'),
                            'meta_description' => $recipe->get('meta_description'),
                            'short_description' => $recipe->get('short_description'),
                            'description' => $recipe->get('description'),
                            'recipe' => $recipe->showUi('Text'),
                        ]);
                        $question = '
                            Este es el JSON de una receta de cocina "' . $jsonRecipe . '".
                            Ojo que short_description es un parrafo cortito que describe el plato, description es un poco mas largo y da mas detalles e incluso a veces alguna curiosidad o incentivo para preparar la receta.
                            Esta tabla con las queries de Google Search Console de los ultimos 30 dias para esta URL, es muy importante que analices las palabras clave: ' . $table . ' 
                            Necesito que me devuelvas el mismo archivo JSON con mejoras SEO, usa un lenguaje bien humano que no parezca escrito por AI (no abuses de adjetivos ni adverbios, busca informacion en otras paginas, tienes que investigar un monton, no me devuelvas la misma cosa con otras palabras, revisa en la red que cosas encuentras, se natural).
                            No cambies la estructura del JSON, solo mejora el contenido basandote tambien en los datos de Google Search Console y mejora tambien la preparacion, busca tips y consejos que sean utiles.';
                        $answer = ChatGPT::answerJSON($question);
                        foreach ($answer as $key => $value) {
                            echo '<strong>' . $key . '</strong><br/>' . nl2br($value) . '<br/><br/>';
                        }
                        echo '<hr/><br/>';
                        echo $table;
                    } else {
                        echo '<div class="message message_info">No hay datos de queries para los ultimos 30 dias en esta URL.</div>';
                    }
                }
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
                    if (substr($content, 0, 4) == 'http') {
                        $content = file_get_contents($content);
                        $questionRecipe = 'Encuentra en este codigo HTML una receta y traducila al español. Dame un archivo JSON con "titulo", "descripcion", "ingredientes" y "preparacion". El contenido es: "' . $content . '"';
                        $responseJson = [];
                        $maxAttempts = 3;
                        $attempts = 0;
                        while (empty($responseJson['titulo']) && $attempts < $maxAttempts) {
                            $responseJson = ChatGPT::answerJson($questionRecipe);
                            $attempts++;
                        }
                        $content = json_encode($responseJson);
                    }
                    $categories = (new Category)->readList();
                    $categoriesInfo = [];
                    foreach ($categories as $category) {
                        $categoriesInfo[] = $category->id() . ':' . $category->get('name');
                    }
                    $questionRecipe = 'Escribe un archivo JSON, sin ningun texto adicional, con los campos titulo, metaDescripcion (140 caracteres), descripcion (350 caracteres), descripcionHtml (descripcion sencilla del plato, debe ser distinta a la descripcion y debe contener mejor informacion sobre la receta, de maximo 1000 caracteres en codigo HTML), idCategory (' . implode(',', $categoriesInfo) . '), tiempoPreparacion (5_minutes,15_minutes,30_minutes,45_minutes,1_hour,2_hours,3_hours,4_hours,5_hours,1_day,2_days), numeroPorciones, ingredientes (lista, separar cada ingrediente si una linea tiene varios), pasos (lista, que incluya a todos los ingredientes, siempre en tercera persona y con un lenguaje muy amigable, se debe tener una sola linea por paso). La receta es: "' . $content . '"';
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
            case 'create-version':
                $this->mode = 'json';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $recipeInfo = $recipe->toJson();
                    unset($recipeInfo['images']);
                    unset($recipeInfo['image_ingredients']);
                    unset($recipeInfo['questions']);
                    unset($recipeInfo['ingredients_raw']);
                    unset($recipeInfo['preparation_raw']);
                    $recipeJson = json_encode($recipeInfo);
                    $questionVersion = 'Crea una version alternativa de la receta usando el mismo formato JSON (cook_time:5_minutes,15_minutes,30_minutes,45_minutes,1_hour,2_hours,3_hours,4_hours,5_hours,1_day,2_days; cooking_method:fried,steamed,boiled,baked,grilled; diet:diabetic,gluten_free,halal,hindu,kosher,low_calorie,low_fat,low_lactose,low_salt,vegan,vegetarian). La receta original es: "' . $recipeJson . '"';
                    $response = ChatGPT::answerJson($questionVersion);
                    if (isset($response['id'])) {
                        unset($response['id']);
                        $response['id_recipe'] = $this->id;
                        $response['active'] = '1';
                        $version = new RecipeVersion($response);
                        $version->persist();
                    }
                }
                header('Location:' . $recipe->urlAdmin());
                exit();
                break;
            case 'translate':
                $this->mode = 'json';
                $recipe = (new Recipe)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $recipe->translate();
                }
                header('Location:' . $recipe->urlAdmin());
                exit();
                break;
        }
    }
}
