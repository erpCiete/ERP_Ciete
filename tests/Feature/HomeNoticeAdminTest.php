<?php

namespace Tests\Feature;

use App\Models\HomeNotice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeNoticeAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_home_notices_have_requested_distribution_and_featured_message(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('home_notices', 10);

        $this->assertSame(3, HomeNotice::query()
            ->where('category', HomeNotice::CATEGORY_INTERNAL_NOTICE)
            ->where('is_featured', false)
            ->count());
        $this->assertSame(3, HomeNotice::query()->where('category', HomeNotice::CATEGORY_SYSTEM_UPDATE)->count());
        $this->assertSame(3, HomeNotice::query()->where('category', HomeNotice::CATEGORY_COMPANY_NEWS)->count());

        $this->assertDatabaseHas('home_notices', [
            'category' => HomeNotice::CATEGORY_INTERNAL_NOTICE,
            'title_es' => 'ERP Ciete v2.2.0 listo para demo con CIETE',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->assertSame(1, HomeNotice::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->count());
    }

    public function test_home_screen_reads_active_messages_without_exposing_admin_editing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('index'));

        $response->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Welcome')
                ->has('homeNotices.notices', 3)
                ->has('homeNotices.updates', 3)
                ->has('homeNotices.companyNews', 3)
                ->where('featuredNotice.title_es', 'ERP Ciete v2.2.0 listo para demo con CIETE'));
    }

    public function test_admin_can_manage_home_notices_and_only_one_active_featured_notice_remains(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.notices.store'), [
            'category' => 'updates',
            'title_es' => 'Control operativo de accesos',
            'body_es' => 'Se revisarán los accesos operativos del entorno antes del cierre de la jornada.',
            'title_en' => 'Operational access control',
            'body_en' => 'Operational environment access will be reviewed before the end of the day.',
            'is_active' => true,
            'is_featured' => false,
        ])->assertSessionHasNoErrors();

        $notice = HomeNotice::query()
            ->where('title_es', 'Control operativo de accesos')
            ->firstOrFail();

        $this->actingAs($admin)->put(route('admin.notices.update', $notice), [
            'category' => HomeNotice::CATEGORY_SYSTEM_UPDATE,
            'title_es' => 'Control operativo de accesos actualizado',
            'body_es' => 'Se revisarán los accesos operativos del entorno antes del cierre de la jornada.',
            'title_en' => 'Updated operational access control',
            'body_en' => 'Operational environment access will be reviewed before the end of the day.',
            'is_active' => true,
            'is_featured' => false,
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->patch(route('admin.notices.feature', $notice), [
            'is_featured' => true,
        ])->assertSessionHasNoErrors();

        $this->assertTrue($notice->fresh()->is_featured);
        $this->assertSame(1, HomeNotice::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->count());
        $this->assertDatabaseHas('home_notices', [
            'title_es' => 'ERP Ciete v2.2.0 listo para demo con CIETE',
            'is_featured' => false,
        ]);

        $this->actingAs($admin)->patch(route('admin.notices.toggle', $notice), [
            'is_active' => false,
        ])->assertSessionHasNoErrors();

        $notice->refresh();
        $this->assertFalse($notice->is_active);
        $this->assertFalse($notice->is_featured);

        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'home_notices',
            'registro_id' => $notice->getKey(),
        ]);
    }

    public function test_admin_notice_validation_requires_bilingual_content_and_category(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.notices.store'), [
            'category' => '',
            'title_es' => '',
            'body_es' => '',
            'title_en' => '',
            'body_en' => '',
        ])->assertSessionHasErrors([
            'category',
            'title_es',
            'body_es',
            'title_en',
            'body_en',
        ]);
    }
}
