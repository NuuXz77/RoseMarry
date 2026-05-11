<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sale Model
 * 
 * Header/induk dari transaksi penjualan di kasir.
 * Hanya Students (siswa kasir) yang bisa melakukan transaksi di POS.
 * Pelanggan bersifat optional untuk transaksi dengan pelanggan umum (tidak terdaftar).
 */
class Sales extends Model
{
    public const ORDER_STATUS_TAKE_AWAY = 'Take away';
    public const ORDER_STATUS_DINE_IN = 'Dine in';
    public const PRODUCTION_STATUS_PENDING = 'pending';
    public const PRODUCTION_STATUS_COOKING = 'cooking';
    public const PRODUCTION_STATUS_DONE = 'done';
    public const PRODUCTION_STATUS_DELIVERED = 'delivered';
    public const PRODUCTION_STATUS_COMPLETED = 'completed';

    public const ORDER_STATUSES = [
        self::ORDER_STATUS_TAKE_AWAY,
        self::ORDER_STATUS_DINE_IN,
    ];

    protected $fillable = [
        'invoice_number',    // Nomor invoice unik
        'queue_number',      // Nomor antrean take away (YYYYMMDD-XXX)
        'customer_id',       // FK ke customers (optional, untuk pelanggan umum)
        'guest_name',        // Nama pembeli jika Guest (tidak terdaftar)
        'table_number',      // Nomor meja (optional)
        'status_order',      // Enum: 'Take away', 'Dine in'
        'production_status', // Enum: 'pending', 'cooking', 'done', 'delivered', 'completed'
        'shift_id',          // FK ke shifts (shift saat transaksi)
        'cashier_student_id',// FK ke students (siswa kasir)
        'subtotal',          // Subtotal belanja
        'tax_amount',        // Jumlah pajak
        'discount_amount',   // Jumlah diskon
        'total_amount',      // Total akhir (subtotal - discount + tax)
        'paid_amount',       // Jumlah yang dibayar
        'change_amount',     // Kembalian
        'payment_method',    // Enum: 'cash', 'qris', 'transfer'
        'status',            // Enum: 'paid', 'unpaid', 'cancelled'
        'catatan',           // Catatan dari pelanggan
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function isDineIn(): bool
    {
        return $this->status_order === self::ORDER_STATUS_DINE_IN;
    }

    public function getServiceIdentityAttribute(): string
    {
        $guestName = trim((string) $this->guest_name);
        $customerName = trim((string) ($this->customer?->name ?? ''));

        if ($this->status_order === self::ORDER_STATUS_TAKE_AWAY) {
            if ($guestName !== '') {
                return $guestName;
            }

            if ($customerName !== '') {
                return $customerName;
            }

            return $this->queue_number ?: 'Tamu';
        }

        return $customerName !== '' ? $customerName : ($guestName !== '' ? $guestName : 'Tamu');
    }

    public function getProductionStatusLabelAttribute(): string
    {
        return match ($this->production_status) {
            self::PRODUCTION_STATUS_COOKING => 'Sedang Diproses',
            self::PRODUCTION_STATUS_DONE => 'Selesai Masak',
            self::PRODUCTION_STATUS_DELIVERED => 'Diantar',
            self::PRODUCTION_STATUS_COMPLETED => 'Selesai',
            default => 'Menunggu',
        };
    }

    /**
     * Relasi Many-to-One ke Customers
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customers::class);
    }

    /**
     * Relasi Many-to-One ke Shifts
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relasi Many-to-One ke Students (cashier_student_id)
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Students::class, 'cashier_student_id');
    }

    /**
     * Relasi One-to-Many ke SaleItems
     * Detail produk yang terjual dalam transaksi ini
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItems::class, 'sale_id');
    }

    /**
     * Scope: Hanya transaksi yang sudah dibayar
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: Filter berdasarkan rentang tanggal
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    }
}
