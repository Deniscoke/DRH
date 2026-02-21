<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_State_Repository
{
    public function get_state(string $player_id, string $campaign_id): array
    {
        $key = $this->state_key($player_id, $campaign_id);
        $state = get_option($key, null);

        if (is_array($state)) {
            return $state;
        }

        $initial = $this->default_state();
        $this->save_state($player_id, $campaign_id, $initial);

        return $initial;
    }

    public function save_state(string $player_id, string $campaign_id, array $state): void
    {
        update_option($this->state_key($player_id, $campaign_id), $state, false);
    }

    public function apply_patch(array $state, array $patch): array
    {
        $state['hp']['current'] = $this->clamp_resource(
            (int) ($patch['hp'] ?? $state['hp']['current']),
            0,
            (int) $state['hp']['max']
        );

        $state['mana']['current'] = $this->clamp_resource(
            (int) ($patch['mana'] ?? $state['mana']['current']),
            0,
            (int) $state['mana']['max']
        );

        $state['xp'] = max(0, (int) ($patch['xp'] ?? $state['xp']));

        $state['inventory'] = $this->merge_set(
            (array) $state['inventory'],
            (array) ($patch['inventory_add'] ?? []),
            (array) ($patch['inventory_remove'] ?? [])
        );

        $state['flags'] = $this->merge_set(
            (array) $state['flags'],
            (array) ($patch['flags_add'] ?? []),
            (array) ($patch['flags_remove'] ?? [])
        );

        return $state;
    }

    public function append_log(array $state, string $actor, string $message): array
    {
        $state['log_history'][] = [
            'ts' => gmdate('c'),
            'actor' => $actor,
            'message' => wp_strip_all_tags($message),
        ];

        if (count($state['log_history']) > 60) {
            $state['log_history'] = array_slice($state['log_history'], -60);
        }

        return $state;
    }

    private function default_state(): array
    {
        return [
            'hp' => ['current' => 12, 'max' => 12],
            'mana' => ['current' => 6, 'max' => 6],
            'xp' => 0,
            'level' => 1,
            'inventory' => [],
            'status_effects' => [],
            'flags' => [],
            'log_history' => [],
        ];
    }

    private function state_key(string $player_id, string $campaign_id): string
    {
        return 'dd_ai_rpg_state_' . md5($player_id . '|' . $campaign_id);
    }

    private function clamp_resource(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    private function merge_set(array $base, array $add, array $remove): array
    {
        $base = array_values(array_unique(array_filter(array_map('sanitize_text_field', $base))));

        foreach ($add as $item) {
            $sanitized = sanitize_text_field((string) $item);
            if ($sanitized !== '' && !in_array($sanitized, $base, true)) {
                $base[] = $sanitized;
            }
        }

        if (!empty($remove)) {
            $remove = array_map(static fn($item) => sanitize_text_field((string) $item), $remove);
            $base = array_values(array_filter($base, static fn($item) => !in_array($item, $remove, true)));
        }

        return $base;
    }
}
