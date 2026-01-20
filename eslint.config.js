import js from '@eslint/js';
import prettierConfig from '@vue/eslint-config-prettier';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import pluginVue from 'eslint-plugin-vue';
import globals from 'globals';

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
                ...globals.browser,
            },
        },
        rules: {
            // Code quality
            'no-console': ['error', { allow: ['warn', 'error'] }],
            'no-debugger': 'warn',

            // Vue specific
            'vue/multi-word-component-names': 'off',
            'vue/no-template-shadow': 'error',

            // Remove these rules, once they are fixed
            'vue/block-lang': 'off',
        },
    },
);
