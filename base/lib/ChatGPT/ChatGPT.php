<?php
/**
 * @class ChatGPT
 *
 * This class defines the controller for the project users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class ChatGPT extends Controller
{

    static public function answer($question, $options = [])
    {
        $options['roleContent'] = (!isset($options['roleContent'])) ? 'Eres una escritora latinoamericana de un sitio de recetas, tu publico es amigable y te gusta escribir de forma calmada.' : $options['roleContent'];
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . Parameter::code('openai_api')
        ];
        $data = [
            'model' => 'gpt-4.1-nano',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $options['roleContent']
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

    static public function answerJson($question, $options = [])
    {
        $response = ChatGPT::answer($question, $options);
        preg_match('/\{(?:[^{}]|(?R))*\}/', $response, $matches);
        $jsonString = (isset($matches[0])) ? $matches[0] : '';
        return json_decode($jsonString, true);
    }

}
