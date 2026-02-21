<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_OpenAI_Client
{
    private string $endpoint = 'https://api.openai.com/v1/chat/completions';

    public function generate_turn(array $prompt): array
    {
        $api_key = trim((string) get_option('dd_ai_openai_api_key', ''));
        $model = trim((string) get_option('dd_ai_openai_model', 'gpt-4o-mini'));

        if ($api_key === '') {
            return $this->fallback_turn();
        }

        $body = [
            'model' => $model,
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user', 'content' => $prompt['user']],
            ],
        ];

        $response = wp_remote_post($this->endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 25,
            'body' => wp_json_encode($body),
        ]);

        if (is_wp_error($response)) {
            return $this->fallback_turn('OpenAI request failed: ' . $response->get_error_message());
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw_body = (string) wp_remote_retrieve_body($response);

        if ($code < 200 || $code >= 300) {
            return $this->fallback_turn('OpenAI HTTP ' . $code);
        }

        $json = json_decode($raw_body, true);
        $content = $json['choices'][0]['message']['content'] ?? '';
        $decoded = is_string($content) ? json_decode($content, true) : null;

        if (!is_array($decoded)) {
            return $this->fallback_turn('Model output was not valid JSON.');
        }

        return $decoded;
    }

    private function fallback_turn(string $reason = ''): array
    {
        $suffix = $reason ? ' (' . $reason . ')' : '';

        return [
            'narration' => 'V diaľke zašuští lístie a svet čaká na tvoj ďalší krok' . $suffix . '.',
            'choices' => [
                ['id' => 'A', 'text' => 'Preskúmať okolie opatrne.'],
                ['id' => 'B', 'text' => 'Vydať sa priamo za zvukom.'],
                ['id' => 'C', 'text' => 'Ustúpiť a pripraviť sa na obranu.'],
            ],
            'state_patch' => [
                'hp' => null,
                'mana' => null,
                'xp' => null,
                'inventory_add' => [],
                'inventory_remove' => [],
                'flags_add' => [],
                'flags_remove' => [],
            ],
        ];
    }
}
