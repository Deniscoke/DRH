<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_Dice
{
    public function roll_d20(): int
    {
        return random_int(1, 20);
    }

    public function resolve_skill_check(string $skill, int $dc): array
    {
        $result = $this->roll_d20();

        return [
            'type' => 'skill_check',
            'skill' => sanitize_text_field($skill),
            'dc' => $dc,
            'result' => $result,
            'success' => $result >= $dc,
        ];
    }
}
