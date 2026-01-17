import js from '@eslint/js';
import prettierConfig from '@vue/eslint-config-prettier';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import pluginVue from 'eslint-plugin-vue';

export default defineConfigWithVueTs(
    {
        name: 'app/files-to-lint',
        files: ['**/*.{js,ts,vue}'],
    },
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    prettierConfig,
    vueTsConfigs.recommended,
    {
        ignores: [
            'vendor',
            'node_modules',
            'public',
            'tests-coverage',
            'bootstrap/ssr',
            'storage',
            'bootstrap/cache',
            '**/*.min.js',
            '**/*.bundle.js',
            'resources/types/Api.gen.ts', // Auto-generated from Swagger
        ],
    },
    {
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                // Browser globals
                window: 'readonly',
                document: 'readonly',
                navigator: 'readonly',
                console: 'readonly',
                // Laravel globals
                route: 'readonly',
                axios: 'readonly',
                // External libraries
                $: 'readonly', // jQuery
                L: 'readonly', // Leaflet
                notyf: 'readonly', // Notyf notifications
                Awesomplete: 'readonly', // Awesomplete autocomplete
                Status: 'readonly', // Laravel blade injected
                Settings: 'readonly', // Laravel blade injected
                urlDisconnect: 'readonly', // Laravel blade injected
                setTilingLayer: 'readonly', // Maps helper
                // TypeScript/Vue types
                PropType: 'readonly', // Vue PropType
                SelectOption: 'readonly', // Custom type
                TimeDuration: 'readonly', // Custom type
            },
        },
        rules: {
            // Code quality
            'no-console': ['warn', { allow: ['warn', 'error'] }],
            'no-debugger': 'warn',
            'no-unused-vars': 'off', // Handled by TypeScript
            '@typescript-eslint/no-unused-vars': [
                'warn',
                {
                    argsIgnorePattern: '^_',
                    varsIgnorePattern: '^_',
                },
            ],
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/ban-ts-comment': 'warn',
            '@typescript-eslint/no-this-alias': 'warn',
            'no-constant-binary-expression': 'warn',
            'no-unsafe-optional-chaining': 'warn',
            'no-undef': 'error',
            'no-empty': 'warn',
            'no-prototype-builtins': 'warn',

            // Vue specific
            'vue/multi-word-component-names': 'off',
            'vue/require-default-prop': 'off',
            'vue/no-v-html': 'warn',
        },
    },
);
