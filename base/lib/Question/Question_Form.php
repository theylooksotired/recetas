<?php
class Question_Form extends Form { 

    static public function showPublic() {
        $fields = FormField::show('text', ['name' => 'question', 'label' => __('ask_your_question'), 'required' => true]);
        return Form::createForm($fields, ['submit' => __('send'), 'recaptchav3' => true]);
    }
}
