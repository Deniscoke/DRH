<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_Rules_Retriever
{
    public function get_relevant_chunks(string $query, string $mode = 'rules'): array
    {
        // RAG storage/retrieval can later be replaced by real embeddings/vector DB.
        $defaults = [
            'Combat checks should use d20 and compare with difficulty class.',
            'Do not apply permanent state changes without explicit, valid state patch fields.',
            'Narration should be immersive and concise, no meta commentary.',
        ];

        if ($mode === 'story') {
            return array_slice($defaults, 1, 2);
        }

        return $defaults;
    }
}
