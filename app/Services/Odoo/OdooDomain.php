<?php

namespace App\Services\Odoo;

/**
 * Builder kecil untuk domain Odoo (prefix notation).
 *
 * Odoo memakai prefix '|': OR(A,B) = ['|', A, B],
 * OR(A,B,C) = ['|', '|', A, B, C]. Helper ini menghindari
 * susunan '|' manual yang rawan salah di tiap service.
 */
final class OdooDomain
{
    public static function ilike(string $field, string $value): array
    {
        return [$field, 'ilike', $value];
    }

    /**
     * @param  array<int, array|string>  $conditions
     */
    public static function or(array ...$conditions): array
    {
        $conditions = array_values(array_filter($conditions, fn ($c) => $c !== [] && $c !== ''));

        if (count($conditions) < 2) {
            return $conditions === [] ? [] : [$conditions[0]];
        }

        return [...array_fill(0, count($conditions) - 1, '|'), ...$conditions];
    }

    /**
     * AND di Odoo implisit (koma), jadi cukup gabungkan.
     *
     * Hasil or() (mis. ['|', A, B]) ikut di-flat agar domain tetap
     * satu level — Odoo melempar `unhashable type: 'list'` bila ada
     * kondisi yang tersarang sebagai satu elemen.
     *
     * @param  array<int, array|string>  $parts
     */
    public static function and(array|string ...$parts): array
    {
        $domain = [];

        foreach ($parts as $part) {
            if ($part === [] || $part === '' || $part === null) {
                continue;
            }

            if (is_array($part) && array_is_list($part) && isset($part[0])
                && (is_array($part[0]) || in_array($part[0], ['|', '&', '!'], true))
            ) {
                array_push($domain, ...$part);
            } else {
                $domain[] = $part;
            }
        }

        return $domain;
    }
}
