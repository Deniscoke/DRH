<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_REST_Controller
{
    private DD_AI_RPG_State_Repository $repository;
    private DD_AI_RPG_Dice $dice;
    private DD_AI_RPG_Rules_Retriever $rules;
    private DD_AI_RPG_Prompt_Builder $prompts;
    private DD_AI_RPG_OpenAI_Client $openai;

    public function __construct(
        DD_AI_RPG_State_Repository $repository,
        DD_AI_RPG_Dice $dice,
        DD_AI_RPG_Rules_Retriever $rules,
        DD_AI_RPG_Prompt_Builder $prompts,
        DD_AI_RPG_OpenAI_Client $openai
    ) {
        $this->repository = $repository;
        $this->dice = $dice;
        $this->rules = $rules;
        $this->prompts = $prompts;
        $this->openai = $openai;
    }

    public function register_routes(): void
    {
        register_rest_route('dd-ai/v1', '/rpg', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_rpg_turn'],
            'permission_callback' => '__return_true',
            'args' => [
                'player_id' => ['required' => true, 'type' => 'string'],
                'campaign_id' => ['required' => true, 'type' => 'string'],
                'action' => ['required' => true, 'type' => 'string'],
                'message' => ['required' => false, 'type' => 'string'],
                'mode' => ['required' => false, 'type' => 'string'],
            ],
        ]);
    }

    public function handle_rpg_turn(WP_REST_Request $request): WP_REST_Response
    {
        $player_id = sanitize_text_field((string) $request->get_param('player_id'));
        $campaign_id = sanitize_text_field((string) $request->get_param('campaign_id'));
        $action = sanitize_key((string) $request->get_param('action'));
        $message = sanitize_textarea_field((string) $request->get_param('message'));
        $mode = sanitize_key((string) $request->get_param('mode') ?: 'rules');

        if (!in_array($action, ['player_input', 'choice_id', 'roll'], true)) {
            return new WP_REST_Response(['message' => 'Invalid action type.'], 400);
        }

        $state = $this->repository->get_state($player_id, $campaign_id);
        $state = $this->repository->append_log($state, 'player', $message);

        $rules_context = $this->rules->get_relevant_chunks($message, $mode);
        $prompt = $this->prompts->build([
            'action' => $action,
            'message' => $message,
            'mode' => $mode,
        ], $state, $rules_context, $mode);

        $ai = $this->openai->generate_turn($prompt);

        $rolls = [];
        if (!empty($ai['requested_roll']) && is_array($ai['requested_roll'])) {
            $skill = sanitize_text_field((string) ($ai['requested_roll']['skill'] ?? 'general'));
            $dc = max(2, (int) ($ai['requested_roll']['dc'] ?? 10));
            $rolls[] = $this->dice->resolve_skill_check($skill, $dc);
        }

        $patch = $this->normalize_patch((array) ($ai['state_patch'] ?? []), $state);
        $state = $this->repository->apply_patch($state, $patch);
        $state = $this->repository->append_log($state, 'narrator', (string) ($ai['narration'] ?? ''));
        $this->repository->save_state($player_id, $campaign_id, $state);

        return new WP_REST_Response([
            'narration' => (string) ($ai['narration'] ?? ''),
            'choices' => $this->normalize_choices((array) ($ai['choices'] ?? [])),
            'state_patch' => $patch,
            'rolls' => $rolls,
        ]);
    }

    private function normalize_patch(array $patch, array $state): array
    {
        return [
            'hp' => isset($patch['hp']) ? (int) $patch['hp'] : (int) $state['hp']['current'],
            'mana' => isset($patch['mana']) ? (int) $patch['mana'] : (int) $state['mana']['current'],
            'xp' => isset($patch['xp']) ? (int) $patch['xp'] : (int) $state['xp'],
            'inventory_add' => array_values(array_map('sanitize_text_field', (array) ($patch['inventory_add'] ?? []))),
            'inventory_remove' => array_values(array_map('sanitize_text_field', (array) ($patch['inventory_remove'] ?? []))),
            'flags_add' => array_values(array_map('sanitize_text_field', (array) ($patch['flags_add'] ?? []))),
            'flags_remove' => array_values(array_map('sanitize_text_field', (array) ($patch['flags_remove'] ?? []))),
        ];
    }

    private function normalize_choices(array $choices): array
    {
        $result = [];

        foreach ($choices as $index => $choice) {
            if (!is_array($choice)) {
                continue;
            }

            $id = sanitize_text_field((string) ($choice['id'] ?? chr(65 + $index)));
            $text = sanitize_text_field((string) ($choice['text'] ?? '')); 

            if ($text === '') {
                continue;
            }

            $result[] = ['id' => $id, 'text' => $text];
        }

        return array_slice($result, 0, 5);
    }
}
