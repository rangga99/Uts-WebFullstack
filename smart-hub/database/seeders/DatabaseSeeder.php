<?php

namespace Database\Seeders;

// File: database/seeders/DatabaseSeeder.php

use App\Models\Booking;
use App\Models\Equipment;
use App\Models\EquipmentCheckout;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------------
        // USERS
        // ------------------------------------------------------------------
        $admin = User::create([
            'name'              => 'Admin SmartHub',
            'email'             => 'admin@smarthub.com',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'phone'             => '081234560001',
            'membership_number' => 'ADM-001',
            'is_active'         => true,
        ]);

        $member1 = User::create([
            'name'              => 'Budi Santoso',
            'email'             => 'budi@example.com',
            'password'          => Hash::make('password'),
            'role'              => 'member',
            'phone'             => '081234560010',
            'membership_number' => 'MBR-2025-001',
            'is_active'         => true,
        ]);

        $member2 = User::create([
            'name'              => 'Sari Dewi',
            'email'             => 'sari@example.com',
            'password'          => Hash::make('password'),
            'role'              => 'member',
            'phone'             => '081234560011',
            'membership_number' => 'MBR-2025-002',
            'is_active'         => true,
        ]);

        // ------------------------------------------------------------------
        // ROOMS
        // ------------------------------------------------------------------
        $studioA = Room::create([
            'name'           => 'Studio A — Photography',
            'code'           => 'STUDIO-A',
            'type'           => 'studio',
            'capacity'       => 8,
            'description'    => 'Studio foto profesional dengan backdrop dan pencahayaan lengkap.',
            'facilities'     => ['Backdrop putih', 'Backdrop hitam', 'Ring light', 'Reflector', 'AC'],
            'price_per_hour' => 75000,
            'is_available'   => true,
        ]);

        $studioB = Room::create([
            'name'           => 'Studio B — Podcast & Audio',
            'code'           => 'STUDIO-B',
            'type'           => 'studio',
            'capacity'       => 4,
            'description'    => 'Ruang rekaman audio terisolasi bunyi.',
            'facilities'     => ['Soundproofing', 'Mixer', 'Microphone stand', 'Headphone monitor', 'AC'],
            'price_per_hour' => 60000,
            'is_available'   => true,
        ]);

        Room::create([
            'name'           => 'Co-Working Space',
            'code'           => 'COWORK-01',
            'type'           => 'workspace',
            'capacity'       => 20,
            'description'    => 'Ruang kerja terbuka dengan meja dan kursi ergonomis.',
            'facilities'     => ['WiFi 100Mbps', 'Proyektor', 'Whiteboard', 'AC', 'Colokan listrik'],
            'price_per_hour' => 25000,
            'is_available'   => true,
        ]);

        Room::create([
            'name'           => 'Meeting Room — Inovasi',
            'code'           => 'MTG-01',
            'type'           => 'meeting',
            'capacity'       => 10,
            'description'    => 'Ruang rapat dengan fasilitas presentasi.',
            'facilities'     => ['TV 65"', 'HDMI', 'Whiteboard', 'AC', 'Teleconference'],
            'price_per_hour' => 50000,
            'is_available'   => true,
        ]);

        // ------------------------------------------------------------------
        // EQUIPMENT
        // ------------------------------------------------------------------
        $camera1 = Equipment::create([
            'name'           => 'Canon EOS R5',
            'code'           => 'CAM-001',
            'category'       => 'camera',
            'brand'          => 'Canon',
            'model'          => 'EOS R5',
            'serial_number'  => 'SN-CANON-R5-001',
            'condition'      => 'excellent',
            'status'         => 'available',
            'purchase_date'  => '2023-06-15',
            'purchase_price' => 45000000,
            'location'       => 'Cabinet A-1',
        ]);

        $camera2 = Equipment::create([
            'name'           => 'Sony A7 III',
            'code'           => 'CAM-002',
            'category'       => 'camera',
            'brand'          => 'Sony',
            'model'          => 'Alpha A7 III',
            'serial_number'  => 'SN-SONY-A7III-001',
            'condition'      => 'good',
            'status'         => 'available',
            'purchase_date'  => '2022-03-10',
            'purchase_price' => 32000000,
            'location'       => 'Cabinet A-1',
        ]);

        $mic1 = Equipment::create([
            'name'           => 'Rode NT1-A Microphone',
            'code'           => 'AUD-001',
            'category'       => 'audio',
            'brand'          => 'Rode',
            'model'          => 'NT1-A',
            'serial_number'  => 'SN-RODE-NT1A-001',
            'condition'      => 'excellent',
            'status'         => 'available',
            'location'       => 'Cabinet B-1',
        ]);

        Equipment::create([
            'name'          => 'Godox SL-60W LED Light',
            'code'          => 'LIT-001',
            'category'      => 'lighting',
            'brand'         => 'Godox',
            'model'         => 'SL-60W',
            'condition'     => 'good',
            'status'        => 'available',
            'location'      => 'Studio A Storage',
        ]);

        Equipment::create([
            'name'          => 'MacBook Pro 16" M3',
            'code'          => 'COM-001',
            'category'      => 'computer',
            'brand'         => 'Apple',
            'model'         => 'MacBook Pro 16" M3',
            'serial_number' => 'SN-APPLE-MBP-001',
            'condition'     => 'excellent',
            'status'        => 'available',
            'location'      => 'Cabinet C-1',
        ]);

        // ------------------------------------------------------------------
        // SAMPLE BOOKING
        // ------------------------------------------------------------------
        $booking = Booking::create([
            'booking_code'   => 'BK-20250512-001',
            'user_id'        => $member1->id,
            'room_id'        => $studioA->id,
            'start_datetime' => now()->addDay()->setHour(10)->setMinute(0),
            'end_datetime'   => now()->addDay()->setHour(13)->setMinute(0),
            'duration_hours' => 3,
            'total_price'    => 3 * $studioA->price_per_hour,
            'status'         => 'confirmed',
            'confirmed_by'   => $admin->id,
            'confirmed_at'   => now(),
        ]);

        // ------------------------------------------------------------------
        // SAMPLE CHECKOUT
        // ------------------------------------------------------------------
        EquipmentCheckout::create([
            'checkout_code'      => 'CO-20250512-001',
            'user_id'            => $member2->id,
            'equipment_id'       => $mic1->id,
            'checked_out_at'     => now()->subHours(2),
            'expected_return_at' => now()->addHours(6),
            'status'             => 'active',
            'condition_before'   => 'excellent',
            'notes_checkout'     => 'Untuk recording podcast episode 5',
            'processed_by'       => $admin->id,
        ]);

        // Update mic status to checked_out
        $mic1->update(['status' => 'checked_out']);

        $this->command->info('✅ SmartHub seeded: 3 users, 4 rooms, 5 equipment, 1 booking, 1 checkout');
    }
}
