<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_group'    => 'boolean',
        'show_number' => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['pack_id'])) {
            $query->where('pack_items.pack_id', $filters['pack_id']);
        }
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function pack()
    {
        return $this->belongsTo(Pack::class);
    }

    public function parent()
    {
        return $this->belongsTo(PackItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PackItem::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function isGroup(): bool
    {
        return (bool) $this->is_group;
    }

    public function level(): int
    {
        return $this->parent_id ? 1 : 0;
    }

    /**
     * Flatten items of a pack in display order with computed display_no + level.
     * Rules (max 2 level):
     * - Top level with show_number=true gets 1,2,3...
     * - Top group with show_number=false gets '' (e.g. "Part 1 :"), children restart 1,2,3
     * - Children of numbered group get a,b,c...
     * - Children of unnumbered group get 1,2,3 (restart per group)
     * - Loose child item (is_group=false as child) follows same alpha/numeric rule.
     *
     * @return array<int, array{model: PackItem, level: int, display_no: string}>
     */
    public static function flattenedFor(Pack $pack): array
    {
        $tops = $pack->items()->topLevel()->with('children')->get();

        // Fallback for legacy rows where sort_order all 0 (order by id)
        $rows = [];
        $topCounter = 1;
        foreach ($tops as $top) {
            $topNo = $top->show_number ? (string) $topCounter++ : '';
            $rows[] = ['model' => $top, 'level' => 0, 'display_no' => $topNo];

            $children = $top->children->sortBy([['sort_order', 'asc'], ['id', 'asc']])->values();
            if ($children->isEmpty()) {
                continue;
            }
            // Children of numbered parent -> alpha; of unnumbered parent -> numeric restart
            if ($top->show_number) {
                foreach ($children as $ci => $child) {
                    $rows[] = [
                        'model'      => $child,
                        'level'      => 1,
                        'display_no' => $child->show_number ? self::alpha($ci) : '',
                    ];
                }
            } else {
                $cc = 1;
                foreach ($children as $child) {
                    $rows[] = [
                        'model'      => $child,
                        'level'      => 1,
                        'display_no' => $child->show_number ? (string) $cc++ : '',
                    ];
                }
            }
        }

        return $rows;
    }

    public static function alpha(int $zeroBasedIndex): string
    {
        // 0->a, 25->z, 26->aa (practically never exceeds z)
        $n = $zeroBasedIndex;
        $s = '';
        do {
            $s = chr(97 + ($n % 26)) . $s;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);

        return $s;
    }
}
