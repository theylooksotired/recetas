<?php
/**
 * @class NavigationController
 *
 * This is the controller for all the public actions of the website.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Translate_Controller extends Controller
{

    public function getContent()
    {
        switch ($this->action) {
            case 'categories':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $itemsProcessed = [];
                $categories = (new Category)->readList();
                foreach ($categories as $category) {
                    $question = 'Traduci el archivo JSON que contiene la categoria de cocina al ingles, sin ningun texto adicional, el archivo JSON es: "' . $category->toJson() . '"';
                    $response = ChatGPT::answerJson($question);
                    if (isset($response['id'])) {
                        $categoryTranslated = new Category($response);
                        $categoryTranslated->persist();
                        $itemsProcessed[] = 'OK - ' . $categoryTranslated->getBasicInfo();
                    } else {
                        $itemsProcessed[] = 'NOK - ' . $category->getBasicInfo();
                    }
                }
                $result = ['status' => 'OK', 'items' => $itemsProcessed];
                return json_encode($result, JSON_PRETTY_PRINT);
            break;
            case 'subcategories':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $itemsProcessed = [];
                $subCategories = (new SubCategory)->readList();
                foreach ($subCategories as $subCategory) {
                    $question = 'Traduci el archivo JSON que contiene la subCategoria de cocina al ingles, sin ningun texto adicional, el archivo JSON es: "' . $subCategory->toJson() . '"';
                    $response = ChatGPT::answerJson($question);
                    if (isset($response['id'])) {
                        $subCategoryTranslated = new SubCategory($response);
                        $subCategoryTranslated->persist();
                        $itemsProcessed[] = 'OK - ' . $subCategoryTranslated->getBasicInfo();
                    } else {
                        $itemsProcessed[] = 'NOK - ' . $subCategory->getBasicInfo();
                    }
                }
                $result = ['status' => 'OK', 'items' => $itemsProcessed];
                return json_encode($result, JSON_PRETTY_PRINT);
            break;
            case 'info':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $itemsProcessed = [];
                $htmlSections = (new HtmlSection)->readList();
                foreach ($htmlSections as $htmlSection) {
                    $question = 'Traduci el archivo JSON que contiene una pagina de un sitio de cocina al ingles, sin ningun texto adicional, el archivo JSON es: "' . $htmlSection->toJson() . '"';
                    $response = ChatGPT::answerJson($question);
                    if (isset($response['id'])) {
                        $response['title_en'] = $response['title_es'];
                        $response['title_url_en'] = $response['title_url_es'];
                        $response['section_en'] = $response['section_es'];
                        $htmlSectionTranslated = new HtmlSection($response);
                        $htmlSectionTranslated->persist();
                        $itemsProcessed[] = 'OK - ' . $htmlSectionTranslated->getBasicInfo();
                    } else {
                        $itemsProcessed[] = 'NOK - ' . $htmlSection->getBasicInfo();
                    }
                }
                $result = ['status' => 'OK', 'items' => $itemsProcessed];
                return json_encode($result, JSON_PRETTY_PRINT);
            break;
            case 'recipes':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $itemsProcessed = [];
                $recipes = (new Recipe)->readList(['where' => 'translated IS NULL OR translated=""', 'limit' => '10']);
                foreach ($recipes as $recipe) {
                    $recipeJson = $recipe->toJson();
                    $question = 'Traduci el archivo JSON que contiene la receta de cocina al ingles, sin ningun texto adicional, el archivo JSON es: "' . json_encode($recipeJson) . '"';
                    $response = ChatGPT::answerJson($question);
                    if (isset($response['id'])) {
                        Db::execute('DELETE FROM ' . (new RecipeIngredient)->tableName . ' WHERE id_recipe=' . $recipe->id());
                        Db::execute('DELETE FROM ' . (new RecipePreparation)->tableName . ' WHERE id_recipe=' . $recipe->id());
                        $recipe->setValues($response);
                        $recipe->set('translated', 1);
                        $recipe->persist();
                        $itemsProcessed[] = 'OK - ' . $recipe->id() . ' - ' . $recipe->getBasicInfo();
                    } else {
                        $itemsProcessed[] = 'NOK - ' . $recipe->id() . ' - ' . $recipe->getBasicInfo();
                    }
                }
                $result = ['status' => 'OK', 'items' => $itemsProcessed];
                return json_encode($result, JSON_PRETTY_PRINT);
                break;
            case 'recipes-versions':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $itemsProcessed = [];
                $recipes = (new RecipeVersion)->readList(['where' => 'translated IS NULL OR translated=""', 'limit' => '10']);
                foreach ($recipes as $recipe) {
                    $recipeJson = $recipe->toJson();
                    $question = 'Traduci el archivo JSON que contiene la receta de cocina al ingles, sin ningun texto adicional, el archivo JSON es: "' . json_encode($recipeJson) . '"';
                    $response = ChatGPT::answerJson($question);
                    if (isset($response['id'])) {
                        Db::execute('DELETE FROM ' . (new RecipeVersionIngredient)->tableName . ' WHERE id_recipe_version=' . $recipe->id());
                        Db::execute('DELETE FROM ' . (new RecipeVersionPreparation)->tableName . ' WHERE id_recipe_version=' . $recipe->id());
                        $recipe->setValues($response);
                        $recipe->set('translated', 1);
                        $recipe->persist();
                        $itemsProcessed[] = 'OK - ' . $recipe->id() . ' - ' . $recipe->getBasicInfo();
                    } else {
                        $itemsProcessed[] = 'NOK - ' . $recipe->id() . ' - ' . $recipe->getBasicInfo();
                    }
                }
                $result = ['status' => 'OK', 'items' => $itemsProcessed];
                return json_encode($result, JSON_PRETTY_PRINT);
                break;
            case 'post-images':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $itemsProcessed = [];
                $postImages = (new PostImage)->readList();
                foreach ($postImages as $postImage) {
                    $question = 'Traduci el archivo JSON que contiene la image de cocina al ingles, sin ningun texto adicional, el archivo JSON es: "' . $postImage->toJson() . '"';
                    $response = ChatGPT::answerJson($question);
                    if (isset($response['id'])) {
                        $postImageTranslated = new PostImage($response);
                        $postImageTranslated->persist();
                        $itemsProcessed[] = 'OK - ' . $postImageTranslated->getBasicInfo();
                    } else {
                        $itemsProcessed[] = 'NOK - ' . $postImage->getBasicInfo();
                    }
                }
                $result = ['status' => 'OK', 'items' => $itemsProcessed];
                return json_encode($result, JSON_PRETTY_PRINT);
            break;
            case 'posts':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $itemsProcessed = [];
                $posts = (new Post)->readList(['where' => 'translated IS NULL OR translated=""', 'limit' => '10']);
                foreach ($posts as $post) {
                    $postJson = $post->toJson();
                    $question = 'Traduci el archivo JSON que contiene la receta de cocina al ingles, sin ningun texto adicional, el archivo JSON es: "' . json_encode($postJson) . '"';
                    $response = ChatGPT::answerJson($question);
                    if (isset($response['id'])) {
                        $post->setValues($response);
                        $post->set('translated', 1);
                        $post->persist();
                        $itemsProcessed[] = 'OK - ' . $post->id() . ' - ' . $post->getBasicInfo();
                    } else {
                        $itemsProcessed[] = 'NOK - ' . $post->id() . ' - ' . $post->getBasicInfo();
                    }
                }
                $result = ['status' => 'OK', 'items' => $itemsProcessed];
                return json_encode($result, JSON_PRETTY_PRINT);
                break;
            case 'save-urls':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['main' => url('')];
                $items = (new Recipe)->readList();
                foreach ($items as $item) {
                    $result['recipe_' . $item->id()] = $item->url();
                }
                $items = (new Post)->readList();
                foreach ($items as $item) {
                    $result['post_' . $item->id()] = $item->url();
                }
                return json_encode($result, JSON_PRETTY_PRINT);
                break;
            }
    }

    public function checkAuthorization()
    {
        $headers = apache_request_headers();
        if (!isset($headers) || !isset($headers['Authorization']) || $headers['Authorization'] != 'plastic') {
            header('Location: ' . url(''));
            exit();
        }
    }

    static public function loadTranslations()
    {
        $translations = [];
        if (Parameter::code('translated_site') == 'true') {
            $countryCode = Parameter::code('country_code');
            $siteLanguage = Language::active();
            $directory = ASTERION_BASE_FILE . 'data/';
            $files = glob($directory . '*.json');
            foreach ($files as $file) {
                if (strpos($file, $countryCode . '_') !== false) {
                    $languageFile = str_replace($countryCode . '_', '', File::filename($file));
                    $translations[$languageFile] = json_decode(file_get_contents($file), true);
                }
            }
            $translations = (isset($translations[Translate_Controller::translateTo()])) ? $translations[Translate_Controller::translateTo()] : [];
        }
        return $translations;
    }

    static public function translateTo()
    {
        return (Language::active() == 'es') ? 'en' : 'es';
    }

    static public function alternateUrl($code = 'main')
    {
        $translations = Translate_Controller::loadTranslations();
        if (isset($translations[$code])) {
            return '<link rel="alternate" hreflang="' . Translate_Controller::translateTo() . '" href="' . $translations[$code] . '"/>';
        }
    }

}
