<?php

namespace App\Services;

/**
 * SlotWaktuMapper
 * Konversi slot waktu (1-2, 4-5, dst) ke jam nyata
 * Sesuaikan array $slots dengan jadwal kampus Anda
 */
class SlotWaktuMapper
{
    /**
     * Mapping slot ke jam
     * Key   = nomor slot
     * Value = ['mulai' => 'HH:MM', 'selesai' => 'HH:MM']
     */
    private static array $slots = [
    1  => ['mulai' => '08:00', 'selesai' => '08:50'],
    2  => ['mulai' => '08:50', 'selesai' => '09:40'],
    3  => ['mulai' => '09:40', 'selesai' => '10:30'],
    4  => ['mulai' => '10:30', 'selesai' => '11:20'],
    5  => ['mulai' => '11:20', 'selesai' => '12:10'],

    // Istirahat
    6  => ['mulai' => '13:00', 'selesai' => '13:50'],
    7  => ['mulai' => '13:50', 'selesai' => '14:40'],
    8  => ['mulai' => '14:40', 'selesai' => '15:30'],
    9  => ['mulai' => '15:30', 'selesai' => '16:20'],
    10 => ['mulai' => '16:20', 'selesai' => '17:10'],
    11 => ['mulai' => '17:10', 'selesai' => '18:00'],
];

    /**
     * Parse slot waktu dari berbagai format:
     * - "1 - 2", "4-5", "1–2"   (slot angka)
     * - "1"                       (slot tunggal)
     * - "08:00 - 10:30"           (jam langsung)
     * - "08:00"                   (jam tunggal, selesai +50 menit)
     * Return ['jam_mulai' => '07:30', 'jam_selesai' => '09:10']
     */
    public static function parse(string $slotStr): ?array
    {
        // Bersihkan spasi
        $slotStr = trim($slotStr);

        // ── Format jam langsung: "08:00 - 10:30" atau "08:00–10:30" ──
        if (preg_match('/^(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})$/', $slotStr, $m)) {
            return [
                'jam_mulai'    => $m[1],
                'jam_selesai'  => $m[2],
                'slot_mulai'   => null,
                'slot_selesai' => null,
            ];
        }

        // ── Format jam tunggal: "08:00" ──
        if (preg_match('/^(\d{1,2}:\d{2})$/', $slotStr, $m)) {
            // Coba cocokkan dengan tabel slot
            foreach (self::$slots as $no => $s) {
                if ($s['mulai'] === $m[1]) {
                    return [
                        'jam_mulai'    => $s['mulai'],
                        'jam_selesai'  => $s['selesai'],
                        'slot_mulai'   => $no,
                        'slot_selesai' => $no,
                    ];
                }
            }
            // Tidak ada di tabel: pakai jam apa adanya, selesai +50 menit
            $ts = strtotime($m[1]);
            return [
                'jam_mulai'    => $m[1],
                'jam_selesai'  => date('H:i', $ts + 3000),
                'slot_mulai'   => null,
                'slot_selesai' => null,
            ];
        }

        // ── Pola: "1 - 2", "1-2", "1–2", "1 – 2" ──
        if (preg_match('/^(\d+)\s*[-–]\s*(\d+)$/', $slotStr, $m)) {
            $slotMulai   = (int) $m[1];
            $slotSelesai = (int) $m[2];

            if (isset(self::$slots[$slotMulai]) && isset(self::$slots[$slotSelesai])) {
                return [
                    'jam_mulai'    => self::$slots[$slotMulai]['mulai'],
                    'jam_selesai'  => self::$slots[$slotSelesai]['selesai'],
                    'slot_mulai'   => $slotMulai,
                    'slot_selesai' => $slotSelesai,
                ];
            }
        }

        // ── Pola slot tunggal: "1", "5" ──
        if (preg_match('/^(\d+)$/', $slotStr, $m)) {
            $slot = (int) $m[1];
            if (isset(self::$slots[$slot])) {
                return [
                    'jam_mulai'    => self::$slots[$slot]['mulai'],
                    'jam_selesai'  => self::$slots[$slot]['selesai'],
                    'slot_mulai'   => $slot,
                    'slot_selesai' => $slot,
                ];
            }
        }

        return null;
    }

    /**
     * Ambil semua slot beserta jam untuk ditampilkan di UI
     */
    public static function semuaSlot(): array
    {
        return self::$slots;
    }

    /**
     * Format slot untuk display: "Slot 1-2 (07:30 – 09:10)"
     */
    public static function formatDisplay(int $slotMulai, int $slotSelesai): string
    {
        if (!isset(self::$slots[$slotMulai]) || !isset(self::$slots[$slotSelesai])) {
            return "Slot {$slotMulai}-{$slotSelesai}";
        }
        $jam = self::$slots[$slotMulai]['mulai'].' – '.self::$slots[$slotSelesai]['selesai'];
        return "Slot {$slotMulai}-{$slotSelesai} ({$jam})";
    }
}
