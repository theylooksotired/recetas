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

}
