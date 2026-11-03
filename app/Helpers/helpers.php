<?php

/**
 * Helper functions untuk UTS Praktikum Pemrograman Web (Paket 2 Tipe B).
 */

if (!function_exists('statusJumlah')) {
    /**
     * Menentukan status jumlah alat.
     * Jika jumlah_alat >= 3: "Banyak"
     * Jika jumlah_alat < 3: "Sedikit"
     *
     * @param int|float|string $jumlah_alat
     * @return string
     */
    function statusJumlah($jumlah_alat): string
    {
        $jumlah = (int) $jumlah_alat;
        if ($jumlah >= 3) {
            return 'Banyak';
        }
        return 'Sedikit';
    }
}
