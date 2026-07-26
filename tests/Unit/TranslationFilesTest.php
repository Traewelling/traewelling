<?php

declare(strict_types=1);

namespace Tests\Unit;

class TranslationFilesTest extends UnitTestCase
{
    /**
     * Translations must be plain text. Markup belongs into the templates,
     * otherwise it ends up being rendered with v-html or {!! !!}.
     */
    public function test_translation_files_do_not_contain_markup(): void
    {
        $offenders = [];

        foreach (glob(base_path('lang/*.json')) as $file) {
            $translations = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

            foreach ($translations as $key => $value) {
                if (is_string($value) && preg_match('/<[a-zA-Z\/!][^>]*>/', $value) === 1) {
                    $offenders[] = basename($file) . ': ' . $key;
                }
            }
        }

        $this->assertSame([], $offenders, 'Translations must not contain HTML.');
    }
}
