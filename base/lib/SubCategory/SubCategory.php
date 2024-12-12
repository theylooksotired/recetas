<?php
class SubCategory extends Db_Object
{

	public function getTitlePage()
    {
        return ($this->get('title')!='') ? $this->get('title') : $this->get('name');
    }

    public function getRecipes()
    {
        if (isset($this->recipes)) {
            return $this->recipes;
        }
        $this->loadMultipleValues();
        $recipesIds = [];
        foreach ($this->get('recipes') as $recipe) {
            $recipesIds[] = $recipe->get('id_recipe');
        }
        $this->recipes = [];
        if (count($recipesIds) > 0 ) {
            $this->recipes = (new Recipe)->readList(['where' => 'id IN (' . implode(',', $recipesIds) . ') AND active="1"', 'order' => 'title_url']);
        }
        if ($this->get('recipes_query') != '') {
            $this->recipes = array_merge($this->recipes, (new Recipe)->readList(['where' => $this->get('recipes_query') . ' AND active="1"', 'order' => 'views DESC']));
        }
        if ($this->get('ingredients_query') != '') {
            $query = '
                SELECT DISTINCT r.* FROM ' . (new Recipe)->tableName . ' r
                JOIN ' . (new RecipeIngredient)->tableName . ' ri ON r.id=ri.id_recipe
                WHERE ' . $this->get('ingredients_query') . ' AND r.active="1"';
            $this->recipes = array_merge($this->recipes, (new Recipe)->readListQuery($query));
        }
        return $this->recipes;
    }

    public function getRecipesTitles() {
        $recipes = $this->getRecipes();
        $titles = [];
        foreach ($recipes as $recipe) {
            $titles[] = $recipe->getBasicInfo();
        }
        return implode(', ', $titles);
    }

    public function getContentChatGPT()
    {
        $titles = $this->getRecipesTitles();
        $question = 'Tienes que escribir el contenido de una pagina web de recetas de cocina de ' . Parameter::code('meta_title_page') . ', ES MUY IMPORTANTE QUE no uses signos de admiracion, ni dos puntos, ni de interrogacion, sin usar frases vacias, que lleva como nombre "' . $this->getBasicInfo() . '", en la pagina hay una lista de recetas que son "' . $titles . '". Entonces, escribe un archivo JSON que tenga los siguientes campos: "title_page" (es el titulo de la pagina que tenga de 8 a 10 palabras), "best_recipes_title" (el subtitulo para las mejores recetas), "rest_recipes_title" (el subtitulo para el resto de las recetas), "description" (son dos parrafos de 30 palabras cada uno, en codigo HTML que expliquen el contenido de la pagina, capaz algunos nombres de recetas y sus caracteristicas), "meta_description" (un texto de 150 caracteres sobre la pagina) y "short_description" (un texto de 250 caracteres que explique en resumen lo que tiene la pagina).';
        $answerChatGPT = ChatGPT::answer($question);
        preg_match('/\{(?:[^{}]|(?R))*\}/', $answerChatGPT, $matches);
        $jsonString = (isset($matches[0])) ? $matches[0] : '';
        if ($jsonString != '') {
            return json_decode($jsonString, true);
        }
    }

}
