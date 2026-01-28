<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Nettoyer le rate limiter avant chaque test
        RateLimiter::clear('test@example.com|127.0.0.1');
    }

    /** @test */
    public function la_page_de_login_saffiche_correctement()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Connexion');
    }

    /** @test */
    public function un_utilisateur_actif_peut_se_connecter()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'role' => 'user'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');
    }

    /** @test */
    public function un_utilisateur_inactif_ne_peut_pas_se_connecter()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 'inactive'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $response->assertSee('Votre compte n\'est pas actif');
    }

    /** @test */
    public function la_connexion_echoue_avec_mauvais_identifiants()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'mauvais-password'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function lemail_est_requis()
    {
        $response = $this->post('/login', [
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function le_mot_de_passe_est_requis()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com'
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function lemail_doit_etre_valide()
    {
        $response = $this->post('/login', [
            'email' => 'email-invalide',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function remember_me_fonctionne()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => 'on'
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

    /** @test */
    public function rate_limiting_bloque_apres_5_tentatives()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active'
        ]);

        // 5 tentatives échouées
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'mauvais-password'
            ]);
        }

        // 6ème tentative doit être bloquée
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function admin_est_redirige_vers_dashboard()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'role' => 'admin'
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success', 'Bienvenue Admin !');
    }

    /** @test */
    public function manager_est_redirige_vers_dashboard()
    {
        $manager = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'role' => 'manager'
        ]);

        $response = $this->post('/login', [
            'email' => 'manager@example.com',
            'password' => 'password'
        ]);

        $this->assertAuthenticatedAs($manager);
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success', 'Bienvenue Manager !');
    }

    /** @test */
    public function la_session_est_regeneree_apres_connexion()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active'
        ]);

        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $newSessionId = session()->getId();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /** @test */
    public function utilisateur_non_authentifie_peut_acceder_a_login()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function utilisateur_authentifie_est_redirige_depuis_login()
    {
        $user = User::factory()->create(['status' => 'active']);
        
        $this->actingAs($user);
        
        $response = $this->get('/login');
        
        $response->assertRedirect('/dashboard');
    }
}