<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperadminTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Crear el rol superadmin antes de cada test
        Role::create(['name' => 'superadmin']);
    }

    /** @test */
    public function superadmin_puede_aprobar_una_empresa_pendiente()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $empresa = Company::factory()->create(['status' => 'pendiente']);

        $this->actingAs($superadmin, 'sanctum')
            ->putJson("/api/superadmin/aprobar-empresa/{$empresa->id}")
            ->assertStatus(200)
            ->assertJson([
                'mensaje' => 'Empresa aprobada exitosamente.',
                'empresa' => [
                    'id' => $empresa->id,
                    'status' => 'aprobada'
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $empresa->id,
            'status' => 'aprobada'
        ]);
    }

    /** @test */
    public function superadmin_no_puede_aprobar_una_empresa_ya_aprobada()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $empresa = Company::factory()->create(['status' => 'aprobada']);

        $this->actingAs($superadmin, 'sanctum')
            ->putJson("/api/superadmin/aprobar-empresa/{$empresa->id}")
            ->assertStatus(409)
            ->assertJson([
                'mensaje' => 'La empresa ya ha sido aprobada.'
            ]);
    }

    /** @test */
    public function superadmin_puede_rechazar_una_empresa_pendiente()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $empresa = Company::factory()->create(['status' => 'pendiente']);

        $this->actingAs($superadmin, 'sanctum')
            ->putJson("/api/superadmin/rechazar-empresa/{$empresa->id}")
            ->assertStatus(200)
            ->assertJson([
                'mensaje' => 'Empresa rechazada exitosamente.',
                'empresa' => [
                    'id' => $empresa->id,
                    'status' => 'rechazada'
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $empresa->id,
            'status' => 'rechazada'
        ]);
    }

    /** @test */
    public function superadmin_puede_listar_todas_las_empresas()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        Company::factory()->count(5)->create();

        $this->actingAs($superadmin, 'sanctum')
            ->getJson('/api/superadmin/empresas/lista')
            ->assertStatus(200)
            ->assertJsonCount(5, 'empresas');
    }

    /** @test */

}
