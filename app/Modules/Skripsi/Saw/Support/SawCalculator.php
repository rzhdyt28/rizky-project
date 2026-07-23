<?php

namespace App\Modules\Skripsi\Saw\Support;

/**
 * Algoritma SAW (Simple Additive Weighting) murni, tanpa Eloquent, supaya
 * gampang di-unit-test dan polanya bisa direuse (dengan rumus beda) oleh
 * AHP/TOPSIS nanti.
 *
 * Input:
 *   $criteria    = [['id' => 1, 'weight' => 0.3, 'type' => 'benefit'], ...]
 *   $alternatives = [1 => 'Alternatif A', 2 => 'Alternatif B', ...]   (id => name)
 *   $matrix      = [alternativeId => [criterionId => nilai_mentah, ...], ...]
 *
 * Output: array terurut desc berdasarkan skor, tiap item:
 *   ['alternative_id' => .., 'name' => .., 'score' => .., 'rank' => ..]
 */
class SawCalculator
{
    public function calculate(array $criteria, array $alternatives, array $matrix): array
    {
        $totalWeight = array_sum(array_column($criteria, 'weight')) ?: 1;

        // 1) Cari nilai max/min tiap kriteria di seluruh alternatif.
        $max = $min = [];
        foreach ($criteria as $criterion) {
            $values = array_map(
                fn ($altId) => (float) ($matrix[$altId][$criterion['id']] ?? 0),
                array_keys($alternatives)
            );
            $max[$criterion['id']] = $values !== [] ? max($values) : 0;
            $min[$criterion['id']] = $values !== [] ? min($values) : 0;
        }

        // 2) Normalisasi + kalikan bobot (dinormalisasi agar total = 1), lalu jumlahkan.
        $results = [];
        foreach ($alternatives as $altId => $name) {
            $score = 0.0;

            foreach ($criteria as $criterion) {
                $raw = (float) ($matrix[$altId][$criterion['id']] ?? 0);
                $normalizedWeight = $criterion['weight'] / $totalWeight;

                if ($criterion['type'] === 'cost') {
                    $normalized = $raw > 0 ? ($min[$criterion['id']] / $raw) : 0;
                } else {
                    $normalized = $max[$criterion['id']] > 0 ? ($raw / $max[$criterion['id']]) : 0;
                }

                $score += $normalized * $normalizedWeight;
            }

            $results[] = ['alternative_id' => $altId, 'name' => $name, 'score' => round($score, 6)];
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        foreach ($results as $i => &$row) {
            $row['rank'] = $i + 1;
        }

        return $results;
    }
}
