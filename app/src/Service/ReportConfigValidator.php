<?php

namespace App\Service;

class ReportConfigValidator
{
    private const DEFAULTS = [
        'diagramType' => 'bar',
        'xField' => 'player',
        'yField' => 'goals',
    ];

    /**
     * Validates and completes a report config array.
     * Throws \InvalidArgumentException if required fields are missing and cannot be defaulted.
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed> Validated and completed config
     */
    public function validate(array $config): array
    {
        $validated = $config;
        foreach (self::DEFAULTS as $key => $default) {
            if (!isset($validated[$key]) || '' === $validated[$key]) {
                $validated[$key] = $default;
            }
        }

        return $validated;
    }
}
