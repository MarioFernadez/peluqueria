<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Appointment;
use Carbon\Carbon;

class BookingConflictTest extends TestCase
{
    use RefreshDatabase;

    private Barber $barber;
    private Service $service30;
    private Service $service60;

    protected function setUp(): void
    {
        parent::setUp();

        $this->barber = Barber::create([
            'name' => 'Barbero Test',
            'start_time' => '08:00',
            'end_time' => '20:00',
            'working_days' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            'is_active' => true,
        ]);

        $this->service30 = Service::create([
            'name' => 'Corte Simple',
            'category' => 'Corte',
            'price' => 30000,
            'duration_min' => 30,
            'is_active' => true,
        ]);

        $this->service60 = Service::create([
            'name' => 'Corte y Barba Completo',
            'category' => 'Combo',
            'price' => 50000,
            'duration_min' => 60,
            'is_active' => true,
        ]);
    }

    public function test_cannot_book_exact_same_slot_twice(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $time = '10:00';

        // Primer turno: debe crearse exitosamente
        $res1 = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Juan Perez',
            'customer_phone'   => '0981111111',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => $time,
        ]);

        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);
        $this->assertDatabaseHas('appointments', [
            'customer_name'    => 'Juan Perez',
            'appointment_time' => $time,
        ]);

        // Segundo turno: mismo barbero, misma fecha, misma hora -> DEBE FALLAR con 409
        $res2 = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Pedro Gomez',
            'customer_phone'   => '0982222222',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => $time,
        ]);

        $res2->assertStatus(409);
        $res2->assertJson(['success' => false]);
        $this->assertDatabaseMissing('appointments', [
            'customer_name' => 'Pedro Gomez',
        ]);
    }

    public function test_cannot_book_overlapping_service(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Reservar un servicio de 60 minutos a las 14:00 (ocupa de 14:00 a 15:00)
        $res1 = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Carlos Lopez',
            'customer_phone'   => '0983333333',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service60->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '14:00',
        ]);

        $res1->assertStatus(200);

        // Intentar reservar un servicio de 30 minutos a las 14:30 (dentro del rango 14:00-15:00) -> DEBE RECHAZARSE
        $res2 = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Maria Ramos',
            'customer_phone'   => '0984444444',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '14:30',
        ]);

        $res2->assertStatus(409);
        $res2->assertJson(['success' => false]);
    }

    public function test_can_book_immediately_after_completed_window(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Turno de 14:00 a 14:30
        $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente 1',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '14:00',
        ])->assertStatus(200);

        // Turno contiguo a las 14:30 -> DEBE PERMITIRSE
        $res = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente 2',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '14:30',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
    }

    public function test_cancelled_appointment_frees_the_slot(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $time = '11:00';

        // Reservar
        $res1 = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente Que Cancela',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => $time,
        ]);

        $res1->assertStatus(200);
        $apptId = $res1->json('appointment.id');

        // Cancelar el turno
        $this->postJson('/api/booking/cancel', [
            'appointment_id' => $apptId,
        ])->assertStatus(200);

        // Nuevo cliente intenta reservar el mismo horario liberado -> DEBE PERMITIRSE
        $res2 = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Nuevo Cliente',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => $time,
        ]);

        $res2->assertStatus(200);
        $res2->assertJson(['success' => true]);
    }

    public function test_availability_endpoint_shows_occupied_slot_as_unavailable(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $time = '15:00';

        // Reservar
        $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente Existente',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => $time,
        ])->assertStatus(200);

        // Consultar disponibilidad
        $res = $this->getJson("/api/booking/availability?barber_id={$this->barber->id}&service_id={$this->service30->id}&date={$tomorrow}");
        $res->assertStatus(200);

        $availableTimes = $res->json('availableTimes');
        $this->assertNotContains($time, $availableTimes);

        $slot = collect($res->json('allSlots'))->firstWhere('time', $time);
        $this->assertNotNull($slot);
        $this->assertFalse($slot['available']);
    }

    public function test_cannot_book_longer_service_that_encloses_existing_appointment(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Turno existente de 30 min a las 10:30 (10:30 - 11:00)
        $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente Corto',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '10:30',
        ])->assertStatus(200);

        // Intento de reservar turno de 60 min a las 10:00 (10:00 - 11:00) -> choca con el de las 10:30 -> RECHAZADO
        $res = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente Largo',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service60->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '10:00',
        ]);

        $res->assertStatus(409);
        $res->assertJson(['success' => false]);
    }

    public function test_cannot_book_on_blocked_date(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        \App\Models\BlockedDate::create([
            'date' => $tomorrow,
            'reason' => 'Feriado nacional',
            'barber_id' => null, // Global
        ]);

        $res = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente Feriado',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '10:00',
        ]);

        $res->assertStatus(422);
        $res->assertJson(['success' => false]);
    }

    public function test_cannot_book_in_the_past(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $res = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Viajero del Tiempo',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $yesterday,
            'appointment_time' => '10:00',
        ]);

        $res->assertStatus(422);
        $res->assertJson(['success' => false]);
    }

    public function test_cannot_book_outside_barber_working_hours(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        // El barbero trabaja de 08:00 a 20:00. Intentar agendar a las 21:00
        $res = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente Nocturno',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '21:00',
        ]);

        $res->assertStatus(422);
        $res->assertJson(['success' => false]);
    }

    public function test_appointment_booked_yesterday_blocks_booking_made_next_morning(): void
    {
        // Simular que ayer a las 20:00 el Cliente A agendó para hoy a las 17:00
        $today = Carbon::today()->toDateString();
        $targetTime = '17:00';

        // Creamos la cita como ocurrió ayer a las 20:00
        $appointmentA = Appointment::create([
            'customer_name'    => 'Cliente A (Ayer 20hs)',
            'customer_phone'   => '0981111111',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $today,
            'appointment_time' => $targetTime,
            'total_price'      => 30000,
            'duration_min'     => 30,
            'status'           => 'Pendiente',
            'created_at'       => Carbon::yesterday()->setHour(20)->setMinute(0),
        ]);

        // Ahora simulamos que son las 07:00 de hoy por la mañana
        Carbon::setTestNow(Carbon::today()->setHour(7)->setMinute(0));

        // 1. Cliente B consulta la disponibilidad para hoy a las 17:00
        $availRes = $this->getJson("/api/booking/availability?barber_id={$this->barber->id}&service_id={$this->service30->id}&date={$today}");
        $availRes->assertStatus(200);

        // El horario 17:00 NO debe estar en availableTimes
        $this->assertNotContains($targetTime, $availRes->json('availableTimes'));

        // El slot 17:00 debe figurar como NO disponible
        $slot17 = collect($availRes->json('allSlots'))->firstWhere('time', $targetTime);
        $this->assertNotNull($slot17);
        $this->assertFalse($slot17['available']);
        $this->assertNotNull($slot17['booked']);

        // 2. Si Cliente B de todas formas intenta reservar para las 17:00 -> DEBE SER RECHAZADO con 409
        $storeRes = $this->postJson('/api/booking/store', [
            'customer_name'    => 'Cliente B (Hoy 7hs)',
            'customer_phone'   => '0982222222',
            'barber_id'        => $this->barber->id,
            'service_id'       => $this->service30->id,
            'appointment_date' => $today,
            'appointment_time' => $targetTime,
        ]);

        $storeRes->assertStatus(409);
        $storeRes->assertJson(['success' => false]);

        // Verificar que en la base de datos sigue habiendo únicamente 1 turno a las 17:00 (el del Cliente A)
        $countAt17 = Appointment::where('barber_id', $this->barber->id)
            ->whereDate('appointment_date', $today)
            ->where('appointment_time', $targetTime)
            ->whereNotIn('status', ['Cancelado', 'Cancelada'])
            ->count();

        $this->assertEquals(1, $countAt17);

        // Reset test now
        Carbon::setTestNow();
    }
}
