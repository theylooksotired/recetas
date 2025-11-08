<?php
class Question_Ui extends Ui
{

    public function renderPublic($options = [])
    {
        return '
            <div class="question_answered">
                <p class="question">' . $this->object->get('question_formatted') . '</p>
                <p class="answer">' . $this->object->get('answer') . '</p>
            </div>';
    }

    public function renderRecipe($options = [])
    {
        return '
            <div class="question_recipe">
                <p class="question_recipe_date">' . Date::sqlText($this->object->get('created')) . '</p>
                <p class="question_recipe_question">' . $this->object->get('question_formatted') . '</p>
                <p class="question_recipe_answer">' . nl2br($this->object->get('answer')) . '</p>
            </div>';
    }

}
