<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Appointment;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_data_can_be_retrieved(): void
    {
        $barber = Barber::create([
            'name' => 'John Doe',
            'specialties' => ['Corte Clásico'],
            'working_days' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'],
            'start_time' => '09:00',
            'end_time' => '18:00',
            'lunch_start_time' => '13:00',
            'lunch_end_time' => '14:00',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Corte Clásico',
            'category' => 'Corte',
            'description' => 'Corte de cabello tradicional',
            'price' => 1500,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $response = $this->get('/api/booking/data');
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'John Doe']);
        $response->assertJsonFragment(['name' => 'Corte Clásico']);
    }

    public function test_can_create_a_booking(): void
    {
        $barber = Barber::create([
            'name' => 'Jane Doe',
            'specialties' => ['Fade'],
            'working_days' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'],
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Fade',
            'price' => 2000,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $appointmentData = [
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'customer_name' => 'Carlos Cliente',
            'customer_phone' => '1234567890',
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '10:00',
            'total_price' => 2000,
        ];

        $response = $this->postJson('/api/booking/store', $appointmentData);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'appointment_id', 'message']);
        
        $this->assertDatabaseHas('appointments', [
            'customer_name' => 'Carlos Cliente',
            'barber_id' => $barber->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_can_cancel_a_booking(): void
    {
        $barber = Barber::create([
            'name' => 'Jane Doe',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Fade',
            'price' => 2000,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $appointment = Appointment::create([
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'customer_name' => 'Carlos Cliente',
            'customer_phone' => '1234567890',
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '10:00',
            'end_time' => '10:45',
            'total_price' => 2000,
            'status' => 'confirmado'
        ]);

        $response = $this->postJson('/api/booking/cancel', [
            'appointment_id' => $appointment->id
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelado'
        ]);
    }
}
