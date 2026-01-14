import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import tseslint from 'typescript-eslint';

export default [
    // Base recommended configs
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...pluginVue.configs['flat/recommended'],

    // Global ignores
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/build/**',
            'public/hot',
            'public/**/*.min.js',
            'storage/**',
            'bootstrap/cache/**',
            '.phpstorm.meta.php',
            '_ide_helper.php',
            '_ide_helper_models.php',
            '**/*.min.js',
            '**/*.bundle.js',
            'resources/types/Api.gen.ts', // Auto-generated from Swagger
        ],
    },

    // Configuration for all files
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

            // Code style
            indent: [
                'error',
                4,
                {
                    SwitchCase: 1,
                    ignoredNodes: ['TemplateLiteral'],
                },
            ],
            quotes: ['error', 'single', { avoidEscape: true }],
            semi: ['error', 'always'],
            'comma-dangle': ['error', 'always-multiline'],
            'eol-last': ['error', 'always'],
            'no-multiple-empty-lines': ['error', { max: 1, maxEOF: 0 }],
            'no-trailing-spaces': 'error',
            'object-curly-spacing': ['error', 'always'],
            'array-bracket-spacing': ['error', 'never'],
            'arrow-spacing': 'error',
            'space-before-blocks': 'error',
            'keyword-spacing': 'error',
        },
    },

    // Vue-specific configuration
    {
        files: ['**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: tseslint.parser,
                ecmaVersion: 'latest',
                sourceType: 'module',
            },
        },
        rules: {
            'vue/html-indent': ['error', 4],
            'vue/max-attributes-per-line': [
                'error',
                {
                    singleline: 3,
                    multiline: 1,
                },
            ],
            'vue/first-attribute-linebreak': [
                'error',
                {
                    singleline: 'ignore',
                    multiline: 'below',
                },
            ],
            'vue/component-tags-order': [
                'error',
                {
                    order: ['script', 'template', 'style'],
                },
            ],
            // Warn instead of error for gradual migration to TypeScript and Composition API
            'vue/block-lang': 'warn',
            'vue/component-api-style': 'off', // Allow both Options and Composition API for now
            'vue/define-macros-order': [
                'error',
                {
                    order: ['defineProps', 'defineEmits', 'defineSlots'],
                },
            ],
            'vue/one-component-per-file': 'off', // Allow multiple components in app.js for now
            // Vue best practices - warn instead of error for gradual fixes (TODO! temporary to soft introduce eslint)
            'vue/valid-v-bind': 'warn',
            'vue/valid-v-for': 'warn',
            'vue/valid-template-root': 'warn',
            'vue/return-in-computed-property': 'warn',
            'vue/no-unused-components': 'warn',
            'vue/require-valid-default-prop': 'warn',
            'vue/require-v-for-key': 'warn',
            'vue/no-mutating-props': 'warn',
            'vue/no-use-v-if-with-v-for': 'warn',
            'vue/no-useless-template-attributes': 'warn',
            'vue/valid-v-slot': 'warn',
            'vue/no-side-effects-in-computed-properties': 'warn',
            'vue/no-reserved-keys': 'warn',
        },
    },

    // TypeScript files
    {
        files: ['**/*.ts'],
        languageOptions: {
            parser: tseslint.parser,
            parserOptions: {
                project: './tsconfig.json',
            },
        },
    },
];
