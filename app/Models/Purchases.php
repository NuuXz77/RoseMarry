<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Purchase Model
 * 
 * Header/induk dari transaksi pembelian material dari supplier.
 * Hanya User/Staf yang bisa membuat pembelian.
 */
class Purchases extends Model
{
    protected $fillable = [
        'supplier_id',    // FK ke suppliers
        'guest_supplier', // Nama supplier manual jika tidak ada di data master
        'invoice_number', // Nomor invoice unik
        'date',           // Tanggal pembelian
        'total_amount',   // Total jumlah
        'status',         // Enum: 'received', 'pending', 'cancelled'
        'notes',          // Catatan tambahan
        'created_by',     // FK ke users (hanya user)
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relasi Many-to-One ke Suppliers
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }

    /**
     * Relasi Many-to-One ke Users
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi One-to-Many ke Purchase Items
     * Detail material yang dibeli dalam transaksi ini
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItems::class, 'purchase_id');
    }
}
