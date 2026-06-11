<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DosenSeeder
 * 55 dosen dari file Excel jadwal PSTI Ganjil 2023/2024
 * Password default: dosen123 (minta dosen ganti setelah login)
 * Jalankan: php artisan db:seed --class=DosenSeeder
 */
class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $dosenList = [
            [
                'name'          => 'Abdul Haris Junus Ontowirjo, ST, MT',
                'email'         => 'abdul.haris.junus.ontowirjo@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ade Yusupa, S.Pd, M.Kom.',
                'email'         => 'ade.yusupa@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Agustinus Jacobus, ST, M.Cs.',
                'email'         => 'agustinus.jacobus@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Alwin M. Sambul, ST, M.Eng, Ph.D.',
                'email'         => 'alwin.m.sambul@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Arthur M. Rumagit, ST, MT, Ph.D.',
                'email'         => 'arthur.m.rumagit@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Bernad J. D. Sitompul, S.Kom, M.Kom.',
                'email'         => 'bernad.j.d.sitompul@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Brave A. Sugiarso, ST, MT.',
                'email'         => 'brave.a.sugiarso@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dirko G. S Ruindengan, ST, M.Eng.',
                'email'         => 'dirko.g.s.ruindengan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dirko G. S. Ruindengan, ST, M.Eng.',
                'email'         => 'dirko.g.s.ruindengan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dirko G. S. Ruindungan, ST, M.Eng.',
                'email'         => 'dirko.g.s.ruindungan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dr.Eng Sary D. E. Paturusi, ST, M.Eng',
                'email'         => 'eng.sary.d.e.paturusi@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dr.Eng Steven R. Sentinuwo, ST, MTI.',
                'email'         => 'eng.steven.r.sentinuwo@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dr.Eng. Ir. Vecky C. Poekoel, ST, MT.',
                'email'         => 'vecky.c.poekoel@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dr.Eng. Markus Umboh, ST, MT.',
                'email'         => 'markus.umboh@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dr.Eng. Sary D. E. Paturusi, ST, M.Eng',
                'email'         => 'sary.d.e.paturusi@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dr.Eng. Steven R. Sentinuwo, ST, MTI.',
                'email'         => 'steven.r.sentinuwo@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Dringhuzen J. Mamahit, ST, M.Eng.',
                'email'         => 'dringhuzen.j.mamahit@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Feisy D. Kambey, ST, MT.',
                'email'         => 'feisy.d.kambey@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Fransisca J. Pontoh, ST, MT.',
                'email'         => 'fransisca.j.pontoh@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Harni Seven Adinata, S.Kom, M.Kom.',
                'email'         => 'harni.seven.adinata@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Heilbert A. Mapaly, S.Kom, M.Eng.',
                'email'         => 'heilbert.a.mapaly@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Hengky Luntungan, ST, MT.',
                'email'         => 'hengky.luntungan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Henry V. F. Kainde, ST, MT.',
                'email'         => 'henry.v.f.kainde@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Henry Valent Kainde, ST, MT.',
                'email'         => 'henry.valent.kainde@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ir. Arie S. M. Lumenta, ST, MT.',
                'email'         => 'arie.s.m.lumenta@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ir. Benefit S. Narasiang, MT.',
                'email'         => 'benefit.s.narasiang@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ir. Daniel F. Sengkey, ST, M.Eng',
                'email'         => 'daniel.f.sengkey@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ir. Hans F. Wowor, M.Kom.',
                'email'         => 'hans.f.wowor@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ir. Pinrolinvic D. K. Manembu, ST, MT.',
                'email'         => 'pinrolinvic.d.k.manembu@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ir. S. T. G. Kaunang, MT, Ph.D.',
                'email'         => 's.t.g.kaunang@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ir. Suryono, MT.',
                'email'         => 'suryono@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Jimmy R. Robot, ST, MTI.',
                'email'         => 'jimmy.r.robot@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Kenneth Y. R. Palilingan, ST, MT.',
                'email'         => 'kenneth.y.r.palilingan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'LPPM UNSRAT',
                'email'         => 'lppm.unsrat@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'M. Dwisnanto Putro, ST, M.Eng, Ph.D',
                'email'         => 'm.dwisnanto.putro@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Meicsy E. I. Najoan, ST, MT.',
                'email'         => 'meicsy.e.i.najoan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Nancy J. Tuturoong, ST, M.Kom.',
                'email'         => 'nancy.j.tuturoong@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Oktavian A. Lantang, ST, MTI, Ph.D.',
                'email'         => 'oktavian.a.lantang@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Pdt. Dina Pontoh, MTh.',
                'email'         => 'dina.pontoh@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Prodi',
                'email'         => 'prodi@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Pst. Dr. Johanis Josep Montolalu, Pr.',
                'email'         => 'johanis.josep.montolalu@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Pujo H. Saputro, S.Kom, MT',
                'email'         => 'pujo.h.saputro@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Pujo Hari Saputro, S.Kom, MT',
                'email'         => 'pujo.hari.saputro@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Reinhard Komansilan, S.Kom, M.Kom',
                'email'         => 'reinhard.komansilan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Rendy Syahputra, S.Kom, M.Kom.',
                'email'         => 'rendy.syahputra@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Rizal Sengkey, ST, MT.',
                'email'         => 'rizal.sengkey@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Salvius P. Lengkong, S.Pd, M.Eng.',
                'email'         => 'salvius.p.lengkong@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Sherwin R. U. A. Sompie, ST, MT.',
                'email'         => 'sherwin.r.u.a.sompie@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Victor Tarigan, S.Kom, M.Kom',
                'email'         => 'victor.tarigan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Virginia Tulenan, S.Kom, MTI.',
                'email'         => 'virginia.tulenan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Wahyuni F. Zalmi, S.Kom, M.Kom',
                'email'         => 'wahyuni.f.zalmi@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Ws. Sofyan Jimmy Yosadi, SH.',
                'email'         => 'sofyan.jimmy.yosadi@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Xaverius B. N. Najoan, ST, MT.',
                'email'         => 'xaverius.b.n.najoan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Yaulie D. Y. Rindengan, ST, MM, MSc.',
                'email'         => 'yaulie.d.y.rindengan@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
            [
                'name'          => 'Yuri V. Akay, S.Pd, MT.',
                'email'         => 'yuri.v.akay@unsrat.ac.id',
                'role'          => 'dosen',
                'is_active'     => true,
            ],
        ];

        $ditambah = 0;
        $dilewati = 0;
        foreach ($dosenList as $d) {
            $existing = User::where('email', $d['email'])
                ->orWhere('name', $d['name'])
                ->first();
            if ($existing) { $dilewati++; continue; }

            User::create(array_merge($d, [
                'password' => Hash::make('dosen123'),
            ]));
            $ditambah++;
        }

        $this->command->info("DosenSeeder: {$ditambah} dosen ditambahkan, {$dilewati} dilewati (sudah ada).");
        $this->command->info("Password default semua dosen: dosen123");
    }
}
