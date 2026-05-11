<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivateSeederPrivacyTest extends TestCase
{
    public function test_gitignore_protege_seeders_privados_y_fuentes_reales(): void
    {
        $gitignore = (string) file_get_contents(base_path('.gitignore'));

        $this->assertStringContainsString('/database/seeders/private/*', $gitignore);
        $this->assertStringContainsString('!/database/seeders/private/.gitkeep', $gitignore);
        $this->assertStringContainsString('!/database/seeders/private/README.md', $gitignore);
        $this->assertStringContainsString('/docs/Abaco/excelsactualizados/**/*.xlsx', $gitignore);
        $this->assertStringContainsString('/docs/Abaco/excelsactualizados/**/*.docx', $gitignore);
    }

    public function test_private_seeders_readme_exists(): void
    {
        $this->assertFileExists(base_path('database/seeders/private/README.md'));
    }
}
