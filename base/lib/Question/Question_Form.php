<?php
class Question_Form extends Form { 

    static public function showPublic() {
        $fields = '' . '
            ' . FormField::show('text', ['name' => 'question', 'label' => __('ask_your_question'), 'id' => 'question', 'required' => true]) . '
            ' . FormField::show('hidden', ['name' => 'type', 'value' => 'question']);
        return Form::createForm($fields, ['submit' => __('send'), 'id' => 'question_form', 'recaptchav3' => true]);
    }

}
