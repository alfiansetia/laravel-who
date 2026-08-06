<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinEdar extends Model
{
    protected $fillable = [
        'kategori',
        'nomor_izin_edar',
        'tgl_terbit',
        'tgl_exp',
        'merk',
        'jenis_produk',
        'pendaftar',
        'alamat_pendaftar',
        'pabrik',
        'alamat_pabrik',
        'sub_kategori',
        'kelompok_produk',
        'tipe',
        'kelas',
        'kelas_resiko',
        'pabrik2',
    ];

    protected $casts = [
        'tgl_terbit' => 'date',
        'tgl_exp' => 'date',
    ];

    /**
     * Check if this izin edar is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->tgl_exp && $this->tgl_exp->isPast();
    }

    /**
     * Check if this izin edar will expire soon (within 30 days).
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->tgl_exp && !$this->tgl_exp->isPast() && $this->tgl_exp->diffInDays(now()) <= 30;
    }

    /**
     * Scope: filter by kategori.
     */
    public function scopeKategori($query, ?string $kategori)
    {
        if ($kategori) {
            $query->where('kategori', $kategori);
        }
        return $query;
    }

    /**
     * Scope: search by keyword (nomor, merk, pendaftar).
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_izin_edar', 'like', "%{$keyword}%")
                  ->orWhere('merk', 'like', "%{$keyword}%")
                  ->orWhere('pendaftar', 'like', "%{$keyword}%")
                  ->orWhere('jenis_produk', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }
}
