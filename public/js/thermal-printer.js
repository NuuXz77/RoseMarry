/**
 * Thermal Printer — Web Bluetooth + ESC/POS
 * Supports 58mm (32 chars) and 80mm (48 chars) thermal printers via BLE.
 */
class ThermalPrinter {
    constructor(charPerLine = 42) {
        this.charPerLine = charPerLine;
        this.buffer = [];
        this.device = null;
        this.characteristic = null;
    }

    // ─── Low-level helpers ───────────────────────────────
    raw(...bytes) { this.buffer.push(...bytes); return this; }

    text(str) {
        const encoder = new TextEncoder();
        this.buffer.push(...encoder.encode(str));
        return this;
    }

    // ─── ESC/POS Commands ────────────────────────────────

    init() { return this.raw(0x1B, 0x40); }
    center() { return this.raw(0x1B, 0x61, 0x01); }
    left() { return this.raw(0x1B, 0x61, 0x00); }
    boldOn() { return this.raw(0x1B, 0x45, 0x01); }
    boldOff() { return this.raw(0x1B, 0x45, 0x00); }
    doubleStrikeOn() { return this.raw(0x1B, 0x47, 0x01); }
    doubleStrikeOff() { return this.raw(0x1B, 0x47, 0x00); }
    fontA() { return this.raw(0x1B, 0x4D, 0x00); } // Standard font
    fontB() { return this.raw(0x1B, 0x4D, 0x01); } // Thinner/smaller font
    doubleSize() { return this.raw(0x1D, 0x21, 0x11); }
    normalSize() { return this.raw(0x1D, 0x21, 0x00); }
    ln() { return this.raw(0x0A); }
    feed(n = 4) { return this.raw(0x1B, 0x64, n); }
    cut() { return this.raw(0x1D, 0x56, 0x42, 0x00); }

    // Reset to normal mode (Font A = bigger, standard)
    thinMode() {
        return this.fontA().boldOff().doubleStrikeOff().normalSize();
    }

    dashedLine() {
        return this.text('-'.repeat(this.charPerLine)).ln();
    }

    textLn(str) {
        while (str.length > this.charPerLine) {
            this.text(str.substring(0, this.charPerLine)).ln();
            str = str.substring(this.charPerLine);
        }
        return this.text(str).ln();
    }

    row(leftText, rightText) {
        const maxLeft = this.charPerLine - rightText.length - 1;
        if (leftText.length > maxLeft) {
            leftText = leftText.substring(0, maxLeft);
        }
        const spaces = this.charPerLine - leftText.length - rightText.length;
        return this.text(leftText + ' '.repeat(Math.max(1, spaces)) + rightText).ln();
    }

    centerTextLn(str, isDoubleSize = false) {
        const lineLen = isDoubleSize ? Math.floor(this.charPerLine / 2) : this.charPerLine;
        if (str.length >= lineLen) {
            return this.textLn(str);
        }
        const padLen = Math.floor((lineLen - str.length) / 2);
        return this.text(' '.repeat(padLen) + str).ln();
    }

    resetBuffer() { this.buffer = []; }

    getBuffer() { return new Uint8Array(this.buffer); }

    // ─── Bluetooth Connection ────────────────────────────
    async connect() {
        const serviceUUIDs = [
            '0000ff00-0000-1000-8000-00805f9b34fb',
            'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
            '000018f0-0000-1000-8000-00805f9b34fb',
        ];

        this.device = await navigator.bluetooth.requestDevice({
            acceptAllDevices: true,
            optionalServices: serviceUUIDs,
        });

        const server = await this.device.gatt.connect();

        for (const uuid of serviceUUIDs) {
            try {
                const service = await server.getPrimaryService(uuid);
                const chars = await service.getCharacteristics();
                for (const c of chars) {
                    if (c.properties.write || c.properties.writeWithoutResponse) {
                        this.characteristic = c;
                        return true;
                    }
                }
            } catch (_) { continue; }
        }

        throw new Error('Tidak ditemukan characteristic yang bisa ditulis pada printer.');
    }

    isConnected() {
        return !!(this.device?.gatt?.connected && this.characteristic);
    }

    getDeviceName() {
        return this.device?.name || 'Printer';
    }

    async send() {
        if (!this.isConnected()) throw new Error('Printer belum terhubung.');

        const data = this.getBuffer();
        const chunkSize = 100;

        for (let i = 0; i < data.length; i += chunkSize) {
            const chunk = data.slice(i, i + chunkSize);
            if (this.characteristic.properties.writeWithoutResponse) {
                await this.characteristic.writeValueWithoutResponse(chunk);
            } else {
                await this.characteristic.writeValueWithResponse(chunk);
            }
            await new Promise(r => setTimeout(r, 50));
        }

        this.resetBuffer();
    }

    // ─── Receipt Builder ─────────────────────────────────
    buildReceipt(data) {
        this.resetBuffer();
        this.init();

        // ── Header (BOLD + BESAR — hanya ini yang tebal) ──
        this.left().fontA().boldOn().doubleSize();
        this.centerTextLn(data.appName || 'TOKO', true);
        this.thinMode(); // Reset ke tipis
        if (data.appAddress) this.centerTextLn(data.appAddress);
        if (data.appTagline) this.centerTextLn(data.appTagline);
        this.left(); // Tetap left
        this.dashedLine();

        // ── Invoice Info (tipis) ──
        this.left();
        this.row('Invoice', data.invoiceNumber);
        this.row('Waktu', data.createdAt);
        this.row('Kasir', data.cashier);
        this.row('Layanan', data.statusOrder);
        this.row('Pelanggan', data.serviceIdentity);
        if (data.tableNumber) this.row('Meja', data.tableNumber);
        this.dashedLine();

        // ── Items (tipis) ──
        for (const item of data.items) {
            this.textLn(item.name.toUpperCase());
            this.row(
                '  ' + item.qty + ' x ' + this.formatRp(item.price),
                this.formatRp(item.subtotal)
            );
        }
        this.dashedLine();

        // ── Totals ──
        this.row('Subtotal', this.formatRp(data.subtotal));
        if (data.discountAmount > 0) {
            this.row('Diskon', '- ' + this.formatRp(data.discountAmount));
        }
        this.dashedLine();
        // TOTAL — bold tapi tetap Font B (48 char) supaya sejajar
        this.boldOn();
        this.row('TOTAL', this.formatRp(data.totalAmount));
        this.boldOff();
        this.dashedLine();

        // ── Payment (tipis) ──
        this.row('Metode', (data.paymentMethod || '-').toUpperCase());
        this.row('Dibayar', this.formatRp(data.paidAmount));
        if (data.changeAmount > 0) {
            this.row('Kembalian', this.formatRp(data.changeAmount));
        }
        const statusLabel = data.status === 'paid' ? 'LUNAS' : (data.status === 'unpaid' ? 'HUTANG' : 'BATAL');
        this.row('Status', statusLabel);
        this.dashedLine();

        // ── Footer (tipis) ──
        this.left();
        this.centerTextLn('Terima kasih telah');
        this.centerTextLn('berbelanja di ' + (data.appName || 'toko kami'));
        this.ln();
        this.centerTextLn(data.createdAtFull);
        this.feed(4).cut();

        return this;
    }

    formatRp(amount) {
        return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
    }
}

// Global singleton — persists across Livewire navigations
window.__thermalPrinter = window.__thermalPrinter || null;
